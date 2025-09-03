<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan semua permissions dibuat terlebih dahulu
        // Ini akan memastikan semua permission yang didefinisikan di resources tersedia
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $roles = ['super_admin', 'manager', 'employee', 'panel_user'];

        foreach ($roles as $role) {
            $createdRole = Role::firstOrCreate([
                'name' => $role,
                'guard_name' => 'web'
            ]);

            // Khusus untuk super_admin, berikan semua permission
            if ($role === 'super_admin') {
                // Ambil semua permission yang ada
                $permissions = Permission::all();
                $createdRole->syncPermissions($permissions);
            }
        }
    }
}
