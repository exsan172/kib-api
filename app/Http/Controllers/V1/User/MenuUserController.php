<?php

namespace App\Http\Controllers\V1\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MenuUserController extends Controller
{
    // get menu user
    public function getMenuUser()
    {
        $role = auth()->user()->role;
        if ($role) {
            $role_id = $role->id;
            $menus = $role->menus()->where('show_menu', 1)->with('children', function ($query)  use ($role_id) {
                return $query->whereHas('roles', function ($query) use ($role_id) {
                    return $query->where('role_id', $role_id);
                });
            })->whereHas('roles', function ($query) use ($role_id) {
                return $query->where('role_id', $role_id);
            })->where('parent_id')->orderBy('menu_order', 'ASC')->get();

            return response()->json([
                'message' => 'Menu Data',
                'data' => $menus,
            ]);
        }

        return response()->json([
            'message' => 'Menu Data',
            'data' => [],
        ]);
    }
}
