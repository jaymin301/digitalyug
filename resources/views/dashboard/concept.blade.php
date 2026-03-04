@extends('layouts.panel')

@section('title', 'Concept Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Concept Dashboard</li>
@endsection

@section('content')
    <div class="dashboard-overview">
        {{-- Stats Row --}}
        <div class="stat-grid mb-4">
            <div class="stat-card">
                <div class="stat-icon bg-purple"><i class="fa-solid fa-lightbulb"></i></div>
                <div class="stat-body">
                    <div class="stat-value">{{ $myTasks->count() }}</div>
                    <div class="stat-label">Total Tasks</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-orange"><i class="fa-solid fa-spinner"></i></div>
                <div class="stat-body">
                    <div class="stat-value">{{ $myTasks->whereIn('status', ['pending', 'in_progress'])->count() }}</div>
                    <div class="stat-label">Pending/In Progress</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-teal"><i class="fa-solid fa-circle-check"></i></div>
                <div class="stat-body">
                    <div class="stat-value">{{ $myTasks->where('status', 'completed')->count() }}</div>
                    <div class="stat-label">Completed</div>
                </div>
            </div>
        </div>

        @include('dashboard.concept_writer')
    </div>
@endsection
