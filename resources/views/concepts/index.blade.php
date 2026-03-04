@extends('layouts.panel')
@section('title', 'Concept Tasks')
@section('breadcrumb')
    <li class="breadcrumb-item active">Concepts</li>
@endsection
@section('content')
<div class="page-header">
    <h1 class="page-title">Concept Writing <span class="page-subtitle">Track and review creative work</span></h1>
</div>

<div class="panel-card">
    <div class="panel-card-header">
        <h5 class="panel-card-title"><i class="fa-solid fa-lightbulb"></i> Concept Pipeline</h5>
    </div>
    <div class="panel-table-wrapper">
        <table id="conceptsTable" class="table table-hover w-100">
            <thead><tr>
                <th>#</th><th>Project</th><th>Writer</th><th>Required</th>
                <th>Approved</th><th>Due Date</th><th>Status</th><th>Actions</th>
            </tr></thead>
            <tbody>
                @foreach($tasks as $i => $t)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td><strong>{{ $t->project->name ?? 'N/A' }}</strong></td>
                    <td>{{ $t->assignedTo->name ?? 'Unassigned' }}</td>
                    <td>{{ $t->concepts_required }}</td>
                    <td>{{ $t->concepts()->where('status','approved')->count() }}</td>
                    <td>{{ $t->due_date ? $t->due_date->format('d M Y') : '—' }}</td>
                    <td>{!! $t->status_badge !!}</td>
                    <td>
                        <div class="action-btns">
                            @role('Concept Writer')
                                <a href="{{ route('concepts.submit-form', $t) }}" class="btn-action edit" title="Submit Concepts"><i class="fa-solid fa-pen-nib"></i></a>
                            @endrole
                                <a href="{{ route('concepts.project', $t->project_id) }}" class="btn-action view" title="Review"><i class="fa-solid fa-magnifying-glass"></i></a>
                            {{-- @endrole --}}
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#conceptsTable').DataTable({ 
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search Concepts...",
        },
        responsive: true,
        pageLength: 10,

    });
});
</script>
@endpush
