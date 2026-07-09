<?php

namespace App\Console\Commands;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class CreateAdminCommand extends Command
{
    protected $signature = 'app:create-admin
        {--email= : Admin email address (prompted if omitted)}
        {--name= : Admin display name (prompted if omitted)}
        {--password= : Set a specific password instead of generating one (not recommended — nothing this option is passed is stored in shell history-safe form)}';

    protected $description = 'Create (or promote) an admin user directly in the database, without seeders or demo credentials — the safe way to create the first production admin.';

    public function handle(): int
    {
        $email = $this->option('email') ?: $this->ask('Admin email address');
        $name = $this->option('name') ?: $this->ask('Admin display name');

        $validator = validator(
            ['email' => $email, 'name' => $name],
            ['email' => ['required', 'email'], 'name' => ['required', 'string', 'max:255']]
        );

        if ($validator->fails()) {
            $this->error($validator->errors()->first());

            return self::FAILURE;
        }

        $password = $this->option('password') ?: Str::password(24);

        $passwordValidator = validator(
            ['password' => $password],
            ['password' => ['required', Password::min(12)->letters()->numbers()->symbols()]]
        );

        if ($passwordValidator->fails()) {
            $this->error('Password does not meet minimum strength requirements (12+ chars, letters, numbers, symbols).');

            return self::FAILURE;
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'status' => UserStatus::Active,
            ]
        );

        if (! $user->hasRole('admin')) {
            $user->assignRole('admin');
        }

        $this->newLine();
        $this->info('Admin user ready.');
        $this->line("  Email:    {$email}");

        if (! $this->option('password')) {
            $this->warn("  Password: {$password}");
            $this->newLine();
            $this->warn('This password is shown once and is not stored anywhere in plaintext. Save it now (a password manager, not a note file) and change it after first login.');
        }

        return self::SUCCESS;
    }
}
