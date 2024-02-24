<?php

namespace App\Http\Controllers;

use App\Services\PaginationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OwenIt\Auditing\Models\Audit;

class AuditController extends Controller
{
    public function __construct(private PaginationService $paginationService)
    {
        $this->middleware('role:Super admin');
    }

    public function getAllAudits(Request $request)
    {
        $pagination = $this->paginationService->getPaginationData($request);

        $query = Audit::query();

        $query->with(['user', 'auditable']);

        $query->orderBy($pagination['orderBy'], $pagination['order']);
        $audits = $query->paginate($pagination['perPage'], ['*'], 'page', $pagination['page']);

        return response()->json([
            'success' => true,
            'audits' => $audits
        ], 200);
    }
}
