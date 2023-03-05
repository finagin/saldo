<?php

namespace App\Console\Commands\Saldo;

use App\Jobs\Saldo\CompareJob;
use App\Models\Saldo;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;

class Recalculate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'saldo:recalculate
                            {saldo?* : The ID of the saldo models.}
                            {--all : Recalculate all saldos.}}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $this->infoChoice(
                $this
                    ->getModels()
                    ->each([CompareJob::class, 'dispatch'])
            );

            return static::SUCCESS;
        } catch (InvalidArgumentException $exception) {
            $this->error($exception->getMessage());
        }

        return static::FAILURE;
    }

    private function infoChoice(Collection $models)
    {
        $string = trans_choice(
            implode('|', [
                ':count задача перезапущена',
                ':count задачи перезапущены',
                ':count задач перезапущено',
            ]),
            $models,
        );

        $this->info($string);
    }

    private function getModels(): Collection
    {
        $query = Saldo::query();
        $ids = collect($this->argument('saldo'));

        return (match (true) {
            $this->option('all') => $query,
            $ids->isNotEmpty() => $query->whereIn('id', $ids),
            $this->confirm('Do you wish recalculate all jobs?') => $query,
            default => throw new InvalidArgumentException('Command aborted'),
        })->get('id');
    }
}
