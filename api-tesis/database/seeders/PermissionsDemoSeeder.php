<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionsDemoSeeder extends Seeder
{
    /**
     * Create the initial roles and permissions.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        Permission::create(['guard_name' => 'api','name' => 'register_rol']);
        Permission::create(['guard_name' => 'api','name' => 'list_rol']);
        Permission::create(['guard_name' => 'api','name' => 'edit_rol']);
        Permission::create(['guard_name' => 'api','name' => 'delete_rol']);

        Permission::create(['guard_name' => 'api','name' => 'register_institution']);
        Permission::create(['guard_name' => 'api','name' => 'list_institution']);
        Permission::create(['guard_name' => 'api','name' => 'edit_institution']);
        Permission::create(['guard_name' => 'api','name' => 'delete_institution']);
        Permission::create(['guard_name' => 'api','name' => 'profile_institution']);

        Permission::create(['guard_name' => 'api','name' => 'register_postulant']);
        Permission::create(['guard_name' => 'api','name' => 'list_postulant']);
        Permission::create(['guard_name' => 'api','name' => 'edit_postulant']);
        Permission::create(['guard_name' => 'api','name' => 'delete_postulant']);
        Permission::create(['guard_name' => 'api','name' => 'profile_postulant']);

        Permission::create(['guard_name' => 'api','name' => 'register_comission']);
        Permission::create(['guard_name' => 'api','name' => 'list_comission']);
        Permission::create(['guard_name' => 'api','name' => 'edit_comission']);
        Permission::create(['guard_name' => 'api','name' => 'delete_comission']);

        Permission::create(['guard_name' => 'api','name' => 'register_documents']);
        Permission::create(['guard_name' => 'api','name' => 'list_documents']);
        Permission::create(['guard_name' => 'api','name' => 'edit_documents']);
        Permission::create(['guard_name' => 'api','name' => 'delete_documents']);
        
        Permission::create(['guard_name' => 'api','name' => 'register_convocatories']);
        Permission::create(['guard_name' => 'api','name' => 'list_convocatories']);
        Permission::create(['guard_name' => 'api','name' => 'edit_convocatories']);
        Permission::create(['guard_name' => 'api','name' => 'delete_convocatories']);
        
        Permission::create(['guard_name' => 'api','name' => 'calendar']);
        
        Permission::create(['guard_name' => 'api','name' => 'register_evaluation']);
        Permission::create(['guard_name' => 'api','name' => 'list_evaluation']);
        Permission::create(['guard_name' => 'api','name' => 'edit_evaluation']);
        Permission::create(['guard_name' => 'api','name' => 'delete_evaluation']);
        Permission::create(['guard_name' => 'api', 'name' => 'assign_evaluation_to_postulant']);

       
        Permission::create(['guard_name' => 'api','name' => 'show_report_grafics']);
        // create roles and assign existing permissions

        $role3 = Role::create(['guard_name' => 'api','name' => 'Super-Admin']);
        // gets all permissions via Gate::before rule; see AuthServiceProvider

        $user = \App\Models\User::factory()->create([
            'name' => 'Raypy',
            'email' => 'raypy@gmail.com',
            'password' => bcrypt('123456')
        ]);
        $user->assignRole($role3);
    }
}
