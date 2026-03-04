@extends('layouts.panel')
@section('title', 'Shoot Details')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('shoots.index') }}">Shoots</a></li>
    <li class="breadcrumb-item active">Details</li>
@endsection

@push('styles')
<style>
/* ── Shoot Duration Card ──────────────────────────────── */
.shoot-timing-card {
    background: linear-gradient(135deg, #fafaff 0%, #f0ebff 100%);
    border: 1px solid #e8edf5;
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 20px;
}
.duration-icon {
    width: 48px; height: 48px;
    border-radius: 12px;
    background: linear-gradient(135deg, #6c3fc5, #8b5cf6);
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 20px;
    box-shadow: 0 4px 12px rgba(108,63,197,0.25);
    flex-shrink: 0;
}
.timing-stat { padding: 12px 16px; background: #fff; border-radius: 10px; border: 1px solid #f0f2f8; }
.timing-stat .label { font-size: 10px; font-weight: 700; letter-spacing: 0.5px; color: #a0aec0; margin-bottom: 4px; }
.timing-stat .value { font-size: 14px; font-weight: 700; color: #2d3748; }

/* ── Concept Cards ───────────────────────────────────── */
.concept-shoot-card {
    background: #fff;
    border: 1px solid #e8edf5;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 10px;
    transition: box-shadow 0.2s, border-color 0.2s;
    border-left: 4px solid #e2e8f0;
}
.concept-shoot-card:hover { box-shadow: 0 3px 16px rgba(108,63,197,0.08); border-color: #d4c5f5; }
.concept-shoot-card.is-shot { border-left-color: #26de81; }
.concept-shoot-card.is-missed { border-left-color: #eb4d4b; }
.concept-shoot-card.is-review { border-left-color: #45aaf2; }

.concept-num {
    width: 28px; height: 28px; border-radius: 50%;
    background: rgba(108,63,197,0.1); color: #6c3fc5;
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 700; flex-shrink: 0;
}

/* ── Add Concept Button ───────────────────────────────── */
.add-concept-btn {
    border: 2px dashed #d4c5f5;
    border-radius: 12px;
    padding: 16px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
    color: #6c3fc5;
    background: transparent;
    width: 100%;
    font-size: 14px;
    font-weight: 600;
}
.add-concept-btn:hover {
    background: rgba(108,63,197,0.05);
    border-color: #6c3fc5;
}

/* ── Info Sidebar Card ───────────────────────────────── */
.info-row { display: flex; flex-direction: column; padding: 10px 0; border-bottom: 1px solid #f0f2f8; }
.info-row:last-child { border-bottom: none; }
.info-row .info-label { font-size: 11px; font-weight: 600; color: #a0aec0; letter-spacing: 0.4px; margin-bottom: 3px; }
.info-row .info-value { font-size: 14px; font-weight: 600; color: #2d3748; }

/* ── Checkout btn ────────────────────────────────────── */
.btn-checkin  { background: linear-gradient(135deg,#6c3fc5,#8b5cf6); border: none; color: #fff; padding: 12px 32px; border-radius: 10px; font-weight: 600; font-size: 15px; }
.btn-checkout { background: linear-gradient(135deg,#eb4d4b,#f87171); border: none; color: #fff; padding: 12px 32px; border-radius: 10px; font-weight: 600; font-size: 15px; }

/* ── Responsive ──────────────────────────────────────── */
@media (max-width: 767px) {
    .timing-stats-row { flex-direction: column; gap: 8px !important; }
    .btn-checkin, .btn-checkout { width: 100%; margin-top: 12px; }
}
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Shoot Details</h1>
        <p class="mb-0">{!! $shoot->status_badge !!}</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('projects.show', $shoot->project_id) }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-2"></i>Go to Project
        </a>
    </div>
</div>

<div class="row g-4">

    {{-- ── Main Column ────────────────────────────────────── --}}
    <div class="col-lg-8">

        {{-- Timing Card --}}
        <div class="shoot-timing-card">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="duration-icon"><i class="fa-solid fa-clock"></i></div>
                    <div>
                        <div style="font-size:11px;font-weight:700;letter-spacing:0.5px;color:#a0aec0;">SHOOT DURATION</div>
                        <div class="h4 fw-bold mb-0" id="durationText" style="color:#2d3748;">
                            {{ $shoot->duration ?? 'Not Started' }}
                        </div>
                    </div>
                </div>
                <div>
                    @if(!$shoot->checkin_at)
                        <button class="btn-checkin" onclick="checkin()">
                            <i class="fa-solid fa-right-to-bracket me-2"></i>Check-in
                        </button>
                    @elseif(!$shoot->checkout_at)
                        <button class="btn-checkout" onclick="showCheckout()">
                            <i class="fa-solid fa-right-from-bracket me-2"></i>Checkout
                        </button>
                    @endif
                </div>
            </div>

            <div class="d-flex gap-3 flex-wrap timing-stats-row">
                <div class="timing-stat flex-fill">
                    <div class="label">CHECK-IN</div>
                    <div class="value" id="checkinTime">
                        {{ $shoot->checkin_at ? $shoot->checkin_at->format('d M, h:i A') : '—' }}
                    </div>
                </div>
                <div class="timing-stat flex-fill">
                    <div class="label">CHECK-OUT</div>
                    <div class="value" id="checkoutTime">
                        {{ $shoot->checkout_at ? $shoot->checkout_at->format('d M, h:i A') : '—' }}
                    </div>
                </div>
                <div class="timing-stat flex-fill">
                    <div class="label">REELS SHOT</div>
                    <div class="value" style="color:#26de81;">{{ $shoot->reels_shot ?? 0 }}</div>
                </div>
                <div class="timing-stat flex-fill">
                    <div class="label">CONCEPTS</div>
                    <div class="value" style="color:#6c3fc5;">{{ $shoot->concepts->count() }}</div>
                </div>
            </div>
        </div>

        {{-- Concepts to Shoot --}}
        <div class="panel-card">
            <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
                <h5 class="panel-card-title mb-0">
                    <i class="fa-solid fa-lightbulb me-2"></i>Concepts to Shoot
                </h5>
                {{-- Show add button only when checked-in and not checked-out --}}
                @if($shoot->checkin_at && !$shoot->checkout_at)
                <button class="btn btn-sm btn-success" onclick="addNewConcept()">
                    <i class="fa-solid fa-plus me-1"></i>Add New Concept
                </button>
                @endif
            </div>

            @forelse($shoot->concepts as $i => $c)
            @php
                $link = $shoot->checkout_at
                    ? $shoot->conceptLinks()->where('concept_id', $c->id)->first()
                    : null;
                $cardClass = '';
                if ($link) $cardClass = $link->is_shot ? 'is-shot' : 'is-missed';
                if ($c->is_review_reel ?? false) $cardClass = 'is-review';
            @endphp

            <div class="concept-shoot-card {{ $cardClass }}">
                <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
                    {{-- Left: info --}}
                    <div class="d-flex align-items-start gap-3 flex-fill" style="min-width:0;">
                        <div class="concept-num">{{ $i + 1 }}</div>
                        <div style="min-width:0;flex:1;">
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                <span class="fw-bold" style="font-size:14px;color:#2d3748;">{{ $c->title }}</span>
                                @if($c->is_review_reel ?? false)
                                <span class="badge" style="background:rgba(69,170,242,0.12);color:#1a6fa3;font-size:10px;">
                                    <i class="fa-solid fa-video me-1"></i>Review Reel
                                </span>
                                @endif
                                {{-- Shot / Missed badge --}}
                                @if($shoot->checkout_at)
                                    @if($link && $link->is_shot)
                                    <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i>Shot</span>
                                    @else
                                    <span class="badge bg-danger">Missed</span>
                                    @endif
                                @endif
                            </div>
                            @if($c->description)
                            <p class="text-muted small mb-0" style="line-height:1.5;">
                                {{ Str::limit($c->description, 120) }}
                            </p>
                            @else
                            <p class="text-muted small mb-0 fst-italic">No description added yet.</p>
                            @endif
                            @if($c->adjustment_suggestion)
                            <div class="mt-2 p-2 rounded small"
                                style="background:rgba(247,183,49,0.08);border-left:3px solid #f7b731;font-size:12px;">
                                <i class="fa-solid fa-lightbulb me-1 text-warning"></i>
                                <strong>Suggestion:</strong> {{ Str::limit($c->adjustment_suggestion, 80) }}
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Right: action button --}}
                    @if(!$shoot->checkout_at)
                    <div class="flex-shrink-0">
                        <button class="btn btn-sm btn-outline-primary"
                            onclick="editConcept(
                                {{ $c->id }},
                                '{{ addslashes($c->title) }}',
                                `{{ addslashes($c->description ?? '') }}`,
                                `{{ addslashes($c->adjustment_suggestion ?? '') }}`
                            )">
                            <i class="fa-solid fa-pen me-1"></i>Edit
                        </button>
                    </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="text-center py-5">
                <i class="fa-solid fa-lightbulb fa-3x text-muted opacity-25 mb-3 d-block"></i>
                <p class="text-muted">No concepts linked to this shoot.</p>
                @if($shoot->checkin_at && !$shoot->checkout_at)
                <button class="btn btn-outline-primary btn-sm mt-2" onclick="addNewConcept()">
                    <i class="fa-solid fa-plus me-1"></i>Add First Concept
                </button>
                @endif
            </div>
            @endforelse

            {{-- Add concept dashed button (only when active) --}}
            @if($shoot->checkin_at && !$shoot->checkout_at && $shoot->concepts->count() > 0)
            <div class="mt-3">
                <button class="add-concept-btn" onclick="addNewConcept()">
                    <i class="fa-solid fa-plus me-2"></i>Add Another Concept
                </button>
            </div>
            @endif
        </div>
    </div>

    {{-- ── Sidebar ─────────────────────────────────────────── --}}
    <div class="col-lg-4">

        {{-- Shoot Info --}}
        <div class="panel-card mb-4" style="border-top: 4px solid #6c3fc5;border-radius:14px;">
            <h5 class="panel-card-title mb-3">
                <i class="fa-solid fa-calendar-day me-2"></i>Shoot Info
            </h5>

            <div class="info-row">
                <div class="info-label">LOCATION</div>
                <div class="info-value">
                    <i class="fa-solid fa-location-dot me-2 text-danger"></i>{{ $shoot->location }}
                </div>
            </div>

            <div class="info-row">
                <div class="info-label">SHOOTING PERSON</div>
                <div class="info-value">{{ $shoot->shootingPerson->name ?? 'N/A' }}</div>
            </div>

            <div class="d-flex gap-3">
                <div class="info-row flex-fill">
                    <div class="info-label">DATE</div>
                    <div class="info-value">{{ $shoot->shoot_date->format('d M Y') }}</div>
                </div>
                <div class="info-row flex-fill">
                    <div class="info-label">PLANNED TIME</div>
                    <div class="info-value">
                        {{ $shoot->planned_start_time
                            ? \Carbon\Carbon::parse($shoot->planned_start_time)->format('h:i A')
                            : '—' }}
                    </div>
                </div>
            </div>

            @if($shoot->conceptWriter)
            <div class="info-row">
                <div class="info-label">ON-SITE WRITER</div>
                <div class="info-value" style="color:#45aaf2;">{{ $shoot->conceptWriter->name }}</div>
            </div>
            @endif

            @if($shoot->model_name || $shoot->helper_name)
            <div class="d-flex gap-3">
                @if($shoot->model_name)
                <div class="info-row flex-fill">
                    <div class="info-label">MODEL</div>
                    <div class="info-value">{{ $shoot->model_name }}</div>
                </div>
                @endif
                @if($shoot->helper_name)
                <div class="info-row flex-fill">
                    <div class="info-label">HELPER</div>
                    <div class="info-value">{{ $shoot->helper_name }}</div>
                </div>
                @endif
            </div>
            @endif
        </div>

        {{-- Shoot Notes --}}
        @if($shoot->notes)
        <div class="panel-card" style="background:#fafaff;border-radius:14px;">
            <h5 class="panel-card-title mb-3" style="font-size:13px;">
                <i class="fa-solid fa-note-sticky me-2"></i>Shoot Notes
            </h5>
            <div class="text-muted small" style="line-height:1.7;white-space:pre-wrap;">{{ $shoot->notes }}</div>
        </div>
        @endif

        {{-- Quick Stats --}}
        @if($shoot->concepts->count() > 0)
        <div class="panel-card mt-4" style="border-radius:14px;">
            <h5 class="panel-card-title mb-3" style="font-size:13px;">
                <i class="fa-solid fa-chart-simple me-2"></i>Concept Summary
            </h5>
            @php
                $total    = $shoot->concepts->count();
                $reviews  = $shoot->concepts->where('is_review_reel', true)->count();
                $original = $total - $reviews;
            @endphp
            <div class="d-flex justify-content-between small mb-2">
                <span class="text-muted">Original Concepts</span>
                <span class="fw-bold">{{ $original }}</span>
            </div>
            <div class="d-flex justify-content-between small mb-2">
                <span class="text-muted">Review Reels Added</span>
                <span class="fw-bold" style="color:#45aaf2;">{{ $reviews }}</span>
            </div>
            <div class="d-flex justify-content-between small">
                <span class="text-muted">Total</span>
                <span class="fw-bold" style="color:#6c3fc5;">{{ $total }}</span>
            </div>
        </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
// ── Check-in ───────────────────────────────────────────────────
function checkin() {
    Swal.fire({
        title: 'Check-in Now?',
        text: 'Are you starting the shoot?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#6c3fc5',
        confirmButtonText: 'Yes, Check-in'
    }).then(r => {
        if (r.isConfirmed) {
            ajaxPost('{{ route('shoots.checkin', $shoot) }}', {}, function(res) {
                showSuccess(res.message);
                $('#checkinTime').text(res.checkin_at);
                setTimeout(() => location.reload(), 1200);
            });
        }
    });
}

// ── Checkout ───────────────────────────────────────────────────
function showCheckout() {
    const conceptCheckboxes = `
        @foreach($shoot->concepts as $c)
        <div class="form-check mb-1">
            <input class="form-check-input shot-checkbox" type="checkbox"
                value="{{ $c->id }}" id="chk_{{ $c->id }}" checked>
            <label class="form-check-label small" for="chk_{{ $c->id }}">
                {{ $c->title }}
                @if($c->is_review_reel ?? false)
                <span style="font-size:10px;color:#45aaf2;">(Review Reel)</span>
                @endif
            </label>
        </div>
        @endforeach
    `;

    Swal.fire({
        title: 'Checkout & Review',
        html: `
            <div class="text-start">
                <label class="form-label fw-semibold">Total Reels Shot <span class="text-danger">*</span></label>
                <input type="number" id="reelsShot" class="form-control mb-3" value="{{ $shoot->concepts->count() }}" min="0">
                <label class="form-label fw-semibold mb-2">Concepts actually shot:</label>
                ${conceptCheckboxes}
            </div>
        `,
        showCancelButton: true,
        confirmButtonColor: '#eb4d4b',
        confirmButtonText: '<i class="fa-solid fa-flag-checkered me-1"></i>Finish Shoot',
        width: '500px',
        preConfirm: () => {
            const reels = document.getElementById('reelsShot').value;
            if (!reels || reels < 0) return Swal.showValidationMessage('Valid reel count required');
            const shotIds = Array.from(document.querySelectorAll('.shot-checkbox:checked')).map(el => el.value);
            return { reels, shotIds };
        }
    }).then(r => {
        if (r.isConfirmed) {
            ajaxPost(
                '{{ route('shoots.checkout', $shoot) }}',
                { reels_shot: r.value.reels, shot_concept_ids: r.value.shotIds },
                function(res) {
                    showSuccess(res.message);
                    setTimeout(() => location.reload(), 1500);
                }
            );
        }
    });
}

// ── Edit Existing Concept ──────────────────────────────────────
function editConcept(conceptId, title, currentDesc, currentSuggestion) {
    Swal.fire({
        title: 'Edit Concept',
        html: `
            <div class="text-start">
                <label class="form-label fw-semibold" style="font-size:13px;">
                    <i class="fa-solid fa-heading me-1 text-primary"></i>Title
                </label>
                <input id="edit-title" class="form-control mb-3" value="${title}">

                <label class="form-label fw-semibold" style="font-size:13px;">
                    <i class="fa-solid fa-file-lines me-1 text-info"></i>Script / Description
                </label>
                <textarea id="edit-desc" class="form-control mb-3" rows="5"
                    placeholder="Describe the concept...">${currentDesc}</textarea>

                <label class="form-label fw-semibold" style="font-size:13px;">
                    <i class="fa-solid fa-comment-dots me-1 text-warning"></i>
                    Suggestion / Note <span class="text-muted fw-normal">(optional)</span>
                </label>
                <textarea id="edit-suggestion" class="form-control" rows="2"
                    placeholder="e.g. Better lighting needed...">${currentSuggestion}</textarea>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-check me-1"></i>Save Changes',
        confirmButtonColor: '#6c3fc5',
        width: '560px',
        preConfirm: () => {
            const t = document.getElementById('edit-title').value.trim();
            if (!t) { Swal.showValidationMessage('Title is required!'); return false; }
            return {
                updated_title:       t,
                updated_description: document.getElementById('edit-desc').value.trim(),
                suggestion:          document.getElementById('edit-suggestion').value.trim(),
            };
        }
    }).then(r => {
        if (r.isConfirmed) {
            ajaxPost('{{ route('shoots.suggest', $shoot) }}', {
                concept_id:          conceptId,
                updated_title:       r.value.updated_title,
                updated_description: r.value.updated_description,
                suggestion:          r.value.suggestion,
            }, function(res) {
                showSuccess(res.message);
                setTimeout(() => location.reload(), 1200);
            });
        }
    });
}

// ── Add New Concept (Review Reel) ──────────────────────────────
function addNewConcept() {
    Swal.fire({
        title: 'Add New Concept',
        html: `
            <div class="text-start">
                <div class="p-2 rounded mb-3 small"
                    style="background:rgba(69,170,242,0.08);border-left:3px solid #45aaf2;color:#1a6fa3;">
                    <i class="fa-solid fa-circle-info me-1"></i>
                    This concept will be added directly — no approval required.
                </div>
                <label class="form-label fw-semibold" style="font-size:13px;">
                    <i class="fa-solid fa-heading me-1 text-primary"></i>
                    Concept Title <span class="text-danger">*</span>
                </label>
                <input id="new-title" class="form-control mb-3" placeholder="e.g. Product Close-up Shot">

                <label class="form-label fw-semibold" style="font-size:13px;">
                    <i class="fa-solid fa-file-lines me-1 text-info"></i>
                    Script / Description <span class="text-muted fw-normal">(optional)</span>
                </label>
                <textarea id="new-desc" class="form-control" rows="5"
                    placeholder="Describe the scene or shot requirements..."></textarea>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-plus me-1"></i>Add Concept',
        confirmButtonColor: '#26de81',
        width: '520px',
        preConfirm: () => {
            const t = document.getElementById('new-title').value.trim();
            if (!t) { Swal.showValidationMessage('Concept title is required!'); return false; }
            return {
                new_title:       t,
                new_description: document.getElementById('new-desc').value.trim(),
            };
        }
    }).then(r => {
        if (r.isConfirmed) {
            ajaxPost('{{ route('shoots.suggest', $shoot) }}', {
                is_new:          1,
                new_title:       r.value.new_title,
                new_description: r.value.new_description,
            }, function(res) {
                showSuccess(res.message);
                setTimeout(() => location.reload(), 1200);
            });
        }
    });
}
</script>
@endpush