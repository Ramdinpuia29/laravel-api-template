<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(User::class, 'user');
    }

    public function index()
    {
        $users = User::all();

        return response()->json([
            'success' => true,
            'users' => $users
        ], 200);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email:rfc,dns',
            'password' => [
                'sometimes',
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
            'roles' => 'sometimes|required|array',
        ]);

        $user = [];

        $existingUser = User::withTrashed()->where('email', $data['email'])->first();

        if ($existingUser && $existingUser->trashed()) {
            $existingUser->restore();

            $existingUser->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password'])
            ]);

            if (isset($data['roles'])) {
                $existingUser->syncRoles($data['roles']);
            }

            $user = $existingUser;
        } else {
            $newUser = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password'])
            ]);

            $newUser->syncRoles($data['roles']);

            $user = $newUser;
        }

        return response()->json([
            'success' => true,
            'user' => $user
        ], 200);
    }

    public function show(User $user)
    {
        $user = User::findOrFail($user->id);

        return response()->json([
            'success' => true,
            'user' => $user
        ], 200);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string',
            'email' => 'sometimes|required|email:rfc,dns',
            'password' => [
                'sometimes',
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
            'roles' => 'sometimes|required|array',
        ]);

        $user = User::findOrFail($user->id);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        $user->syncRoles($data['roles']);

        return response()->json([
            'success' => true,
            'user' => $user
        ], 200);
    }

    public function destroy(User $user)
    {
        $user = User::findOrFail($user->id);

        $user->delete();

        return response()->json([
            'success' => true,
            'user' => $user
        ]);
    }
}
