<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'show_categories', 
            'add_category', 
            'edit_category', 
            'delete_category', 
            'show_users',
            'add_user',
            'edit_user',
            'delete_user',
            'show_tours',
            'add_tour',
            'edit_tour',
            'delete_tour'
        ];

        foreach ($permissions as $permissionName) {
            Permission::create(['name' => $permissionName]);
        }

    }
}
