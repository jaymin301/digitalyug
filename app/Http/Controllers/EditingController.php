<?php

namespace App\Http\Controllers;

use App\Models\EditTask;
use App\Models\EditTaskVideo;
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
            $tasks = EditTask::with('project', 'concepts')->where('assigned_to', $user->id)->latest()->get();
        }
        else {
            $tasks = EditTask::with('project', 'assignedTo', 'concepts')->latest()->get();
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
        $request->merge([
            'concept_ids' => array_filter($request->input('concept_ids', []), fn($id) => !is_null($id) && $id !== ''),
        ]);
        $validated = $request->validate([
            'assigned_to'       => 'required|exists:users,id',
            'title'             => 'required|string|max:255',
            'description'       => 'nullable|string',
            'total_videos'      => 'required|integer|min:1',
            'concept_ids'       => 'nullable|array',
            'concept_ids.*'     => 'exists:concepts,id',
            'shoot_schedule_id' => 'nullable|exists:shoot_schedules,id',
        ]);

        $task = EditTask::create([
            'project_id'       => $project->id,
            'assigned_to'      => $validated['assigned_to'],
            'assigned_by'      => auth()->id(),
            'title'            => $validated['title'],
            'description'      => $validated['description'] ?? null,
            'total_videos'     => $validated['total_videos'],
            'shoot_schedule_id'=> $validated['shoot_schedule_id'] ?? null,
            'status'           => 'pending',
        ]);

        $conceptIds = array_filter($validated['concept_ids'] ?? [], fn($id) => !empty($id));
        if (!empty($validated['concept_ids'])) {
            $task->concepts()->sync($conceptIds);
        }
        
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
        $editTask->load('project', 'concepts', 'assignedTo', 'assignedBy', 'approvedBy' ,'shootSchedule.concepts','videoEntries.concept');
        return view('editing.show', compact('editTask'));
    }

    public function updateCount(Request $request, EditTask $editTask)
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $request->merge($data);

        $validated = $request->validate([
            'videos'              => 'required|array',
            'videos.*.id'         => 'nullable|exists:edit_task_videos,id',
            'videos.*.concept_id' => 'nullable|exists:concepts,id',
            'videos.*.status'     => 'required|in:pending,completed',
            'videos.*.notes'      => 'nullable|string|max:255',
        ]);

        foreach ($validated['videos'] as $i => $v) {
            EditTaskVideo::updateOrCreate(
                [
                    'id' => $v['id'] ?? null,
                ],
                [
                    'edit_task_id' => $editTask->id,
                    'concept_id'   => $v['concept_id'] ?? null,
                    'video_label'  => 'Video ' . ($i + 1),
                    'status'       => $v['status'],
                    'notes'        => $v['notes'] ?? null,
                ]
            );
        }

        // Sync status to in_progress or review
        $fresh = $editTask->fresh();
        $completed = $fresh->completed_count;

        if ($completed >= $fresh->total_videos) {
            $editTask->update(['status' => 'review']);
        } elseif ($completed > 0) {
            $editTask->update(['status' => 'in_progress']);
        }

        return response()->json([
            'success'  => true,
            'progress' => $editTask->fresh()->progress_percent,
            'completed'=> $editTask->fresh()->completed_count,
        ]);
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
        $tasks = EditTask::with('project', 'assignedTo')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($t) {
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

    public function destroy(EditTask $editTask)
    {
        $editTask->videoEntries()->delete();
        $editTask->concepts()->detach();
        $editTask->delete();

        return response()->json(['success' => true, 'message' => 'Editing task deleted successfully.']);
    }
}
