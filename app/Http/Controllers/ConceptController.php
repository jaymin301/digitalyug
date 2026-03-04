<?php

namespace App\Http\Controllers;

use App\Models\Concept;
use App\Models\ConceptTask;
use App\Models\Project;
use App\Models\User;
use App\Models\PanelNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
        $project->load('concepts', 'concepts.approvedBy', 'concepts.shootingConcept', 'conceptTasks.assignedTo');
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
            'general_remarks' => 'required|string',
            'due_date' => 'nullable|date',
            'concepts_required' => 'required|integer|min:1',
            'concepts' => 'nullable|array',
            'concepts.*.title' => 'nullable|string|max:255',
            'concepts.*.client_allocation' => 'nullable|string|max:255',
            'concepts.*.remarks' => 'nullable|string',
        ]);

        $task = ConceptTask::create([
            'project_id' => $project->id,
            'assigned_to' => $validated['assigned_to'],
            'assigned_by' => auth()->id(),
            'concepts_required' => $validated['concepts_required'],
            'general_remarks' => $validated['general_remarks'],
            'due_date' => $validated['due_date'],
            'status' => 'pending',
        ]);

        if (!empty($validated['concepts'])) {
            foreach ($validated['concepts'] as $concept) {
                // Only create if at least title is present
                if (!empty($concept['title'])) {
                    Concept::create([
                        'concept_task_id' => $task->id,
                        'project_id' => $project->id,
                        'assigned_to' => $validated['assigned_to'],
                        'title' => $concept['title'],
                        'client_allocation' => $concept['client_allocation'] ?? null,
                        'remarks' => $concept['remarks'] ?? null,
                        'status' => 'draft',
                        'description' => null,
                        'writer_notes' => null,
                    ]);
                }
            }
        }

        PanelNotification::send($validated['assigned_to'], 'concept_assigned', 'New Concept Writing Task', "You've been assigned a new writing task for {$project->name}.", route('concepts.submit-form', $task), auth()->id(), $task);

        return response()->json(['success' => true, 'message' => 'Writer assigned successfully!', 'task_id' => $task->id]);
    }

    public function submitForm(ConceptTask $conceptTask)
    {
        $conceptTask->load(['project', 'concepts']);

        $reviewReels = $conceptTask->project->concepts()
            ->where('status', 'approved')
            ->get();

        $remainingRequired = max(0, $conceptTask->concepts_required - $reviewReels->count());

        $writerConcepts = Concept::where('concept_task_id', $conceptTask->id)
            ->where('is_review_reel', false)
            ->get();

        return view('concepts.submit', compact('conceptTask', 'reviewReels',
            'remainingRequired',
            'writerConcepts'));
    }

    public function submit(Request $request, ConceptTask $conceptTask)
    {
        $validated = $request->validate([
            'concepts' => 'required|array|min:1',
            'concepts.*.id' => 'nullable|exists:concepts,id',
            'concepts.*.title' => 'nullable|string|max:255',
            'concepts.*.description' => 'nullable|string',
            'concepts.*.writer_notes' => 'nullable|string',
        ]);

        $submittedCount = 0;

        foreach ($validated['concepts'] as $c) {

            if (empty($c['description']) && empty($c['title'])) {
                continue;
            }

            // Skip if description is empty - writer hasn't filled it yet
            if (empty($c['description'])) {
                continue;
            }
            if (!empty($c['id'])) {
                $concept = Concept::where('id', $c['id'])
                    ->where('concept_task_id', $conceptTask->id)
                    ->first();

                if ($concept && $concept->status !== 'approved') {
                    $concept->update([
                        'title' => $c['title'],
                        'description' => $c['description'],
                        'writer_notes' => $c['writer_notes'] ?? null,
                        'status' => 'client_review',
                    ]);
                    $submittedCount++;
                }
            }
            else {
                Concept::create([
                    'project_id' => $conceptTask->project_id,
                    'concept_task_id' => $conceptTask->id,
                    'assigned_to' => $conceptTask->assigned_to ?? null,
                    'title' => $c['title'],
                    'description' => $c['description'],
                    'writer_notes' => $c['writer_notes'] ?? null,
                    'status' => 'client_review',
                ]);
                $submittedCount++;
            }
        }

        $totalSubmitted = $conceptTask->concepts()
            ->whereNotNull('description')
            ->whereIn('status', ['client_review', 'approved', 'rejected'])
            ->count();

        $remaining = $conceptTask->concepts_required - $totalSubmitted;

        $managers = User::role('Manager')->pluck('id');

        if ($totalSubmitted >= $conceptTask->concepts_required) {
            $conceptTask->update(['status' => 'submitted']);

            foreach ($managers as $mid) {
                PanelNotification::send(
                    $mid,
                    'concepts_submitted',
                    'Concepts Submitted',
                    "Writer {$conceptTask->assignedTo->name} submitted all concepts for {$conceptTask->project->name}.",
                    route('concepts.project', $conceptTask->project_id),
                    auth()->id(),
                    $conceptTask
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'All concepts submitted for review!',
                'complete' => true,
                'submitted' => $totalSubmitted,
                'required' => $conceptTask->concepts_required,
            ]);

        }
        else {

            $conceptTask->update(['status' => 'in_progress']);

            foreach ($managers as $mid) {
                PanelNotification::send(
                    $mid,
                    'concepts_submitted',
                    'Concepts Submitted',
                    "Writer {$conceptTask->assignedTo->name} submitted concepts for {$conceptTask->project->name}.",
                    route('concepts.project', $conceptTask->project_id),
                    auth()->id(), $conceptTask);
            }

            return response()->json([
                'success' => true,
                'message' => "{$submittedCount} concept(s) saved! {$remaining} remaining.",
                'complete' => false,
                'submitted' => $totalSubmitted,
                'required' => $conceptTask->concepts_required,
            ]);
        }
    }

    public function sendToClientReview(Request $request, Concept $concept)
    {
        $concept->update(['status' => 'client_review']);
        return response()->json(['success' => true, 'message' => 'Concept sent to client review!']);
    }

    public function approve(Request $request, Concept $concept)
    {
        $concept->update(['status' => 'approved', 'approved_at' => now(), 'approved_by' => auth()->id()]);

        PanelNotification::send($concept->task->assigned_to, 'concept_approved', 'Concept Approved!', "Your concept '{$concept->title}' was approved!", route('concepts.project', $concept->project_id), auth()->id(), $concept);

        return response()->json(['success' => true, 'message' => 'Concept approved!']);
    }

    public function reject(Request $request, Concept $concept)
    {
        $concept->update(['status' => 'rejected', 'remarks' => $request->remarks]);
        return response()->json(['success' => true, 'message' => 'Concept rejected.']);
    }

    // Generate shareable link for client
    public function generateClientLink(ConceptTask $conceptTask)
    {
        $token = Str::random(40);
        $conceptTask->update([
            'client_token' => $token,
            'client_token_expires_at' => now()->addDays(7),
        ]);

        $link = route('concepts.client-view', $token);

        return response()->json([
            'success' => true,
            'link' => $link,
            'expires' => '7 days',
        ]);
    }

    // Public client view - no auth required
    public function clientView(string $token)
    {
        $conceptTask = ConceptTask::where('client_token', $token)
            ->where('client_token_expires_at', '>', now())
            ->with(['project', 'concepts'])
            ->firstOrFail();

        return view('concepts.client_view', compact('conceptTask'));
    }

    // Client approves a concept - no auth required
    public function clientApprove(Request $request, string $token, Concept $concept)
    {
        $conceptTask = ConceptTask::where('client_token', $token)
            ->where('client_token_expires_at', '>', now())
            ->firstOrFail();

        // Make sure concept belongs to this task
        abort_if($concept->concept_task_id !== $conceptTask->id, 403);

        $concept->update(['status' => 'approved']);

        // Check if all concepts approved
        $allApproved = $conceptTask->concepts()
            ->where('status', '!=', 'approved')
            ->doesntExist();

        if ($allApproved) {
            $conceptTask->update(['status' => 'completed']);
        }

        return response()->json(['success' => true, 'message' => 'Concept approved!']);
    }

    // Client rejects a concept - no auth required
    public function clientReject(Request $request, string $token, Concept $concept)
    {
        $request->validate(['remarks' => 'required|string']);

        $conceptTask = ConceptTask::where('client_token', $token)
            ->where('client_token_expires_at', '>', now())
            ->firstOrFail();

        abort_if($concept->concept_task_id !== $conceptTask->id, 403);

        $concept->update([
            'status' => 'rejected',
            'remarks' => $request->remarks,
        ]);

        return response()->json(['success' => true, 'message' => 'Feedback submitted!']);
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
