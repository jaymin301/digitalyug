@extends('layouts.panel')
@section('title', 'Assign Editing')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('editing.index') }}">Editing</a></li>
    <li class="breadcrumb-item active">Assign</li>
@endsection
@section('content')
<div class="page-header mb-4">
    <div>
        <h1 class="page-title text-primary fw-bold mb-1">Assign Editing Task</h1>
        <p class="text-muted small mb-0">Setup a new video editing pipeline for this project.</p>
    </div>
</div>

<div class="row g-4">
    {{-- Project Summary Sidebar --}}
    <div class="col-lg-4 order-lg-2">
        <div class="panel-card border-0 shadow-sm" style="background: linear-gradient(135deg, #6c3fc5 0%, #4834d4 100%);">
            <div class="p-1">
                <h5 class="text-white fw-bold mb-3"><i class="fa-solid fa-circle-info me-2"></i>Project Summary</h5>
                <div class="mb-3">
                    <label class="text-white-50 small d-block mb-1 text-uppercase tracking-wider">Project Name</label>
                    <div class="text-white fw-semibold h5 mb-0">{{ $project->name }}</div>
                </div>
                <div class="mb-3">
                    <label class="text-white-50 small d-block mb-1 text-uppercase tracking-wider">Client</label>
                    <div class="text-white fw-semibold">{{ $project->lead->customer_name ?? 'N/A' }}</div>
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <label class="text-white-50 small d-block mb-1 text-uppercase tracking-wider">Approved Concepts</label>
                        <div class="text-white fw-bold h4 mb-0">{{ $project->approvedConcepts->count() }}</div>
                    </div>
                    <div class="col-6">
                        <label class="text-white-50 small d-block mb-1 text-uppercase tracking-wider">Stage</label>
                        <div class="badge bg-white text-primary px-2 py-1">{{ ucfirst($project->stage) }}</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="panel-card mt-4 border-start border-primary border-4">
            <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-lightbulb text-warning me-2"></i>Quick Tip</h6>
            <p class="text-muted small mb-0">Linking concepts helps editors understand the specific requirements for each video slot.</p>
        </div>
    </div>

    {{-- Assignment Form --}}
    <div class="col-lg-8 order-lg-1">
        <div class="panel-card shadow-sm border-0">
            <form id="assignEditForm">
                @csrf
                <div class="row g-4">
                    <div class="col-md-12">
                        <label class="form-label fw-bold text-dark">
                            <i class="fa-solid fa-heading text-primary me-2"></i>Task Title <span class="required">*</span>
                        </label>
                        <input type="text" name="title" class="form-control form-control-lg border-2" 
                            value="{{ $project->name }} - Editing" placeholder="e.g. 5 Reels - Skin Care Campaign" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold text-dark">
                            <i class="fa-solid fa-user-tag text-primary me-2"></i>Assign To Editor <span class="required">*</span>
                        </label>
                        <select name="assigned_to" class="form-select select2" data-placeholder="Choose an editor" required>
                            <option value=""></option>
                            @foreach($editors as $e)
                            <option value="{{ $e->id }}">{{ $e->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold text-dark">
                            <i class="fa-solid fa-video text-primary me-2"></i>Total Videos <span class="required">*</span>
                        </label>
                        <div class="input-group">
                            <input type="number" name="total_videos" class="form-control border-2" min="1" value="1" required>
                            <span class="input-group-text bg-light fw-bold text-muted">Videos</span>
                        </div>
                    </div>

                    {{-- <div class="col-md-12">
                        <label class="form-label fw-bold text-dark">
                            <i class="fa-solid fa-link text-primary me-2"></i>Link Approved Concepts
                            <span class="text-muted small fw-normal ms-1">(Optional — select multiple)</span>
                        </label>
                        <select name="concept_ids[]" class="select2-multiple" data-placeholder="Select Concepts to include in this task">
                            <option value=""></option>
                            @foreach($project->approvedConcepts as $c)
                                <option value="{{ $c->id }}" data-subtitle="{{ $c->client_allocation }}s">{{ $c->title }}</option>
                            @endforeach
                        </select>
                    </div> --}}
                    <div class="col-md-12">
                        <label class="form-label fw-bold text-dark">
                            <i class="fa-solid fa-link text-primary me-2"></i>Link Approved Concepts
                            <span class="text-muted small fw-normal ms-1">(Optional — select multiple)</span>
                        </label>
                        <select name="concept_ids[]" class="select2-multiple" data-placeholder="Select Concepts to include in this task">
                            <option value=""></option>
                            @foreach($project->approvedConcepts as $c)
                                @if(in_array($c->id, $assignedConceptIds))
                                    @php
                                        $editorName = $assignedConcepts[$c->id]?->editTask?->assignedTo?->name ?? 'Another Editor';
                                    @endphp
                                    <option
                                        value="{{ $c->id }}"
                                        disabled
                                        data-subtitle="Assigned to {{ $editorName }}"
                                        style="color: #aaa;"
                                    >
                                        {{ $c->title }} — 🔒 {{ $editorName }}
                                    </option>
                                @else
                                    <option
                                        value="{{ $c->id }}"
                                        data-subtitle="{{ $c->client_allocation }}s"
                                    >
                                        {{ $c->title }}
                                    </option>
                                @endif
                            @endforeach
                        </select>

                        @if(count($assignedConceptIds) > 0)
                        <div class="mt-2 d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">
                                <i class="fa-solid fa-lock me-1"></i>
                                {{ count($assignedConceptIds) }} concept(s) already assigned
                            </span>
                            <a href="#" class="small text-primary fw-semibold text-decoration-none"
                                data-bs-toggle="modal" data-bs-target="#assignedConceptsModal">
                                View Details →
                            </a>
                        </div>
                        @endif
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-bold text-dark">
                            <i class="fa-solid fa-calendar-check text-primary me-2"></i>Link Shoot Schedule
                            <span class="text-muted small fw-normal ms-1">(Optional)</span>
                        </label>
                        <select name="shoot_schedule_id" class="form-select select2" data-placeholder="Select a related shoot date">
                            <option value=""></option>
                            @foreach($project->shootSchedules as $s)
                            <option value="{{ $s->id }}">{{ $s->shoot_date->format('d M Y') }} · {{ $s->location }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold text-dark">
                            <i class="fa-solid fa-pen-nib text-primary me-2"></i>Editing Requirements / Style Notes
                        </label>
                        <textarea name="description" class="form-control border-2" rows="4" 
                            placeholder="Mention preferred fonts, music style, transitions, or hooks..."></textarea>
                    </div>
                </div>

                <div class="mt-5 d-flex gap-2 justify-content-end">
                    <a href="{{ route('editing.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary px-5 fw-bold" id="saveBtn">
                        <i class="fa-solid fa-paper-plane me-2"></i>Assign Task
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@if(count($assignedConceptIds) > 0)
<div class="modal fade" id="assignedConceptsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">

            <div class="modal-header border-0 pb-0" style="background:linear-gradient(135deg,#fff7e6,#fff);">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                        style="width:42px;height:42px;background:#fff3cd;">
                        <i class="fa-solid fa-lock text-warning fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0">Locked Concepts</h5>
                        <p class="text-muted small mb-0">Already assigned to an editing task</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body pt-3">
                <div class="d-flex flex-column gap-2">
                    @foreach($project->approvedConcepts as $c)
                        @if(in_array($c->id, $assignedConceptIds))
                            @php
                                $pivot     = $assignedConcepts[$c->id];
                                $linkedTask = $pivot?->editTask;
                                $editor    = $linkedTask?->assignedTo;
                            @endphp
                            <div class="d-flex align-items-start gap-3 p-3 rounded-3 border border-warning-subtle"
                                style="background:#fffdf5;">

                                {{-- Icon --}}
                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 mt-1"
                                    style="width:34px;height:34px;background:#fff3cd;">
                                    <i class="fa-solid fa-film text-warning small"></i>
                                </div>

                                {{-- Info --}}
                                <div class="flex-grow-1">
                                    <div class="fw-semibold text-dark small">{{ $c->title }}</div>
                                    <div class="text-muted" style="font-size:0.78rem;">
                                        Duration: {{ $c->client_allocation }}s
                                    </div>

                                    @if($editor)
                                    <div class="mt-1 d-flex align-items-center gap-1">
                                        <i class="fa-solid fa-user-pen text-muted" style="font-size:0.72rem;"></i>
                                        <span class="text-muted" style="font-size:0.78rem;">Editor:</span>
                                        <span class="fw-semibold text-dark" style="font-size:0.78rem;">{{ $editor->name }}</span>
                                    </div>
                                    @endif

                                    @if($linkedTask)
                                    <div class="mt-1 d-flex align-items-center gap-1 flex-wrap">
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle"
                                            style="font-size:0.71rem;">
                                            <i class="fa-solid fa-arrow-right me-1"></i>{{ $linkedTask->title }}
                                        </span>
                                        <span class="badge
                                            @if($linkedTask->status === 'pending') bg-warning-subtle text-warning border border-warning-subtle
                                            @elseif($linkedTask->status === 'in_progress') bg-info-subtle text-info border border-info-subtle
                                            @else bg-success-subtle text-success border border-success-subtle @endif"
                                            style="font-size:0.71rem;">
                                            {{ ucfirst(str_replace('_', ' ', $linkedTask->status)) }}
                                        </span>
                                    </div>
                                    @endif
                                </div>

                                {{-- Link --}}
                                @if($linkedTask)
                                <a href="{{ route('editing.show', $linkedTask) }}"
                                    class="btn btn-sm btn-outline-secondary flex-shrink-0"
                                    style="font-size:0.75rem;" target="_blank">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                </a>
                                @endif

                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary btn-sm px-4"
                    data-bs-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
$('#assignEditForm').on('submit', function(e) {
    e.preventDefault();
    const btn = $('#saveBtn').prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-2"></i>Assigning...');
    ajaxPost('{{ route('editing.assign', $project) }}', $(this).serialize(), function(res) {
        showSuccess(res.message);
        setTimeout(() => location.href = '{{ route('editing.index') }}', 1500);
    }, () => btn.prop('disabled', false).html('<i class="fa-solid fa-film me-2"></i>Assign Editor'));
});
</script>
@endpush