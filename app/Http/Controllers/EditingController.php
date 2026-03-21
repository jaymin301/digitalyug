<?php

namespace App\Http\Controllers;

use App\Models\EditTask;
use App\Models\EditTaskConcept;
use App\Models\EditTaskVideo;
use App\Models\PanelNotification;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class EditingController extends Controller
{
    /**
     * Display a listing of editing tasks based on user role.
     */
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

    /**
     * Show the form for assigning an editing task.
     */
    public function assignForm(Project $project)
    {
        $project->load('approvedConcepts', 'shootSchedules');
        $editors = User::role('Video Editor')->where('is_active', true)->get();

        $approvedConceptIds = $project->approvedConcepts->pluck('id');

        // Concept ID → Editor Name mapping
        $assignedConcepts = EditTaskConcept::whereIn('concept_id', $approvedConceptIds)
            ->with('editTask.assignedTo') // EditTask model ma assignedTo() relation hovu joie
            ->get()
            ->keyBy('concept_id'); // concept_id => pivot record

        $assignedConceptIds = $assignedConcepts->keys()->toArray();


        return view('editing.assign', compact('project', 'editors','assignedConceptIds','assignedConcepts'));
    }

    /**
     * Handle the assignment of an editing task to an editor.
     */
    public function assign(Request $request, Project $project)
    {
        $request->merge([
            'concept_ids' => array_filter($request->input('concept_ids', []), fn($id) => !is_null($id) && $id !== ''),
        ]);
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'total_videos' => 'required|integer|min:1',
            'concept_ids' => 'nullable|array',
            'concept_ids.*' => 'exists:concepts,id',
            'shoot_schedule_id' => 'nullable|exists:shoot_schedules,id',
        ]);

        $task = EditTask::create([
            'project_id' => $project->id,
            'assigned_to' => $validated['assigned_to'],
            'assigned_by' => auth()->id(),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'total_videos' => $validated['total_videos'],
            'shoot_schedule_id' => $validated['shoot_schedule_id'] ?? null,
            'status' => 'pending',
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

    /**
     * Display details of a specific editing task.
     */
    public function show(EditTask $editTask)
    {
        $editTask->load('project', 'concepts', 'assignedTo', 'assignedBy', 'approvedBy', 'shootSchedule.concepts', 'videoEntries.concept');
        return view('editing.show', compact('editTask'));
    }

    /**
     * Update the progress and video entries for a task.
     * Triggered by the editor when syncing work.
     */
    public function updateCount(Request $request, EditTask $editTask)
    {
        // Explicitly decode the raw request body. This is used to handle 
        // nested JSON data sent from the workboard sync process.
        $data = json_decode($request->getContent(), true) ?? [];
        $request->merge($data);

        $validated = $request->validate([
            'videos' => 'required|array',
            'videos.*.id' => 'nullable|exists:edit_task_videos,id',
            'videos.*.concept_id' => 'nullable|exists:concepts,id',
            'videos.*.status' => 'required|in:pending,completed',
            'videos.*.notes' => 'nullable|string|max:255',
        ]);

        foreach ($validated['videos'] as $i => $v) {
            EditTaskVideo::updateOrCreate(
                ['id' => $v['id'] ?? null],
                [
                    'edit_task_id' => $editTask->id,
                    'concept_id' => $v['concept_id'] ?? null,
                    'video_label' => 'Video ' . ($i + 1),
                    'status' => $v['status'],
                ]
            );
        }

        // Re-sync task counts
        $editTask->load('videoEntries');
        $completedCount = $editTask->videoEntries->where('status', 'completed')->count();
        $approvedCount = $editTask->videoEntries->where('admin_status', 'approved')->count();

        // Determine new status
        $newStatus = $editTask->status;
        if ($approvedCount >= $editTask->total_videos) {
            $newStatus = 'approved';
        }
        elseif ($completedCount >= $editTask->total_videos) {
            $newStatus = 'review';
        }
        elseif ($completedCount > 0) {
            $newStatus = 'in_progress';
        }
        else {
            $newStatus = 'pending';
        }

        $editTask->update([
            'status' => $newStatus,
            'completed_count' => $completedCount,
            'approved_videos' => $approvedCount,
        ]);

        return response()->json([
            'success' => true,
            'progress' => $editTask->progress_percent,
            'completed' => $completedCount,
            'status' => $newStatus,
        ]);
    }

    /**
     * Final approval of the entire task by admin/manager.
     */
    public function approve(Request $request, EditTask $editTask)
    {
        $validated = $request->validate(['approval_notes' => 'nullable|string']);

        $editTask->update([
            'status' => 'approved',
            'approval_notes' => $validated['approval_notes'] ?? $editTask->approval_notes,
            'approved_videos' => $editTask->total_videos, // Mark all as approved if manually sealed
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        PanelNotification::send($editTask->assigned_to, 'edit_approved', 'Editing Approved!', "Your edit '{$editTask->title}' has been approved. Great work!", route('editing.show', $editTask), auth()->id(), $editTask);

        return response()->json(['success' => true, 'message' => 'Edit task approved!']);
    }

    /**
     * Approve an individual video slot with optional notes.
     */
    public function approveVideo(Request $request, EditTask $editTask, EditTaskVideo $video)
    {
        if ($video->edit_task_id !== $editTask->id)
            abort(404);

        $validated = $request->validate([
            'editor_feedback' => 'nullable|string|max:1000',
            'admin_internal_note' => 'nullable|string|max:1000',
        ]);

        $video->update([
            'admin_status' => 'approved',
            'editor_feedback' => $validated['editor_feedback'] ?? null,
            'admin_internal_note' => $validated['admin_internal_note'] ?? null,
            'admin_reviewed_by' => auth()->id(),
            'admin_reviewed_at' => now(),
        ]);

        // Re-sync counts on the task
        $editTask->load('videoEntries');
        $editTask->update([
            'approved_videos' => $editTask->videoEntries->where('admin_status', 'approved')->count(),
            'completed_count' => $editTask->videoEntries->where('status', 'completed')->count(),
        ]);

        // Auto-approve the whole task if every video is now individually approved
        $this->maybeAutoApproveTask($editTask);

        PanelNotification::send($editTask->assigned_to, 'edit_approved', 'Video Approved', "Manager approved a video for '{$editTask->title}'.", route('editing.show', $editTask), auth()->id(), $editTask);

        return response()->json([
            'success' => true,
            'message' => 'Video approved successfully.',
            'admin_status' => 'approved',
            'reviewer_name' => auth()->user()->name,
            'reviewed_at' => now()->format('d M, h:i A'),
            'approved_count' => $editTask->approved_videos,
            'progress' => $editTask->progress_percent,
            'task_auto_approved' => $editTask->fresh()->status === 'approved',
        ]);
    }

    /**
     * Internal logic to check if all videos in a task are approved.
     */
    private function maybeAutoApproveTask(EditTask $editTask): void
    {
        $fresh = $editTask->fresh(['videoEntries']);

        // All slots must exist and all must be approved
        if ($fresh->videoEntries->count() < $fresh->total_videos) {
            return;
        }

        $allApproved = $fresh->videoEntries->where('admin_status', 'approved')->count() >= $fresh->total_videos;

        if ($allApproved) {
            $editTask->update([
                'status' => 'approved',
                'approved_videos' => $fresh->total_videos,
                'approved_at' => now(),
                'approved_by' => auth()->id(),
            ]);
        }
    }

    /**
     * Reject an individual video slot with feedback notes.
     */
    public function rejectVideo(Request $request, EditTask $editTask, EditTaskVideo $video)
    {
        if ($video->edit_task_id !== $editTask->id)
            abort(404);

        $validated = $request->validate([
            'editor_feedback' => 'required|string|max:1000',
            'admin_internal_note' => 'nullable|string|max:1000'
        ]);

        $video->update([
            'admin_status' => 'rejected',
            'editor_feedback' => $validated['editor_feedback'],
            'admin_internal_note' => $validated['admin_internal_note'] ?? null,
            'admin_reviewed_by' => auth()->id(),
            'admin_reviewed_at' => now(),
        ]);

        // Re-sync counts on the task
        $editTask->load('videoEntries');
        $editTask->update([
            'approved_videos' => $editTask->videoEntries->where('admin_status', 'approved')->count(),
            'completed_count' => $editTask->videoEntries->where('status', 'completed')->count(),
        ]);

        // If task was fully approved before, revert it back to review
        if ($editTask->status === 'approved' && $editTask->approved_videos < $editTask->total_videos) {
            $editTask->update(['status' => 'review']);
        }

        PanelNotification::send($editTask->assigned_to, 'edit_revision', 'Revision Requested', "Manager requested revisions on '{$editTask->title}'.", route('editing.show', $editTask), auth()->id(), $editTask);

        return response()->json([
            'success' => true,
            'message' => 'Video rejected.',
            'admin_status' => 'rejected',
            'reviewer_name' => auth()->user()->name,
            'reviewed_at' => now()->format('d M, h:i A'),
            'progress' => $editTask->fresh()->progress_percent,
            'completed' => $editTask->fresh()->completed_count,
            'approved_count' => $editTask->fresh()->approved_videos,
        ]);
    }

    public function updateDescription(Request $request, EditTask $editTask)
    {
        $validated = $request->validate(['description' => 'nullable|string|max:2000']);

        $editTask->update(['description' => $validated['description']]);

        return response()->json([
            'success' => true,
            'message' => 'Editing requirements updated.',
        ]);
    }

    /**
     * Request a task-wide revision.
     */
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

    /**
     * Get data for the tasks DataTables.
     */
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
            'approved_videos' => $t->approved_videos,
            'progress' => $t->progress_percent . '%',
            'status' => $t->status_badge,
            'actions' => $t->id,
            ];
        });
        return response()->json(['data' => $tasks]);
    }

    /**
     * Delete an editing task and its associated video entries.
     */
    public function destroy(EditTask $editTask)
    {
        $editTask->videoEntries()->delete();
        $editTask->concepts()->detach();
        $editTask->delete();

        return response()->json(['success' => true, 'message' => 'Editing task deleted successfully.']);
    }
}
