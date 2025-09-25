<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BlockchainLog;
    use Illuminate\Validation\ValidationException;

class BlockchainLogController extends Controller
{


public function store(Request $request)
{
    try {
        $validated = $request->validate([
            'case_id' => 'nullable|string',
            'tx_hash' => 'required|string|unique:blockchain_logs,tx_hash',
            'action_type' => 'required|string',
            'payload' => 'nullable|string',
        ]);

        BlockchainLog::create($validated);
        return response()->json(['message' => 'Log stored']);
    } catch (ValidationException $e) {
        return response()->json([
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);
    }
}



}
// This controller handles the storage of blockchain logs.
