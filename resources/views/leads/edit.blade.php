@extends('layouts.panel')
@section('title', 'Edit Lead')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('leads.index') }}">Leads</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection
@section('content')
<div class="page-header">
    <h1 class="page-title">Edit Lead <span class="page-subtitle">{{ $lead->customer_name }}</span></h1>
    <a href="{{ route('leads.show', $lead) }}" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Back</a>
</div>
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="panel-card">
            <form id="editLeadForm">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Date <span class="required">*</span></label>
                        <input type="text" name="date" id="leadDate" class="form-control datepicker" value="{{ $lead->date->format('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Day</label>
                        <input type="text" id="leadDay" class="form-control" value="{{ $lead->day }}" readonly style="background:#f7f8ff;">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            @foreach(['new','contacted','confirmed','converted','lost'] as $s)
                            <option value="{{ $s }}" {{ $lead->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Customer Name <span class="required">*</span></label>
                        <input type="text" name="customer_name" class="form-control" value="{{ $lead->customer_name }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contact Number</label>
                        <input type="text" name="contact_number" class="form-control" value="{{ $lead->contact_number }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Total Reels</label>
                        <input type="number" name="total_reels" class="form-control" value="{{ $lead->total_reels }}" min="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Total Posts</label>
                        <input type="number" name="total_posts" class="form-control" value="{{ $lead->total_posts }}" min="0">
                    </div>
                    <div class="col-md-4"></div>
                    <div class="col-md-4">
                        <label class="form-label">Total Meta Budget (₹)</label>
                        <input type="number" name="total_meta_budget" id="totalBudget" class="form-control" value="{{ $lead->total_meta_budget }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Client Budget (₹)</label>
                        <input type="number" name="client_meta_budget" id="clientBudget" class="form-control" value="{{ $lead->client_meta_budget }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Digital Yug Budget (₹)</label>
                        <input type="number" name="dy_meta_budget" id="dyBudget" class="form-control" value="{{ $lead->dy_meta_budget }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3">{{ $lead->notes }}</textarea>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2 justify-content-end">
                    <a href="{{ route('leads.show', $lead) }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary" id="updateBtn"><i class="fa-solid fa-floppy-disk me-2"></i>Update Lead</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
$(document).ready(function() {
    flatpickr('#leadDate', { dateFormat: 'Y-m-d', allowInput: true,
        onChange: function(d) {
            if(d[0]) { const days=['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday']; $('#leadDay').val(days[d[0].getDay()]); }
        }
    });
    function calc() { $('#dyBudget').val(Math.max(0, ($('#totalBudget').val()||0) - ($('#clientBudget').val()||0))); }
    $('#totalBudget,#clientBudget').on('input', calc);
    $('#editLeadForm').on('submit', function(e) {
        e.preventDefault();
        const btn = $('#updateBtn').prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-2"></i>Updating...');
        ajaxPost('{{ route('leads.update', $lead) }}', $(this).serialize(), function(res) {
            showSuccess(res.message);
            setTimeout(() => location.href='{{ route('leads.show', $lead) }}', 1500);
        }, () => btn.prop('disabled',false).html('<i class="fa-solid fa-floppy-disk me-2"></i>Update Lead'));
    });
});
</script>
@endpush
