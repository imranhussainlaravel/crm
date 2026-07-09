<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database. Safe to run in every environment,
     * including production: no user accounts or credentials of any kind
     * are created here. Create the first admin with `php artisan app:create-admin`
     * instead — see CHANGELOG.md's Deployment section.
     */
    public function run(): void
    {
        foreach (['admin', 'agent', 'production'] as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        if (SystemSetting::get('discount_approval_threshold') === null) {
            SystemSetting::set('discount_approval_threshold', 10);
        }
    }
}
