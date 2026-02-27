@extends('layouts.panel')
@section('title', 'Submit Concepts')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('concepts.index') }}">Concepts</a></li>
    <li class="breadcrumb-item active">Submit</li>
@endsection
@section('content')
<div class="page-header">
    <h1 class="page-title">Submit Concepts <span class="page-subtitle">Project: {{ $conceptTask->project->name }} · {{ $conceptTask->concepts_required }} Required</span></h1>
</div>

<div class="row justify-content-center">
    <div class="col-lg-11">
        <form id="submitForm">
            @csrf
            @role('Admin|Manager') @method('POST') @endrole {{-- Handled by submit route --}}

            @php $count = 0; @endphp
            @foreach($conceptTask->concepts as $c)
            @php $count++; @endphp
            <div class="panel-card mb-4">
                <div class="row g-3">
                    <input type="hidden" name="concepts[{{ $count }}][id]" value="{{ $c->id }}">
                    <div class="col-md-9">
                        <label class="form-label">Concept #{{ $count }} Title <span class="required">*</span></label>
                        <input type="text" name="concepts[{{ $count }}][title]" class="form-control" value="{{ $c->title }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <div class="pt-2">{!! $c->status_badge !!}</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description / Script <span class="required">*</span></label>
                        <textarea name="concepts[{{ $count }}][description]" class="form-control" rows="5" placeholder="Write the full script or concept description here..." required>{{ $c->description }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">My Notes for Manager</label>
                        <input type="text" name="concepts[{{ $count }}][writer_notes]" class="form-control" value="{{ $c->writer_notes }}" placeholder="Any context or suggestion...">
                    </div>
                </div>
            </div>
            @endforeach

            {{-- Handle missing concepts if fewer than required were pre-defined --}}
            @for($i = $count + 1; $i <= $conceptTask->concepts_required; $i++)
            <div class="panel-card mb-4" style="border-left: 4px solid #f7b731;">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Concept #{{ $i }} Title <span class="required">*</span></label>
                        <input type="text" name="concepts[{{ $i }}][title]" class="form-control" placeholder="New Concept Title" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description / Script <span class="required">*</span></label>
                        <textarea name="concepts[{{ $i }}][description]" class="form-control" rows="5" placeholder="Write the full script or concept description here..." required></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">My Notes</label>
                        <input type="text" name="concepts[{{ $i }}][writer_notes]" class="form-control" placeholder="Any context...">
                    </div>
                </div>
            </div>
            @endfor

            <div class="panel-card text-center py-4 bg-light">
                <i class="fa-solid fa-circle-info text-info mb-2 d-block"></i>
                <p class="text-muted small mb-3">Once submitted, the manager will review your concepts. You can still edit until they are locked for shooting.</p>
                <div class="d-flex gap-2 justify-content-center">
                    <a href="{{ route('concepts.index') }}" class="btn btn-outline-secondary">Discard Changes</a>
                    <button type="submit" class="btn btn-lg btn-primary" id="submitBtn"><i class="fa-solid fa-check-double me-2"></i>Submit Concepts for Review</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$('#submitForm').on('submit', function(e) {
    e.preventDefault();
    const btn = $('#submitBtn').prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-2"></i>Submitting...');
    ajaxPost('{{ route('concepts.submit', $conceptTask) }}', $(this).serialize(), function(res) {
        showSuccess(res.message);
        setTimeout(() => location.href = '{{ route('concepts.index') }}', 1500);
    }, () => btn.prop('disabled',false).html('<i class="fa-solid fa-check-double me-2"></i>Submit Concepts for Review'));
});
</script>
@endpush
