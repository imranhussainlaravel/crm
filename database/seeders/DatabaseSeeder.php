<?php

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        foreach (['admin', 'agent', 'production'] as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

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

        if (SystemSetting::get('discount_approval_threshold') === null) {
            SystemSetting::set('discount_approval_threshold', 10);
        }
    }
}
