<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Review Concepts – {{ $conceptTask->project->name }}</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; font-family: 'Inter', sans-serif; }
        .concept-card { border-radius: 16px; border: none; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
        .brand-header { background: linear-gradient(135deg, #6c3fc5, #45aaf2); color: white; padding: 2rem; border-radius: 16px; margin-bottom: 2rem; }
    </style>
</head>
<body>
<div class="container py-5" style="max-width: 860px;">

    {{-- Header --}}
    <div class="brand-header">
        <div class="d-flex align-items-center gap-3">
            <div style="width:48px;height:48px;background:rgba(255,255,255,0.2);border-radius:12px;display:flex;align-items:center;justify-content:center;">
                <i class="fa-solid fa-bolt fa-lg"></i>
            </div>
            <div>
                <h4 class="mb-0 fw-bold">Digital Yug</h4>
                <small class="opacity-75">Creative Concepts for Review</small>
            </div>
        </div>
        <hr style="border-color:rgba(255,255,255,0.2);">
        <h2 class="fw-bold mb-1">{{ $conceptTask->project->name }}</h2>
        <p class="mb-0 opacity-75">
            <i class="fa-solid fa-lightbulb me-1"></i>{{ $conceptTask->concepts->count() }} concepts submitted for your review
        </p>
    </div>

    {{-- Concepts --}}
    @foreach($conceptTask->concepts->whereIn('status', ['client_review', 'rejected']) as $i => $concept)
    <div class="concept-card card mb-4 p-4">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white"
                    style="width:36px;height:36px;background:#6c3fc5;font-size:14px;">
                    {{ $i + 1 }}
                </div>
                <div>
                    <h5 class="fw-bold mb-0">{{ $concept->title }}</h5>
                    @if($concept->client_allocation)
                        <small class="text-muted"><i class="fa-solid fa-tag me-1"></i>{{ $concept->client_allocation }}</small>
                    @endif
                </div>
            </div>
            <span class="badge rounded-pill px-3 py-2
                @if($concept->status === 'approved') bg-success
                @elseif($concept->status === 'rejected') bg-danger
                @else" style="background:rgba(108,63,197,0.1);color:#6c3fc5;" @endif">
                {{ ucfirst(str_replace('_', ' ', $concept->status)) }}
            </span>
        </div>

        {{-- Description --}}
        <div class="p-3 rounded-3 mb-3" style="background:#f8f9fa;border:1px dashed #dee2e6;white-space:pre-wrap;font-size:14px;line-height:1.7;">{{ $concept->description }}</div>

        @if($concept->writer_notes)
        <div class="small p-2 rounded mb-3" style="background:rgba(247,183,49,0.1);border-left:3px solid #f7b731;">
            <i class="fa-solid fa-comment-dots me-1 text-warning"></i>
            <strong>Writer Notes:</strong> {{ $concept->writer_notes }}
        </div>
        @endif

        @if($concept->remarks)
        <div class="small p-2 rounded mb-3" style="background:rgba(235,77,75,0.1);border-left:3px solid #eb4d4b;">
            <i class="fa-solid fa-circle-exclamation me-1 text-danger"></i>
            <strong>Feedback:</strong> {{ $concept->remarks }}
        </div>
        @endif

        {{-- Action buttons --}}
        @if($concept->status === 'client_review')
        <div class="d-flex gap-2 mt-3">
            <button class="btn btn-success btn-approve" 
                data-id="{{ $concept->id }}"
                data-url="{{ route('concepts.client-approve', [$conceptTask->client_token, $concept]) }}">
                <i class="fa-solid fa-check me-1"></i>Approve
            </button>
            <button class="btn btn-outline-danger btn-reject"
                data-id="{{ $concept->id }}"
                data-url="{{ route('concepts.client-reject', [$conceptTask->client_token, $concept]) }}">
                <i class="fa-solid fa-xmark me-1"></i>Request Changes
            </button>
        </div>
        @endif
    </div>
    @endforeach

    <div class="text-center text-muted small mt-4">
        <i class="fa-solid fa-lock me-1"></i>This is a secure review link shared by Digital Yug. 
        Please do not share this link publicly.
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.7.32/sweetalert2.all.min.js"></script>
<script>
const TOKEN = '{{ $conceptTask->client_token }}';

$('.btn-approve').on('click', function() {
    const url = $(this).data('url');
    const btn = $(this);
    Swal.fire({
        title: 'Approve this concept?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#26de81',
        confirmButtonText: 'Yes, Approve!'
    }).then((result) => {
        if (result.isConfirmed) {
            btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i>');
            $.post(url, { _token: '{{ csrf_token() }}' })
            .done(function(res) {
                Swal.fire('Approved!', res.message, 'success')
                    .then(() => location.reload());
            });
        }
    });
});

$('.btn-reject').on('click', function() {
    const url = $(this).data('url');
    const btn = $(this);
    Swal.fire({
        title: 'Request Changes',
        input: 'textarea',
        inputPlaceholder: 'Describe what changes you need...',
        showCancelButton: true,
        confirmButtonColor: '#eb4d4b',
        confirmButtonText: 'Submit Feedback',
        inputValidator: (v) => { if (!v) return 'Please provide feedback!' }
    }).then((result) => {
        if (result.isConfirmed) {
            btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i>');
            $.post(url, { _token: '{{ csrf_token() }}', remarks: result.value })
            .done(function(res) {
                Swal.fire('Feedback Sent!', res.message, 'success')
                    .then(() => location.reload());
            });
        }
    });
});
</script>
</body>
</html>