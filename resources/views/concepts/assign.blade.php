@extends('layouts.panel')
@section('title', 'Assign Concept Task')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('projects.show', $project) }}">Project</a></li>
    <li class="breadcrumb-item active">Assign Concepts</li>
@endsection
@section('content')
<div class="page-header">
    <h1 class="page-title">Assign Concepts <span class="page-subtitle">Project: {{ $project->name }}</span></h1>
</div>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="panel-card">
            <form id="assignForm">
                @csrf
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Assign To Writer <span class="required">*</span></label>
                        <select name="assigned_to" class="form-select select2" required>
                            <option value="">Select Writer</option>
                            @foreach($writers as $w)
                            <option value="{{ $w->id }}">{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">No. of Concepts <span class="required">*</span></label>
                        <input type="number" name="concepts_required" class="form-control" min="1" max="100" value="1" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Due Date</label>
                        <input type="text" name="due_date" class="form-control datepicker" placeholder="YYYY-MM-DD">
                    </div>
                    
                    <div class="col-12">
                        <label class="form-label">General Remarks / Assignment Details</label>
                        <textarea name="general_remarks" class="form-control" rows="3" placeholder="Enter specific requirements, theme, or guidelines..."></textarea>
                    </div>

                    <div class="col-12 mt-4 fst-italic text-muted small border-top pt-3">
                        Note: You can pre-define concept titles below or let the writer define them.
                    </div>

                    <div id="conceptArea" class="col-12">
                        {{-- Dynamic rows for concept stubs --}}
                    </div>

                    <div class="col-12">
                        <button type="button" class="btn-add-row" onclick="addConceptRow()">
                            <i class="fa-solid fa-plus me-2"></i>Add Concept Stub
                        </button>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2 justify-content-end">
                    <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary" id="saveBtn"><i class="fa-solid fa-paper-plane me-2"></i>Assign Task</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let conceptCount = 0;
function addConceptRow() {
    conceptCount++;
    const html = `
    <div class="dynamic-row mb-3" id="row_${conceptCount}">
        <span class="row-number">#${conceptCount}</span>
        <button type="button" class="btn-remove-row" onclick="removeRow(${conceptCount})"><i class="fa-solid fa-times"></i></button>
        <div class="row g-3">
            <div class="col-md-6">
                <input type="text" name="concepts[${conceptCount}][title]" class="form-control" placeholder="Concept Title (e.g. Skin Transformation)" required>
            </div>
            <div class="col-md-3">
                <select name="concepts[${conceptCount}][client_allocation]" class="form-select">
                    <option value="">Allocation</option>
                    <option value="Reel">Reel</option>
                    <option value="Post">Post</option>
                    <option value="Story">Story</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" name="concepts[${conceptCount}][remarks]" class="form-control" placeholder="Remarks/Topic">
            </div>
        </div>
    </div>`;
    $('#conceptArea').append(html);
}

function removeRow(id) { $(`#row_${id}`).remove(); }

$('#assignForm').on('submit', function(e) {
    e.preventDefault();
    const btn = $('#saveBtn').prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-2"></i>Assigning...');
    ajaxPost('{{ route('concepts.assign', $project) }}', $(this).serialize(), function(res) {
        showSuccess(res.message);
        setTimeout(() => location.href = '{{ route('projects.show', $project) }}', 1500);
    }, () => btn.prop('disabled',false).html('<i class="fa-solid fa-paper-plane me-2"></i>Assign Task'));
});
</script>
@endpush
