<?php

namespace App\Http\Middleware;

use App\Models\Doctor;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsDoctor
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // نتحقق أن المستخدم هو "طبيب"
        if ($request->user() && $request->user() instanceof Doctor) {
            return $next($request);
        }

        return response()->json(['message' => 'هذه الصفحة خاصة بالأطباء فقط'], 403);
    }
}
