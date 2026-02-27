@extends('layouts.panel')
@section('title', 'Employees')
@section('breadcrumb')
    <li class="breadcrumb-item active">Employees</li>
@endsection
@section('content')
<div class="page-header">
    <h1 class="page-title">Employees <span class="page-subtitle">Manage team members and their roles</span></h1>
    <a href="{{ route('admin.employees.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus me-2"></i>Add Employee</a>
</div>

<div class="panel-card">
    <div class="panel-card-header">
        <h5 class="panel-card-title"><i class="fa-solid fa-users"></i> All Employees</h5>
        <span class="badge rounded-pill" style="background:rgba(108,63,197,0.1);color:#6c3fc5;">{{ $employees->count() }} Total</span>
    </div>
    <div class="panel-table-wrapper">
        <table id="employeesTable" class="table table-hover w-100">
            <thead><tr>
                <th>#</th><th>Name</th><th>Email</th><th>Phone</th>
                <th>Role</th><th>Status</th><th>Actions</th>
            </tr></thead>
            <tbody>
                @foreach($employees as $i => $emp)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:34px;height:34px;border-radius:50%;background:rgba(108,63,197,0.12);color:#6c3fc5;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;flex-shrink:0;">
                                {{ strtoupper(substr($emp->name, 0, 1)) }}
                            </div>
                            <strong>{{ $emp->name }}</strong>
                        </div>
                    </td>
                    <td>{{ $emp->email }}</td>
                    <td>{{ $emp->phone ?? '—' }}</td>
                    <td>
                        @foreach($emp->roles as $role)
                            @php
                                $roleClass = match($role->name) {
                                    'Admin' => 'admin', 'Manager' => 'manager',
                                    'Sales Executive' => 'sales', 'Concept Writer' => 'concept-writer',
                                    'Shooting Person' => 'shooting', 'Video Editor' => 'video-editor',
                                    default => 'manager'
                                };
                            @endphp
                        <span class="role-badge {{ $roleClass }}">{{ $role->name }}</span>
                        @endforeach
                    </td>
                    <td>
                        @if($emp->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div class="action-btns">
                            <a href="{{ route('admin.employees.show', $emp) }}" class="btn-action view" title="View"><i class="fa-solid fa-eye"></i></a>
                            <a href="{{ route('admin.employees.edit', $emp) }}" class="btn-action edit" title="Edit"><i class="fa-solid fa-pen"></i></a>
                            <button class="btn-action toggle"
                                onclick="toggleStatus({{ $emp->id }}, '{{ $emp->name }}')" title="{{ $emp->is_active ? 'Deactivate' : 'Activate' }}">
                                <i class="fa-solid {{ $emp->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                            </button>
                            @if($emp->id !== auth()->id())
                            <button class="btn-action delete btn-delete" data-url="{{ route('admin.employees.destroy', $emp) }}" title="Delete"><i class="fa-solid fa-trash"></i></button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#employeesTable').DataTable({ responsive: true, pageLength: 15, order: [[0, 'asc']] });
});

function toggleStatus(id, name) {
    Swal.fire({
        title: 'Toggle Status?',
        text: `Change active status for ${name}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#6c3fc5',
        confirmButtonText: 'Yes, toggle it!'
    }).then(result => {
        if (result.isConfirmed) {
            $.post(`/admin/employees/${id}/toggle-status`, function(res) {
                showSuccess(res.message);
                setTimeout(() => location.reload(), 1200);
            });
        }
    });
}
</script>
@endpush
