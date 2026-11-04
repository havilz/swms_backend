<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $databaseStatus = 'healthy';

        try {
            DB::select('SELECT 1');
        } catch (QueryException $exception) {
            Log::error('Admin health dashboard database check failed.', [
                'exception' => $exception->getMessage(),
            ]);
            $databaseStatus = 'unhealthy';
        }

        return view('admin.dashboard', [
            'databaseStatus' => $databaseStatus,
            'apiHealthUrl' => url('/up'),
        ]);
    }
}
