<?php

namespace App\Http\Controllers\V1\Sites;

use App\Http\Controllers\Controller;
use App\Http\Resources\MenuResource;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // load all menu
        $menus = Menu::with('roles')->get();
        return response()->json([
            'message' => 'Menu Data',
            'data' => MenuResource::collection($menus),
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function list(Request $request)
    {
        // list all
        $search = $request->search;
        $status = $request->status;
        $menu =  Menu::query()->with(['roles', 'children', 'children.roles']);
        if ($search) {
            $menu->where(function ($query) use ($search) {
                $query->where('menu_label', 'like', "%$search%");
                $query->orWhere('menu_route', 'like', "%$search%");
            });
        }

        if ($status) {
            $menu->where('status', $status);
        }

        $menus = $menu->whereNull('parent_id')->paginate($request->perpage ?? 10);
        return response()->json([
            'status' => 'success',
            'data' => $menus,
            'message' => 'List menu'
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
        // store menu
        try {
            DB::beginTransaction();

            $lastMenu = Menu::orderBy('menu_order', 'desc')->first();
            $menu = Menu::create([
                'uuid' => Uuid::uuid4(),                'menu_label' => $request->menu_label,
                'menu_icon' => $request->menu_icon,
                'menu_route' => $request->menu_route,
                'menu_order' => $lastMenu ? $lastMenu->menu_order + 0 : 1,
                'show_menu' => $request->show_menu,
                'parent_id' => $request->parent_id,
            ]);

            // attach role
            $menu->roles()->attach($request->role_id);

            DB::commit();

            return response()->json([
                'message' => 'Create Menu Success',
                'data' => new MenuResource($menu),
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Create Menu Failed',
                'error' => $th->getMessage()
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $menu = Menu::with('roles')->find($id);
        return response()->json([
            'message' => 'Menu Data',
            'data' => new MenuResource($menu),
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
        // update menu
        try {
            DB::beginTransaction();

            $menu = Menu::where('uuid', $id)->first();
            $menu->update([
                'menu_label' => $request->menu_label,
                'menu_icon' => $request->menu_icon,
                'menu_route' => $request->menu_route,
                'menu_order' => $request->menu_order,
                'show_menu' => $request->show_menu,
                'parent_id' => $request->parent_id,
            ]);

            // attach role
            $menu->roles()->sync($request->role_id);

            DB::commit();

            return response()->json([
                'message' => 'Update Menu Success',
                'data' => new MenuResource($menu),
                'request' => $request->all()
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Update Menu Failed',
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // delete menu
        try {
            DB::beginTransaction();

            $menu = Menu::where('uuid', $id)->first();
            $menu->roles()->detach();
            $menu->delete();

            DB::commit();

            return response()->json([
                'message' => 'Delete Menu Success',
                'data' => new MenuResource($menu),
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Delete Menu Failed',
            ], 400);
        }
    }

    // updateMenuRole
    public function updateMenuRole(Request $request, $id)
    {
        // update menu role
        try {
            DB::beginTransaction();

            $menu = Menu::where('uuid', $id)->first();
            // check if role already attached
            $role = $menu->roles()->where('role_id', $request->id)->first();

            if ($role) {
                $menu->roles()->detach($request->id);
                DB::commit();
                return response()->json([
                    'message' => 'Update Menu Role Success',
                    'data' => new MenuResource($menu),
                ]);
            }

            $menu->roles()->attach($request->id);
            DB::commit();
            return response()->json([
                'message' => 'Update Menu Role Success',
                'data' => new MenuResource($menu),
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Update Menu Role Failed',
            ], 400);
        }
    }
}
