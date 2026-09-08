<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $admin = User::where('email', $request->email)
            ->where('role', 'admin')
            ->first();

         if (! $admin || ! Hash::check($request->password, $admin->password)) {
            return response()->json(['message' => 'بيانات الدخول غير صحيحة'], 401);
        }

        return response()->json([
            'access_token' => $admin->createToken('admin_token')->plainTextToken,
            'token_type' => 'Bearer',
            'user' => $admin,
        ]);
    }
}
