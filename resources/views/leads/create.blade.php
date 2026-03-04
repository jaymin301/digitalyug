@extends('layouts.panel')
@section('title', 'Add Lead')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('leads.index') }}">Leads</a></li>
    <li class="breadcrumb-item active">Add Lead</li>
@endsection
@section('content')
<div class="page-header">
    <h1 class="page-title">New Lead <span class="page-subtitle">Add a Digital Yug client inquiry</span></h1>
    <a href="{{ route('leads.index') }}" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Back</a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="panel-card">
            <form id="leadForm" method="POST">
                @csrf
                <div class="row g-3">
                    {{-- Date & Day --}}
                    <div class="col-md-4">
                        <label class="form-label">Date <span class="required">*</span></label>
                        <input type="text" name="date" id="leadDate" class="form-control datepicker" placeholder="YYYY-MM-DD" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Day</label>
                        <input type="text" id="leadDay" class="form-control" readonly placeholder="Auto-filled" style="background:#f7f8ff;">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Agency <span class="required">*</span></label>
                        <select name="agency_id" class="form-select @error('agency_id') is-invalid @enderror" required>
                            <option value="">Select Agency</option>
                            @foreach($agencies as $agency)
                                <option value="{{ $agency->id }}">{{ $agency->name }}</option>
                            @endforeach
                        </select>
                        @error('agency_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Customer details --}}
                    <div class="col-md-6">
                        <label class="form-label">Customer Name <span class="required">*</span></label>
                        <input type="text" name="customer_name" class="form-control" placeholder="e.g. Bodhi Wellness Spa" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contact Number <span class="required">*</span></label>
                        <input type="text" name="contact_number" class="form-control" placeholder="e.g. 9876543210" required>
                    </div>

                    {{-- Content plan --}}
                    <div class="col-md-4">
                        <label class="form-label">Total Reels <span class="required">*</span></label>
                        <input type="number" name="total_reels" class="form-control" min="0" value="0" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Total Posts <span class="required">*</span></label>
                        <input type="number" name="total_posts" class="form-control" min="0" value="0" required>
                    </div>
                    
                    {{-- Meta Budget --}}
                    <div class="col-12"><hr class="my-1"><label class="form-label fw-bold">Meta Budget Distribution</label></div>
                    <div class="col-md-4">
                        <label class="form-label">Total Meta Budget (₹) <span class="required">*</span></label>
                        <input type="number" name="total_meta_budget" id="totalBudget" class="form-control" min="0" placeholder="e.g. 30000" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Client-Side Budget (₹) <span class="required">*</span></label>
                        <input type="number" name="client_meta_budget" id="clientBudget" class="form-control" min="0" placeholder="e.g. 18000" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Digital Yug Budget (₹) <span class="required">*</span></label>
                        <input type="number" name="dy_meta_budget" id="dyBudget" class="form-control" min="0" placeholder="Auto or manual" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Any additional info about this lead..."></textarea>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2 justify-content-end">
                    <a href="{{ route('leads.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary" id="submitBtn"><i class="fa-solid fa-plus me-2"></i>Create Lead</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-fill day from date
    flatpickr('#leadDate', {
        dateFormat: 'Y-m-d',
        allowInput: true,
        onChange: function(selectedDates) {
            if (selectedDates[0]) {
                const days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
                $('#leadDay').val(days[selectedDates[0].getDay()]);
            }
        }
    });

    // Auto-calculate DY budget
    function calcDyBudget() {
        const total = parseFloat($('#totalBudget').val()) || 0;
        const client = parseFloat($('#clientBudget').val()) || 0;
        $('#dyBudget').val(Math.max(0, total - client).toFixed(0));
    }
    $('#totalBudget, #clientBudget').on('input', calcDyBudget);

    // AJAX form submit
    $('#leadForm').on('submit', function(e) {
        e.preventDefault();
        const btn = $('#submitBtn').prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-2"></i>Saving...');
        ajaxPost('{{ route('leads.store') }}', $(this).serialize(), function(res) {
            showSuccess(res.message);
            setTimeout(() => location.href = '{{ route('leads.index') }}', 1500);
        }, () => btn.prop('disabled',false).html('<i class="fa-solid fa-plus me-2"></i>Create Lead'));
    });
});
</script>
@endpush
