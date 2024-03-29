<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PaginationService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AclController extends Controller
{
    private string $superAdminName;

    public function __construct(private PaginationService $paginationService)
    {
        $this->middleware('role:Super admin');
        $this->superAdminName = config('roles-permissions.super_admin_name');
    }

    public function getAllRoles(Request $request)
    {
        $pagination = $this->paginationService->getPaginationData($request);

        $isSuperAdmin = $request->user()->hasRole($this->superAdminName);

        $query = Role::query();

        // Helai logic hi ACL admin awm chuan a ngai dawn
        if (!$isSuperAdmin) {
            $query->where('name', '!=', $this->superAdminName);
        }

        $query->with('permissions', function ($query) {
            $query->select(['id', 'name']);
        })->select(['id', 'name']);

        $query->orderBy($pagination['orderBy'], $pagination['order']);
        $roles = $query->paginate($pagination['perPage'], ['*'], 'page', $pagination['page']);

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

    public function updateRole(Request $request, string $roleId)
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

    public function assignRolesToUser(Request $request, string $userId)
    {
        $data = $request->validate([
            'roles' => 'required|array'
        ]);

        $user = User::find($userId);

        $user->syncRoles($data['roles']);

        return response()->json([
            'success' => true,
            'message' => 'Role assigned'
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

    public function getAllPermissions(Request $request)
    {
        $pagination = $this->paginationService->getPaginationData($request);

        $query = Permission::query();

        $query->orderBy($pagination['orderBy'], $pagination['order']);
        $permissions = $query->paginate($pagination['perPage'], ['*'], 'page', $pagination['page']);

        return response()->json([
            'success' => true,
            'permissions' => $permissions
        ], 200);
    }
}
