<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Project;
use App\Models\User;
use App\Models\PanelNotification;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::with('manager', 'lead')->latest()->get();
        return view('projects.index', compact('projects'));
    }

    public function show(Project $project)
    {
        $project->load(['manager', 'lead', 'conceptTasks.assignedTo', 'shootSchedules', 'editTasks.assignedTo', 'concepts']);
        return view('projects.show', compact('project'));
    }

    public function createFromLead(Lead $lead)
    {
        if ($lead->project) {
            return response()->json(['success' => false, 'message' => 'Project already exists for this lead.']);
        }

        $project = Project::create([
            'lead_id' => $lead->id,
            'name' => 'Project: ' . $lead->customer_name,
            'manager_id' => auth()->id(), // Default to creator
            'stage' => 'pending',
        ]);

        $lead->update(['status' => 'converted']);

        return response()->json(['success' => true, 'message' => 'Project created! Now activate it.', 'project_id' => $project->id]);
    }

    public function activate(Request $request, Project $project)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            // 'manager_id' => 'required|exists:users,id',
        ]);

        $project->update([
            'start_date' => $validated['start_date'],
            'manager_id' => auth()->id(),
            'stage' => 'concept',
        ]);

        // Notify manager
        PanelNotification::send(
            auth()->id(),
            'project_activated',
            'New Project Activated',
            "Project '{$project->name}' has been activated. Please assign concept writers.",
            route('projects.show', $project),
            auth()->id(),
            $project
        );

        return response()->json(['success' => true, 'message' => 'Project activated! Workflow started.']);
    }

    public function dataTable()
    {
        $projects = Project::with(['manager', 'lead', 'conceptTasks', 'shootSchedules', 'editTasks', 'concepts'])->latest()->get()->map(function ($p) {
            return [
            'id' => $p->id,
            'name' => $p->name,
            'manager' => $p->manager->name ?? 'N/A',
            'start_date' => $p->start_date ? $p->start_date->format('d M Y') : 'Not Set',
            'end_date' => $p->end_date ? $p->end_date->format('d M Y') : 'Not Set',
            'progress' => $p->workflow_progress . '%',
            'stage' => $p->stage_badge,
            'actions' => $p->id
            ];
        });
        return response()->json(['data' => $projects]);
    }
}
