<?php

namespace App\Http\Controllers;

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
        $shoots = ShootSchedule::with('project', 'teams')->latest()->paginate(15);
        return view('shoots.index', compact('shoots'));
    }

    public function create(Project $project)
    {
        $project->load('approvedConcepts');
        $staff = User::role('Shooting Person')->where('is_active', true)->get();
        return view('shoots.create', compact('project', 'staff'));
    }

    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'shoot_date' => 'required|date',
            'location' => 'required|string|max:255',
            'team' => 'required|array|min:1',
            'team.*' => 'exists:users,id',
            'concepts' => 'required|array|min:1',
            'concepts.*' => 'exists:concepts,id',
            'notes' => 'nullable|string',
        ]);

        $shoot = ShootSchedule::create([
            'project_id' => $project->id,
            'shoot_date' => $validated['shoot_date'],
            'location' => $validated['location'],
            'notes' => $validated['notes'],
        ]);

        $shoot->teams()->attach($validated['team']);

        foreach ($validated['concepts'] as $cid) {
            ShootConceptLink::create(['shoot_schedule_id' => $shoot->id, 'concept_id' => $cid]);
        }

        // Notify team
        foreach ($validated['team'] as $uid) {
            PanelNotification::send($uid, 'shoot_scheduled', 'New Shoot Scheduled', "You have a shoot scheduled for {$project->name} at {$shoot->location}.", route('shoots.show', $shoot), auth()->id(), $shoot);
        }

        return response()->json(['success' => true, 'message' => 'Shoot scheduled successfully!', 'shoot_id' => $shoot->id]);
    }

    public function show(ShootSchedule $shoot)
    {
        $shoot->load('project', 'teams', 'concepts');
        return view('shoots.show', compact('shoot'));
    }

    public function checkin(ShootSchedule $shoot)
    {
        $shoot->update(['checkin_at' => now(), 'checkin_lat' => request()->lat, 'checkin_long' => request()->lng]);
        return response()->json(['success' => true, 'message' => 'Checked in! Duration tracking started.']);
    }

    public function checkout(ShootSchedule $shoot)
    {
        $shoot->update(['checkout_at' => now(), 'reel_count_reported' => request()->reels]);
        return response()->json(['success' => true, 'message' => 'Checked out! Work logged.']);
    }

    public function suggestAdjustment(Request $request, ShootSchedule $shoot)
    {
        $shoot->update(['notes' => $shoot->notes . "\n[Suggestion]: " . $request->suggestion]);
        return response()->json(['success' => true, 'message' => 'Suggestion sent to manager.']);
    }

    public function dataTable()
    {
        $shoots = ShootSchedule::with('project')->latest()->get()->map(function ($s) {
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
