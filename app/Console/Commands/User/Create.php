<?php

namespace App\Console\Commands\User;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Throwable;

class Create extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create';

    /**
     * Execute the console command.
     *
     * @throws Throwable
     */
    public function handle(): void
    {
        $name = $this->asking('Name:', ['required', 'string']);
        $email = $this->asking('Email:', ['required', 'email', 'unique:users,email']);
        $password = $this->asking('Password:', ['required', Password::defaults()]);
        $this->asking('Confirm password:', ['required', 'string', 'password_confirmation']);

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);

        $this->info('User created successfully.');
    }

    protected function asking(string $question, array $rules): string
    {
        do {
            $data = $this->ask($question);
            $validator = Validator::make(['data' => $data], ['data' => $rules]);

            if ($validator->fails()) {
                $this->error($validator->errors()->first());
            }
        } while ($validator->fails());

        return $data;
    }
}
