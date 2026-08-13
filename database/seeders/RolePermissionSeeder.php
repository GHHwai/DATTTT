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
            'tickets.view.own' => 'View own tickets',
            'tickets.view.all' => 'View the IT ticket queue (all tickets)',
            'tickets.create' => 'Submit tickets',
            'tickets.comment' => 'Comment on tickets',
            'tickets.claim' => 'Claim tickets from the queue',
            'tickets.resolve' => 'Update ticket status / resolve tickets',
            'reports.view' => 'View admin reports & analytics',
            'users.manage' => 'Manage users & roles',
        ];

        foreach ($permissions as $name => $label) {
            Permission::firstOrCreate(['name' => $name], ['label' => $label]);
        }

        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['label' => 'Administrator']);
        $itRole = Role::firstOrCreate(['name' => 'it_staff'], ['label' => 'IT Staff']);
        $userRole = Role::firstOrCreate(['name' => 'user'], ['label' => 'Employee']);

        // Admins get everything — including reports/analytics and user management,
        // which IT staff deliberately do NOT get.
        $adminRole->permissions()->sync(Permission::all()->pluck('id'));

        // IT staff: work the shared ticket queue, claim tickets, resolve them,
        // comment — but no reports/analytics, no user management.
        $itRole->permissions()->sync(
            Permission::whereIn('name', [
                'tickets.view.own',
                'tickets.view.all',
                'tickets.create',
                'tickets.comment',
                'tickets.claim',
                'tickets.resolve',
            ])->pluck('id')
        );

        // Regular employees: submit tickets and follow their own.
        $userRole->permissions()->sync(
            Permission::whereIn('name', [
                'tickets.view.own',
                'tickets.create',
                'tickets.comment',
            ])->pluck('id')
        );

        // Demo accounts — change these passwords immediately after seeding.
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role_id' => $adminRole->id,
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'itstaff@example.com'],
            [
                'name' => 'IT Staff',
                'password' => Hash::make('password'),
                'role_id' => $itRole->id,
                'email_verified_at' => now(),
            ]
        );
    }
}
