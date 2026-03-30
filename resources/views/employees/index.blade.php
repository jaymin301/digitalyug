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
                <th class="details-control-header"></th>
                <th>#</th><th>Name</th><th>Email</th><th>Phone</th>
                <th>Role</th><th>Status</th><th class="text-end">Actions</th>
            </tr></thead>
            <tbody>
                @foreach($employees as $i => $emp)
                <tr data-details="{{ json_encode([
                    'name' => $emp->name,
                    'email' => $emp->email,
                    'phone' => $emp->phone ?? '—',
                    'role' => $emp->roles->pluck('name')->implode(', '),
                    'status' => $emp->is_active ? 'Active' : 'Inactive'
                ]) }}">
                    <td class="details-control">
                        <div class="expand-icon"><i class="fa-solid fa-plus"></i></div>
                    </td>
                    <td><span class="text-muted fw-bold">#{{ $i + 1 }}</span></td>
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
                        <div class="action-btns justify-content-end">
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
$(document).ready(function () {
    function formatDetails(d) {
        return `
            <div class="expanded-row-details">
                <div class="row g-2">
                    <div class="col-md-6">
                        <div class="detail-item mb-2">
                            <label class="mb-1">Contact Information</label>
                            <div class="detail-value"><strong>Email:</strong> ${d.email}</div>
                            <div class="detail-value"><strong>Phone:</strong> ${d.phone}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-item mb-2">
                            <label class="mb-1">Organizational Details</label>
                            <div class="detail-value"><strong>Role:</strong> ${d.role}</div>
                            <div class="detail-value"><strong>Status:</strong> ${d.status}</div>
                        </div>
                    </div>
                </div>
            </div>`;
    }

    var table = $('#employeesTable').DataTable({ 
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search Employees...",
            lengthMenu: "Show _MENU_",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            paginate: {
                next: '<i class="fa-solid fa-chevron-right"></i>',
                previous: '<i class="fa-solid fa-chevron-left"></i>'
            }
        },
        dom: '<"table-controls mb-3"<"d-flex justify-content-between align-items-center flex-wrap gap-3"lf>>t<"table-footer mt-3"<"d-flex justify-content-between align-items-center flex-wrap gap-3"ip>>',
        responsive: false,
        pageLength: 10,
        ordering: true,
        order: [[1, 'asc']],
        columnDefs: [
            { orderable: false, targets: [0, 7] }
        ],
        initComplete: function() {
            if ($(window).width() <= 767) {
                setTimeout(buildMobileCards, 100);
            }
        }
    });

    // Expand logic
    $('#employeesTable tbody').on('click', 'td.details-control', function () {
        var tr = $(this).closest('tr');
        var row = table.row(tr);
        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
            $(this).find('i').removeClass('fa-minus').addClass('fa-plus');
        } else {
            row.child(formatDetails(tr.data('details'))).show();
            tr.addClass('shown');
            $(this).find('i').removeClass('fa-plus').addClass('fa-minus');
        }
    });

    // Mobile Cards
    function buildMobileCards() {
        if ($(window).width() > 767) return;
        $('.lead-cards-mobile').remove();
        var $container = $('<div class="lead-cards-mobile"></div>');

        $('#employeesTable tbody tr').each(function() {
            var $tr = $(this);
            var d = $tr.data('details');
            if (!d) return;

            var name = $tr.find('td:eq(2)').html();
            var phone = $tr.find('td:eq(4)').text().trim();
            var role = $tr.find('td:eq(5)').html();
            var status = $tr.find('td:eq(6)').html();
            var actions = $tr.find('td:eq(7) .action-btns').html();

            var card = `
            <div class="lead-card">
                <div class="lead-card-header">
                    <div>${name}</div>
                    <div class="lead-card-number">#${$tr.find('td:eq(1)').text().trim().replace('#','')}</div>
                </div>
                <div class="lead-card-meta">
                    <span class="lead-card-phone"><i class="fa-solid fa-phone"></i>${phone}</span>
                </div>
                <div class="lead-card-footer">
                    <div class="d-flex align-items-center gap-2">
                        ${status}
                        <div class="lead-card-expand-btn"><i class="fa-solid fa-plus"></i></div>
                    </div>
                    <div class="action-btns">${actions}</div>
                </div>
                <div class="lead-card-details">
                    <div class="detail-row"><span class="detail-label">Email</span><span class="detail-val">${d.email}</span></div>
                    <div class="detail-row"><span class="detail-label">Role</span><span class="detail-val">${d.role}</span></div>
                </div>
            </div>`;
            $container.append(card);
        });

        var $wrapper = $('#employeesTable_wrapper');
        if ($wrapper.length) {
            $wrapper.find('.table-controls').after($container);
        } else {
            $('.panel-table-wrapper').prepend($container);
        }
        $('#employeesTable').hide();

        // Expand toggle
        $(document).off('click', '.lead-card-expand-btn').on('click', '.lead-card-expand-btn', function() {
            var $details = $(this).closest('.lead-card').find('.lead-card-details');
            var isOpen = $details.hasClass('open');
            $details.toggleClass('open');
            $(this).toggleClass('open');
            $(this).find('i').toggleClass('fa-plus', isOpen).toggleClass('fa-minus', !isOpen);
        });
    }

    table.on('draw', function() {
        if ($(window).width() <= 767) buildMobileCards();
    });

    $(window).on('resize', function() {
        if ($(window).width() > 767) {
            $('.lead-cards-mobile').remove();
            $('#employeesTable').show();
        } else {
            buildMobileCards();
        }
    });

    if ($(window).width() <= 767) buildMobileCards();
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
