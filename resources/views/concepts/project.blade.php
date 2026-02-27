@extends('layouts.panel')
@section('title', 'Project Concepts – ' . $project->name)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('concepts.index') }}">Concepts</a></li>
    <li class="breadcrumb-item active">{{ $project->name }}</li>
@endsection
@section('content')
<div class="page-header">
    <h1 class="page-title">{{ $project->name }} <span class="page-subtitle">Concept Review &amp; Approval Stage</span></h1>
    <div class="d-flex gap-2">
        <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-secondary">Go to Project</a>
        @role('Admin|Manager')
        <a href="{{ route('concepts.assign-form', $project) }}" class="btn btn-primary"><i class="fa-solid fa-plus me-2"></i>Add More Concepts</a>
        @endrole
    </div>
</div>

<div class="row g-4">
    <div class="col-12">
        <div class="panel-card">
            <h5 class="panel-card-title mb-4"><i class="fa-solid fa-list-check"></i> Concept Status Breakdown</h5>
            <div class="row text-center g-3">
                <div class="col-md-3">
                    <div class="p-3 bg-light rounded-3">
                        <div class="text-muted small mb-1">TOTAL REQUIRED</div>
                        <div class="h4 fw-bold mb-0 text-dark">{{ $project->conceptTasks->sum('concepts_required') }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 bg-light rounded-3">
                        <div class="text-muted small mb-1 text-primary">SUBMITTED</div>
                        <div class="h4 fw-bold mb-0 text-primary">{{ $project->concepts->count() }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 bg-light rounded-3">
                        <div class="text-muted small mb-1 text-success">APPROVED</div>
                        <div class="h4 fw-bold mb-0 text-success">{{ $project->approved_concepts }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 bg-light rounded-3">
                        <div class="text-muted small mb-1 text-danger">REJECTED</div>
                        <div class="h4 fw-bold mb-0 text-danger">{{ $project->concepts->where('status','rejected')->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @foreach($project->concepts as $c)
    <div class="col-md-6 col-xl-4">
        <div class="panel-card h-100 d-flex flex-column">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <span class="badge mb-2" style="background:rgba(108,63,197,0.1);color:#6c3fc5;">Concept #{{ $c->sequence }}</span>
                    <h6 class="fw-bold mb-0">{{ $c->title }}</h6>
                </div>
                {!! $c->status_badge !!}
            </div>
            
            <div class="text-muted small flex-grow-1 mb-3" style="font-size:13px;line-height:1.6;white-space:pre-wrap;">{{ $c->description }}</div>
            
            @if($c->writer_notes)
            <div class="p-2 mb-3 rounded bg-light" style="font-size:11px;border-left:3px solid #dee2e6;">
                <span class="fw-bold text-muted">Writer Info:</span> {{ $c->writer_notes }}
            </div>
            @endif

            <div class="mt-auto border-top pt-3 d-flex justify-content-between align-items-center">
                <span class="small text-muted"><i class="fa-solid fa-clock me-1"></i>{{ $c->updated_at->format('d M, h:i A') }}</span>
                @role('Admin|Manager')
                    <div class="action-btns">
                        @if($c->status !== 'approved')
                        <button class="btn-action approve btn-approve-concept" data-id="{{ $c->id }}" title="Approve"><i class="fa-solid fa-check"></i></button>
                        <button class="btn-action delete btn-reject-concept" data-id="{{ $c->id }}" title="Reject/Revise"><i class="fa-solid fa-xmark"></i></button>
                        @endif
                        @if($c->status === 'draft')
                        <button class="btn-action view btn-client-review" data-id="{{ $c->id }}" title="Send to Client Review"><i class="fa-solid fa-paper-plane"></i></button>
                        @endif
                    </div>
                @endrole
            </div>
        </div>
    </div>
    @endforeach

    @if($project->concepts->count() === 0)
    <div class="col-12 text-center py-5">
        <i class="fa-solid fa-lightbulb fa-4x text-muted opacity-25 mb-3"></i>
        <h4 class="text-muted">No concepts submitted yet</h4>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('.btn-approve-concept').on('click', function() {
        const id = $(this).data('id');
        ajaxPost(`/concepts/${id}/approve`, {}, function(res) {
            showSuccess(res.message);
            setTimeout(() => location.reload(), 1200);
        });
    });

    $('.btn-reject-concept').on('click', function() {
        const id = $(this).data('id');
        Swal.fire({ title: 'Reject Concept?', text: 'Ask writer for revision?', icon: 'warning', showCancelButton: true })
        .then(r => {
            if(r.isConfirmed) {
                ajaxPost(`/concepts/${id}/reject`, {}, function(res) {
                    showSuccess(res.message);
                    setTimeout(() => location.reload(), 1200);
                });
            }
        });
    });

    $('.btn-client-review').on('click', function() {
        const id = $(this).data('id');
        ajaxPost(`/concepts/${id}/send-to-client`, {}, function(res) {
            showSuccess(res.message);
            setTimeout(() => location.reload(), 1200);
        });
    });
});
</script>
@endpush
