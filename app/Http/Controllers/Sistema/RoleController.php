<?php

namespace App\Http\Controllers\Sistema;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:Administrador');
    }

    /**
     * Display a listing of roles with permission and user counts.
     */
    public function index()
    {
        $roles = Role::withCount('permissions')
            ->withCount('users')
            ->orderBy('name', 'asc')
            ->get();

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Sistema', 'url' => route('sistema.index')],
            ['name' => 'Roles y Permisos', 'url' => ''],
        ];

        return view('sistema.roles.index', compact('roles', 'breadcrumbs'));
    }

    /**
     * Show the form for editing role permissions.
     */
    public function edit(Role $role)
    {
        // Get all permissions grouped by module (parent_id)
        $parentPermissions = Permission::whereNull('parent_id')
            ->orderBy('name', 'asc')
            ->get();

        $modulePermissions = [];
        foreach ($parentPermissions as $parent) {
            $children = Permission::where('parent_id', $parent->id)
                ->orderBy('name', 'asc')
                ->get();

            if ($children->isNotEmpty()) {
                $modulePermissions[] = [
                    'parent' => $parent,
                    'children' => $children,
                ];
            }
        }

        $rolePermissions = $role->permissions->pluck('id')->toArray();

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Sistema', 'url' => route('sistema.index')],
            ['name' => 'Roles y Permisos', 'url' => route('sistema.roles.index')],
            ['name' => 'Editar ' . $role->name, 'url' => ''],
        ];

        return view('sistema.roles.edit', compact('role', 'modulePermissions', 'rolePermissions', 'breadcrumbs'));
    }

    /**
     * Update role permissions.
     */
    public function update(Request $request, Role $role)
    {
        $permissionIds = $request->input('permissions', []);

        $role->syncPermissions($permissionIds);

        return redirect()->route('sistema.roles.edit', $role)
            ->with('success', 'Permisos actualizados con éxito.');
    }
}
