<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Project;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LeadController extends Controller
{
    public function index()
    {
        return view('leads.index');
    }

    public function create()
    {
        return view('leads.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'contact_number' => 'required|string|max:15',
            'total_reels' => 'required|integer|min:0',
            'total_posts' => 'required|integer|min:0',
            'total_meta_budget' => 'required|numeric|min:0',
            'client_meta_budget' => 'required|numeric|min:0',
            'dy_meta_budget' => 'required|numeric|min:0',
            'date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $lead = Lead::create(array_merge($validated, [
            'created_by' => auth()->id(),
            'agency_name' => 'Digital Yug',
            'status' => 'pending'
        ]));

        return response()->json(['success' => true, 'message' => 'Lead created successfully!', 'lead_id' => $lead->id]);
    }

    public function show(Lead $lead)
    {
        return view('leads.show', compact('lead'));
    }

    public function edit(Lead $lead)
    {
        return view('leads.edit', compact('lead'));
    }

    public function update(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'contact_number' => 'required|string|max:15',
            'total_reels' => 'required|integer|min:0',
            'total_posts' => 'required|integer|min:0',
            'total_meta_budget' => 'required|numeric|min:0',
            'client_meta_budget' => 'required|numeric|min:0',
            'dy_meta_budget' => 'required|numeric|min:0',
            'date' => 'required|date',
            'status' => 'required|in:pending,confirmed,converted,junk',
            'notes' => 'nullable|string',
        ]);

        $lead->update($validated);

        return response()->json(['success' => true, 'message' => 'Lead updated successfully!']);
    }

    public function destroy(Lead $lead)
    {
        $lead->delete();
        return response()->json(['success' => true, 'message' => 'Lead deleted!']);
    }

    public function dataTable()
    {
        $leads = Lead::with('creator')->latest()->get()->map(function ($l) {
            return [
            'id' => $l->id,
            'date' => $l->date->format('Y-m-d'),
            'day' => $l->day,
            'customer' => $l->customer_name,
            'contact' => $l->contact_number,
            'reels' => $l->total_reels,
            'posts' => $l->total_posts,
            'budget' => '₹' . number_format($l->total_meta_budget),
            'status' => $l->status_badge,
            'actions' => $l->id
            ];
        });
        return response()->json(['data' => $leads]);
    }
}
