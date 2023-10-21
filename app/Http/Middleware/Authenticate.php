<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Closure;

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? response()->json(
            ['message' => 'Unauthorized'],
            401
        ) : abort(401, 'Unauthorized');
    }
}
