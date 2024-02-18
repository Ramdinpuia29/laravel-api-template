<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);

        $existingUser = User::withTrashed()->where('email', $data['email'])->first();

        if ($existingUser && $existingUser->trashed()) {
            $existingUser->restore();
        }

        if (!Auth::attempt($data)) {
            throw new HttpException(403, 'Unauthorized.');
        }

        $user = User::where('email', $request->email)->firstOrFail();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => $user
        ], 200);
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
            'user' => $user
        ], 200);
    }
}
