<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Role::create(['name' => 'admin']);
        // Role::create(['name' => 'staff']);
        // Role::create(['name' => 'tour listing associate']);

        Role::create(['name' => 'Super Admin']);
        $admin = Role::create(['name' => 'Admin']);
        $tourManager = Role::create(['name' => 'Tour Listing Associate']);

        $admin->givePermissionTo([
            'show_users',
            'add_user',
            'edit_user',
            'delete_user',
            'show_tours',
            'add_tour',
            'edit_tour',
            'delete_tour'
        ]);

        $tourManager->givePermissionTo([
            'show_tours',
            'add_tour',
            'edit_tour',
            'delete_tour'
        ]);
    }
}
