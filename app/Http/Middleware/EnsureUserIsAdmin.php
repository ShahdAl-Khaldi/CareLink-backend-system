<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // نتحقق إذا كان المستخدم المسجل (من خلال التوكين) هو أدمن
        if ($request->user() instanceof Admin) {
            return $next($request);
        }

        return response()->json(['message' => 'غير مصرح لك بالدخول'], 403);
    }
}
