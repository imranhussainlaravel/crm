<?php

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Local development convenience data only.
     *
     * Run explicitly with `php artisan db:seed --class=DemoDataSeeder`.
     * It is NOT part of the default `php artisan db:seed` run.
     * Passwords are randomly generated and printed to the console once —
     * no credentials are stored in source code.
     */
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command->error('DemoDataSeeder must not run in production. Use `php artisan app:create-admin` instead.');

            return;
        }

        $adminPassword = Str::password(24);
        $admin = User::firstOrCreate(
            ['email' => 'admin@crm.local'],
            [
                'name' => 'Admin',
                'password' => Hash::make($adminPassword),
                'status' => UserStatus::Active,
            ]
        );

        if (! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        $agentPassword = Str::password(24);
        $production = User::firstOrCreate(
            ['email' => 'pat.production@crm.local'],
            [
                'name' => 'Pat Production',
                'password' => Hash::make($agentPassword),
                'status' => UserStatus::Active,
            ]
        );

        if (! $production->hasRole('production')) {
            $production->assignRole('production');
        }

        $this->command->warn('');
        $this->command->warn('  Demo accounts created with the following credentials:');
        $this->command->warn("  Admin:      admin@crm.local / {$adminPassword}");
        $this->command->warn("  Production: pat.production@crm.local / {$agentPassword}");
        $this->command->warn('');
        $this->command->warn('  These passwords are shown ONCE. Save them now.');
        $this->command->warn('');
    }
}
