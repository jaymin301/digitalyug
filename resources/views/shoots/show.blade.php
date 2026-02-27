@extends('layouts.panel')
@section('title', 'Shoot Details')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('shoots.index') }}">Shoots</a></li>
    <li class="breadcrumb-item active">Details</li>
@endsection
@section('content')
<div class="page-header">
    <h1 class="page-title">Shoot Details <span class="page-subtitle">{!! $shoot->status_badge !!}</span></h1>
    <div class="d-flex gap-2">
        <a href="{{ route('projects.show', $shoot->project_id) }}" class="btn btn-outline-secondary">Go to Project</a>
    </div>
</div>

<div class="row g-4">
    {{-- Main Column --}}
    <div class="col-lg-8">
        {{-- Check-in / Checkout Card --}}
        <div class="panel-card mb-4" style="background:#fafaff;">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon bg-purple shadow-sm"><i class="fa-solid fa-clock"></i></div>
                        <div>
                            <div class="text-muted small">SHOOT DURATION</div>
                            <div class="h4 fw-bold mb-0" id="durationText">{{ $shoot->duration ?? 'Not Started' }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                    @if(!$shoot->checkin_at)
                    <button class="btn btn-primary btn-lg px-5" onclick="checkin()">Check-in <i class="fa-solid fa-right-to-bracket ms-2"></i></button>
                    @elseif(!$shoot->checkout_at)
                    <button class="btn btn-danger btn-lg px-5" onclick="showCheckout()">Checkout <i class="fa-solid fa-right-from-bracket ms-2"></i></button>
                    @endif
                </div>
            </div>
            
            <div class="row mt-4 pt-3 border-top g-3">
                <div class="col-6 col-sm-4">
                    <div class="small text-muted">CHECK-IN</div>
                    <div class="fw-bold" id="checkinTime">{{ $shoot->checkin_at ? $shoot->checkin_at->format('d M, h:i A') : '—' }}</div>
                </div>
                <div class="col-6 col-sm-4">
                    <div class="small text-muted">CHECK-OUT</div>
                    <div class="fw-bold" id="checkoutTime">{{ $shoot->checkout_at ? $shoot->checkout_at->format('d M, h:i A') : '—' }}</div>
                </div>
                <div class="col-sm-4">
                    <div class="small text-muted">REELS SHOT</div>
                    <div class="fw-bold text-success">{{ $shoot->reels_shot ?? 0 }}</div>
                </div>
            </div>
        </div>

        {{-- Linked Concepts --}}
        <div class="panel-card">
            <h5 class="panel-card-title mb-4"><i class="fa-solid fa-lightbulb"></i> Concepts to Shoot</h5>
            @forelse($shoot->concepts as $c)
            <div class="p-3 border rounded-3 mb-3 d-flex flex-wrap align-items-center justify-content-between gap-3 bg-white shadow-sm">
                <div style="flex:1;min-width:200px;">
                    <h6 class="fw-bold mb-1">{{ $c->title }}</h6>
                    <p class="text-muted small mb-0">{{ Str::limit($c->description, 100) }}</p>
                </div>
                <div class="d-flex align-items-center gap-3">
                    @if($shoot->checkout_at)
                        @php $link = $shoot->conceptLinks()->where('concept_id',$c->id)->first(); @endphp
                        @if($link && $link->is_shot)
                            <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i>Shot</span>
                        @else
                            <span class="badge bg-danger">Missed</span>
                        @endif
                    @endif
                    <button class="btn btn-sm btn-outline-info" onclick="suggestAdjustment({{ $c->id }}, '{{ $c->title }}')">Suggest Adjust</button>
                </div>
            </div>
            @empty
            <div class="text-center py-4 text-muted">No specific concepts linked.</div>
            @endforelse
        </div>
    </div>

    {{-- Detail Sidebar --}}
    <div class="col-lg-4">
        <div class="panel-card mb-4 border-top border-4 border-purple">
            <h5 class="panel-card-title mb-4"><i class="fa-solid fa-calendar-day"></i> Shoot Info</h5>
            <div class="mb-3">
                <label class="form-label">Location</label>
                <div class="fw-bold"><i class="fa-solid fa-location-dot me-2 text-danger"></i>{{ $shoot->location }}</div>
            </div>
            <div class="mb-3">
                <label class="form-label">Shooting Person</label>
                <div class="fw-bold">{{ $shoot->shootingPerson->name ?? 'N/A' }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-6">
                    <label class="form-label">Date</label>
                    <div class="fw-bold">{{ $shoot->shoot_date->format('d M Y') }}</div>
                </div>
                <div class="col-6">
                    <label class="form-label">Planned Time</label>
                    <div class="fw-bold">{{ $shoot->planned_start_time ? Carbon\Carbon::parse($shoot->planned_start_time)->format('h:i A') : '—' }}</div>
                </div>
            </div>
            @if($shoot->conceptWriter)
            <div class="mb-3">
                <label class="form-label">On-site Concept Writer</label>
                <div class="fw-bold text-info">{{ $shoot->conceptWriter->name }}</div>
            </div>
            @endif
            <div class="row g-2">
                @if($shoot->model_name)
                <div class="col-6"><label class="form-label">Model</label><div class="small fw-bold">{{ $shoot->model_name }}</div></div>
                @endif
                @if($shoot->helper_name)
                <div class="col-6"><label class="form-label">Helper</label><div class="small fw-bold">{{ $shoot->helper_name }}</div></div>
                @endif
            </div>
        </div>

        @if($shoot->notes)
        <div class="panel-card bg-light">
            <h5 class="panel-card-title mb-3" style="font-size:14px;"><i class="fa-solid fa-note-sticky"></i> Shoot Notes</h5>
            <div class="text-muted small">{{ $shoot->notes }}</div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function checkin() {
    Swal.fire({
        title: 'Check-in Now?',
        text: "Are you starting the shoot?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Check-in'
    }).then(r => {
        if(r.isConfirmed) {
            ajaxPost('{{ route('shoots.checkin', $shoot) }}', {}, function(res) {
                showSuccess(res.message);
                $('#checkinTime').text(res.checkin_at);
                setTimeout(() => location.reload(), 1200);
            });
        }
    });
}

function showCheckout() {
    Swal.fire({
        title: 'Checkout & Review',
        html: `
            <div class="text-start">
                <label class="form-label">Total Reels Shot Today <span class="required">*</span></label>
                <input type="number" id="reelsShot" class="form-control mb-3" value="0">
                <label class="form-label mb-2">Tick concepts actually shot:</label>
                @foreach($shoot->concepts as $c)
                <div class="form-check mb-1">
                    <input class="form-check-input shot-checkbox" type="checkbox" value="{{ $c->id }}" id="chk_{{ $c->id }}">
                    <label class="form-check-label small" for="chk_{{ $c->id }}">{{ $c->title }}</label>
                </div>
                @endforeach
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Finish Shoot',
        preConfirm: () => {
            const reels = document.getElementById('reelsShot').value;
            if(!reels || reels < 0) return Swal.showValidationMessage('Valid reel count required');
            const shotIds = Array.from(document.querySelectorAll('.shot-checkbox:checked')).map(el => el.value);
            return { reels, shotIds };
        }
    }).then(r => {
        if(r.isConfirmed) {
            ajaxPost('{{ route('shoots.checkout', $shoot) }}', { reels_shot: r.value.reels, shot_concept_ids: r.value.shotIds }, function(res) {
                showSuccess(res.message);
                setTimeout(() => location.reload(), 1500);
            });
        }
    });
}

function suggestAdjustment(conceptId, title) {
    Swal.fire({
        title: 'Suggest Adjustment',
        text: `Suggest a change for: ${title}`,
        input: 'textarea',
        inputPlaceholder: 'e.g. Needs better lighting, Change background for this scene...',
        showCancelButton: true
    }).then(r => {
        if(r.value) {
            ajaxPost('{{ route('shoots.suggest-adjustment', $shoot) }}', { concept_id: conceptId, suggestion: r.value }, function(res) {
                showSuccess(res.message);
            });
        }
    });
}
</script>
@endpush
