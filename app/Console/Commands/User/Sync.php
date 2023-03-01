<?php

namespace App\Console\Commands\User;

use App\Models\User;
use App\Support\Enum\UserSyncAction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class Sync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:sync {--file=users.json : The file to sync}';

    public const CACHE_KEY = 'file:users:hash';

    public const CACHE_TTL = 60 * 60 * 24;

    private int $createdCount = 0;

    private int $updatedCount = 0;

    private int $deletedCount = 0;

    /**
     * Execute the console command.
     *
     * @throws \JsonException
     */
    public function handle(): int
    {
        if (is_int($users = $this->readFile($this->option('file')))) {
            return $users;
        }

        $this->deleteUsers($users);

        foreach ($users as $email => $password) {
            $user = $this->getUser($email, $password);

            $this->action($user);
        }

        $this->statistic();

        return static::SUCCESS;
    }

    private function getUser(string $email, string $password): User
    {
        return User::withTrashed()
            ->firstOrNew([
                'email' => $email,
            ])
            ->fill([
                'password' => $password,
                'name' => 'Manager',
            ]);
    }

    private function action(User $user)
    {
        switch (UserSyncAction::get($user)) {
            case UserSyncAction::MUST_CREATE:
                $user->save();
                $this->createdCount++;
                break;
            case UserSyncAction::MUST_RESTORE:
                $user->restore();
                $this->createdCount++;
                break;
            case UserSyncAction::MUST_UPDATE:
                $user->save();
                $this->updatedCount++;
                break;
            default:
                Log::emergency('User not processed', compact('user'));
                break;
        }
    }

    private function deleteUsers(array $users): void
    {
        $this->deletedCount = User::whereNotIn('email', array_keys($users))
            ->delete();
    }

    private function statistic(): void
    {
        $messages = [
            ':count пользователь создан|:count пользователя создано|:count пользователей создано' => $this->createdCount,
            ':count пользователь обновлён|:count пользователя обновлено|:count пользователей обновлено' => $this->updatedCount,
            ':count пользователь удален|:count пользователя удалено|:count пользователей удалено' => $this->deletedCount,
        ];

        foreach ($messages as $message => $count) {
            $message = trans_choice(
                $message,
                $this->createdCount,
                ['count' => str_pad($count, 3, pad_type: STR_PAD_LEFT)]
            );

            foreach ([$this, Log::class] as $logger) {
                call_user_func([$logger, 'info'], $message);
            }
        }
    }

    /**
     * @throws \JsonException
     */
    private function readFile(string $file): int|array
    {
        $file = base_path($file);

        if (! file_exists($file)) {
            $this->error('File not found.');

            return static::FAILURE;
        }

        if (! $hash = md5_file($file)) {
            $this->error('Can\'t hash file.');

            return static::FAILURE;
        }

        if (Cache::missing(static::CACHE_KEY) || Cache::get(static::CACHE_KEY) !== $hash) {
            $result = json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
        }

        Cache::put(static::CACHE_KEY, $hash, static::CACHE_TTL);

        return $result ?? static::SUCCESS;
    }
}
