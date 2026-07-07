<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        $data = Role::where('name', '<>', 'Super Admin')->orderBy('id','DESC')->get();
        return view('admin.role.index', compact('data'));
    }

    public function create()
    {
        $permissions = Permission::all();
        return view('admin.role.create',compact( 'permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles|max:255',
        ]);
        $role = Role::create(['name' => $request->name]);

        $permissions = Permission::whereIn('id', $request->permissions)->get(['name'])->toArray();
        
        $role->syncPermissions($permissions);

        return redirect()->route('admin.role.index')->with('success','Role created successfully.');
    }

    public function edit($id)
    {
        $data = Role::where('id',decrypt($id))->first();

        if($data->name=='Super Admin'){
            abort(403, 'SUPER ADMIN ROLE CAN NOT BE EDITED');
        }

        $permissions = Permission::all();
        $rolePermissions = DB::table("role_has_permissions")->where("role_id",$data->id)
            ->pluck('permission_id')
            ->all();
        return view('admin.role.edit',compact('data', 'permissions', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|max:255',
        ]);

        $input = $request->only('name');

        $role->update($input);

        $permissions = Permission::whereIn('id', $request->permissions)->get(['name'])->toArray();

        $role->syncPermissions($permissions);

        return redirect()->back()->with('success','Role updated successfully.');
    }

    public function destroy($id)
    {
        Role::where('id',decrypt($id))->delete();
        return redirect()->route('admin.role.index')->with('error','Role deleted successfully.');
    }
}
