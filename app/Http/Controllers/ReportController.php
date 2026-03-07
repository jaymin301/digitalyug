<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Project;
use App\Models\EditTask;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function monthly()
    {
        return view('reports.monthly');
    }

    public function monthlyData(Request $request)
    {
        $year = $request->get('year', now()->year);
        $months = [];

        for ($m = 1; $m <= 12; $m++) {
            $date = Carbon::create($year, $m, 1);
            $months[] = [
                'month' => $date->format('M'),
                'total_leads' => Lead::whereYear('date', $year)->whereMonth('date', $m)->count(),
                'converted_leads' => Lead::whereYear('date', $year)->whereMonth('date', $m)->where('status', 'converted')->count(),
                // 'revenue' => Lead::whereYear('date', $year)->whereMonth('date', $m)->where('status', 'converted')->sum('total_meta_budget'),
                'completed_projects' => Project::whereYear('created_at', $year)->whereMonth('created_at', $m)->where('stage', 'completed')->count(),
                'active_projects' => Project::whereYear('created_at', $year)->whereMonth('created_at', $m)->whereNotIn('stage', ['completed', 'pending'])->count(),
                // 'edits_approved' => EditTask::whereYear('approved_at', $year)->whereMonth('approved_at', $m)->where('status', 'approved')->count(),
            ];
        }

        $totals = [
            'total_leads' => Lead::whereYear('date', $year)->count(),
            'converted_leads' => Lead::whereYear('date', $year)->where('status', 'converted')->count(),
            'total_revenue' => Lead::whereYear('date', $year)->where('status', 'converted')->sum('total_meta_budget'),
            'completed_projects' => Project::whereYear('created_at', $year)->where('stage', 'completed')->count(),
        ];

        return response()->json(['months' => $months, 'totals' => $totals, 'year' => $year]);
    }
}
