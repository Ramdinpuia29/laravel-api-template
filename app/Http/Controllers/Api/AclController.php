<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AclController extends Controller
{
    private string $superAdminName;

    public function __construct()
    {
        $this->superAdminName = config('roles-permissions.super_admin_name');
    }

    public function getAllRoles()
    {
        $isSuperAdmin = request()->user()->hasRole($this->superAdminName);

        $query = Role::query();

        $roles = [];

        // Helai logic hi ACL admin awm chuan a ngai dawn
        if (!$isSuperAdmin) {
            $query->where('name', '!=', $this->superAdminName);
        }

        $roles = $query->select(['id', 'name'])->get();

        return response()->json([
            'success' => true,
            'roles' => $roles
        ], 200);
    }

    public function createRole(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'permissions' => 'array'
        ]);

        $role = Role::create([
            'name' => $data['name'],
            'guard_name' => 'web'
        ]);

        $role->syncPermissions($data['permissions']);

        return response()->json([
            'success' => true,
            'role' => $role
        ], 200);;
    }

    public function updateRole(Request $request, int $roleId)
    {
        $data = $request->validate([
            'name' => 'string',
            'permissions' => 'array'
        ]);

        $role = Role::find($roleId);

        $role->update([
            'name' => $data['name']
        ]);

        $role->syncPermissions($data['permissions']);

        return response()->json([
            'success' => true,
            'role' => $role
        ], 200);
    }

    public function deleteRole(int $roleId)
    {
        Role::find($roleId)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role deleted'
        ], 200);
    }

    public function getAllPermissions()
    {
        $permissions = Permission::select(['id', 'name'])->get();

        return response()->json([
            'success' => true,
            'permissions' => $permissions
        ], 200);
    }
}
