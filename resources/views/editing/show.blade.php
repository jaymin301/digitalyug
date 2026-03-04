@extends('layouts.panel')
@section('title', 'Editing Task Details')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('editing.index') }}">Editing</a></li>
    <li class="breadcrumb-item active">Details</li>
@endsection
@section('content')
<div class="page-header">
    <h1 class="page-title">Edit Task: {{ $editTask->title }} <span class="page-subtitle">{!! $editTask->status_badge !!}</span></h1>
    <div class="d-flex gap-2">
        <a href="{{ route('projects.show', $editTask->project_id) }}" class="btn btn-outline-secondary">Go to Project</a>
    </div>
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
            <div class="mt-4 p-3 rounded bg-light text-center">
                <label class="form-label d-block fw-bold mb-3">Update your progress:</label>
                <div class="d-flex align-items-center justify-content-center gap-3">
                    <button class="btn btn-outline-primary btn-sm" onclick="updateProgress(-1)"><i class="fa-solid fa-minus"></i></button>
                    <div id="countDisplay" class="h4 fw-bold mb-0 mx-2" style="min-width:40px;">{{ $editTask->completed_count }}</div>
                    <button class="btn btn-outline-primary btn-sm" onclick="updateProgress(1)"><i class="fa-solid fa-plus"></i></button>
                </div>
                <button class="btn btn-primary mt-3 w-100" id="btnUpdate" onclick="saveProgress()">Save Progress</button>
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
                        @if($editTask->approvedAt) <div class="small text-muted mb-0">Approved on {{ $editTask->approvedAt->format('d M, h:i A') }} by {{ $editTask->approvedBy->name ?? 'Admin' }}</div> @endif
                    </div>
                @endif
            @endrole
        </div>

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
                    <div style="width:24px;height:24px;border-radius:50%;background:rgba(108,63,197,0.1);color:#6c3fc5;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;">{{ substr($editTask->assignedTo->name,0,1) }}</div>
                    {{ $editTask->assignedTo->name }}
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label text-muted small">PROJECT</label>
                <div class="fw-bold">{{ $editTask->project->name }}</div>
            </div>
            @if($editTask->concept)
            <div class="mb-3">
                <label class="form-label text-muted small">LINKED CONCEPT</label>
                <div class="fw-bold text-primary">{{ $editTask->concept->title }}</div>
            </div>
            @endif
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
    }, () => btn.prop('disabled',false).html('Save Progress'));
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
    if(!notes) return Swal.fire('Error', 'Please provide feedback/reasons for revision', 'error');
    ajaxPost('{{ route('editing.revision', $editTask) }}', { approval_notes: notes }, function(res) {
        showSuccess(res.message);
        setTimeout(() => location.reload(), 1500);
    });
}
</script>
@endpush
