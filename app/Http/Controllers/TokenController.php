<?php

namespace App\Http\Controllers;

use App\Services\PaginationService;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class TokenController extends Controller
{
    public function __construct(private PaginationService $paginationService)
    {
        $this->middleware('role:Super admin');
    }

    public function getAllTokens(Request $request)
    {
        $pagination = $this->paginationService->getPaginationData($request);

        $query = PersonalAccessToken::query();

        $query->with('tokenable');

        $query->orderBy($pagination['orderBy'], $pagination['order']);
        $tokens = $query->paginate($pagination['perPage'], ['*'], 'page', $pagination['page']);

        return response()->json([
            'success' => true,
            'tokens' => $tokens
        ], 200);
    }

    public function revoke(string $id)
    {
        $token = PersonalAccessToken::with('tokenable')->findOrFail($id);

        $token->delete();

        return response()->json([
            'success' => true,
            'token' => $token
        ], 200);
    }
}
