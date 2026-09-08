<?php

namespace App\Http\Middleware;

use App\Models\Patient;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsPatient
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // نتحقق أن المستخدم هو "مريض"
        if ($request->user() && $request->user() instanceof Patient) {
            return $next($request);
        }

        return response()->json(['message' => 'هذه الصفحة خاصة بالمرضى فقط'], 403);
    }
}
