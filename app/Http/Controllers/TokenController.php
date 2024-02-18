<?php

namespace App\Http\Controllers;

use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

class TokenController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:Super admin');
    }

    public function getAllTokens()
    {
        $tokens = PersonalAccessToken::with('tokenable')->paginate();

        return response()->json([
            'success' => true,
            'tokens' => $tokens
        ], 200);
    }

    public function revoke(string $id)
    {
        $token = PersonalAccessToken::findOrFail($id);

        $token->delete();

        return response()->json([
            'success' => true,
            'token' => $token
        ], 200);
    }
}
