@extends('layouts.panel')
@section('title', 'Projects')
@section('breadcrumb')
    <li class="breadcrumb-item active">Projects</li>
@endsection
@section('content')
<div class="page-header">
    <h1 class="page-title">Projects <span class="page-subtitle">Track active campaigns and workflow stages</span></h1>
</div>

<div class="panel-card">
    <div class="panel-card-header">
        <h5 class="panel-card-title"><i class="fa-solid fa-folder-open"></i> Project Pipeline</h5>
        <div class="d-flex gap-2">
            <span class="badge rounded-pill" style="background:rgba(108,63,197,0.1);color:#6c3fc5;">{{ $projects->count() }} Total</span>
        </div>
    </div>
    <div class="panel-table-wrapper">
        <table id="projectsTable" class="table table-hover w-100">
            <thead><tr>
                <th>#</th><th>Project Name</th><th>Client</th><th>Manager</th>
                <th>Start Date</th><th>End Date</th><th>Stage</th><th>Actions</th>
            </tr></thead>
            <tbody>
                @foreach($projects as $i => $p)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td><strong>{{ $p->name }}</strong></td>
                    <td>{{ $p->lead->customer_name ?? 'N/A' }}</td>
                    <td>{{ $p->manager->name ?? 'Unassigned' }}</td>
                    <td>{{ $p->start_date ? $p->start_date->format('d M Y') : '—' }}</td>
                    <td>{{ $p->end_date ? $p->end_date->format('d M Y') : '—' }}</td>
                    <td>{!! $p->stage_badge !!}</td>
                    <td>
                        <div class="action-btns">
                            <a href="{{ route('projects.show', $p) }}" class="btn-action view" title="View Details"><i class="fa-solid fa-eye"></i></a>
                            @role('Admin|Manager')
                                @if($p->stage === 'pending')
                                    <button class="btn-action approve" onclick="activateProject({{ $p->id }}, '{{ $p->name }}')" title="Activate Project"><i class="fa-solid fa-bolt"></i></button>
                                @endif
                                <button class="btn-action delete btn-delete" data-url="{{ route('projects.destroy', $p) }}" title="Delete"><i class="fa-solid fa-trash"></i></button>
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
    $('#projectsTable').DataTable({
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search Projects...",
        },
        responsive: true, 
        pageLength: 10,
        order: [[0, 'asc']]
    });
});

function activateProject(id, name) {
    Swal.fire({
        title: 'Activate Project?',
        text: `Set start date for "${name}" to begin workflow?`,
        html: `<input type="date" id="projStartDate" class="form-control" value="{{ date('Y-m-d') }}">`,
        icon: 'info',
        showCancelButton: true,
        confirmButtonColor: '#6c3fc5',
        confirmButtonText: 'Activate Now',
        preConfirm: () => {
            const date = document.getElementById('projStartDate').value;
            if (!date) Swal.showValidationMessage('Please select a start date');
            return date;
        }
    }).then(result => {
        if (result.isConfirmed) {
            ajaxPost(`/projects/${id}/activate`, { start_date: result.value }, function(res) {
                showSuccess(res.message);
                setTimeout(() => location.reload(), 1500);
            });
        }
    });
}
</script>
@endpush
