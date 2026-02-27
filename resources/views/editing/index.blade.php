@extends('layouts.panel')
@section('title', 'Editing Tasks')
@section('breadcrumb')
    <li class="breadcrumb-item active">Editing</li>
@endsection
@section('content')
<div class="page-header">
    <h1 class="page-title">Video Editing <span class="page-subtitle">Manage post-production and approvals</span></h1>
    @role('Admin|Manager')
    <div class="dropdown">
        <button class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">Assign Editing</button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li class="dropdown-header">Select Project</li>
            @foreach(\App\Models\Project::whereNotIn('stage',['completed','pending'])->get() as $p)
            <li><a class="dropdown-item" href="{{ route('editing.assign-form', $p) }}">{{ $p->name }}</a></li>
            @endforeach
        </ul>
    </div>
    @endrole
</div>

<div class="panel-card">
    <div class="panel-card-header">
        <h5 class="panel-card-title"><i class="fa-solid fa-film"></i> Editing Queue</h5>
    </div>
    <div class="panel-table-wrapper">
        <table id="editingTable" class="table table-hover w-100">
            <thead><tr>
                <th>#</th><th>Task Title</th><th>Project</th><th>Editor</th>
                <th>Concept</th><th>Progress</th><th>Status</th><th>Actions</th>
            </tr></thead>
            <tbody>
                @foreach($tasks as $i => $t)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td><strong>{{ $t->title }}</strong></td>
                    <td>{{ $t->project->name ?? 'N/A' }}</td>
                    <td>{{ $t->assignedTo->name ?? 'N/A' }}</td>
                    <td><span style="font-size:12px;">{{ $t->concept->title ?? 'General' }}</span></td>
                    <td style="min-width:120px;">
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1"><div class="progress-bar" style="width:{{ $t->progress_percent }}%"></div></div>
                            <span class="small fw-bold">{{ $t->completed_count }}/{{ $t->total_videos }}</span>
                        </div>
                    </td>
                    <td>{!! $t->status_badge !!}</td>
                    <td>
                        <div class="action-btns">
                            <a href="{{ route('editing.show', $t) }}" class="btn-action view" title="View/Update"><i class="fa-solid fa-eye"></i></a>
                            @role('Admin|Manager')
                            <button class="btn-action delete btn-delete" data-url="{{ route('editing.destroy', $t) }}" title="Delete"><i class="fa-solid fa-trash"></i></button>
                            @endrole
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
    $('#editingTable').DataTable({ responsive: true, pageLength: 15, order: [[0, 'asc']] });
});
</script>
@endpush
