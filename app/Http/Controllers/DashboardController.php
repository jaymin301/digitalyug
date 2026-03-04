<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Project;
use App\Models\EditTask;
use App\Models\ShootSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $data = $this->getStats();

        if ($user->hasRole('Admin')) {
            return view('dashboard.admin', $data);
        }
        if ($user->hasRole('Manager')) {
            return view('dashboard.manager', $data);
        }
        if ($user->hasRole('Sales Executive')) {
            return view('dashboard.sales_dashboard', ['myLeads' => Lead::where('created_by', $user->id)->latest()->take(5)->get()]);
        }
        if ($user->hasRole('Concept Writer')) {
            return view('dashboard.concept', ['myTasks' => $user->conceptTasks()->with('project')->latest()->take(5)->get()]);
        }
        if ($user->hasRole('Shooting Person')) {
            return view('dashboard.shooting_dashboard', ['myShoots' => $user->shootSchedules()->with('project')->latest()->take(5)->get()]);
        }
        if ($user->hasRole('Video Editor')) {
            return view('dashboard.editor_dashboard', ['myEdits' => $user->editTasks()->with('project')->latest()->take(5)->get()]);
        }

        return view('dashboard.admin', $data);
    }

    private function getStats(): array
    {
        $projects = Project::with(['manager', 'lead', 'conceptTasks', 'shootSchedules', 'editTasks', 'concepts'])->get();
        return [
            'totalLeads' => Lead::count(),
            'newLeads' => Lead::where('status', 'new')->count(),
            'convertedLeads' => Lead::where('status', 'converted')->count(),
            'totalProjects' => $projects->count(),
            'pendingProjects' => $projects->where('stage', 'pending')->count(),
            'conceptProjects' => $projects->where('stage', 'concept')->count(),
            'shootingProjects' => $projects->where('stage', 'shooting')->count(),
            'editingProjects' => $projects->where('stage', 'editing')->count(),
            'completedProjects' => $projects->where('stage', 'completed')->count(),
            'totalEdits' => EditTask::count(),
            'pendingEdits' => EditTask::whereIn('status', ['pending', 'in_progress', 'review'])->count(),
            'approvedEdits' => EditTask::where('status', 'approved')->count(),
            'recentLeads' => Lead::with('createdBy')->latest()->take(5)->get(),
            'kanbanProjects' => $projects->groupBy('stage'),
            'monthlyRevenue' => $this->monthlyRevenue(),
        ];
    }

    public function stats()
    {
        return response()->json($this->getStats());
    }

    private function monthlyRevenue(): array
    {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = Carbon::now()->subMonths($i);
            $months[] = [
                'month' => $d->format('M Y'),
                'revenue' => Lead::whereYear('date', $d->year)
                ->whereMonth('date', $d->month)
                ->where('status', 'converted')
                ->sum('total_meta_budget'),
                'leads' => Lead::whereYear('date', $d->year)
                ->whereMonth('date', $d->month)
                ->count(),
            ];
        }
        return $months;
    }
}
