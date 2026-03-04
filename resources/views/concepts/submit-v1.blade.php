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
                @if($c->status === 'approved')
                    <div class="panel-card mb-3" 
                        style="border-left: 4px solid #26de81; cursor:pointer;"
                        onclick="toggleApproved(this)">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white"
                                    style="width:32px;height:32px;background:#26de81;font-size:13px;flex-shrink:0;">
                                    {{ $count }}
                                </div>
                                <div>
                                    <span class="fw-semibold" style="font-size:14px;">{{ $c->title }}</span>
                                    <span class="text-muted small ms-2">· Click to view</span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                {!! $c->status_badge !!}
                                <i class="fa-solid fa-chevron-down text-muted toggle-icon" style="font-size:11px;transition:transform 0.3s;"></i>
                            </div>
                        </div>
                        {{-- Expandable content --}}
                        <div class="approved-content" style="display:none;">
                            <hr class="my-3">
                            <div class="p-3 rounded-3 bg-light mb-2" style="font-size:13.5px;line-height:1.7;white-space:pre-wrap;border:1px dashed #e2e8f0;">{{ $c->description }}</div>
                            @if($c->writer_notes)
                            <div class="small p-2 rounded" style="background:rgba(247,183,49,0.08);border-left:3px solid #f7b731;">
                                <i class="fa-solid fa-comment-dots me-1 text-warning"></i>
                                <strong>Writer Notes:</strong> {{ $c->writer_notes }}
                            </div>
                            @endif
                        </div>
                        {{-- Hidden inputs - form submission ke liye --}}
                        <input type="hidden" name="concepts[{{ $count }}][id]" value="{{ $c->id }}">
                        <input type="hidden" name="concepts[{{ $count }}][title]" value="{{ $c->title }}">
                        <input type="hidden" name="concepts[{{ $count }}][description]" value="{{ $c->description }}">
                        <input type="hidden" name="concepts[{{ $count }}][writer_notes]" value="{{ $c->writer_notes }}">
                    </div>

                    @else
                        {{-- ✅ NON-APPROVED — Full editable card --}}
                        <div class="panel-card mb-4" style="
                            @if($c->status === 'rejected') border-left: 4px solid #eb4d4b;
                            @elseif($c->status === 'client_review') border-left: 4px solid #45aaf2;
                            @else border-left: 4px solid #e2e8f0;
                            @endif
                        ">
                            <div class="row g-3">
                                <input type="hidden" name="concepts[{{ $count }}][id]" value="{{ $c->id }}">
                                <div class="col-md-9">
                                    <label class="form-label">
                                        Concept #{{ $count }} Title
                                        @if($c->status === 'rejected')
                                            <span class="badge bg-danger ms-1" style="font-size:10px;">Revision Required</span>
                                        @endif
                                    </label>
                                    <input type="text" name="concepts[{{ $count }}][title]" class="form-control" value="{{ $c->title }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Status</label>
                                    <div class="pt-2">{!! $c->status_badge !!}</div>
                                </div>
                                @if($c->status === 'rejected' && $c->remarks)
                                <div class="col-12">
                                    <div class="p-2 rounded small" style="background:rgba(235,77,75,0.08);border-left:3px solid #eb4d4b;">
                                        <i class="fa-solid fa-circle-exclamation me-1 text-danger"></i>
                                        <strong>Manager Feedback:</strong> {{ $c->remarks }}
                                    </div>
                                </div>
                                @endif
                                @if($c->adjustment_suggestion)
                                <div class="col-12">
                                    <div class="p-2 rounded small" style="background:rgba(247,183,49,0.08);border-left:3px solid #f7b731;">
                                        <i class="fa-solid fa-lightbulb me-1 text-warning"></i>
                                        <strong>Suggestion:</strong> {{ $c->adjustment_suggestion }}
                                    </div>
                                </div>
                                @endif
                                <div class="col-12">
                                    <label class="form-label">Description / Script</label>
                                    <textarea name="concepts[{{ $count }}][description]" class="form-control" rows="5"
                                        placeholder="Write the full script or concept description here...">{{ $c->description }}</textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">My Notes for Manager</label>
                                    <input type="text" name="concepts[{{ $count }}][writer_notes]" class="form-control"
                                        value="{{ $c->writer_notes }}" placeholder="Any context or suggestion...">
                                </div>
                            </div>
                        </div>
                    @endif
                {{-- <div class="panel-card mb-4">
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
                            <textarea name="concepts[{{ $count }}][description]" class="form-control" rows="5" placeholder="Write the full script or concept description here...">{{ $c->description }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">My Notes for Manager</label>
                            <input type="text" name="concepts[{{ $count }}][writer_notes]" class="form-control" value="{{ $c->writer_notes }}" placeholder="Any context or suggestion...">
                        </div>
                    </div>
                </div> --}}
            @endforeach

            {{-- Handle missing concepts if fewer than required were pre-defined --}}
            @for($i = $count + 1; $i <= $conceptTask->concepts_required; $i++)
                <div class="panel-card mb-4" style="border-left: 4px solid #f7b731;">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Concept #{{ $i }} Title</label>
                            <input type="text" name="concepts[{{ $i }}][title]" class="form-control" placeholder="New Concept Title">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description / Script</label>
                            <textarea name="concepts[{{ $i }}][description]" class="form-control" rows="5" placeholder="Write the full script or concept description here..."></textarea>
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
                @php
                    $submittedConcepts = $conceptTask->concepts
                        ->whereNotNull('description')
                        ->whereIn('status', ['client_review', 'approved', 'rejected'])
                        ->count();
                    $remaining = $conceptTask->concepts_required - $submittedConcepts;
                @endphp

                {{-- Progress --}}
                <div class="mb-3">
                    <div class="d-flex justify-content-center gap-3 mb-2 flex-wrap">
                        <span class="badge" style="background:rgba(108,63,197,0.1);color:#6c3fc5;font-size:12px;padding:6px 14px;">
                            <i class="fa-solid fa-check me-1"></i>{{ $submittedConcepts }} Submitted
                        </span>
                        <span class="badge" style="background:rgba(247,183,49,0.1);color:#f7b731;font-size:12px;padding:6px 14px;">
                            <i class="fa-solid fa-clock me-1"></i>{{ $remaining }} Remaining
                        </span>
                        <span class="badge" style="background:rgba(38,222,129,0.1);color:#26de81;font-size:12px;padding:6px 14px;">
                            <i class="fa-solid fa-list me-1"></i>{{ $conceptTask->concepts_required }} Total
                        </span>
                    </div>
                    <div class="progress mx-auto" style="max-width:400px;height:8px;border-radius:10px;">
                        <div class="progress-bar" style="width:{{ $conceptTask->concepts_required > 0 ? ($submittedConcepts / $conceptTask->concepts_required) * 100 : 0 }}%;background:#6c3fc5;border-radius:10px;transition:width 0.3s;"></div>
                    </div>
                </div>

                <p class="text-muted small mb-3">You can save partially and come back later to complete remaining concepts.</p>

                <div class="d-flex gap-2 justify-content-center">
                    <a href="{{ route('concepts.index') }}" class="btn btn-outline-secondary">
                        <i class="fa-solid fa-arrow-left me-1"></i>Back
                    </a>
                    <button type="submit" class="btn btn-lg btn-primary" id="submitBtn">
                        <i class="fa-solid fa-check-double me-2"></i>
                        {{ $remaining > 0 ? 'Save Progress' : 'Submit All for Review' }}
                    </button>
                </div>
            </div>

            {{-- <div class="panel-card text-center py-4 bg-light">
                <i class="fa-solid fa-circle-info text-info mb-2 d-block"></i>
                <p class="text-muted small mb-3">Once submitted, the manager will review your concepts. You can still edit until they are locked for shooting.</p>
                <div class="d-flex gap-2 justify-content-center">
                    <a href="{{ route('concepts.index') }}" class="btn btn-outline-secondary">Discard Changes</a>
                    <button type="submit" class="btn btn-lg btn-primary" id="submitBtn"><i class="fa-solid fa-check-double me-2"></i>Submit Concepts for Review</button>
                </div>
            </div> --}}
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleApproved(card) {
    const content = card.querySelector('.approved-content');
    const icon = card.querySelector('.toggle-icon');
    const isOpen = content.style.display !== 'none';
    if (isOpen) {
        $(content).slideUp(200);
        icon.style.transform = 'rotate(0deg)';
    } else {
        $(content).slideDown(200);
        icon.style.transform = 'rotate(180deg)';
    }
}

$(document).ready(function() {
    $('#submitForm').on('submit', function(e) {
        e.preventDefault();
        const btn = $('#submitBtn');
        btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-2"></i>Saving...');
        ajaxPost('{{ route('concepts.submit', $conceptTask) }}', $(this).serialize(),
            function(res) {
                if (res.complete) {
                    showSuccess(res.message);
                    setTimeout(() => location.href = '{{ route('concepts.index') }}', 1500);
                } else {
                    showSuccess(res.message);
                    setTimeout(() => location.reload(), 1500);
                }
            },
            function() {
                btn.prop('disabled', false)
                   .html('<i class="fa-solid fa-check-double me-2"></i>Save Progress');
            }
        );
    });
});
</script>
@endpush

{{-- @push('scripts')
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
@endpush --}}
