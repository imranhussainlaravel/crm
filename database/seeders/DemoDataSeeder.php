<?php

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Local development convenience data only. Never run this in production —
     * it creates accounts with fixed, publicly-known-from-this-repo passwords.
     * Run explicitly with `php artisan db:seed --class=DemoDataSeeder`, it is
     * not part of the default `php artisan db:seed` run.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@crm.local'],
            [
                'name' => 'Admin',
                'password' => Hash::make('AdminCrm2026!'),
                'status' => UserStatus::Active,
            ]
        );

        if (! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        $production = User::firstOrCreate(
            ['email' => 'pat.production@crm.local'],
            [
                'name' => 'Pat Production',
                'password' => Hash::make('AgentPass123!'),
                'status' => UserStatus::Active,
            ]
        );

        if (! $production->hasRole('production')) {
            $production->assignRole('production');
        }
    }
}
