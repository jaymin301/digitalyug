@extends('layouts.panel')
@section('title', 'Edit Employee')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.employees.index') }}">Employees</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection
@section('content')
<div class="page-header">
    <h1 class="page-title">Edit Employee <span class="page-subtitle">Update {{ $employee->name }}'s details</span></h1>
    <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Back</a>
</div>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="panel-card">
            <form id="editForm">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Full Name <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ $employee->name }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email Address <span class="required">*</span></label>
                        <input type="email" name="email" class="form-control" value="{{ $employee->email }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-control" value="{{ $employee->phone }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Role <span class="required">*</span></label>
                        <select name="role" class="form-select select2" required>
                            @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ $employee->hasRole($role->name) ? 'selected' : '' }}>{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">New Password <small class="text-muted">(leave blank to keep)</small></label>
                        <input type="password" name="password" class="form-control" placeholder="Min 8 characters">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control">
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2 justify-content-end">
                    <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary" id="updateBtn"><i class="fa-solid fa-floppy-disk me-2"></i>Update Employee</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
$('#editForm').on('submit', function(e) {
    e.preventDefault();
    const btn = $('#updateBtn').prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-2"></i>Updating...');
    ajaxPost('{{ route('admin.employees.update', $employee) }}', $(this).serialize(), function(res) {
        showSuccess(res.message);
        setTimeout(() => location.href = '{{ route('admin.employees.index') }}', 1500);
    }, () => btn.prop('disabled',false).html('<i class="fa-solid fa-floppy-disk me-2"></i>Update Employee'));
});
</script>
@endpush
