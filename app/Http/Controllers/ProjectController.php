<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\Lead;
use App\Models\Project;
use App\Models\User;
use App\Models\PanelNotification;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::with('manager', 'lead', 'agency')->latest()->get();
        return view('projects.index', compact('projects'));
    }

    public function show(Project $project)
    {
        $project->load(['manager', 'lead', 'agency', 'conceptTasks.assignedTo', 'shootSchedules', 'editTasks.assignedTo', 'concepts']);
        return view('projects.show', compact('project'));
    }

    // ── Direct Project Creation ────────────────────────────

    public function create()
    {
        $agencies = Agency::orderBy('name')->get();
        return view('projects.create', compact('agencies'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'date'               => 'required|date',
            'agency_id'          => 'nullable|exists:agencies,id',
            'customer_name'      => 'required|string|max:255',
            'contact_number'     => 'required|string|max:20',
            'total_reels'        => 'required|integer|min:0',
            'total_posts'        => 'required|integer|min:0',
            'total_meta_budget'  => 'required|numeric|min:0',
            'client_meta_budget' => 'required|numeric|min:0',
            'dy_meta_budget'     => 'required|numeric|min:0',
            'notes'              => 'nullable|string',
        ]);

        $project = Project::create(array_merge($validated, [
            'lead_id'    => null,
            'manager_id' => auth()->id(),
            'stage'      => 'pending',
        ]));

        return response()->json([
            'success'    => true,
            'message'    => 'Project created successfully!',
            'project_id' => $project->id,
        ]);
    }

    // ── Edit / Update ──────────────────────────────────────

    public function edit(Project $project)
    {
        $agencies = Agency::orderBy('name')->get();
        return view('projects.edit', compact('project', 'agencies'));
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'date'               => 'nullable|date',
            'agency_id'          => 'nullable|exists:agencies,id',
            'customer_name'      => 'nullable|string|max:255',
            'contact_number'     => 'nullable|string|max:20',
            'total_reels'        => 'nullable|integer|min:0',
            'total_posts'        => 'nullable|integer|min:0',
            'total_meta_budget'  => 'nullable|numeric|min:0',
            'client_meta_budget' => 'nullable|numeric|min:0',
            'dy_meta_budget'     => 'nullable|numeric|min:0',
            'notes'              => 'nullable|string',
        ]);

        $project->update($validated);

        return response()->json(['success' => true, 'message' => 'Project updated successfully!']);
    }

    // ── Lead Conversion Flow ───────────────────────────────

    public function createFromLead(Lead $lead)
    {
        if ($lead->project) {
            return response()->json(['success' => false, 'message' => 'Project already exists for this lead.']);
        }

        $project = Project::create([
            'lead_id'            => $lead->id,
            'name'               => 'Project: ' . $lead->customer_name,
            'manager_id'         => auth()->id(),
            'stage'              => 'pending',
            // Copy lead fields into project so it's self-contained
            'date'               => $lead->date,
            'agency_id'          => $lead->agency_id,
            'customer_name'      => $lead->customer_name,
            'contact_number'     => $lead->contact_number,
            'total_reels'        => $lead->total_reels,
            'total_posts'        => $lead->total_posts,
            'total_meta_budget'  => $lead->total_meta_budget,
            'client_meta_budget' => $lead->client_meta_budget,
            'dy_meta_budget'     => $lead->dy_meta_budget,
            'notes'              => $lead->notes,
        ]);

        $lead->update(['status' => 'converted']);

        return response()->json([
            'success'    => true,
            'message'    => 'Project created! Now activate it.',
            'project_id' => $project->id,
        ]);
    }

    // ── Activate ───────────────────────────────────────────

    public function activate(Request $request, Project $project)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
        ]);

        $project->update([
            'start_date' => $validated['start_date'],
            'manager_id' => auth()->id(),
            'stage'      => 'concept',
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

    // ── DataTable ──────────────────────────────────────────

    public function dataTable()
    {
        $projects = Project::with(['manager', 'lead', 'agency', 'conceptTasks', 'shootSchedules', 'editTasks', 'concepts'])
            ->latest()->get()->map(function ($p) {
                // Use own customer_name first; fall back to lead for legacy records
                $clientName = $p->customer_name ?: ($p->lead->customer_name ?? 'N/A');
                return [
                    'id'         => $p->id,
                    'name'       => $p->name,
                    'manager'    => $p->manager->name ?? 'N/A',
                    'client'     => $clientName,
                    'start_date' => $p->start_date ? $p->start_date->format('d M Y') : 'Not Set',
                    'end_date'   => $p->end_date ? $p->end_date->format('d M Y') : 'Not Set',
                    'progress'   => $p->progress_percent . '%',
                    'stage'      => $p->stage_badge,
                    'actions'    => $p->id,
                ];
            });

        return response()->json(['data' => $projects]);
    }

    // ── Delete ─────────────────────────────────────────────

    public function destroy(Project $project)
    {
        $project->delete();
        return response()->json(['success' => true, 'message' => 'Project deleted!']);
    }
}
