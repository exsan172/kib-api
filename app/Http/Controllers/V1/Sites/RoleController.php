<?php

namespace App\Http\Controllers\V1\Sites;

use App\Http\Controllers\Controller;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $roles = Role::all();
        return response()->json([
            'message' => 'Role Data',
            'data' => RoleResource::collection($roles),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // store role
        $role = Role::create([
            'uuid' => Uuid::uuid4(),
            'role_name' => $request->role_name,
            'role_type' => $request->role_type,
        ]);

        return response()->json([
            'message' => 'Create Role Success',
            'data' => new RoleResource($role),
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $role = Role::whereUuid($id)->first();
        return response()->json([
            'message' => 'Role Data',
            'data' => new RoleResource($role),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $role = Role::whereUuid($id)->first();
        $role->update([
            'role_name' => $request->role_name,
            'role_type' => $request->role_type,
        ]);

        return response()->json([
            'message' => 'Update Role Success',
            'data' => new RoleResource($role),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $role = Role::whereUuid($id)->first();
        $role->delete();

        return response()->json([
            'message' => 'Delete Role Success',
        ]);
    }
}
