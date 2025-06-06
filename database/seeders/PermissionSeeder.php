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
            'category', 
            'category-create', 
            'category-edit', 
            'category-delete', 
            'extra', 
            'extra-create', 
            'extra-edit', 
            'extra-delete',
            'pickups', 
            'pickups-create', 
            'pickups-edit', 
            'pickups-delete',
            'tour',
            'tour-create',
            'tour-edit',
            'tour-delete',
            'user',
            'user-create',
            'user-edit',
            'user-delete',
        ];

        foreach ($permissions as $permissionName) {
            Permission::create(['name' => $permissionName]);
        }

    }
}
