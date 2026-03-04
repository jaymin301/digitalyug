<?php

namespace App\Http\Controllers;

use App\Models\Concept;
use App\Models\ShootSchedule;
use App\Models\Project;
use App\Models\User;
use App\Models\PanelNotification;
use App\Models\ShootConceptLink;
use Illuminate\Http\Request;

class ShootController extends Controller
{
    public function index()
    {
        $shoots = ShootSchedule::with('project', 'shootingPerson')->latest()->paginate(15);
        return view('shoots.index', compact('shoots'));
    }

    public function create(Project $project)
    {
        $project->load('approvedConcepts');
        $shooters = User::role('Shooting Person')->where('is_active', true)->get();
        $writers = User::role('Concept Writer')->where('is_active', true)->get();
        return view('shoots.create', compact('project', 'shooters', 'writers'));
    }

    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'shoot_date' => 'required|date',
            'location' => 'required|string|max:255',
            'shooting_person_id' => 'required|exists:users,id',
            'planned_start_time' => 'nullable|string|max:10',
            'concept_writer_id' => 'nullable|exists:users,id',
            'model_name' => 'nullable|string|max:255',
            'helper_name' => 'nullable|string|max:255',
            'concept_ids' => 'required|array|min:1',
            'concept_ids.*' => 'exists:concepts,id',
            'notes' => 'nullable|string',
        ]);

        $shoot = ShootSchedule::create([
            'project_id' => $project->id,
            'shoot_date' => $validated['shoot_date'],
            'location' => $validated['location'],
            'shooting_person_id' => $validated['shooting_person_id'], // ✅
            'planned_start_time' => $validated['planned_start_time'] ?? null,
            'concept_writer_id' => $validated['concept_writer_id'] ?? null,
            'model_name' => $validated['model_name'] ?? null,
            'helper_name' => $validated['helper_name'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => 'scheduled',
            'created_by' => auth()->id(),
        ]);

        foreach ($validated['concept_ids'] as $cid) {
            ShootConceptLink::create(['shoot_schedule_id' => $shoot->id, 'concept_id' => $cid]);
        }

        PanelNotification::send(
            $validated['shooting_person_id'],
            'shoot_scheduled',
            'New Shoot Scheduled',
            "You have a shoot scheduled for {$project->name} at {$shoot->location}.",
            route('shoots.show', $shoot),
            auth()->id(),
            $shoot
        );

        if (!empty($validated['concept_writer_id'])) {
            PanelNotification::send(
                $validated['concept_writer_id'],
                'shoot_scheduled',
                'On-site Shoot Assignment',
                "You are assigned on-site for {$project->name} shoot at {$shoot->location}.",
                route('shoots.show', $shoot),
                auth()->id(),
                $shoot
            );
        }

        return response()->json(['success' => true, 'message' => 'Shoot scheduled successfully!', 'shoot_id' => $shoot->id]);
    }

    public function show(ShootSchedule $shoot)
    {
        $shoot->load('project', 'concepts', 'shootingPerson', 'conceptWriter');
        return view('shoots.show', compact('shoot'));
    }

    public function checkin(ShootSchedule $shoot)
    {
        if ($shoot->checkin_at) {
            return response()->json([
                'success' => false,
                'message' => 'Already checked in.',
            ], 422);
        }

        $shoot->update([
            'checkin_at' => now(),
            'status' => 'in_progress',
            'checkin_lat' => request()->lat,
            'checkin_long' => request()->lng
        ]);

        $managers = User::role('Manager')->pluck('id');
        foreach ($managers as $mid) {
            PanelNotification::send(
                $mid,
                'shoot_scheduled',
                'Shoot Started',
                "{$shoot->shootingPerson->name} checked in for {$shoot->project->name} at {$shoot->location}.",
                route('shoots.show', $shoot),
                auth()->id(),
                $shoot
            );
        }
        return response()->json(['success' => true, 'message' => 'Checked in! Duration tracking started.']);
    }

    public function checkout(Request $request, ShootSchedule $shoot)
    {
        $request->validate([
            'reels_shot' => 'required|integer|min:0',
            'shot_concept_ids' => 'nullable|array',
            'shot_concept_ids.*' => 'exists:concepts,id',
        ]);

        if ($shoot->checkout_at) {
            return response()->json([
                'success' => false,
                'message' => 'Already checked out.',
            ], 422);
        }

        $shoot->update([
            'checkout_at' => now(),
            'reels_shot' => request()->reels_shot,
            'status' => 'completed',
        ]);

        $shotIds = $request->shot_concept_ids ?? [];
        foreach ($shoot->concepts as $concept) {
            ShootConceptLink::updateOrCreate(
            [
                'shoot_schedule_id' => $shoot->id,
                'concept_id' => $concept->id,
            ],
            [
                'is_shot' => in_array($concept->id, $shotIds),
            ]
            );
        }

        $managers = User::role('Manager')->pluck('id');
        foreach ($managers as $mid) {
            PanelNotification::send(
                $mid,
                'shoot_scheduled',
                'Shoot Completed',
                "{$shoot->shootingPerson->name} completed shoot for {$shoot->project->name}. {$request->reels_shot} reels shot.",
                route('shoots.show', $shoot),
                auth()->id(),
                $shoot
            );
        }
        return response()->json(['success' => true, 'message' => 'Checked out! Work logged.']);
    }

    public function suggestAdjustment(Request $request, ShootSchedule $shoot)
    {
        $request->validate([
            'concept_id' => 'nullable|exists:concepts,id',
            'suggestion' => 'nullable|string',
            'updated_description' => 'nullable|string',
            'updated_title' => 'nullable|string',
            'is_new' => 'nullable|boolean',
            'new_title' => 'nullable|string|required_if:is_new,1',
            'new_description' => 'nullable|string',
        ]);

        if ($request->is_new) {
            $concept = Concept::create([
                'project_id' => $shoot->project_id,
                'concept_task_id' => null,
                'shoot_id' => $shoot->id,
                'assigned_to' => auth()->id(),
                'title' => $request->new_title,
                'description' => $request->new_description ?? null,
                'status' => 'approved',
                'is_review_reel' => true,
                'approved_at' => now(),
                'approved_by' => auth()->id(),
            ]);

            // ✅ Link to this shoot
            ShootConceptLink::create([
                'shoot_schedule_id' => $shoot->id,
                'concept_id' => $concept->id,
            ]);

            // ✅ Update project counts
            // Dynamic accessors handle this now

            // ✅ Notify managers
            $managers = User::role('Manager')->pluck('id');
            foreach ($managers as $mid) {
                PanelNotification::send(
                    $mid,
                    'concept_assigned',
                    'New Review Reel Added',
                    auth()->user()->name . " added new concept '{$concept->title}' during shoot for {$shoot->project->name}.",
                    route('concepts.project', $shoot->project_id),
                    auth()->id(),
                    $shoot
                );
            }

            return response()->json([
                'success' => true,
                'message' => "'{$concept->title}' added successfully!",
            ]);
        }
        $concept = Concept::find($request->concept_id);
        if (!$concept) {
            return response()->json([
                'success' => false,
                'message' => 'Concept not found.',
            ], 404);
        }
        $concept->update([
            'title' => $request->updated_title ?? $concept->title,
            'description' => $request->updated_description ?? $concept->description,
            'adjustment_suggestion' => $request->suggestion ?? $concept->adjustment_suggestion,
        ]);
        if ($request->suggestion) {
            $shoot->update([
                'notes' => trim($shoot->notes) . "\n[Suggestion on '{$concept->title}']: " . $request->suggestion,
            ]);
        }

        $managers = User::role('Manager')->pluck('id');
        foreach ($managers as $mid) {
            PanelNotification::send(
                $mid,
                'concept_assigned',
                'Concept Updated During Shoot',
                auth()->user()->name . " updated '{$concept->title}' during shoot for {$shoot->project->name}.",
                route('concepts.project', $shoot->project_id),
                auth()->id(),
                $shoot
            );
        }
        return response()->json(['success' => true, 'message' => 'Suggestion sent to manager.']);
    }

    public function destroy(ShootSchedule $shoot)
    {
        $shoot->conceptLinks()->delete();
        $shoot->delete();

        return response()->json([
            'success' => true,
            'message' => 'Shoot deleted successfully.',
        ]);
    }
    public function dataTable()
    {
        $shoots = ShootSchedule::with('project')
            ->orderBy('created_at', 'desc')->get()
            ->map(function ($s) {
            return [
            'id' => $s->id,
            'date' => $s->shoot_date->format('d M Y'),
            'project' => $s->project->name ?? 'N/A',
            'location' => $s->location,
            'status' => $s->checkin_at ? ($s->checkout_at ? '<span class="badge bg-success">Completed</span>' : '<span class="badge bg-warning">In Progress</span>') : '<span class="badge bg-secondary">Upcoming</span>',
            'actions' => $s->id
            ];
        });
        return response()->json(['data' => $shoots]);
    }
}
