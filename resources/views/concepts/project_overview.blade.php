@extends('layouts.panel')

@section('title', 'Project Concepts – ' . $project->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('concepts.index') }}">Concepts</a></li>
    <li class="breadcrumb-item active">{{ $project->name }}</li>
@endsection

@section('content')

@php
    $allConcepts    = $project->concepts;
    $totalRequired  = $project->conceptTasks->sum('concepts_required');
    $submitted      = $allConcepts->count();
    $approved       = $allConcepts->where('status', 'approved')->count();
    $rejected       = $allConcepts->where('status', 'rejected')->count();
    $pending        = $allConcepts->whereIn('status', ['draft', 'client_review'])->count();
    $pct            = $totalRequired > 0 ? round(($approved / $totalRequired) * 100) : 0;
@endphp

{{-- ── Page Header ──────────────────────────────────────── --}}
<div class="page-header">
    <div>
        <h1 class="page-title">{{ $project->name }}</h1>
        <p class="text-muted mb-0">Review and manage creative concepts for this project.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @role('Admin|Manager')
            <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-2"></i>Back to Project
            </a>
            <a href="{{ route('concepts.assign-form', $project) }}" class="btn btn-primary">
                <i class="fa-solid fa-plus me-2"></i>Add Concept Task
            </a>
        @endrole
        @role('Concept Writer')
            <a href="{{ route('concepts.index') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-2"></i>Back to Concepts
            </a>
        @endrole
        @if($project->conceptTasks->first())
        <button class="btn btn-success" id="generateLinkBtn"
            data-url="{{ route('concepts.generate-link', $project->conceptTasks->first()) }}">
            <i class="fa-solid fa-share-nodes me-2"></i>Share with Client
        </button>
        @endif
    </div>
</div>

{{-- ── Stats Row ─────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="co-stat-card">
            <div class="co-stat-label">TOTAL REQUIRED</div>
            <div class="co-stat-value text-dark">{{ $totalRequired }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="co-stat-card">
            <div class="co-stat-label" style="color:#45aaf2;">SUBMITTED</div>
            <div class="co-stat-value" style="color:#45aaf2;">{{ $submitted }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="co-stat-card">
            <div class="co-stat-label" style="color:#26de81;">APPROVED</div>
            <div class="co-stat-value" style="color:#26de81;">{{ $approved }}</div>
            <div class="co-stat-bar">
                <div class="co-stat-bar-fill" style="width:{{ $pct }}%;background:#26de81;"></div>
            </div>
            <div class="co-stat-pct">{{ $pct }}% of required</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="co-stat-card">
            <div class="co-stat-label" style="color:#eb4d4b;">REJECTED</div>
            <div class="co-stat-value" style="color:#eb4d4b;">{{ $rejected }}</div>
        </div>
    </div>
</div>

{{-- ── Filter Tabs + Controls ────────────────────────────── --}}
<div class="panel-card mb-3">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">

        {{-- Filter tabs --}}
        <div class="co-filter-tabs">
            <button class="co-tab active" data-filter="all">
                All <span class="co-tab-count">{{ $submitted }}</span>
            </button>
            <button class="co-tab" data-filter="client_review,draft">
                Pending <span class="co-tab-count" style="background:rgba(247,183,49,0.15);color:#c67c00;">{{ $pending }}</span>
            </button>
            <button class="co-tab" data-filter="approved">
                Approved <span class="co-tab-count" style="background:rgba(38,222,129,0.15);color:#1a8754;">{{ $approved }}</span>
            </button>
            <button class="co-tab" data-filter="rejected">
                Rejected <span class="co-tab-count" style="background:rgba(235,77,75,0.15);color:#eb4d4b;">{{ $rejected }}</span>
            </button>
        </div>

        {{-- Expand/Collapse all --}}
        <div class="d-flex gap-3 align-items-center">
            <span style="font-size:12px;color:#a0aec0;" id="visibleCount">{{ $submitted }} concepts</span>
            <button type="button" class="co-ctrl-btn" onclick="expandAll()">
                <i class="fa-solid fa-expand me-1"></i>Expand All
            </button>
            <button type="button" class="co-ctrl-btn" onclick="collapseAll()">
                <i class="fa-solid fa-compress me-1"></i>Collapse All
            </button>
        </div>
    </div>
</div>

{{-- ── Concept Cards ────────────────────────────────────── --}}
<div id="conceptsGrid">
    @forelse($allConcepts as $i => $concept)
    @php
        $statusClass = match($concept->status) {
            'approved'     => 'approved',
            'rejected'     => 'rejected',
            'client_review'=> 'review',
            default        => 'draft',
        };
        $statusColor = match($concept->status) {
            'approved'     => '#26de81',
            'rejected'     => '#eb4d4b',
            'client_review'=> '#45aaf2',
            default        => '#a0aec0',
        };
        // Approved starts collapsed, others open
        $isOpen = $concept->status !== 'approved';
    @endphp

    <div class="co-card {{ $statusClass }} {{ $isOpen ? 'open' : '' }}"
         data-status="{{ $concept->status }}">

        {{-- Always visible header --}}
        <div class="co-card-header" onclick="toggleConcept(this.closest('.co-card'))">
            <div class="d-flex align-items-center gap-3 flex-1 min-w-0">
                {{-- Number circle --}}
                <div class="co-num" style="background:{{ $statusColor }};">{{ $i + 1 }}</div>

                {{-- Title + meta --}}
                <div class="co-title-wrap">
                    <div class="co-title">{{ $concept->title }}
                        @if ($concept->is_review_reel)
                            <span class="badge" style="background:rgba(69,170,242,0.12);color:#1a6fa3;font-size:10px;">
                                <i class="fa-solid fa-user me-1"></i>{{ $concept->shootingConcept->shootingPerson->name ?? 'N/A' }}
                            </span>
                        @endif
                    </div>
                    <div class="co-meta">
                        <span>Concept #{{ $concept->sequence }}</span>
                        @if ($concept->is_review_reel)
                            <span class="badge" style="background:rgba(69,170,242,0.12);color:#1a6fa3;font-size:10px;">
                                <i class="fa-solid fa-video me-1"></i>Shooting concept
                            </span>
                        @endif
                        @if($concept->updated_at)
                            <span>·</span>
                            <span>{{ $concept->updated_at->diffForHumans() }}</span>
                        @endif
                        @if($concept->status === 'approved')
                            <span>·</span>
                            <span style="color:#26de81;"><i class="fa-solid fa-lock fa-xs me-1"></i>Locked</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                {!! $concept->status_badge !!}
                <i class="fa-solid fa-chevron-down co-chevron"></i>
            </div>
        </div>

        {{-- Collapsible body --}}
        <div class="co-card-body">
            <hr style="margin: 0 0 16px; border-color: #f0f2f8;">

            {{-- Concept script --}}
            @if($concept->description)
            <div class="co-script-box mb-3">
                <div class="co-script-label">
                    <i class="fa-solid fa-file-lines me-1"></i>Concept Script
                </div>
                <div class="co-script-content">{{ $concept->description }}</div>
            </div>
            @else
            <div class="co-empty-script mb-3">
                <i class="fa-solid fa-pen-to-square me-1 opacity-50"></i>
                No description submitted yet.
            </div>
            @endif

            {{-- Writer notes --}}
            @if($concept->writer_notes)
            <div class="co-info-box warning mb-3">
                <i class="fa-solid fa-comment-dots me-1 text-warning"></i>
                <strong>Writer Notes:</strong> {{ $concept->writer_notes }}
            </div>
            @endif

            {{-- Manager remarks --}}
            @if($concept->remarks)
            <div class="co-info-box danger mb-3">
                <i class="fa-solid fa-circle-exclamation me-1 text-danger"></i>
                <strong>Manager Remarks:</strong> {{ $concept->remarks }}
            </div>
            @endif

            {{-- Action buttons --}}
            @role('Admin|Manager')
            @if($concept->status !== 'approved')
            <div class="d-flex gap-2 mt-3 pt-3" style="border-top: 1px solid #f0f2f8;">
                <button class="btn btn-sm btn-success btn-approve-concept"
                    data-id="{{ $concept->id }}">
                    <i class="fa-solid fa-check me-1"></i>Approve
                </button>
                <button class="btn btn-sm btn-outline-danger btn-reject-concept"
                    data-id="{{ $concept->id }}">
                    <i class="fa-solid fa-xmark me-1"></i>Reject
                </button>
            </div>
            @else
            <div class="mt-3 pt-3 d-flex align-items-center gap-2" style="border-top:1px solid #f0f2f8;">
                <span style="font-size:12px;color:#26de81;">
                    <i class="fa-solid fa-circle-check me-1"></i>Approved & locked for shooting
                </span>
            </div>
            @endif
            @endrole
        </div>
    </div>
    @empty
    <div class="panel-card text-center py-5">
        <i class="fa-solid fa-lightbulb fa-4x text-muted opacity-25 mb-3 d-block"></i>
        <h5 class="text-muted">No concepts submitted yet</h5>
        <p class="text-muted small">Once concept tasks are completed, they will appear here.</p>
    </div>
    @endforelse

    {{-- Empty state for filter --}}
    <div id="filterEmpty" style="display:none;">
        <div class="panel-card text-center py-5">
            <i class="fa-solid fa-filter fa-3x text-muted opacity-25 mb-3 d-block"></i>
            <h5 class="text-muted">No concepts match this filter</h5>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ── Toggle single card ─────────────────────────────────────────
function toggleConcept(card) {
    const body   = card.querySelector('.co-card-body');
    const isOpen = card.classList.contains('open');
    if (isOpen) {
        $(body).slideUp(200, () => card.classList.remove('open'));
    } else {
        card.classList.add('open');
        $(body).hide().slideDown(200);
    }
}

// ── Expand all visible ─────────────────────────────────────────
function expandAll() {
    document.querySelectorAll('.co-card:not([style*="display: none"])').forEach(card => {
        if (!card.classList.contains('open')) {
            card.classList.add('open');
            $(card.querySelector('.co-card-body')).hide().slideDown(150);
        }
    });
}

// ── Collapse all visible ───────────────────────────────────────
function collapseAll() {
    document.querySelectorAll('.co-card:not([style*="display: none"])').forEach(card => {
        if (card.classList.contains('open')) {
            $(card.querySelector('.co-card-body')).slideUp(150, () => card.classList.remove('open'));
        }
    });
}

// ── Copy link ──────────────────────────────────────────────────
function copyLink() {
    const link = document.getElementById('clientLink');
    link.select();
    document.execCommand('copy');
    showSuccess('Link copied to clipboard!');
}

$(document).ready(function () {

    // ── Filter tabs ────────────────────────────────────────────
    $('.co-tab').on('click', function () {
        $('.co-tab').removeClass('active');
        $(this).addClass('active');

        const filter = $(this).data('filter');
        const statuses = filter === 'all' ? null : filter.split(',');
        let visible = 0;

        $('.co-card').each(function () {
            const status = $(this).data('status');
            if (!statuses || statuses.includes(status)) {
                $(this).show();
                visible++;
            } else {
                $(this).hide();
            }
        });

        $('#visibleCount').text(visible + ' concept' + (visible !== 1 ? 's' : ''));
        $('#filterEmpty').toggle(visible === 0);
    });

    // ── Approve concept ────────────────────────────────────────
    $(document).on('click', '.btn-approve-concept', function () {
        const id  = $(this).data('id');
        const btn = $(this);

        Swal.fire({
            title: 'Approve Concept?',
            text: 'This concept will be marked as approved and ready for shooting.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#26de81',
            confirmButtonText: 'Yes, approve it!'
        }).then(result => {
            if (result.isConfirmed) {
                btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i>');
                $.post(`/concepts/${id}/approve`, {
                    _token: $('meta[name="csrf-token"]').attr('content')
                })
                .done(res => {
                    showSuccess(res.message || 'Concept approved!');
                    setTimeout(() => location.reload(), 1000);
                })
                .fail(() => {
                    btn.prop('disabled', false).html('<i class="fa-solid fa-check me-1"></i>Approve');
                    showError('Failed to approve. Please try again.');
                });
            }
        });
    });

    // ── Reject concept ─────────────────────────────────────────
    $(document).on('click', '.btn-reject-concept', function () {
        const id  = $(this).data('id');
        const btn = $(this);

        Swal.fire({
            title: 'Request Revision',
            text: 'Provide feedback for the writer:',
            input: 'textarea',
            inputPlaceholder: 'Enter your remarks here...',
            showCancelButton: true,
            confirmButtonColor: '#eb4d4b',
            confirmButtonText: 'Submit Rejection',
            inputValidator: v => { if (!v) return 'Remarks are required!'; }
        }).then(result => {
            if (result.isConfirmed) {
                btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i>');
                $.post(`/concepts/${id}/reject`, {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    remarks: result.value
                })
                .done(res => {
                    showSuccess(res.message || 'Concept sent back for revision.');
                    setTimeout(() => location.reload(), 1000);
                })
                .fail(() => {
                    btn.prop('disabled', false).html('<i class="fa-solid fa-xmark me-1"></i>Reject');
                    showError('Failed to reject concept.');
                });
            }
        });
    });

    // ── Generate client link ───────────────────────────────────
    $('#generateLinkBtn').on('click', function () {
        const url = $(this).data('url');
        $.post(url, { _token: $('meta[name="csrf-token"]').attr('content') })
        .done(res => {
            Swal.fire({
                title: 'Client Shareable Link',
                html: `
                    <p class="text-muted small">Share this link with your client. Valid for <strong>${res.expires}</strong>.</p>
                    <div class="input-group mt-2">
                        <input type="text" id="clientLink" class="form-control" value="${res.link}" readonly>
                        <button class="btn btn-primary" type="button" onclick="copyLink()">
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