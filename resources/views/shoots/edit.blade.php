@extends('layouts.panel')
@section('title', 'Edit Shoot')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('shoots.index') }}">Shoots</a></li>
    <li class="breadcrumb-item"><a href="{{ route('shoots.show', $shoot) }}">Details</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection
@section('styles')
<style>
    .concept-selection-card:hover { border-color: #6c3fc5 !important; box-shadow: 0 4px 12px rgba(108,63,197,0.1) !important; transform: translateY(-2px); }
    .concept-checkbox:checked + label { color: #6c3fc5 !important; }
</style>
@endsection
@section('content')
<div class="page-header">
    <h1 class="page-title">Edit Shoot <span class="page-subtitle">Project: {{ $project->name }}</span></h1>
</div>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="panel-card">
            <form id="editShootForm">
                @csrf
                @method('PUT')
                <div class="row g-4">
                    <div class="col-12">
                        <label class="form-label">Location <span class="required">*</span></label>
                        <input type="text" name="location" class="form-control" value="{{ $shoot->location }}" required>
                    </div>

                    <!-- Shooting Team Section -->
                    <div class="col-12 mt-4">
                        <div class="p-3 border rounded shadow-sm" style="background: rgba(108, 63, 197, 0.05); border-color: rgba(108, 63, 197, 0.2) !important;">
                            <h6 class="fw-bold text-primary mb-3"><i class="fa-solid fa-users-viewfinder me-2"></i>Shooting Team</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Main Shooting Person <span class="required">*</span></label>
                                    <select name="shooting_person_id" class="form-select select2" required>
                                        @foreach($shooters as $s)
                                        <option value="{{ $s->id }}" {{ $shoot->shooting_person_id == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">On-site Concept Writer</label>
                                    <select name="concept_writer_id" class="form-select select2">
                                        <option value="">None</option>
                                        @foreach($writers as $w)
                                        <option value="{{ $w->id }}" {{ $shoot->concept_writer_id == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Model Name</label>
                                    <input type="text" name="model_name" class="form-control" value="{{ $shoot->model_name }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Helper Name</label>
                                    <input type="text" name="helper_name" class="form-control" value="{{ $shoot->helper_name }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Anchor Team Section -->
                    <div class="col-12 mt-4">
                        <div class="p-3 border rounded shadow-sm" style="background: rgba(38, 222, 129, 0.05); border-color: rgba(38, 222, 129, 0.2) !important;">
                            <h6 class="fw-bold text-success mb-3"><i class="fa-solid fa-microphone-lines me-2"></i>Anchor Team</h6>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Assign Anchor Persons</label>
                                    @php $currentAnchors = $shoot->anchors->pluck('id')->toArray(); @endphp
                                    <select name="anchor_ids[]" class="form-select select2-multiple" multiple="multiple" data-placeholder="Select one or more anchors">
                                        @foreach($anchors as $a)
                                        <option value="{{ $a->id }}" {{ in_array($a->id, $currentAnchors) ? 'selected' : '' }}>{{ $a->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Shoot Date <span class="required">*</span></label>
                        <input type="text" name="shoot_date" class="form-control datepicker" value="{{ $shoot->shoot_date->format('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Planned Start Time</label>
                        <input type="time" name="planned_start_time" class="form-control" value="{{ $shoot->planned_start_time }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status <span class="required">*</span></label>
                        <select name="status" class="form-select select2" required>
                            <option value="scheduled" {{ $shoot->status == 'scheduled' ? 'selected' : '' }}>Upcoming</option>
                            <option value="in_progress" {{ $shoot->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="completed" {{ $shoot->status == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ $shoot->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold"><i class="fa-solid fa-list-check me-2 text-primary"></i>Select Concepts to Shoot & Assign Anchor</label>
                        <div class="row g-3">
                            @php $currentConcepts = $shoot->concepts->pluck('id')->toArray(); @endphp
                            @forelse($project->approvedConcepts as $c)
                            <div class="col-md-6">
                                <div class="concept-selection-card p-3 border rounded shadow-sm d-flex flex-column h-100 {{ in_array($c->id, $currentConcepts) ? 'border-primary' : '' }}" style="background:#fff; transition: all 0.2s ease;">
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <div class="form-check m-0">
                                            <input type="checkbox" name="concept_ids[]" value="{{ $c->id }}" id="c_{{ $c->id }}" 
                                                class="form-check-input concept-checkbox" style="width:20px; height:20px; cursor:pointer;"
                                                {{ in_array($c->id, $currentConcepts) ? 'checked' : '' }}>
                                        </div>
                                        <label for="c_{{ $c->id }}" class="fw-semibold mb-0 text-dark flex-grow-1" style="cursor:pointer; font-size:14px;">{{ $c->title }}</label>
                                        @if($c->is_review_reel)
                                            <span class="badge bg-soft-info text-info small" style="font-size:10px;">Review Reel</span>
                                        @endif
                                    </div>
                                    
                                    <div class="anchor-assignment-area mt-auto pt-2 border-top">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="fa-solid fa-microphone-lines text-muted" style="font-size:12px;"></i>
                                            <select name="concept_anchors[{{ $c->id }}]" class="form-select form-select-sm concept-anchor-select" 
                                                {{ $c->anchor_id ? 'disabled' : '' }}
                                                style="font-size:12px; border-radius:6px;">
                                                <option value="">Select Anchor</option>
                                                @if($c->anchor_id)
                                                    <option value="{{ $c->anchor_id }}" selected>{{ $c->anchor->name }}</option>
                                                @elseif(!empty($shoot->anchors))
                                                    @foreach($shoot->anchors as $sa)
                                                        <option value="{{ $sa->id }}">{{ $sa->name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        @if($c->anchor_id)
                                            <div class="text-muted mt-1" style="font-size:10px;">
                                                <i class="fa-solid fa-lock me-1"></i>Permanently assigned to {{ $c->anchor->name }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="col-12"><div class="alert alert-warning py-3 text-center mb-0"><i class="fa-solid fa-triangle-exclamation me-2"></i>No approved concepts found.</div></div>
                            @endforelse
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Shoot Notes / Requirements</label>
                        <textarea name="notes" class="form-control" rows="3">{{ $shoot->notes }}</textarea>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2 justify-content-end">
                    <a href="{{ route('shoots.show', $shoot) }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary" id="saveBtn"><i class="fa-solid fa-save me-2"></i>Update Shoot</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Sync Anchor Dropdowns
    const syncAnchorDropdowns = function() {
        const selectedAnchors = [];
        $('select[name="anchor_ids[]"] option:selected').each(function() {
            selectedAnchors.push({ id: $(this).val(), name: $(this).text() });
        });

        $('.concept-anchor-select').each(function() {
            if ($(this).is(':disabled')) return;

            const currentVal = $(this).val();
            let options = '<option value="">Select Anchor</option>';
            selectedAnchors.forEach(a => {
                options += `<option value="${a.id}" ${a.id == currentVal ? 'selected' : ''}>${a.name}</option>`;
            });
            $(this).html(options);
        });
    };

    $('select[name="anchor_ids[]"]').on('change', syncAnchorDropdowns);

    $('#editShootForm').on('submit', function(e) {
        e.preventDefault();
        const btn = $('#saveBtn').prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-2"></i>Updating...');
        ajaxPost('{{ route('shoots.update', $shoot) }}', $(this).serialize(), function(res) {
            showSuccess(res.message);
            setTimeout(() => location.href = '{{ route('shoots.show', $shoot) }}', 1500);
        }, () => btn.prop('disabled',false).html('<i class="fa-solid fa-save me-2"></i>Update Shoot'));
    });

    // Initial sync
    syncAnchorDropdowns();
});
</script>
@endpush
