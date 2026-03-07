@extends('layouts.panel')
@section('title', 'Editing Task Details')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('editing.index') }}">Editing</a></li>
    <li class="breadcrumb-item active">Details</li>
@endsection
@section('content')
<div class="page-header">
    <h1 class="page-title">Edit Task: {{ $editTask->title }} <span class="page-subtitle">{!! $editTask->status_badge !!}</span></h1>
    @role('Admin|Manager')
        <div class="d-flex gap-2">
            <a href="{{ route('projects.show', $editTask->project_id) }}" class="btn btn-outline-secondary">Go to Project</a>
        </div>
    @endrole
</div>

<div class="row g-4">
    <div class="col-lg-8">
        {{-- Progress Tracking Card --}}
        <div class="panel-card mb-4 overflow-hidden" style="border-top: 5px solid #6c3fc5;">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="panel-card-title"><i class="fa-solid fa-bars-progress"></i> Completion Progress</h5>
                <span class="h4 fw-bold text-primary mb-0">{{ $editTask->progress_percent }}%</span>
            </div>

            <div class="progress progress-lg mb-4" style="height:12px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated" id="mainProgress" style="width:{{ $editTask->progress_percent }}%"></div>
            </div>

            <div class="row text-center g-3 border-top pt-4">
                <div class="col-6">
                    <div class="h3 fw-bold mb-0" id="completedText">{{ $editTask->completed_count }}</div>
                    <div class="text-muted small">VIDEOS COMPLETED</div>
                </div>
                <div class="col-6 border-start">
                    <div class="h3 fw-bold mb-0">{{ $editTask->total_videos }}</div>
                    <div class="text-muted small">TOTAL REQUIRED</div>
                </div>
            </div>

            @role('Video Editor')
            @if($editTask->status !== 'approved')
            <div class="mt-4 pt-4 border-top">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <label class="form-label fw-bold mb-0">Video Progress</label>
                    <span class="text-muted small">Mark each video and select its concept</span>
                </div>

                <div id="videoList">
                    @for($i = 0; $i < $editTask->total_videos; $i++)
                        @php $entry = $editTask->videoEntries->get($i); @endphp
                        <div class="video-row d-flex align-items-center gap-2 p-2 rounded-3 mb-2"
                            style="border:1px solid #f0f2f8;background:#fafbff;">

                            {{-- Checkbox --}}
                            <input type="hidden" class="v-id" value="{{ $entry->id ?? '' }}">
                            <div class="form-check mb-0">
                                <input type="checkbox" class="form-check-input v-status"
                                    id="video_{{ $i }}"
                                    {{ ($entry && $entry->status === 'completed') ? 'checked' : '' }}
                                    {{ $editTask->status === 'review' ? 'disabled' : '' }}>
                            </div>

                            {{-- Label --}}
                            <label for="video_{{ $i }}" class="fw-semibold mb-0 flex-shrink-0"
                                style="font-size:13px;min-width:60px;cursor:pointer;">
                                Video {{ $i + 1 }}
                            </label>

                            {{-- Concept Select --}}
                            <select class="form-select form-select-sm v-concept select2" data-placeholder="Select Concept"
                                style="font-size:12px;border-radius:8px;"
                                {{ $editTask->status === 'review' ? 'disabled' : '' }}>
                                <option value="">— concept —</option>
                                @foreach($editTask->concepts as $c)
                                    <option value="{{ $c->id }}"
                                        {{ ($entry && $entry->concept_id == $c->id) ? 'selected' : '' }}>
                                        {{ $c->title }}
                                    </option>
                                @endforeach
                            </select>

                            {{-- Status indicator --}}
                            @if($entry && $entry->status === 'completed')
                                <span class="badge bg-success flex-shrink-0" style="font-size:10px;">Done</span>
                            @else
                                <span class="badge bg-secondary flex-shrink-0" style="font-size:10px;">Pending</span>
                            @endif
                        </div>
                    @endfor
                </div>

                <button class="btn btn-primary mt-3 w-100" id="btnUpdate" onclick="saveProgress()">
                    <i class="fa-solid fa-floppy-disk me-2"></i>Save Progress
                </button>
            </div>
            @endif
            @endrole

            @role('Admin|Manager')
                @if($editTask->status === 'review')
                    <div class="mt-4 pt-4 border-top">
                        <div class="alert alert-info py-2 small mb-3"><i class="fa-solid fa-circle-info me-2"></i>Review this work and provide feedback.</div>
                        <div class="row g-2">
                            <div class="col-12 mb-2">
                                <textarea id="approvalNotes" class="form-control" rows="3" placeholder="Approval notes or Revision feedback..."></textarea>
                            </div>
                            <div class="col-md-6">
                                <button class="btn btn-success w-100" onclick="approveTask()"><i class="fa-solid fa-check-double me-2"></i>Approve Design</button>
                            </div>
                            <div class="col-md-6">
                                <button class="btn btn-outline-danger w-100" onclick="requestRevision()"><i class="fa-solid fa-rotate-left me-2"></i>Request Revision</button>
                            </div>
                        </div>
                    </div>
                @elseif($editTask->status === 'approved')
                    <div class="mt-4 p-3 rounded bg-success-light text-center">
                        <div class="h5 text-success fw-bold mb-1"><i class="fa-solid fa-circle-check me-2"></i>Task Approved</div>
                        @if($editTask->approvedAt)
                            <div class="small text-muted mb-0">Approved on {{ $editTask->approvedAt->format('d M, h:i A') }} by {{ $editTask->approvedBy->name ?? 'Admin' }}</div>
                        @endif
                    </div>
                @endif
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

                        {{-- Header --}}
                        <h2 class="accordion-header" id="lcHead{{ $i }}">
                            <button class="accordion-button collapsed d-flex align-items-center gap-2 py-2 px-3"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#lcBody{{ $i }}"
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

                        {{-- Body --}}
                        <div id="lcBody{{ $i }}" class="accordion-collapse collapse"
                            aria-labelledby="lcHead{{ $i }}"
                            data-bs-parent="#linkedConceptsAccordion">
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

        {{-- Linked Shoot Card --}}
        @if($editTask->shootSchedule)
        <div class="panel-card mb-4">
            <h5 class="panel-card-title mb-3">
                <i class="fa-solid fa-camera me-2"></i>Linked Shoot Schedule
            </h5>

            {{-- Shoot Info --}}
            <div class="d-flex align-items-center gap-3 p-3 rounded-3 mb-3"
                style="border:1px solid #f0f2f8;background:#fafbff;">
                <div class="d-flex align-items-center justify-content-center rounded-3 flex-shrink-0"
                    style="width:40px;height:40px;background:rgba(247,183,49,0.1);">
                    <i class="fa-solid fa-camera" style="color:#c67c00;font-size:16px;"></i>
                </div>
                <div class="flex-grow-1" style="min-width:0;">
                    <div class="fw-semibold text-dark" style="font-size:13px;">
                        {{ $editTask->shootSchedule->shoot_date->format('d M Y') }}
                        <span class="text-muted fw-normal">·</span>
                        {{ $editTask->shootSchedule->location }}
                    </div>
                    <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                        @if($editTask->shootSchedule->shootingPerson)
                        <span class="text-muted" style="font-size:11px;">
                            <i class="fa-solid fa-user me-1"></i>{{ $editTask->shootSchedule->shootingPerson->name }}
                        </span>
                        @endif
                        <span style="font-size:11px;">{!! $editTask->shootSchedule->status_badge !!}</span>
                        <span style="font-size:11px;padding:2px 8px;border-radius:10px;background:rgba(69,170,242,0.1);color:#1a6fa3;">
                            <i class="fa-solid fa-film me-1"></i>{{ $editTask->shootSchedule->reels_shot }} reels
                        </span>
                    </div>
                </div>
            </div>

            {{-- Concepts shot in this shoot --}}
            @if($editTask->shootSchedule->concepts && $editTask->shootSchedule->concepts->isNotEmpty())
                <div class="mt-2">
                    <div class="text-muted small fw-semibold mb-2" style="letter-spacing:0.05em;">
                        CONCEPTS SHOT
                        <span class="ms-1" style="color:#6c3fc5;">( {{ $editTask->shootSchedule->concepts->count() }} )</span>
                    </div>

                    <div class="accordion" id="shootConceptsAccordion">
                        @foreach($editTask->shootSchedule->concepts as $i => $sc)
                        <div class="accordion-item mb-2" style="border:1px solid #f0f2f8;border-radius:10px;overflow:hidden;">

                            {{-- Header --}}
                            <h2 class="accordion-header" id="scHead{{ $i }}">
                                <button class="accordion-button collapsed d-flex align-items-center gap-2 py-2 px-3"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#scBody{{ $i }}"
                                    aria-expanded="false"
                                    style="background:#fafbff;border-radius:10px;box-shadow:none;font-size:13px;">

                                    {{-- Icon --}}
                                    <div class="d-flex align-items-center justify-content-center rounded-2 flex-shrink-0"
                                        style="width:28px;height:28px;background:rgba(108,63,197,0.08);">
                                        <i class="fa-solid fa-lightbulb" style="color:#6c3fc5;font-size:11px;"></i>
                                    </div>

                                    {{-- Title + badge --}}
                                    <div class="flex-grow-1 text-truncate fw-semibold text-dark">{{ $sc->title }}</div>
                                    <div class="flex-shrink-0 me-2">{!! $sc->status_badge !!}</div>
                                </button>
                            </h2>

                            {{-- Body --}}
                            <div id="scBody{{ $i }}" class="accordion-collapse collapse"
                                aria-labelledby="scHead{{ $i }}"
                                data-bs-parent="#shootConceptsAccordion">
                                <div class="accordion-body pt-2 pb-3 px-3" style="background:#fff;font-size:13px;">

                                    @if($sc->description)
                                    <div class="text-muted mb-3" style="line-height:1.6;white-space:pre-wrap;">{{ $sc->description }}</div>
                                    @else
                                    <div class="text-muted fst-italic mb-3">No description provided.</div>
                                    @endif

                                    <div class="d-flex flex-wrap gap-2">
                                        @if($sc->client_allocation)
                                        <span style="font-size:11px;padding:2px 10px;border-radius:8px;background:rgba(69,170,242,0.1);color:#1a6fa3;">
                                            <i class="fa-solid fa-clock me-1"></i>{{ $sc->client_allocation }}s
                                        </span>
                                        @endif
                                        @if($sc->remarks)
                                        <span style="font-size:11px;padding:2px 10px;border-radius:8px;background:rgba(247,183,49,0.1);color:#c67c00;">
                                            <i class="fa-solid fa-comment me-1"></i>{{ Str::limit($sc->remarks, 40) }}
                                        </span>
                                        @endif
                                        @if($sc->writer_notes)
                                        <span style="font-size:11px;padding:2px 10px;border-radius:8px;background:rgba(108,63,197,0.08);color:#6c3fc5;">
                                            <i class="fa-solid fa-pen me-1"></i>{{ Str::limit($sc->writer_notes, 40) }}
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="text-muted small fst-italic">
                    <i class="fa-solid fa-circle-info me-1"></i>No concepts linked to this shoot.
                </div>
            @endif
        </div>
        @endif

        {{-- Task Details --}}
        <div class="panel-card">
            <h5 class="panel-card-title mb-4"><i class="fa-solid fa-file-lines"></i> Editing Requirements</h5>
            @if($editTask->description)
                <div class="text-muted mb-4" style="line-height:1.6;white-space:pre-wrap;">{{ $editTask->description }}</div>
            @else
                <div class="text-muted fst-italic mb-4">No specific style notes provided.</div>
            @endif

            @if($editTask->approval_notes)
            <div class="p-4 rounded-3 bg-light border-start border-4 border-warning">
                <div class="fw-bold text-warning mb-2"><i class="fa-solid fa-comment-dots me-2"></i>Feedback from Manager</div>
                <div class="small text-muted">{{ $editTask->approval_notes }}</div>
            </div>
            @endif
        </div>
    </div>

    {{-- Detail Sidebar --}}
    <div class="col-lg-4">
        <div class="panel-card mb-4 border-top border-4 border-primary">
            <h5 class="panel-card-title mb-4"><i class="fa-solid fa-desktop"></i> Work Context</h5>
            <div class="mb-3">
                <label class="form-label text-muted small">ASSIGNED EDITOR</label>
                <div class="fw-bold d-flex align-items-center gap-2">
                    <div style="width:24px;height:24px;border-radius:50%;background:rgba(108,63,197,0.1);color:#6c3fc5;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;">
                        {{ substr($editTask->assignedTo->name, 0, 1) }}
                    </div>
                    {{ $editTask->assignedTo->name }}
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label text-muted small">PROJECT</label>
                <div class="fw-bold">{{ $editTask->project->name }}</div>
            </div>

            {{-- Concepts count in sidebar --}}
            <div class="mb-3">
                <label class="form-label text-muted small">LINKED CONCEPTS</label>
                @if($editTask->concepts->isNotEmpty())
                    <div class="d-flex flex-wrap gap-1 mt-1">
                        @foreach($editTask->concepts as $concept)
                            <span style="font-size:11px;padding:3px 10px;border-radius:8px;background:rgba(108,63,197,0.08);color:#6c3fc5;font-weight:600;border:1px solid rgba(108,63,197,0.15);">
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
        data: JSON.stringify({
            _token: '{{ csrf_token() }}',
            videos: videos
        }),
        success: function(res) {
            showSuccess('Progress updated!');
            $('#mainProgress').css('width', res.progress + '%');
            $('#completedText').text(res.completed);
            setTimeout(() => location.reload(), 1000);
        },
        error: function() {
            btn.prop('disabled', false).html('<i class="fa-solid fa-floppy-disk me-2"></i>Save Progress');
        }
    });
}

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

{{-- 
@push('scripts')
<script>
let currentCount = {{ $editTask->completed_count }};
const totalVideos = {{ $editTask->total_videos }};

function updateProgress(val) {
    const newVal = currentCount + val;
    if(newVal >= 0 && newVal <= totalVideos) {
        currentCount = newVal;
        $('#countDisplay').text(currentCount);
    }
}

function saveProgress() {
    const btn = $('#btnUpdate').prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-2"></i>Saving...');
    ajaxPost('{{ route('editing.update-count', $editTask) }}', { completed_count: currentCount }, function(res) {
        showSuccess('Progress updated!');
        $('#mainProgress').css('width', res.progress + '%');
        $('#completedText').text(currentCount);
        setTimeout(() => location.reload(), 1000);
    }, () => btn.prop('disabled', false).html('Save Progress'));
}

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
@endpush --}}