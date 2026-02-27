<?php

namespace App\Http\Controllers;

use App\Models\EditTask;
use App\Models\PanelNotification;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class EditingController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if ($user->hasRole('Video Editor')) {
            $tasks = EditTask::with('project', 'concept')->where('assigned_to', $user->id)->latest()->get();
        }
        else {
            $tasks = EditTask::with('project', 'assignedTo', 'concept')->latest()->get();
        }
        return view('editing.index', compact('tasks'));
    }

    public function assignForm(Project $project)
    {
        $project->load('approvedConcepts', 'shootSchedules');
        $editors = User::role('Video Editor')->where('is_active', true)->get();
        return view('editing.assign', compact('project', 'editors'));
    }

    public function assign(Request $request, Project $project)
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'total_videos' => 'required|integer|min:1',
            'concept_id' => 'nullable|exists:concepts,id',
            'shoot_schedule_id' => 'nullable|exists:shoot_schedules,id',
        ]);

        $task = EditTask::create(array_merge($validated, [
            'project_id' => $project->id,
            'assigned_by' => auth()->id(),
            'status' => 'pending',
        ]));

        if ($project->stage !== 'editing') {
            $project->update(['stage' => 'editing']);
        }

        // Notify editor
        PanelNotification::send(
            $validated['assigned_to'],
            'edit_assigned',
            'New Editing Task Assigned',
            "You have been assigned to edit: {$validated['title']} for {$project->name}.",
            route('editing.show', $task),
            auth()->id(),
            $task
        );

        return response()->json(['success' => true, 'message' => 'Editing task assigned!', 'task_id' => $task->id]);
    }

    public function show(EditTask $editTask)
    {
        $editTask->load('project', 'concept', 'assignedTo', 'assignedBy', 'approvedBy');
        return view('editing.show', compact('editTask'));
    }

    public function updateCount(Request $request, EditTask $editTask)
    {
        $validated = $request->validate([
            'completed_count' => 'required|integer|min:0|max:' . $editTask->total_videos,
        ]);

        $editTask->update([
            'completed_count' => $validated['completed_count'],
            'status' => $validated['completed_count'] >= $editTask->total_videos ? 'review' : 'in_progress',
        ]);

        if ($editTask->status === 'review') {
            // Notify manager
            $managers = User::role('Manager')->pluck('id');
            foreach ($managers as $mid) {
                PanelNotification::send($mid, 'edit_review', 'Edit Ready for Review', "{$editTask->assignedTo->name} completed editing: {$editTask->title}. Ready for approval.", route('editing.show', $editTask), auth()->id(), $editTask);
            }
        }

        return response()->json(['success' => true, 'progress' => $editTask->progress_percent, 'status' => $editTask->status_badge]);
    }

    public function approve(Request $request, EditTask $editTask)
    {
        $validated = $request->validate(['approval_notes' => 'nullable|string']);

        $editTask->update([
            'status' => 'approved',
            'approval_notes' => $validated['approval_notes'] ?? null,
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        $editTask->project->increment('completed_edits');

        PanelNotification::send($editTask->assigned_to, 'edit_approved', 'Editing Approved!', "Your edit '{$editTask->title}' has been approved. Great work!", route('editing.show', $editTask), auth()->id(), $editTask);

        return response()->json(['success' => true, 'message' => 'Edit task approved!']);
    }

    public function requestRevision(Request $request, EditTask $editTask)
    {
        $validated = $request->validate(['approval_notes' => 'required|string']);

        $editTask->update([
            'status' => 'revision',
            'approval_notes' => $validated['approval_notes'],
        ]);

        PanelNotification::send($editTask->assigned_to, 'edit_revision', 'Revision Requested', "Manager requested revisions on '{$editTask->title}': {$validated['approval_notes']}", route('editing.show', $editTask), auth()->id(), $editTask);

        return response()->json(['success' => true, 'message' => 'Revision requested.']);
    }

    public function dataTable()
    {
        $tasks = EditTask::with('project', 'assignedTo')->latest()->get()->map(function ($t) {
            return [
            'id' => $t->id,
            'title' => $t->title,
            'project' => $t->project->name ?? 'N/A',
            'assigned_to' => $t->assignedTo->name ?? 'N/A',
            'total_videos' => $t->total_videos,
            'completed_count' => $t->completed_count,
            'progress' => $t->progress_percent . '%',
            'status' => $t->status_badge,
            'actions' => $t->id,
            ];
        });
        return response()->json(['data' => $tasks]);
    }
}
