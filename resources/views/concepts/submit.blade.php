@extends('layouts.panel')

@section('title', 'Submit Concepts')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('concepts.index') }}" class="text-decoration-none text-muted">Concepts</a></li>
    <li class="breadcrumb-item active">Submit</li>
@endsection

@php
    $totalRequired  = $remainingRequired + $writerConcepts->count();
    $existingConcepts = $writerConcepts;
    $approvedCount  = $writerConcepts->where('status', 'approved')->count();
    $rejectedCount = $existingConcepts->where('status', 'rejected')->count();
    $submittedForReview  = $writerConcepts->whereIn('status', ['client_review','approved'])->count();
    
    // As per controller logic: totalSubmitted is where description is not null and status is client_review, approved, or rejected
    $totalSubmittedCount = $existingConcepts->whereNotNull('description')
        ->whereIn('status', ['client_review', 'approved', 'rejected'])
        ->count();
    
    $remaining = max(0, $totalRequired - $totalSubmittedCount);
    $percent = $totalRequired > 0 ? round(($totalSubmittedCount / $totalRequired) * 100) : 0;
    
    // Dasharray for progress ring: 2 * PI * r = 2 * 3.14 * 20 = 125.6
    $radius = 20;
    $circumference = 2 * M_PI * $radius;
    $offset = $circumference - ($percent / 100 * $circumference);
@endphp

@section('content')
<div class="concept-submit-container">
    {{-- Page Header --}}
    <div class="concept-header-card">
        <div class="header-left">
            <h1>Submit Concepts</h1>
            <p>
                Project: <span class="fw-bold text-dark">{{ $conceptTask->project->name }}</span> 
                &middot; 
                <span class="text-purple fw-bold">{{ $totalRequired }}</span> Required
            </p>
        </div>
        <div class="header-right">
            <div class="progress-stats">
                @if($approvedCount > 0)
                    <span class="stat-item approved"><i class="fa-solid fa-check-circle me-1"></i>{{ $approvedCount }} Approved</span>
                @endif
                @if($rejectedCount > 0)
                    <span class="stat-item rejected"><i class="fa-solid fa-circle-xmark me-1"></i>{{ $rejectedCount }} Rejected</span>
                @endif
                <span class="stat-item text-muted">{{ $totalSubmittedCount }} / {{ $totalRequired }} Submitted</span>
            </div>
            
            <div class="progress-ring-container">
                <svg>
                    <circle class="bg" cx="24" cy="24" r="{{ $radius }}"></circle>
                    <circle class="progress" cx="24" cy="24" r="{{ $radius }}" 
                            style="stroke-dasharray: {{ $circumference }}; stroke-dashoffset: {{ $offset }};"></circle>
                </svg>
                <div class="progress-text">{{ $percent }}%</div>
            </div>
        </div>
    </div>

    <form id="submitForm" method="POST" action="{{ route('concepts.submit', $conceptTask->id) }}">
        @csrf
        
        {{-- Controls --}}
        <div class="concept-controls">
            <div class="count-label">
                <i class="fa-solid fa-list-check me-2 text-muted"></i>
                Total Concepts: {{ $totalRequired }}
            </div>
            <div class="control-btns">
                <button type="button" class="btn-text" onclick="expandAll()">Expand All</button>
                <span class="text-muted mx-1">|</span>
                <button type="button" class="btn-text" onclick="collapseAll()">Collapse All</button>
            </div>
        </div>

        {{-- Existing Concepts Loop --}}
        @foreach($existingConcepts as $index => $c)
            @php
                $count = $index;
                $statusData = match($c->status) {
                    'approved'      => ['class' => 'approved', 'label' => 'Approved', 'icon' => 'fa-check', 'isOpen' => false, 'cardClass' => 'status-approved'],
                    'rejected'      => ['class' => 'rejected', 'label' => 'Needs Revision', 'icon' => 'fa-redo', 'isOpen' => true, 'cardClass' => 'status-rejected'],
                    'client_review' => ['class' => 'client_review', 'label' => 'Pending Review', 'icon' => 'fa-clock', 'isOpen' => false, 'cardClass' => 'status-client_review'],
                    default         => ['class' => 'draft', 'label' => 'Draft', 'icon' => 'fa-pen', 'isOpen' => true, 'cardClass' => 'status-draft'],
                };
            @endphp

            <div class="concept-card {{ $statusData['cardClass'] }} {{ $statusData['isOpen'] ? 'open' : '' }}" id="card-{{ $c->id }}">
                <div class="card-header" onclick="toggleCard(this.closest('.concept-card'))">
                    <div class="concept-num {{ $statusData['class'] }}">{{ $index + 1 }}</div>
                    <div class="concept-info">
                        <span class="title">{{ $c->title ?? 'Untitled Concept' }}</span>
                        <span class="sub-label">{{ $statusData['label'] }}</span>
                    </div>
                    <div class="header-actions">
                        {!! $c->status_badge !!}
                        <i class="fa-solid fa-chevron-down chevron"></i>
                    </div>
                </div>

                <div class="card-body-collapse" style="{{ $statusData['isOpen'] ? 'display:block;' : '' }}">
                    @if($c->status === 'approved')
                        {{-- Read Only View --}}
                        <div class="read-only-view">
                            <h4 class="ro-title">{{ $c->title }}</h4>
                            <div class="ro-description">{{ $c->description }}</div>
                            
                            @if($c->writer_notes)
                                <div class="feedback-box info">
                                    <i class="fa-solid fa-info-circle"></i>
                                    <div>
                                        <strong>Your Notes:</strong><br>
                                        {{ $c->writer_notes }}
                                    </div>
                                </div>
                            @endif

                            {{-- Hidden inputs to maintain data on submit --}}
                            <input type="hidden" name="concepts[{{ $count }}][id]" value="{{ $c->id }}">
                            <input type="hidden" name="concepts[{{ $count }}][title]" value="{{ $c->title }}">
                            <input type="hidden" name="concepts[{{ $count }}][description]" value="{{ $c->description }}">
                            <input type="hidden" name="concepts[{{ $count }}][writer_notes]" value="{{ $c->writer_notes }}">
                        </div>
                    @else
                        {{-- Editable View --}}
                        <input type="hidden" name="concepts[{{ $count }}][id]" value="{{ $c->id }}">
                        
                        @if($c->status === 'rejected' && $c->remarks)
                            <div class="feedback-box rejected">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                                <div>
                                    <strong>Manager Feedback:</strong><br>
                                    {{ $c->remarks }}
                                </div>
                            </div>
                        @endif

                        @if($c->adjustment_suggestion)
                             <div class="feedback-box suggestion">
                                <i class="fa-solid fa-lightbulb"></i>
                                <div>
                                    <strong>Adjustment Suggestion:</strong><br>
                                    {{ $c->adjustment_suggestion }}
                                </div>
                            </div>
                        @endif

                        <div class="form-group-custom">
                            <label>Concept Title</label>
                            <input type="text" name="concepts[{{ $count }}][title]" class="form-control" 
                                   value="{{ $c->title }}" placeholder="Enter concept title..." required>
                        </div>

                        <div class="form-group-custom">
                            <label>Detailed Description</label>
                            <textarea name="concepts[{{ $count }}][description]" class="form-control" rows="6" 
                                      placeholder="Write your concept details here..." required>{{ $c->description }}</textarea>
                        </div>

                        <div class="form-group-custom">
                            <label>Writer Notes (Optional)</label>
                            <input type="text" name="concepts[{{ $count }}][writer_notes]" class="form-control" 
                                   value="{{ $c->writer_notes }}" placeholder="Add any notes for the manager...">
                        </div>
                    @endif
                </div>
            </div>
        @endforeach

        {{-- New Placeholders --}}
        @for($i = $writerConcepts->count(); $i < $totalRequired; $i++)
            <div class="concept-card is-new">
                <div class="card-header" onclick="toggleCard(this.closest('.concept-card'))">
                    <div class="concept-num new">{{ $i + 1 }}</div>
                    <div class="concept-info">
                        <span class="title">New Concept {{ $i + 1 }}</span>
                        <span class="sub-label text-warning">Not yet started</span>
                    </div>
                    <div class="header-actions">
                        <span class="badge bg-light text-dark border">New</span>
                        <i class="fa-solid fa-chevron-down chevron"></i>
                    </div>
                </div>

                <div class="card-body-collapse">
                    <div class="form-group-custom">
                        <label>Concept Title</label>
                        <input type="text" name="concepts[{{ $i }}][title]" class="form-control" 
                               placeholder="Enter concept title...">
                    </div>

                    <div class="form-group-custom">
                        <label>Detailed Description</label>
                        <textarea name="concepts[{{ $i }}][description]" class="form-control" rows="6" 
                                  placeholder="Write your concept details here..."></textarea>
                    </div>

                    <div class="form-group-custom">
                        <label>Writer Notes (Optional)</label>
                        <input type="text" name="concepts[{{ $i }}][writer_notes]" class="form-control" 
                               placeholder="Add any notes for the manager...">
                    </div>
                </div>
            </div>
        @endfor

        {{-- Sticky Footer --}}
        <div class="submit-footer">
            <div class="footer-inner">
                <div class="badge-row">
                    <span class="badge bg-purple-soft text-purple">Submitted: {{ $totalSubmittedCount }}</span>
                    <span class="badge bg-warning-soft text-warning">Remaining: {{ $remaining }}</span>
                    <span class="badge bg-success-soft text-success">Approved: {{ $approvedCount }}</span>
                    @if($rejectedCount > 0)
                        <span class="badge bg-danger-soft text-danger">Need Revision: {{ $rejectedCount }}</span>
                    @endif
                </div>

                <div class="progress-bar-container">
                    <div class="fill" style="width: {{ $percent }}%"></div>
                </div>

                <div class="action-row">
                    <div class="helper-text">
                        <i class="fa-solid fa-circle-info me-1"></i>
                        @if($remaining > 0)
                            Finish all concepts to submit for final review.
                        @else
                            All concepts ready! Submit now for review.
                        @endif
                    </div>
                    <div class="btns">
                        <a href="{{ route('concepts.index') }}" class="btn-premium-outline">Back</a>
                        <button type="submit" id="submitBtn" class="btn-premium-submit">
                            <span class="btn-text">
                                {{ $remaining > 0 ? 'Save Progress' : 'Submit All for Review' }}
                            </span>
                            <i class="fa-solid fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
    .bg-purple-soft { background: rgba(108, 63, 197, 0.1); }
    .text-purple { color: #6c3fc5; }
    .bg-success-soft { background: rgba(38, 222, 129, 0.1); }
    .bg-warning-soft { background: rgba(246, 173, 85, 0.1); }
    .bg-danger-soft { background: rgba(235, 77, 75, 0.1); }
</style>
@endpush

@push('scripts')
<script>
    // Global functions for card management
    function toggleCard(card) {
        const body = $(card).find('.card-body-collapse');
        const isOpen = $(card).hasClass('open');

        if (isOpen) {
            body.slideUp(200);
            $(card).removeClass('open');
        } else {
            $(card).addClass('open');
            body.slideDown(200);
        }
    }

    function expandAll() {
        $('.concept-card').not('.open').each(function() {
            $(this).addClass('open');
            $(this).find('.card-body-collapse').slideDown(150);
        });
    }

    function collapseAll() {
        $('.concept-card.open').each(function() {
            $(this).find('.card-body-collapse').slideUp(150);
            $(this).removeClass('open');
        });
    }

    $(document).ready(function() {
        $('#submitForm').on('submit', function(e) {
            e.preventDefault();
            
            const btn = $('#submitBtn');
            const btnText = btn.find('.btn-text');
            const originalText = btnText.text();
            
            // UI Feedback
            btn.prop('disabled', true);
            btnText.html('<i class="fa-solid fa-spinner fa-spin me-2"></i>Processing...');
            
            const formData = $(this).serialize();
            
            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: formData,
                success: function(res) {
                    if (res.success) {
                        showSuccess(res.message);
                        setTimeout(() => {
                            if (res.complete) {
                                window.location.href = "{{ route('concepts.index') }}";
                            } else {
                                window.location.reload();
                            }
                        }, 1500);
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false);
                    btnText.text(originalText);
                    
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        let msg = 'Validation Error:';
                        Object.values(errors).forEach(err => {
                            msg += '\n- ' + err[0];
                        });
                        showError(msg);
                    } else {
                        showError('Something went wrong. Please try again.');
                    }
                }
            });
        });
    });

    // Helper for showing alerts (assuming panel layout has some alert mechanism or using vanilla)
    function showSuccess(msg) {
        if (typeof toastr !== 'undefined') {
            toastr.success(msg);
        } else {
            // Fallback to a simple alert if toastr not available
            alert(msg);
        }
    }

    function showError(msg) {
        if (typeof toastr !== 'undefined') {
            toastr.error(msg);
        } else {
            alert(msg);
        }
    }
</script>
@endpush
