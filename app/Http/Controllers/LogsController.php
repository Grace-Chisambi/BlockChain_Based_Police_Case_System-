<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Log;
use App\Models\BlockchainLog;
use Carbon\Carbon;

class LogsController extends Controller
{
    public function index(Request $request)
    {
        $systemQuery = Log::query();
        $blockchainQuery = BlockchainLog::query();

        // Filter System Logs by date range if provided
        if ($request->filled('system_date_range')) {
            $now = Carbon::now();
            switch ($request->input('system_date_range')) {
                case 'week':
                    $systemQuery->where('created_at', '>=', $now->subWeek());
                    break;
                case 'month':
                    $systemQuery->where('created_at', '>=', $now->subMonth());
                    break;
                case 'year':
                    $systemQuery->where('created_at', '>=', $now->subYear());
                    break;
            }
        }

        // Filter Blockchain Logs by date range if provided
        if ($request->filled('blockchain_date_range')) {
            $now = Carbon::now();
            switch ($request->input('blockchain_date_range')) {
                case 'week':
                    $blockchainQuery->where('created_at', '>=', $now->subWeek());
                    break;
                case 'month':
                    $blockchainQuery->where('created_at', '>=', $now->subMonth());
                    break;
                case 'year':
                    $blockchainQuery->where('created_at', '>=', $now->subYear());
                    break;
            }
        }

        $logs = $systemQuery->with('user')->paginate(10)->appends($request->all());
        $blockchainLogs = $blockchainQuery->paginate(4)->appends($request->all());

        return view('pages.logs', compact('logs', 'blockchainLogs'));
    }
}
