<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            $map = [
                'garant'  => [
                    'profile.view_other',
                    'company.search',
                    'practice.view_detail_other',
                    'practice.update_fields_any',
                    'practice.change_status_to_approved',
                    'practice.can_see_all_documents',
                    'practice.generate_export',
                    'dashboard.view_overview'
                ],
                'študent' => [
                    'company.search',
                    'practice.create',
                    'practice.view_detail_own',
                    'practice.generate_agreement_pdf',
                    'practice.upload_agreement',
                    'practice.upload_statement',
                    'practice.can_see_own_documents',
                    'dashboard.view_own_practices'
                ],
                'firma' => [
                    'practice.view_detail_own',
                    'practice.upload_statement',
                    'practice.approve_statement',
                    'practice.change_status_to_accepted',
                    'practice.can_see_own_documents',
                    'dashboard.view_own_practices'
                ],
            ];

            $roles = Role::pluck('id', 'name');
            $perms = Permission::pluck('id', 'name');

            foreach ($map as $roleName => $permNames) {
                if (! isset($roles[$roleName])) {
                    Log::warning("Role {$roleName} neexistuje");
                    continue;
                }

                $roleId = $roles[$roleName];

                $permIds = [];
                foreach ($permNames as $pName) {
                    if (isset($perms[$pName])) {
                        $permIds[] = $perms[$pName];
                    } else {
                        Log::warning("Permission '{$pName}' neexistuje, pre rolu '{$roleName}' sa nepriradí.");
                    }
                }

                $role = Role::find($roleId);
                $role->permissions()->sync($permIds);
            }
        });
    }
}
