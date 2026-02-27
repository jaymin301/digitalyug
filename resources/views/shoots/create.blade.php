@extends('layouts.panel')
@section('title', 'Schedule Shoot')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('shoots.index') }}">Shoots</a></li>
    <li class="breadcrumb-item active">Schedule</li>
@endsection
@section('content')
<div class="page-header">
    <h1 class="page-title">Schedule Shoot <span class="page-subtitle">Project: {{ $project->name }}</span></h1>
</div>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="panel-card">
            <form id="shootForm">
                @csrf
                <div class="row g-4">
                    <div class="col-md-8">
                        <label class="form-label">Location <span class="required">*</span></label>
                        <input type="text" name="location" class="form-control" placeholder="e.g. Skin Transformation Clinic, Mumbai" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Shoot Date <span class="required">*</span></label>
                        <input type="text" name="shoot_date" class="form-control datepicker" placeholder="YYYY-MM-DD" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Assign Shooting Person <span class="required">*</span></label>
                        <select name="shooting_person_id" class="form-select select2" required>
                            <option value="">Select Shooter</option>
                            @foreach($shooters as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Planned Start Time</label>
                        <input type="time" name="planned_start_time" class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Concept Writer (On-site)</label>
                        <select name="concept_writer_id" class="form-select select2">
                            <option value="">None</option>
                            @foreach($writers as $w)
                            <option value="{{ $w->id }}">{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Model Name</label>
                        <input type="text" name="model_name" class="form-control" placeholder="Optional">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Helper Name</label>
                        <input type="text" name="helper_name" class="form-control" placeholder="Optional">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Select Concepts to Shoot</label>
                        <div class="row g-2">
                            @forelse($project->approvedConcepts as $c)
                            <div class="col-md-6 col-lg-4">
                                <div class="p-2 border rounded d-flex align-items-center gap-2" style="background:#f7f8ff;">
                                    <input type="checkbox" name="concept_ids[]" value="{{ $c->id }}" id="c_{{ $c->id }}" class="form-check-input mt-0">
                                    <label for="c_{{ $c->id }}" class="small mb-0 text-truncate" style="cursor:pointer;">{{ $c->title }}</label>
                                </div>
                            </div>
                            @empty
                            <div class="col-12"><div class="alert alert-warning py-2 small mb-0"><i class="fa-solid fa-triangle-exclamation me-2"></i>No approved concepts found for this project yet.</div></div>
                            @endforelse
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Shoot Notes / Requirements</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Enter special gear requirements or client preferences..."></textarea>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2 justify-content-end">
                    <a href="{{ route('shoots.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary" id="saveBtn"><i class="fa-solid fa-calendar-check me-2"></i>Schedule Shoot</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$('#shootForm').on('submit', function(e) {
    e.preventDefault();
    const btn = $('#saveBtn').prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-2"></i>Scheduling...');
    ajaxPost('{{ route('shoots.store', $project) }}', $(this).serialize(), function(res) {
        showSuccess(res.message);
        setTimeout(() => location.href = '{{ route('shoots.index') }}', 1500);
    }, () => btn.prop('disabled',false).html('<i class="fa-solid fa-calendar-check me-2"></i>Schedule Shoot'));
});
</script>
@endpush
