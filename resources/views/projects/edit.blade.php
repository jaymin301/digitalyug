@extends('layouts.panel')
@section('title', 'Edit Project')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('projects.index') }}">Projects</a></li>
    <li class="breadcrumb-item"><a href="{{ route('projects.show', $project) }}">{{ $project->name }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection
@section('content')
<div class="page-header">
    <h1 class="page-title">Edit Project <span class="page-subtitle">{{ $project->name }}</span></h1>
    <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Back</a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="panel-card">
            <form id="editProjectForm">
                @csrf @method('PUT')
                <div class="row g-3">

                    {{-- Project Name --}}
                    <div class="col-12">
                        <label class="form-label">Project Name <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ $project->name }}" required>
                    </div>

                    {{-- Date & Day --}}
                    <div class="col-md-4">
                        <label class="form-label">Date</label>
                        <input type="text" name="date" id="projectDate" class="form-control datepicker"
                               value="{{ $project->date ? $project->date->format('Y-m-d') : '' }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Day</label>
                        <input type="text" id="projectDay" class="form-control" value="{{ $project->day }}" readonly style="background:#f7f8ff;">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Agency</label>
                        <select name="agency_id" class="form-select">
                            <option value="">Select Agency</option>
                            @foreach($agencies as $agency)
                                <option value="{{ $agency->id }}" {{ $project->agency_id == $agency->id ? 'selected' : '' }}>{{ $agency->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Customer details --}}
                    <div class="col-md-6">
                        <label class="form-label">Customer Name</label>
                        <input type="text" name="customer_name" class="form-control" value="{{ $project->customer_name }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contact Number</label>
                        <input type="text" name="contact_number" class="form-control" value="{{ $project->contact_number }}">
                    </div>

                    {{-- Content plan --}}
                    <div class="col-md-4">
                        <label class="form-label">Total Reels</label>
                        <input type="number" name="total_reels" class="form-control" min="0" value="{{ $project->total_reels }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Total Posts</label>
                        <input type="number" name="total_posts" class="form-control" min="0" value="{{ $project->total_posts }}">
                    </div>
                    <div class="col-md-4"></div>

                    {{-- Meta Budget --}}
                    <div class="col-12"><hr class="my-1"><label class="form-label fw-bold">Meta Budget Distribution</label></div>
                    <div class="col-md-4">
                        <label class="form-label">Total Meta Budget (₹)</label>
                        <input type="number" name="total_meta_budget" id="totalBudget" class="form-control" min="0" value="{{ $project->total_meta_budget }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Client-Side Budget (₹)</label>
                        <input type="number" name="client_meta_budget" id="clientBudget" class="form-control" min="0" value="{{ $project->client_meta_budget }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Digital Yug Budget (₹)</label>
                        <input type="number" name="dy_meta_budget" id="dyBudget" class="form-control" min="0" value="{{ $project->dy_meta_budget }}">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3">{{ $project->notes }}</textarea>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2 justify-content-end">
                    <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary" id="updateBtn"><i class="fa-solid fa-floppy-disk me-2"></i>Update Project</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    flatpickr('#projectDate', {
        dateFormat: 'Y-m-d',
        allowInput: true,
        onChange: function(d) {
            if (d[0]) {
                const days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
                $('#projectDay').val(days[d[0].getDay()]);
            }
        }
    });

    function calc() {
        $('#dyBudget').val(Math.max(0, ($('#totalBudget').val() || 0) - ($('#clientBudget').val() || 0)));
    }
    $('#totalBudget, #clientBudget').on('input', calc);

    $('#editProjectForm').on('submit', function(e) {
        e.preventDefault();
        const btn = $('#updateBtn').prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-2"></i>Updating...');
        ajaxPost('{{ route('projects.update', $project) }}', $(this).serialize(), function(res) {
            showSuccess(res.message);
            setTimeout(() => location.href = '{{ route('projects.show', $project) }}', 1500);
        }, () => btn.prop('disabled', false).html('<i class="fa-solid fa-floppy-disk me-2"></i>Update Project'));
    });
});
</script>
@endpush
