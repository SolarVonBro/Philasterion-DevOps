<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FailedJobController extends Controller
{
    public function index(Request $request)
    {
        $perPage = 15;
        $page    = (int) $request->query('page', 1);
        $total   = DB::table('failed_jobs')->count();
        $items   = DB::table('failed_jobs')
            ->orderByDesc('failed_at')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        return response()->json([
            'data' => $items,
            'meta' => [
                'current_page' => $page,
                'per_page'     => $perPage,
                'total'        => $total,
                'last_page'    => (int) ceil($total / $perPage),
            ],
        ]);
    }

    public function destroy(int $id)
    {
        DB::table('failed_jobs')->where('id', $id)->delete();

        return response()->json(['message' => 'Failed job deleted successfully.']);
    }
}
