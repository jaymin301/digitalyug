@extends('layouts.panel')
@section('title', 'Lead – ' . $lead->customer_name)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('leads.index') }}">Leads</a></li>
    <li class="breadcrumb-item active">{{ $lead->customer_name }}</li>
@endsection
@section('content')
<div class="page-header">
    <h1 class="page-title">{{ $lead->customer_name }} <span class="page-subtitle">Lead Details · {!! $lead->status_badge !!}</span></h1>
    <div class="d-flex gap-2">
        @role('Admin|Manager|Sales Executive')
        <a href="{{ route('leads.edit', $lead) }}" class="btn btn-outline-primary"><i class="fa-solid fa-pen me-2"></i>Edit</a>
        @endrole
        @role('Admin|Manager')
        @if(!$lead->project && $lead->status === 'converted')
        <button class="btn btn-success" onclick="createProject()"><i class="fa-solid fa-folder-plus me-2"></i>Create Project</button>
        @endif
        @endrole
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="panel-card">
            <h5 class="panel-card-title mb-4"><i class="fa-solid fa-circle-info"></i> Lead Information</h5>
            <div class="row g-3">
                <div class="col-sm-6">
                    <label class="form-label">Agency</label>
                    <div class="fw-bold">{{ $lead->agency_name }}</div>
                </div>
                <div class="col-sm-6">
                    <label class="form-label">Date</label>
                    <div class="fw-bold">{{ $lead->date->format('d M Y') }} ({{ $lead->day }})</div>
                </div>
                <div class="col-sm-6">
                    <label class="form-label">Customer Name</label>
                    <div class="fw-bold" style="font-size:16px;">{{ $lead->customer_name }}</div>
                </div>
                <div class="col-sm-6">
                    <label class="form-label">Contact</label>
                    <div class="fw-bold">{{ $lead->contact_number }}</div>
                </div>
                <div class="col-sm-3">
                    <label class="form-label">Total Reels</label>
                    <div class="fw-bold" style="font-size:22px;color:#6c3fc5;">{{ $lead->total_reels }}</div>
                </div>
                <div class="col-sm-3">
                    <label class="form-label">Total Posts</label>
                    <div class="fw-bold" style="font-size:22px;color:#45aaf2;">{{ $lead->total_posts }}</div>
                </div>
                <div class="col-sm-6">
                    <label class="form-label">Added By</label>
                    <div class="fw-bold">{{ $lead->createdBy?->name ?? 'N/A' }}</div>
                </div>
                @if($lead->notes)
                <div class="col-12">
                    <label class="form-label">Notes</label>
                    <div style="background:#f7f8ff;border-radius:8px;padding:12px 16px;font-size:14px;color:#4a5568;">{{ $lead->notes }}</div>
                </div>
                @endif
            </div>
        </div>

        {{-- Project link --}}
        @if($lead->project)
        <div class="panel-card">
            <h5 class="panel-card-title mb-4"><i class="fa-solid fa-folder-open"></i> Linked Project</h5>
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="fw-bold">{{ $lead->project->name }}</div>
                    <div class="text-muted" style="font-size:13px;">Stage: {!! $lead->project->stage_badge !!}</div>
                    @if($lead->project->start_date)
                    <div class="text-muted" style="font-size:12px;">{{ $lead->project->start_date->format('d M Y') }} → {{ $lead->project->end_date->format('d M Y') }}</div>
                    @endif
                </div>
                <a href="{{ route('projects.show', $lead->project) }}" class="btn btn-primary btn-sm">View Project</a>
            </div>
        </div>
        @endif
    </div>

    {{-- Budget sidebar --}}
    <div class="col-lg-4">
        <div class="panel-card">
            <h5 class="panel-card-title mb-4"><i class="fa-solid fa-indian-rupee-sign"></i> Meta Budget</h5>
            <div class="mb-3">
                <div class="text-muted mb-1" style="font-size:12px;">TOTAL BUDGET</div>
                <div style="font-size:28px;font-weight:800;color:#1a1a2e;">₹{{ number_format($lead->total_meta_budget, 0) }}</div>
            </div>
            <div class="mb-3">
                <div class="d-flex justify-content-between mb-1">
                    <span style="font-size:12px;color:#718096;">Client Budget</span>
                    <strong>₹{{ number_format($lead->client_meta_budget, 0) }}</strong>
                </div>
                <div class="progress"><div class="progress-bar" style="width:{{ $lead->total_meta_budget > 0 ? ($lead->client_meta_budget / $lead->total_meta_budget * 100) : 0 }}%;background:#45aaf2;"></div></div>
            </div>
            <div>
                <div class="d-flex justify-content-between mb-1">
                    <span style="font-size:12px;color:#718096;">Digital Yug Budget</span>
                    <strong>₹{{ number_format($lead->dy_meta_budget, 0) }}</strong>
                </div>
                <div class="progress"><div class="progress-bar" style="width:{{ $lead->total_meta_budget > 0 ? ($lead->dy_meta_budget / $lead->total_meta_budget * 100) : 0 }}%;background:#6c3fc5;"></div></div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
function createProject() {
    Swal.fire({ title: 'Create Project?', text: 'Convert this lead into a project?', icon: 'question', showCancelButton: true, confirmButtonColor: '#6c3fc5', confirmButtonText: 'Yes, create!' })
    .then(r => {
        if (r.isConfirmed) {
            ajaxPost('/projects/from-lead/{{ $lead->id }}', {}, function(res) {
                showSuccess(res.message);
                setTimeout(() => location.href = '/projects/' + res.project_id, 1500);
            });
        }
    });
}
</script>
@endpush
