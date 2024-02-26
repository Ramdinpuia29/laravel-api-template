<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);

        $user = User::withTrashed()->where('email', $data['email'])->first();

        if ($user && $user->trashed()) {
            $user->restore();
        }

        if ($user && Hash::check($data['password'], $user->password)) {
            $token = $user->createToken($data['email'])->plainTextToken;

            return response()->json([
                'success' => true,
                'token' => $token,
                'user' => $user
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials'
        ], 401);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ], 200);
    }

    public function user(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'user' => [
                "id" => $user->id,
                "name" => $user->name,
                "email" => $user->email,
                'roles' => $user->getRoleNames()->toArray(),
                'permissions' => $user->getPermissionsViaRoles()->pluck('name')->toArray(),
            ],
        ], 200);
    }
}
