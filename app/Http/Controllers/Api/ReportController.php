<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'reportable_type' => 'required|in:listing,user',
            'reportable_id'   => 'required|integer',
            'reason'          => 'required|string|max:255',
            'description'     => 'nullable|string|max:1000',
        ]);

        $report = Report::create([
            'reporter_id'     => $request->user()->id,
            'reportable_type' => $request->reportable_type,
            'reportable_id'   => $request->reportable_id,
            'reason'          => $request->reason,
            'description'     => $request->description,
            'status'          => 'pending',
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Report submitted. Our team will review it shortly.',
            'report'  => $report,
        ], 201);
    }
}
