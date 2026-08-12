<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'tasks.view' => 'View tasks',
            'tasks.create' => 'Create tasks',
            'tasks.edit' => 'Edit tasks',
            'tasks.delete' => 'Delete tasks',
            'reports.view' => 'View admin reports',
            'users.manage' => 'Manage users & roles',
        ];

        foreach ($permissions as $name => $label) {
            Permission::firstOrCreate(['name' => $name], ['label' => $label]);
        }

        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['label' => 'Administrator']);
        $userRole = Role::firstOrCreate(['name' => 'user'], ['label' => 'User']);

        // Admins get everything.
        $adminRole->permissions()->sync(Permission::all()->pluck('id'));

        // Regular users can manage their own tasks only.
        $userRole->permissions()->sync(
            Permission::whereIn('name', ['tasks.view', 'tasks.create', 'tasks.edit', 'tasks.delete'])->pluck('id')
        );

        // A default admin account — change this password immediately after seeding.
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role_id' => $adminRole->id,
                'email_verified_at' => now(),
            ]
        );
    }
}
