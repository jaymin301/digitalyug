@extends('layouts.panel')
@section('title', 'Editing Task Details')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('editing.index') }}">Editing</a></li>
    <li class="breadcrumb-item active">Details</li>
@endsection
@section('content')
<div class="page-header mb-4">
    <div>
        <h1 class="page-title text-primary fw-bold mb-1">
            <i class="fa-solid fa-film me-2"></i>{{ $editTask->title }}
        </h1>
        <div class="d-flex align-items-center gap-2">
            {!! $editTask->status_badge !!}
            <span class="text-muted small">Assigned on {{ $editTask->created_at->format('d M, Y') }}</span>
        </div>
    </div>
    @role('Admin|Manager')
        <div class="d-flex gap-2">
            <a href="{{ route('projects.show', $editTask->project_id) }}" class="btn btn-outline-primary btn-sm px-3">
                <i class="fa-solid fa-arrow-left me-1"></i>Back to Project
            </a>
        </div>
    @endrole
</div>

<div class="row g-4">
    <div class="col-lg-8">

        {{-- Progress Tracking Card --}}
        <div class="panel-card mb-4 overflow-hidden border-0 shadow-sm" style="background: #fff; border-top: 5px solid #6c3fc5 !important;">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="panel-card-title mb-0 fw-bold"><i class="fa-solid fa-chart-line text-primary me-2"></i>Overall Progress</h5>
                <div class="text-end">
                    <span class="h3 fw-bold text-primary mb-0" id="progressPercent">{{ $editTask->progress_percent }}%</span>
                    <div class="text-muted small text-uppercase tracking-wider">Target Met</div>
                </div>
            </div>

            <div class="progress progress-xl mb-4" style="height:14px; border-radius:10px; background: rgba(108,63,197,0.1);">
                <div class="progress-bar progress-bar-animated" id="mainProgress"
                    style="width:{{ $editTask->progress_percent }}%; background: linear-gradient(90deg, #6c3fc5 0%, #a29bfe 100%);"></div>
            </div>

            <div class="row text-center g-0 border-top mt-2">
                <div class="col-4 py-4 border-end">
                    <div class="h3 fw-bold mb-0 text-info" id="completedText">{{ $editTask->completed_count }}</div>
                    <div class="text-muted small fw-semibold">SUBMITTED</div>
                </div>
                <div class="col-4 py-4 border-end">
                    <div class="h3 fw-bold mb-0 text-success" id="approvedText">{{ $editTask->approved_videos }}</div>
                    <div class="text-muted small fw-semibold">APPROVED</div>
                </div>
                <div class="col-4 py-4">
                    <div class="h3 fw-bold mb-0 text-dark">{{ $editTask->total_videos }}</div>
                    <div class="text-muted small fw-semibold">TOTAL SLOTS</div>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════
                 VIDEO EDITOR VIEW
            ════════════════════════════════════════════════ --}}
            @role('Video Editor')
                @if($editTask->status !== 'approved')
                    <div class="mt-2 pt-4 border-top">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <h6 class="fw-bold text-dark mb-0">Your Workboard</h6>
                            <span class="text-muted small"><i class="fa-solid fa-info-circle me-1"></i>Mark items as completed as you finish them</span>
                        </div>

                        <div id="videoList">
                            @for($i = 0; $i < $editTask->total_videos; $i++)
                                @php $entry = $editTask->videoEntries->get($i); @endphp
                                @php $isAdminApproved = $entry && $entry->admin_status === 'approved'; @endphp
                                @php $isAdminRejected = $entry && $entry->admin_status === 'rejected'; @endphp

                                <div class="video-row d-flex align-items-center gap-3 p-3 rounded-4 mb-3 transition-all"
                                    style="border:1px solid {{ $isAdminApproved ? '#d1fae5' : ($isAdminRejected ? '#fee2e2' : '#f1f5f9') }};
                                        background:{{ $isAdminApproved ? '#f0fdf4' : ($isAdminRejected ? '#fff5f5' : '#fff') }};
                                        box-shadow: 0 2px 4px rgba(0,0,0,0.02);">

                                    <input type="hidden" class="v-id" value="{{ $entry->id ?? '' }}">

                                    {{-- Checkbox --}}
                                    <div class="form-check mb-0">
                                        <input type="checkbox" class="form-check-input v-status" 
                                            style="width:20px; height:20px; cursor:pointer;"
                                            id="video_{{ $i }}"
                                            {{ ($entry && $entry->status === 'completed') ? 'checked' : '' }}
                                            {{ ($editTask->status === 'review' || $isAdminApproved) ? 'disabled' : '' }}>
                                    </div>

                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <label for="video_{{ $i }}" class="fw-bold mb-0" 
                                                style="font-size:14px; cursor:{{ $isAdminApproved ? 'default' : 'pointer' }};">
                                                Video Slot {{ $i + 1 }}
                                            </label>
                                            @if($isAdminApproved)
                                                <span class="badge bg-success py-1 px-2" style="font-size:9px; border-radius:12px;">PASSED QC</span>
                                            @elseif($isAdminRejected)
                                                <span class="badge bg-danger py-1 px-2" style="font-size:9px; border-radius:12px;">FAILED QC</span>
                                            @endif
                                        </div>
                                        
                                        <select class="form-select form-select-sm v-concept border-0 p-0 text-muted bg-transparent select2 mb-2" 
                                            style="font-size:12px; box-shadow:none;"
                                            {{ ($editTask->status === 'review' || $isAdminApproved) ? 'disabled' : '' }}>
                                            <option value="">Select linked concept </option>
                                            @foreach($editTask->concepts as $c)
                                                <option value="{{ $c->id }}"
                                                    {{ ($entry && $entry->concept_id == $c->id) ? 'selected' : '' }}>
                                                    {{ $c->title }}
                                                </option>
                                            @endforeach
                                        </select>

                                        {{-- Editor Feedback (from Admin) --}}
                                        @if($entry && $entry->editor_feedback)
                                            <div class="mt-2 p-2 rounded-3 small animate__animated animate__fadeIn" 
                                                style="background: #fff5f5; border-left: 3px solid #ef4444; color: #991b1b;">
                                                <i class="fa-solid fa-comment-dots me-1"></i>
                                                <strong>Admin Feedback:</strong> {{ $entry->editor_feedback }}
                                            </div>
                                        @endif
                                    </div>

                                    <div class="text-end">
                                        @if($entry && $entry->admin_reviewed_by)
                                            <div class="text-muted mt-2" style="font-size:10px;">
                                                <i class="fa-solid fa-user-check me-1 text-success"></i>{{ $entry->adminReviewer->name }} · {{ $entry->admin_reviewed_at->format('d M, h:i A') }}
                                            </div>
                                        @elseif($entry && $entry->status === 'completed')
                                            <div class="mt-2">
                                                <span class="badge bg-info text-white py-1 px-2" style="font-size:10px; border-radius: 8px;">Awaiting Review</span>
                                            </div>
                                        @else
                                            <div class="mt-2">
                                                <span class="text-muted small fst-italic" style="font-size:10px;">Not started</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endfor
                        </div>

                        <button class="btn btn-primary mt-3 w-100 py-3 fw-bold shadow-sm" id="btnUpdate" onclick="saveProgress()">
                            <i class="fa-solid fa-cloud-arrow-up me-2"></i>Sync Progress to Server
                        </button>
                    </div>
                @endif
            @endrole

            {{-- ═══════════════════════════════════════════════
                 ADMIN / MANAGER VIEW
            ════════════════════════════════════════════════ --}}
            @role('Admin|Manager')
            <div class="mt-2 pt-4 border-top">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h6 class="fw-bold text-dark mb-0">Per-Video Audit</h6>
                    <span class="text-muted small">Quality check individual video slots</span>
                </div>

                <div id="adminVideoList">
                    @for($i = 0; $i < $editTask->total_videos; $i++)
                        @php $entry = $editTask->videoEntries->get($i); @endphp
                        @php $isCompleted  = $entry && $entry->status === 'completed'; @endphp
                        @php $adminStatus  = $entry->admin_status ?? 'pending'; @endphp

                        <div class="video-admin-row d-md-flex align-items-center gap-3 p-3 rounded-4 mb-3 transition-all"
                            id="adminRow_{{ $i }}"
                            style="border:1px solid
                                {{ $adminStatus === 'approved' ? '#d1fae5' : ($adminStatus === 'rejected' ? '#fee2e2' : '#f1f5f9') }};
                               background:
                                {{ $adminStatus === 'approved' ? '#f0fdf4' : ($adminStatus === 'rejected' ? '#fff5f5' : '#fff') }};
                                box-shadow: 0 2px 4px rgba(0,0,0,0.02);">

                            <div class="d-flex align-items-center flex-grow-1 gap-3 mb-3 mb-md-0">
                                <div class="flex-shrink-0">
                                    <div class="d-flex align-items-center justify-content-center rounded-3 bg-light" 
                                        style="width:40px; height:40px;">
                                        <span class="fw-bold text-primary">{{ $i + 1 }}</span>
                                    </div>
                                </div>

                                <div class="flex-grow-1" style="min-width: 150px;">
                                    <div class="fw-bold text-dark" style="font-size:14px;">Video Slot {{ $i + 1 }}</div>
                                    @if($entry && $entry->concept)
                                        <div class="text-muted" style="font-size:11px;">
                                            <i class="fa-solid fa-lightbulb text-warning me-1"></i>{{ $entry->concept->title }}
                                        </div>
                                    @else
                                        <div class="text-muted small fst-italic" style="font-size:11px;">No concept linked</div>
                                    @endif
                                </div>

                                <div class="text-start me-md-3">
                                    <div id="badge_{{ $i }}" class="mb-1">
                                        @if($adminStatus === 'approved')
                                            <span class="badge bg-success rounded-pill px-2" style="font-size:10px;">Approved</span>
                                        @elseif($adminStatus === 'rejected')
                                            <span class="badge bg-danger rounded-pill px-2" style="font-size:10px;">Rejected</span>
                                        @elseif($isCompleted)
                                            <span class="badge bg-info rounded-pill px-2" style="font-size:10px;">Pending Review</span>
                                        @else
                                            <span class="badge bg-light text-muted rounded-pill px-2" style="font-size:10px;">In Progress</span>
                                        @endif
                                    </div>
                                    <div id="reviewerInfo_{{ $i }}" class="text-muted" style="font-size:9px;">
                                        @if($entry && $entry->adminReviewer)
                                            <i class="fa-solid fa-user-check me-1 text-success"></i>{{ $entry->adminReviewer->name }} · {{ $entry->admin_reviewed_at->format('d M, h:i A') }}
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Action buttons --}}
                            <div class="d-flex gap-2 justify-content-end mt-2 mt-md-0" id="actionBtns_{{ $i }}">
                                @if($isCompleted && $adminStatus === 'pending' && $editTask->status !== 'approved')
                                    <button class="btn btn-sm btn-success rounded-3 px-3 fw-bold" 
                                        onclick="openAuditModal({{ $entry->id }}, 'approve', {{ $i }})"
                                        style="font-size: 11px;">
                                        <i class="fa-solid fa-check me-1"></i> Approve
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger rounded-3 px-3 fw-bold bg-white" 
                                        onclick="openAuditModal({{ $entry->id }}, 'reject', {{ $i }})"
                                        style="font-size: 11px;">
                                        <i class="fa-solid fa-xmark me-1"></i> Reject
                                    </button>
                                @endif
                                @if($entry && $adminStatus !== 'pending')
                                    <button class="btn btn-sm btn-light rounded-3" 
                                        data-feedback="{{ $entry->editor_feedback }}"
                                        data-note="{{ $entry->admin_internal_note }}"
                                        onclick="openAuditModal({{ $entry->id }}, '{{ $adminStatus }}', {{ $i }}, true, this)" 
                                        title="View Details">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endfor
                </div>
            </div>

            {{-- Overall task review section --}}
            {{-- @if($editTask->status === 'review')
                <div class="mt-4 p-4 rounded-4 bg-light border-0 shadow-sm">
                    <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-check-double text-primary me-2"></i>Final Decision</h6>
                    <textarea id="approvalNotes" class="form-control border-0 mb-3 p-3" rows="3"
                        style="background: #fff; border-radius:12px;"
                        placeholder="Add overall task feedback or revision instructions..."></textarea>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <button class="btn btn-success w-100 py-3 fw-bold rounded-3" onclick="approveTask()">
                                <i class="fa-solid fa-circle-check me-2"></i>Seal & Approve Task
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button class="btn btn-outline-danger w-100 py-3 fw-bold rounded-3 bg-white" onclick="requestRevision()">
                                <i class="fa-solid fa-rotate-left me-2"></i>Send Back for Revision
                            </button>
                        </div>
                    </div>
                </div>
            @elseif($editTask->status === 'approved')
                <div class="mt-4 p-3 rounded-4 text-center shadow-sm" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%);">
                    <div class="h4 text-white fw-bold mb-0">
                        <i class="fa-solid fa-circle-check me-2"></i>Task Completed Successfully
                    </div>
                    @if($editTask->approvedAt)
                        <div class="text-white-50 small">
                            Final approval granted on {{ $editTask->approvedAt->format('d M, Y \a\t h:i A') }}
                            by {{ $editTask->approvedBy->name ?? 'Admin' }}
                        </div>
                    @endif
                </div>
            @endif --}}
            @endrole
        </div>

        {{-- Linked Concepts Card --}}
        @if($editTask->concepts->isNotEmpty())
            <div class="panel-card mb-4">
                <h5 class="panel-card-title mb-3">
                    <i class="fa-solid fa-lightbulb me-2"></i>Linked Concepts
                    <span class="badge ms-2" style="background:rgba(108,63,197,0.1);color:#6c3fc5;font-size:11px;border-radius:8px;">
                        {{ $editTask->concepts->count() }}
                    </span>
                </h5>

                <div class="accordion" id="linkedConceptsAccordion">
                    @foreach($editTask->concepts as $i => $concept)
                    <div class="accordion-item mb-2" style="border:1px solid #f0f2f8;border-radius:10px;overflow:hidden;">
                        <h2 class="accordion-header" id="lcHead{{ $i }}">
                            <button class="accordion-button collapsed d-flex align-items-center gap-2 py-2 px-3"
                                type="button" data-bs-toggle="collapse" data-bs-target="#lcBody{{ $i }}"
                                aria-expanded="false"
                                style="background:#fafbff;border-radius:10px;box-shadow:none;font-size:13px;">
                                <div class="d-flex align-items-center justify-content-center rounded-2 flex-shrink-0"
                                    style="width:28px;height:28px;background:rgba(108,63,197,0.08);">
                                    <i class="fa-solid fa-lightbulb" style="color:#6c3fc5;font-size:11px;"></i>
                                </div>
                                <div class="flex-grow-1 text-truncate fw-semibold text-dark">{{ $concept->title }}</div>
                                <div class="flex-shrink-0 me-2">{!! $concept->status_badge !!}</div>
                            </button>
                        </h2>
                        <div id="lcBody{{ $i }}" class="accordion-collapse collapse"
                            aria-labelledby="lcHead{{ $i }}" data-bs-parent="#linkedConceptsAccordion">
                            <div class="accordion-body pt-2 pb-3 px-3" style="background:#fff;font-size:13px;">
                                @if($concept->description)
                                    <div class="text-muted mb-3" style="line-height:1.6;white-space:pre-wrap;">{{ $concept->description }}</div>
                                @else
                                    <div class="text-muted fst-italic mb-3">No description provided.</div>
                                @endif
                                <div class="d-flex flex-wrap gap-2">
                                    @if($concept->client_allocation)
                                    <span style="font-size:11px;padding:2px 10px;border-radius:8px;background:rgba(69,170,242,0.1);color:#1a6fa3;">
                                        <i class="fa-solid fa-clock me-1"></i>{{ $concept->client_allocation }}s
                                    </span>
                                    @endif
                                    @if($concept->remarks)
                                    <span style="font-size:11px;padding:2px 10px;border-radius:8px;background:rgba(247,183,49,0.1);color:#c67c00;">
                                        <i class="fa-solid fa-comment me-1"></i>{{ Str::limit($concept->remarks, 40) }}
                                    </span>
                                    @endif
                                    @if($concept->writer_notes)
                                    <span style="font-size:11px;padding:2px 10px;border-radius:8px;background:rgba(108,63,197,0.08);color:#6c3fc5;">
                                        <i class="fa-solid fa-pen me-1"></i>{{ Str::limit($concept->writer_notes, 40) }}
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Task Details / Editing Requirements --}}
        <div class="panel-card mt-4">
            <div class="d-md-flex align-items-center justify-content-between mb-4 gap-3">
                <h5 class="panel-card-title mb-3 mb-md-0"><i class="fa-solid fa-file-lines"></i> Editing Requirements</h5>
                @role('Admin|Manager')
                <button class="btn btn-sm btn-primary px-3 rounded-3 fw-bold w-md-auto" onclick="saveDescription()">
                    <i class="fa-solid fa-floppy-disk me-2"></i>Save Requirements
                </button>
                @endrole
            </div>

            @role('Admin|Manager')
                <textarea id="taskDescription" class="form-control border-0 p-3" rows="6" 
                    style="background: #f8fafc; border-radius: 12px; font-size: 14px; line-height: 1.6;"
                    placeholder="Enter detailed editing requirements here...">{{ $editTask->description }}</textarea>
            @else
                <div class="p-4 rounded-4 bg-light" style="line-height:1.6; white-space:pre-wrap; font-size: 14px; color: #475569;">{{ $editTask->description ?: 'No specific style notes provided.' }}</div>
            @endrole

            @role('Admin|Manager')
                @if($editTask->approval_notes)
                <div class="mt-4 p-4 rounded-3 border-start border-4 border-warning" style="background: #fffbeb;">
                    <div class="fw-bold text-warning-emphasis mb-2"><i class="fa-solid fa-comment-dots me-2"></i>Internal Admin Notes</div>
                    <div class="small text-muted">{{ $editTask->approval_notes }}</div>
                </div>
                @endif
            @endrole
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="col-lg-4">
        <div class="panel-card mb-4 border-top border-4 border-primary">
            <h5 class="panel-card-title mb-4"><i class="fa-solid fa-desktop"></i> Work Context</h5>
            <div class="mb-3">
                <label class="form-label text-muted small">ASSIGNED EDITOR</label>
                <div class="fw-bold d-flex align-items-center gap-2">
                    <div style="width:24px;height:24px;border-radius:50%;background:rgba(108,63,197,0.1);color:#6c3fc5;
                        display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;">
                        {{ substr($editTask->assignedTo->name, 0, 1) }}
                    </div>
                    {{ $editTask->assignedTo->name }}
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label text-muted small">PROJECT</label>
                <div class="fw-bold">{{ $editTask->project->name }}</div>
            </div>

            {{-- Video approval summary in sidebar --}}
            <div class="mb-3">
                <label class="form-label text-muted small">VIDEO REVIEW STATUS</label>
                <div class="mt-1">
                    @php
                        $approvedVids  = $editTask->videoEntries->where('admin_status','approved')->count();
                        $rejectedVids  = $editTask->videoEntries->where('admin_status','rejected')->count();
                        $pendingVids   = $editTask->total_videos - $approvedVids - $rejectedVids;
                    @endphp
                    <div class="d-flex gap-2 flex-wrap">
                            <span style="font-size:11px;padding:3px 10px;border-radius:8px;background:#d1fae5;color:#065f46;font-weight:600;">
                                <i class="fa-solid fa-check me-1"></i>{{ $approvedVids }} Approved
                            </span>
                        @if($rejectedVids > 0)
                            <span style="font-size:11px;padding:3px 10px;border-radius:8px;background:#fee2e2;color:#991b1b;font-weight:600;">
                                <i class="fa-solid fa-xmark me-1"></i>{{ $rejectedVids }} Rejected
                            </span>
                        @endif
                        @if($pendingVids > 0)
                            <span style="font-size:11px;padding:3px 10px;border-radius:8px;background:#f1f5f9;color:#64748b;font-weight:600;">
                                <i class="fa-solid fa-hourglass me-1"></i>{{ $pendingVids }} Pending
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label text-muted small">LINKED CONCEPTS</label>
                @if($editTask->concepts->isNotEmpty())
                    <div class="d-flex flex-wrap gap-1 mt-1">
                        @foreach($editTask->concepts as $concept)
                            <span style="font-size:11px;padding:3px 10px;border-radius:8px;background:rgba(108,63,197,0.08);
                                color:#6c3fc5;font-weight:600;border:1px solid rgba(108,63,197,0.15);">
                                {{ $concept->title }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <div class="text-muted small fst-italic">None linked</div>
                @endif
            </div>

            <div class="mt-4 pt-3 border-top">
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label text-muted small">ASSIGNED BY</label>
                        <div class="fw-bold small">{{ $editTask->assignedBy->name ?? 'System' }}</div>
                    </div>
                    <div class="col-6">
                        <label class="form-label text-muted small">ASSIGNED ON</label>
                        <div class="fw-bold small">{{ $editTask->created_at->format('d M Y') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// ─── Editor: Save Progress ───────────────────────────────────────────────────
function saveProgress() {
    const videos = [];
    $('#videoList .video-row').each(function() {
        videos.push({
            id:         $(this).find('.v-id').val() || null,
            status:     $(this).find('.v-status').is(':checked') ? 'completed' : 'pending',
            concept_id: $(this).find('.v-concept').val() || null,
        });
    });

    const btn = $('#btnUpdate').prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-2"></i>Saving...');

    $.ajax({
        url: '{{ route('editing.update-count', $editTask) }}',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ _token: '{{ csrf_token() }}', videos }),
        success(res) {
            showSuccess('Progress updated!');
            $('#mainProgress').css('width', res.progress + '%');
            $('#progressPercent').text(res.progress + '%');
            $('#completedText').text(res.completed);
            setTimeout(() => location.reload(), 1000);
        },
        error() {
            btn.prop('disabled', false).html('<i class="fa-solid fa-floppy-disk me-2"></i>Save Progress');
        }
    });
}

// ─── Admin: Save Global Description ──────────────────────────────────────────
function saveDescription() {
    const desc = $('#taskDescription').val();
    const btn = $('button[onclick="saveDescription()"]').prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-2"></i>Saving...');
    
    $.ajax({
        url: '{{ route('editing.update-description', $editTask) }}',
        method: 'POST',
        data: { _token: '{{ csrf_token() }}', description: desc },
        success(res) {
            showSuccess(res.message);
            btn.prop('disabled', false).html('<i class="fa-solid fa-floppy-disk me-2"></i>Save Requirements');
        },
        error() {
            showError('Failed to update requirements.');
            btn.prop('disabled', false).html('<i class="fa-solid fa-floppy-disk me-2"></i>Save Requirements');
        }
    });
}

// ─── Admin: Per-video Audit Modal ────────────────────────────────────────────
function openAuditModal(videoId, action, rowIndex, viewOnly = false, btn = null) {
    $('#auditVideoId').val(videoId);
    $('#auditRowIndex').val(rowIndex);
    $('#auditAction').val(action);
    
    const isApprove = action === 'approve';
    const isReject = action === 'reject';
    
    // Set text based on action
    if (viewOnly) {
        $('#auditModalTitle').text('Performance Details');
        $('#auditModalSub').text('Reviewing stored feedback and notes');
    } else {
        $('#auditModalTitle').text(isApprove ? 'Approve Video' : 'Reject Video');
        $('#auditModalSub').text('Reviewing video slot performance');
    }

    // Toggle button visibility
    if (viewOnly) {
        $('#btnAuditSubmit').addClass('d-none');
    } else {
        $('#btnAuditSubmit').removeClass('d-none btn-success btn-danger')
            .addClass(isApprove ? 'btn-success' : 'btn-danger')
            .text(isApprove ? 'Confirm Approval' : 'Confirm Rejection');
    }
    
    // Load values
    let feedback = '';
    let adminNote = '';
    
    if (btn) {
        feedback = $(btn).data('feedback') || '';
        adminNote = $(btn).data('note') || '';
    }
    
    $('#auditEditorFeedback').val(feedback).prop('readonly', viewOnly);
    $('#auditAdminNote').val(adminNote).prop('readonly', viewOnly);
    
    const modal = new bootstrap.Modal(document.getElementById('auditModal'));
    modal.show();
}

function submitAudit() {
    const videoId = $('#auditVideoId').val();
    const rowIndex = $('#auditRowIndex').val();
    const action = $('#auditAction').val();
    const feedback = $('#auditEditorFeedback').val();
    const adminNote = $('#auditAdminNote').val();
    const isApprove = action === 'approve';

    if (!isApprove && !feedback.trim()) {
        return Swal.fire('Error', 'Editor feedback is required when rejecting.', 'error');
    }

    const url = isApprove
        ? `/editing/{{ $editTask->id }}/video/${videoId}/approve`
        : `/editing/{{ $editTask->id }}/video/${videoId}/reject`;

    const $btn = $('#btnAuditSubmit').prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-2"></i>Processing...');

    $.ajax({
        url,
        method: 'POST',
        data: { 
            _token: '{{ csrf_token() }}',
            editor_feedback: feedback,
            admin_internal_note: adminNote
        },
        success(res) {
            bootstrap.Modal.getInstance(document.getElementById('auditModal')).hide();
            
            // Update UI
            const $badgeContainer = $(`#badge_${rowIndex}`);
            const $adminRow       = $(`#adminRow_${rowIndex}`);
            
            if (res.admin_status === 'approved') {
                $badgeContainer.html('<span class="badge bg-success rounded-pill px-2" style="font-size:10px;">Approved</span>');
                $adminRow.css({ border: '1px solid #d1fae5', background: '#f0fdf4' });
            } else {
                $badgeContainer.html('<span class="badge bg-danger rounded-pill px-2" style="font-size:10px;">Rejected</span>');
                $adminRow.css({ border: '1px solid #fee2e2', background: '#fff5f5' });
            }

            $(`#reviewerInfo_${rowIndex}`).text(`${res.reviewer_name} · ${res.reviewed_at}`);
            showSuccess(res.message);

            if (res.progress !== undefined) {
                $('#mainProgress').css('width', res.progress + '%');
                $('#progressPercent').text(res.progress + '%');
                $('#completedText').text(res.completed);
                $('#approvedText').text(res.approved_count);
            }
            
            if (res.task_auto_approved) {
                setTimeout(() => location.reload(), 1200);
            } else {
                // Instead of "Updated", show the "View Details" button with updated data
                const viewBtn = `
                    <button class="btn btn-sm btn-light rounded-3" 
                        data-feedback="${feedback}"
                        data-note="${adminNote}"
                        onclick="openAuditModal(${videoId}, '${res.admin_status}', ${rowIndex}, true, this)" 
                        title="View Details">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                `;
                $(`#actionBtns_${rowIndex}`).html(viewBtn);
            }
        },
        error() {
            $btn.prop('disabled', false).text('Confirm Action');
            showError('Action failed.');
        }
    });
}

// ─── Admin: Whole-task approve / revision ────────────────────────────────────
function approveTask() {
    const notes = $('#approvalNotes').val();
    ajaxPost('{{ route('editing.approve', $editTask) }}', { approval_notes: notes }, function(res) {
        showSuccess(res.message);
        setTimeout(() => location.reload(), 1500);
    });
}

function requestRevision() {
    const notes = $('#approvalNotes').val().trim();
    if (!notes) return Swal.fire('Error', 'Please provide feedback/reasons for revision', 'error');
    ajaxPost('{{ route('editing.revision', $editTask) }}', { approval_notes: notes }, function(res) {
        showSuccess(res.message);
        setTimeout(() => location.reload(), 1500);
    });
}
</script>
@endpush

{{-- ── Audit Modal ────────────────────────────────────────────────────────── --}}
@role('Admin|Manager')
<div class="modal fade" id="auditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="auditModalTitle">Audit Video Slot</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="text-muted small mb-3" id="auditModalSub">Reviewing video slot performance</p>
                
                <input type="hidden" id="auditVideoId">
                <input type="hidden" id="auditRowIndex">
                <input type="hidden" id="auditAction">

                <div class="mb-3">
                    <label class="form-label fw-semibold" style="font-size:13px;">Editor Feedback</label>
                    <textarea id="auditEditorFeedback" class="form-control shadow-none" rows="3" 
                        placeholder="Write feedback for the editor (visible to editor)..."
                        style="font-size:13px; border-radius:10px;"></textarea>
                </div>

                <div class="mb-0">
                    <label class="form-label fw-semibold" style="font-size:13px;">Admin Internal Note <span class="badge bg-secondary text-white ms-2" style="font-size:9px; font-weight: 500;">INTERNAL</span></label>
                    <textarea id="auditAdminNote" class="form-control shadow-none" rows="2" 
                        placeholder="Internal notes for managers only..."
                        style="font-size:13px; border-radius:10px; background: #f8fafc; border: 1px dashed #cbd5e1;"></textarea>
                </div>
            </div>
            <div class="modal-footer border-top-0 pt-0 pb-4 justify-content-center">
                <button type="button" class="btn btn-light rounded-3 px-4 fw-semibold" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary rounded-3 px-4 fw-bold shadow-sm" id="btnAuditSubmit" onclick="submitAudit()">
                    Confirm Action
                </button>
            </div>
        </div>
    </div>
</div>
@endrole
