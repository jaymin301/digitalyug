<?php

namespace App\Http\Controllers;

use App\Models\Concept;
use App\Models\ConceptTask;
use App\Models\Project;
use App\Models\User;
use App\Models\PanelNotification;
use Illuminate\Http\Request;

class ConceptController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if ($user->hasRole('Concept Writer')) {
            $tasks = ConceptTask::with('project')->where('assigned_to', $user->id)->latest()->get();
        }
        else {
            $tasks = ConceptTask::with('project', 'assignedTo')->latest()->get();
        }
        return view('concepts.index', compact('tasks'));
    }

    public function projectConcepts(Project $project)
    {
        $project->load('concepts.approvedBy', 'conceptTasks.assignedTo');
        return view('concepts.project_overview', compact('project'));
    }

    public function assignForm(Project $project)
    {
        $writers = User::role('Concept Writer')->where('is_active', true)->get();
        return view('concepts.assign', compact('project', 'writers'));
    }

    public function assign(Request $request, Project $project)
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'deadline' => 'nullable|date',
        ]);

        $task = ConceptTask::create(array_merge($validated, [
            'project_id' => $project->id,
            'assigned_by' => auth()->id(),
            'status' => 'pending'
        ]));

        PanelNotification::send($validated['assigned_to'], 'concept_assigned', 'New Concept Writing Task', "You've been assigned a new writing task for {$project->name}.", route('concepts.submit-form', $task), auth()->id(), $task);

        return response()->json(['success' => true, 'message' => 'Writer assigned successfully!', 'task_id' => $task->id]);
    }

    public function submitForm(ConceptTask $conceptTask)
    {
        return view('concepts.submit', compact('conceptTask'));
    }

    public function submit(Request $request, ConceptTask $conceptTask)
    {
        $validated = $request->validate([
            'concepts' => 'required|array|min:1',
            'concepts.*.title' => 'required|string|max:255',
            'concepts.*.script' => 'required|string',
        ]);

        foreach ($validated['concepts'] as $c) {
            Concept::create([
                'project_id' => $conceptTask->project_id,
                'concept_task_id' => $conceptTask->id,
                'title' => $c['title'],
                'script' => $c['script'],
                'status' => 'client_review',
            ]);
        }

        $conceptTask->update(['status' => 'submitted']);

        // Notify managers
        $managers = User::role('Manager')->pluck('id');
        foreach ($managers as $mid) {
            PanelNotification::send($mid, 'concepts_submitted', 'Concepts Submitted', "Writer {$conceptTask->assignedTo->name} submitted concepts for {$conceptTask->project->name}.", route('concepts.project', $conceptTask->project_id), auth()->id(), $conceptTask);
        }

        return response()->json(['success' => true, 'message' => 'Concepts submitted for review!']);
    }

    public function approve(Request $request, Concept $concept)
    {
        $concept->update(['status' => 'approved', 'approved_at' => now(), 'approved_by' => auth()->id()]);
        $concept->project->increment('approved_concepts_count');

        PanelNotification::send($concept->task->assigned_to, 'concept_approved', 'Concept Approved!', "Your concept '{$concept->title}' was approved!", route('concepts.project', $concept->project_id), auth()->id(), $concept);

        return response()->json(['success' => true, 'message' => 'Concept approved!']);
    }

    public function reject(Request $request, Concept $concept)
    {
        $concept->update(['status' => 'rejected', 'remarks' => $request->remarks]);
        return response()->json(['success' => true, 'message' => 'Concept rejected.']);
    }

    public function dataTable()
    {
        $tasks = ConceptTask::with('project', 'assignedTo')->latest()->get()->map(function ($t) {
            return [
            'id' => $t->id,
            'title' => $t->title,
            'project' => $t->project->name ?? 'N/A',
            'writer' => $t->assignedTo->name ?? 'N/A',
            'status' => $t->status_badge,
            'actions' => $t->id
            ];
        });
        return response()->json(['data' => $tasks]);
    }
}
