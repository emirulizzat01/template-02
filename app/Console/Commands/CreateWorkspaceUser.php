<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateWorkspaceUser extends Command
{
    protected $signature = 'template:user {--environment=* : Existing monitoring environment slugs to grant}';

    protected $description = 'Create a workspace user with a hidden password prompt and explicit environment access';

    public function handle(): int
    {
        if (! $this->input->isInteractive()) {
            $this->components->error('Run this command interactively to enter the password securely.');

            return self::FAILURE;
        }

        $email = $this->ask('Email address');
        $name = $this->ask('Name');
        $password = $this->secret('Password (at least 12 characters)');
        $confirmation = $this->secret('Confirm password');
        $slugs = array_values(array_unique($this->option('environment')));
        $validator = Validator::make([
            'email' => $email,
            'name' => $name,
            'password' => $password,
            'password_confirmation' => $confirmation,
            'environments' => $slugs,
        ], [
            'email' => ['required', 'email', 'max:254', 'unique:users,email'],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Password::min(12)],
            'environments' => ['array'],
            'environments.*' => ['string', 'exists:environments,slug'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->components->error($message);
            }

            return self::FAILURE;
        }

        DB::transaction(function () use ($email, $name, $password, $slugs): void {
            $user = User::create(['email' => $email, 'name' => $name, 'password' => $password]);

            foreach (DB::table('environments')->whereIn('slug', $slugs)->pluck('id') as $environmentId) {
                DB::table('environment_user')->insert([
                    'environment_id' => $environmentId,
                    'user_id' => $user->id,
                ]);
            }
        });

        $this->components->info('User created. Monitoring access is limited to the selected environments.');

        return self::SUCCESS;
    }
}
