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
            $conceptTasksIds = $user->conceptTasks()->pluck('id');
            $totalProjects = $user->conceptTasks()->pluck('project_id')->unique()->count();
            $totalApprovedConcepts = \App\Models\Concept::whereIn('concept_task_id', $conceptTasksIds)->where('status', 'approved')->count();

            return view('dashboard.concept', [
                'myTasks' => $user->conceptTasks()->with('project')->latest()->take(5)->get(),
                'totalProjects' => $totalProjects,
                'totalApprovedConcepts' => $totalApprovedConcepts,
            ]);
        }
        if ($user->hasRole('Shooting Person')) {
            return view('dashboard.shooting_dashboard', [
                'myShoots' => $user->shootSchedules()->with('project')->latest()->take(5)->get(),
                'performance' => $this->getPerformanceStats($user)
            ]);
        }
        if ($user->hasRole('Video Editor')) {
            return view('dashboard.editor_dashboard', ['myEdits' => $user->editTasks()->with('project')->latest()->take(5)->get()]);
        }
        if ($user->hasRole('Anchor Person')) {
            return view('dashboard.shooting_dashboard', [
                'myShoots' => $user->anchorShoots()->with('project')->latest()->take(5)->get(),
                'performance' => $this->getPerformanceStats($user)
            ]);
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
            'teamPerformance' => $this->getTeamPerformance(),
        ];
    }

    private function getPerformanceStats($user): array
    {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = Carbon::now()->subMonths($i);
            
            $query = ShootSchedule::whereYear('shoot_date', $d->year)
                ->whereMonth('shoot_date', $d->month)
                ->where('status', 'completed');

            if ($user->hasRole('Shooting Person')) {
                $query->where('shooting_person_id', $user->id);
            } elseif ($user->hasRole('Anchor Person')) {
                $query->whereHas('anchors', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            }

            $shoots = $query->with('concepts')->get();

            if ($user->hasRole('Shooting Person')) {
                $concept_count = $shoots->sum(fn($s) => $s->concepts->count());
            } else {
                // Anchor Person: Count only concepts assigned to them
                $concept_count = 0;
                foreach($shoots as $s) {
                    $concept_count += $s->concepts->where('anchor_id', $user->id)->count();
                }
            }

            $months[] = [
                'month' => $d->format('M Y'),
                'shoot_count' => $shoots->count(),
                'project_count' => $shoots->pluck('project_id')->unique()->count(),
                'concept_count' => $concept_count,
            ];
        }
        return $months;
    }

    private function getTeamPerformance(): array
    {
        $users = \App\Models\User::role(['Shooting Person', 'Anchor Person'])->get();
        $currentMonth = Carbon::now();
        
        $performance = [];
        foreach ($users as $user) {
            $query = ShootSchedule::whereYear('shoot_date', $currentMonth->year)
                ->whereMonth('shoot_date', $currentMonth->month)
                ->where('status', 'completed');

            if ($user->hasRole('Shooting Person')) {
                $query->where('shooting_person_id', $user->id);
            } else {
                $query->whereHas('anchors', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            }

            $shoots = $query->with('concepts')->get();
            
            if ($user->hasRole('Shooting Person')) {
                $concept_count = $shoots->sum(fn($s) => $s->concepts->count());
            } else {
                $concept_count = 0;
                foreach($shoots as $s) {
                    $concept_count += $s->concepts->where('anchor_id', $user->id)->count();
                }
            }

            $performance[] = [
                'name' => $user->name,
                'role' => $user->roles->first()?->name ?? 'N/A',
                'projects' => $shoots->pluck('project_id')->unique()->count(),
                'shoots' => $shoots->count(),
                'concepts' => $concept_count,
            ];
        }
        return $performance;
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
