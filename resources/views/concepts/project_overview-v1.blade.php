@extends('layouts.panel')

@section('title', 'Project Concepts – ' . $project->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('concepts.index') }}">Concepts</a></li>
    <li class="breadcrumb-item active">{{ $project->name }}</li>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">{{ $project->name }}</h1>
        <p class="text-muted mb-0">Review and manage creative concepts for this project.</p>
    </div>
    <div class="d-flex gap-2">
        @role('Admin|Manager')
            <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-2"></i>Back to Project
            </a>
            <a href="{{ route('concepts.assign-form', $project) }}" class="btn btn-primary">
                <i class="fa-solid fa-plus me-2"></i>Add Concept Task
            </a>
        @endrole
        @role('Concept Writer')
            <a href="{{ route('concepts.index') }}" class="btn btn-primary">
                <i class="fa-solid fa-arrow-left me-2"></i>Back to Concepts
            </a>
        @endrole
        {{-- @role('Admin|Manager') --}}
            <button class="btn btn-success" id="generateLinkBtn" 
                data-url="{{ route('concepts.generate-link', $project->conceptTasks->first()) }}">
                <i class="fa-solid fa-share-nodes me-2"></i>Share with Client
            </button>   
        {{-- @endrole --}}
    </div>
</div>

<div class="row g-4 mb-4">
    {{-- Status Breakdown --}}
    <div class="col-12">
        <div class="panel-card">
            <h5 class="panel-card-title mb-4"><i class="fa-solid fa-chart-pie me-2"></i>Concept Pipeline Status</h5>
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-light rounded-4 text-center border-0 h-100">
                        <div class="text-muted small mb-1">TOTAL REQUIRED</div>
                        <div class="h3 fw-bold mb-0 text-dark">{{ $project->conceptTasks->sum('concepts_required') }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-light rounded-4 text-center border-0 h-100">
                        <div class="text-muted small mb-1 text-primary">SUBMITTED</div>
                        <div class="h3 fw-bold mb-0 text-primary">{{ $project->concepts->count() }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-light rounded-4 text-center border-0 h-100">
                        <div class="text-muted small mb-1 text-success">APPROVED</div>
                        <div class="h3 fw-bold mb-0 text-success">{{ $project->approvedConcepts()->count() }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-light rounded-4 text-center border-0 h-100">
                        <div class="text-muted small mb-1 text-danger">REJECTED</div>
                        <div class="h3 fw-bold mb-0 text-danger">{{ $project->concepts->where('status', 'rejected')->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    @forelse($project->concepts as $i => $concept)
        <div class="col-md-6 col-xl-4">
            <div class="panel-card h-100 d-flex flex-column border-0 shadow-sm" style="transition: transform 0.2s;">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-weight: 700;">
                            {{ $i + 1 }}
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0">{{ $concept->title }}</h6>
                            <small class="text-muted">Concept #{{ $concept->sequence }}</small>
                        </div>
                    </div>
                    {!! $concept->status_badge !!}
                </div>

                <div class="flex-grow-1">
                    <div class="p-3 rounded-3 bg-light mb-3" style="font-size: 13.5px; line-height: 1.6; max-height: 200px; overflow-y: auto; border: 1px dashed #e2e8f0;">
                        <strong class="d-block mb-1 text-muted small uppercase">Concept Script:</strong>
                        <div style="white-space: pre-wrap;">{{ $concept->description }}</div>
                    </div>

                    @if($concept->writer_notes)
                    <div class="small p-2 rounded bg-warning bg-opacity-10 border-start border-warning border-3 mb-3">
                        <i class="fa-solid fa-comment-dots me-1 text-warning"></i> <strong>Writer Notes:</strong> {{ $concept->writer_notes }}
                    </div>
                    @endif
                    
                    @if($concept->remarks)
                    <div class="small p-2 rounded bg-danger bg-opacity-10 border-start border-danger border-3 mb-3">
                        <i class="fa-solid fa-circle-exclamation me-1 text-danger"></i> <strong>Manager Remarks:</strong> {{ $concept->remarks }}
                    </div>
                    @endif
                </div>

                <div class="mt-3 pt-3 border-top d-flex justify-content-between align-items-center">
                    <div class="small text-muted">
                        <i class="fa-solid fa-clock-rotate-left me-1"></i>{{ $concept->updated_at->diffForHumans() }}
                    </div>
                    
                    @role('Admin|Manager')
                    <div class="action-btns">
                        @if($concept->status !== 'approved')
                        <button class="btn btn-sm btn-success btn-approve-concept" data-id="{{ $concept->id }}" title="Approve Concept">
                            <i class="fa-solid fa-check me-1"></i>Approve
                        </button>
                        <button class="btn btn-sm btn-outline-danger btn-reject-concept" data-id="{{ $concept->id }}" title="Request Revision">
                            <i class="fa-solid fa-xmark me-1"></i>Reject
                        </button>
                        @endif
                    </div>
                    @endrole
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="text-center py-5">
                <div class="mb-3">
                    <i class="fa-solid fa-lightbulb fa-4x text-muted opacity-25"></i>
                </div>
                <h4 class="text-muted">No concepts submitted yet</h4>
                <p class="text-muted">Once concept tasks are completed, they will appear here for review.</p>
            </div>
        </div>
    @endforelse
</div>
@endsection

@push('scripts')
<script>
function copyLink() {
    const link = document.getElementById('clientLink');
    link.select();
    document.execCommand('copy');
    showSuccess('Link copied to clipboard!');
}
$(document).ready(function() {
    // Approve Concept
    $('.btn-approve-concept').on('click', function() {
        const id = $(this).data('id');
        const btn = $(this);
        
        Swal.fire({
            title: 'Approve Concept?',
            text: "This concept will be marked as approved and ready for shooting.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#26de81',
            confirmButtonText: 'Yes, approve it!'
        }).then((result) => {
            if (result.isConfirmed) {
                btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i>');
                $.post(`/concepts/${id}/approve`, {
                    _token: $('meta[name="csrf-token"]').attr('content')
                })
                .done(function(res) {
                    showSuccess(res.message || 'Concept approved successfully!');
                    setTimeout(() => location.reload(), 1000);
                })
                .fail(function() {
                    btn.prop('disabled', false).html('<i class="fa-solid fa-check me-1"></i>Approve');
                    showError('Failed to approve concept. Please try again.');
                });
            }
        });
    });

    // Reject Concept
    $('.btn-reject-concept').on('click', function() {
        const id = $(this).data('id');
        const btn = $(this);

        Swal.fire({
            title: 'Request Revision',
            text: 'Please provide feedback for the writer:',
            input: 'textarea',
            inputPlaceholder: 'Enter your remarks here...',
            showCancelButton: true,
            confirmButtonColor: '#eb4d4b',
            confirmButtonText: 'Submit Rejection',
            inputValidator: (value) => {
                if (!value) return 'Remarks are required for rejection!';
            }
        }).then((result) => {
            if (result.isConfirmed) {
                btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i>');
                $.post(`/concepts/${id}/reject`, {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    remarks: result.value
                })
                .done(function(res) {
                    showSuccess(res.message || 'Concept sent back for revision.');
                    setTimeout(() => location.reload(), 1000);
                })
                .fail(function() {
                    btn.prop('disabled', false).html('<i class="fa-solid fa-xmark me-1"></i>Reject');
                    showError('Failed to reject concept.');
                });
            }
        });
    });

    // Generate client link
    $('#generateLinkBtn').on('click', function() {
        const url = $(this).data('url');
        $.post(url, { _token: $('meta[name="csrf-token"]').attr('content') })
        .done(function(res) {
            Swal.fire({
                title: 'Client Shareable Link',
                html: `
                    <p class="text-muted small">Share this link with your client. Valid for <strong>${res.expires}</strong>.</p>
                    <div class="input-group mt-2">
                        <input type="text" id="clientLink" class="form-control" value="${res.link}" readonly>
                        <button class="btn btn-primary" onclick="copyLink()">
                            <i class="fa-solid fa-copy"></i> Copy
                        </button>
                    </div>
                `,
                showConfirmButton: false,
                showCloseButton: true,
            });
        });
    });
});

</script>
@endpush
