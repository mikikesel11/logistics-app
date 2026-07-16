<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Roles for a brokerage. Kept global (not per-org) for the single-tenant
     * phase; spatie "teams" can scope them per organization later.
     */
    private const ROLES = ['admin', 'broker', 'dispatcher', 'viewer'];

    public function run(): void
    {
        foreach (self::ROLES as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        $organization = Organization::firstOrCreate(
            ['slug' => 'demo-brokerage'],
            ['name' => 'Demo Brokerage'],
        );

        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'organization_id' => $organization->id,
            ],
        );

        $admin->syncRoles(['admin']);
    }
}
