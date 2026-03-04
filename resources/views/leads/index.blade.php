@extends('layouts.panel')
@section('title', 'Leads')
@section('breadcrumb')
    <li class="breadcrumb-item active">Leads</li>
@endsection
@section('content')
<div class="page-header">
    <h1 class="page-title">Leads Management <span class="page-subtitle">All client leads and inquiries</span></h1>
    @role('Admin|Manager|Sales Executive')
    <a href="{{ route('leads.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus me-2"></i>Add Lead</a>
    @endrole
</div>

<div class="panel-card">
    <div class="panel-card-header">
        <h5 class="panel-card-title"><i class="fa-solid fa-handshake"></i> All Leads</h5>
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <span class="badge rounded-pill" style="background:rgba(108,63,197,0.1);color:#6c3fc5;">{{ $leads->count() }} Total</span>
        </div>
    </div>
    <div class="panel-table-wrapper">
        <table id="leadsTable" class="table table-hover w-100">
            <thead><tr>
                <th>#</th><th>Date</th><th>Day</th><th>Customer</th>
                <th>Contact</th><th>Reels</th><th>Posts</th>
                <th>Budget</th><th>Status</th><th>By</th><th>Actions</th>
            </tr></thead>
            <tbody>
                @foreach($leads as $i => $lead)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $lead->date->format('d M Y') }}</td>
                    <td><span style="font-size:12px;color:#718096;">{{ $lead->day }}</span></td>
                    <td>
                        <strong>{{ $lead->customer_name }}</strong>
                        @if($lead->project)
                        <br><span class="badge" style="background:rgba(38,222,129,0.1);color:#1a8754;font-size:10px;">Has Project</span>
                        @endif
                    </td>
                    <td>{{ $lead->contact_number }}</td>
                    <td>{{ $lead->total_reels }}</td>
                    <td>{{ $lead->total_posts }}</td>
                    <td>
                        <div style="font-size:13px;font-weight:700;">₹{{ number_format($lead->total_meta_budget) }}</div>
                        <div style="font-size:11px;color:#718096;">Client: ₹{{ number_format($lead->client_meta_budget) }} | DY: ₹{{ number_format($lead->dy_meta_budget) }}</div>
                    </td>
                    <td>{!! $lead->status_badge !!}</td>
                    <td>{{ $lead->createdBy?->name ?? 'N/A' }}</td>
                    <td>
                        <div class="action-btns">
                            <a href="{{ route('leads.show', $lead) }}" class="btn-action view" title="View"><i class="fa-solid fa-eye"></i></a>
                            @role('Admin|Manager|Sales Executive')
                                <a href="{{ route('leads.edit', $lead) }}" class="btn-action edit" title="Edit"><i class="fa-solid fa-pen"></i></a>
                            @endrole
                            @role('Admin|Manager')
                                @if(!$lead->project && $lead->status === 'converted')
                                    <button class="btn-action approve" onclick="createProject({{ $lead->id }}, '{{ $lead->customer_name }}')" title="Create Project"><i class="fa-solid fa-folder-plus"></i></button>
                                @endif
                                <button class="btn-action delete btn-delete" data-url="{{ route('leads.destroy', $lead) }}" title="Delete"><i class="fa-solid fa-trash"></i></button>
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
    $('#leadsTable').DataTable({ 
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search Leads...",
        },
        responsive: true, 
        pageLength: 10, 
        ordering: true,
        order: [[1,'desc']]
    });
});

function createProject(leadId, name) {
    Swal.fire({
        title: 'Create Project?',
        text: `Convert lead "${name}" into a project?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#6c3fc5',
        confirmButtonText: 'Yes, create!'
    }).then(r => {
        if (r.isConfirmed) {
            ajaxPost(`/projects/from-lead/${leadId}`, {}, function(res) {
                showSuccess(res.message);
                setTimeout(() => window.location.href = `/projects/${res.project_id}`, 1500);
            });
        }
    });
}
</script>
@endpush
