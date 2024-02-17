<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:Super admin');
    }

    public function getAllAudits(Request $request)
    {
        $audits = DB::table('audits')->paginate();

        return response()->json([
            'success' => true,
            'audits' => $audits
        ], 200);
    }
}
