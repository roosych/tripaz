<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Seed application roles and their associated permissions.
     *
     * Roles:
     *   admin      — full access to everything
     *   moderator  — can moderate listings (approve/reject/suspend)
     *   host       — can create and manage their own listings
     *   user       — read-only authenticated access, can book
     */
    public function run(): void
    {
        // Listing permissions
        $permissions = [
            'listings.view',
            'listings.create',
            'listings.update',
            'listings.delete',
            'listings.moderate',
            'listings.restore',
            'listings.force-delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Admin — all permissions
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions($permissions);

        // Moderator — can view and moderate
        $moderator = Role::firstOrCreate(['name' => 'moderator', 'guard_name' => 'web']);
        $moderator->syncPermissions([
            'listings.view',
            'listings.moderate',
        ]);

        // Host — CRUD on own listings (ownership checked by policy)
        $host = Role::firstOrCreate(['name' => 'host', 'guard_name' => 'web']);
        $host->syncPermissions([
            'listings.view',
            'listings.create',
            'listings.update',
            'listings.delete',
        ]);

        // Regular user — view only (own bookings handled by future module)
        $user = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
        $user->syncPermissions([
            'listings.view',
        ]);
    }
}
