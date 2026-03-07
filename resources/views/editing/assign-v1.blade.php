@extends('layouts.panel')
@section('title', 'Assign Editing')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('editing.index') }}">Editing</a></li>
    <li class="breadcrumb-item active">Assign</li>
@endsection
@section('content')
<div class="page-header">
    <h1 class="page-title">Assign Editing <span class="page-subtitle">Project: {{ $project->name }}</span></h1>
</div>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="panel-card">
            <form id="assignEditForm">
                @csrf
                <div class="row g-4">
                    <div class="col-md-8">
                        <label class="form-label">Task Title <span class="required">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. 5 Reels - Skin Care Campaign" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Total Videos to Edit <span class="required">*</span></label>
                        <input type="number" name="total_videos" class="form-control" min="1" value="1" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Assign To Editor <span class="required">*</span></label>
                        <select name="assigned_to" class="form-select select2" required>
                            <option value="">Select Editor</option>
                            @foreach($editors as $e)
                            <option value="{{ $e->id }}">{{ $e->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Link Approved Concept (Optional)</label>
                        <select name="concept_id" class="form-select select2">
                            <option value="">None / Multiple</option>
                            @foreach($project->approvedConcepts as $c)
                            <option value="{{ $c->id }}">{{ $c->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Link Shoot Schedule (Optional)</label>
                        <select name="shoot_schedule_id" class="form-select select2">
                            <option value="">Select Shoot</option>
                            @foreach($project->shootSchedules as $s)
                            <option value="{{ $s->id }}">{{ $s->shoot_date->format('d M') }} · {{ $s->location }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Editing Requirements / Style Notes</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Mention preferred fonts, music style, transitions, or hooks..."></textarea>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2 justify-content-end">
                    <a href="{{ route('editing.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary" id="saveBtn"><i class="fa-solid fa-film me-2"></i>Assign Editor</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$('#assignEditForm').on('submit', function(e) {
    e.preventDefault();
    const btn = $('#saveBtn').prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-2"></i>Assigning...');
    ajaxPost('{{ route('editing.assign', $project) }}', $(this).serialize(), function(res) {
        showSuccess(res.message);
        setTimeout(() => location.href = '{{ route('editing.index') }}', 1500);
    }, () => btn.prop('disabled',false).html('<i class="fa-solid fa-film me-2"></i>Assign Editor'));
});
</script>
@endpush
