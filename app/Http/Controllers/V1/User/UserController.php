<?php

namespace App\Http\Controllers\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Ramsey\Uuid\Uuid;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // load all menu
        $users = User::all();
        return response()->json([
            'message' => 'User Data',
            'data' => UserResource::collection($users),
        ]);
    }

    public function list(Request $request)
    {
        // list all
        $search = $request->search;
        $status = $request->status;
        $user =  User::query();
        if ($search) {
            $user->where(function ($query) use ($search) {
                $query->where('name', 'like', "%$search%");
                $query->orWhere('email', 'like', "%$search%");
            });
        }

        if ($status) {
            $user->where('status', $status);
        }

        $users = $user->paginate($request->perpage ?? 10);
        return response()->json([
            'status' => 'success',
            'data' => $users,
            'message' => 'List user'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $user = User::create([
                'uuid' => Uuid::uuid4(),
                'name' => $request->name,
                'email' => $request->email,
                'telepon' => $request->telepon,
                'status' => $request->status ?? 1,
                'password' => $request->password ? Hash::make($request->password) :  Hash::make('admin123')
            ]);
            // attach role
            $user->roles()->attach($request->role_id);

            DB::commit();

            return response()->json([
                'message' => 'Create User Success',
                'data' => new UserResource($user),
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Create User Failed',
                'error' => $th->getMessage()
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::whereUuid($id)->first();
        return response()->json([
            'message' => 'User Detail',
            'data' => new UserResource($user),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            DB::beginTransaction();
            $user = User::whereUuid($id)->first();

            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'telepon' => $request->telepon,
                'status' => $request->status,
            ];

            if ($request->password) {
                $data['password'] =  Hash::make($request->password);
            }

            $user->update($data);

            // update role
            $user->roles()->sync($request->role_id);

            DB::commit();

            return response()->json([
                'message' => 'Update User Success',
                'data' => new UserResource($user),
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Update User Failed',
                'error' => $th->getMessage()
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::whereUuid($id)->first();
        $user->delete();

        return response()->json([
            'message' => 'Delete User Success',
            'data' => new UserResource($user),
        ]);
    }
}
