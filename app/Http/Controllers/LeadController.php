<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Agency;
use App\Models\Project;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LeadController extends Controller
{
    public function index()
    {
        $leads = Lead::with('createdBy')->latest()->get();
        return view('leads.index', compact('leads'));
    }

    public function create()
    {
        $agencies = Agency::orderBy('name')->get();
        return view('leads.create', compact('agencies'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'contact_number' => 'required|string|max:15',
            'agency_id' => 'nullable|exists:agencies,id',
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
            'status' => 'new'
        ]));

        return response()->json(['success' => true, 'message' => 'Lead created successfully!', 'lead_id' => $lead->id]);
    }

    public function show(Lead $lead)
    {
        return view('leads.show', compact('lead'));
    }

    public function edit(Lead $lead)
    {
        $agencies = Agency::orderBy('name')->get();
        return view('leads.edit', compact('lead', 'agencies'));
    }

    public function update(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'contact_number' => 'required|string|max:15',
            'agency_id' => 'nullable|exists:agencies,id',
            'total_reels' => 'required|integer|min:0',
            'total_posts' => 'required|integer|min:0',
            'total_meta_budget' => 'required|numeric|min:0',
            'client_meta_budget' => 'required|numeric|min:0',
            'dy_meta_budget' => 'required|numeric|min:0',
            'date' => 'required|date',
            'status' => 'required|in:new,contacted,confirmed,converted,lost',
            'notes' => 'nullable|string',
        ]);
        $validated['updated_by'] = auth()->id();
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
        $leads = Lead::with('createdBy')->latest()->get()->map(function ($l) {
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
