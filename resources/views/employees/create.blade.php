@extends('layouts.panel')
@section('title', 'Add Employee')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.employees.index') }}">Employees</a></li>
    <li class="breadcrumb-item active">Add Employee</li>
@endsection
@section('content')
<div class="page-header">
    <h1 class="page-title">Add Employee <span class="page-subtitle">Create a new team member account</span></h1>
    <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Back</a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="panel-card">
            <form id="employeeForm">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Full Name <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Rahul Sharma" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email Address <span class="required">*</span></label>
                        <input type="email" name="email" class="form-control" placeholder="e.g. rahul@digitalyug.com" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-control" placeholder="e.g. 9876543210">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Assign Role <span class="required">*</span></label>
                        <select name="role" class="form-select select2" required>
                            <option value="">Select Role</option>
                            @foreach($roles as $role)
                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Password <span class="required">*</span></label>
                        <input type="password" name="password" class="form-control" placeholder="Min 8 characters" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirm Password <span class="required">*</span></label>
                        <input type="password" name="password_confirmation" class="form-control" placeholder="Re-enter password" required>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2 justify-content-end">
                    <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fa-solid fa-user-plus me-2"></i>Create Employee
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$('#employeeForm').on('submit', function(e) {
    e.preventDefault();
    const btn = $('#submitBtn');
    btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-2"></i>Creating...');
    ajaxPost('{{ route('admin.employees.store') }}', $(this).serialize(), function(res) {
        showSuccess(res.message);
        setTimeout(() => window.location.href = '{{ route('admin.employees.index') }}', 1500);
    }, function() { btn.prop('disabled',false).html('<i class="fa-solid fa-user-plus me-2"></i>Create Employee'); });
});
</script>
@endpush
