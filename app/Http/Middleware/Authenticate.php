<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Closure;

class Authenticate extends Middleware
{
    public function handle($request, Closure $next, ...$guards)
    {
        $authorizationHeader = $request->header('Authorization');
        if (empty($authorizationHeader)) {
            return response()->json(['message' => 'Authorization header tidak ditemukan'], 401);
        }

        if (strpos($authorizationHeader, 'Bearer ') !== 0) {
            return response()->json(['message' => 'Token tidak valid'], 401);
        }

        return $next($request);
    }

}
