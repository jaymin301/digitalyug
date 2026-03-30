@extends('layouts.panel')
@section('title', 'Projects')
@section('breadcrumb')
    <li class="breadcrumb-item active">Projects</li>
@endsection
@section('content')
<div class="page-header">
    <h1 class="page-title">Projects <span class="page-subtitle">Track active campaigns and workflow stages</span></h1>
</div>

<div class="panel-card">
    <div class="panel-card-header">
        <h5 class="panel-card-title"><i class="fa-solid fa-folder-open"></i> Project Pipeline</h5>
        <div class="d-flex gap-2">
            <span class="badge rounded-pill" style="background:rgba(108,63,197,0.1);color:#6c3fc5;">{{ $projects->count() }} Total</span>
        </div>
    </div>
    <div class="panel-table-wrapper">
        <table id="projectsTable" class="table table-hover w-100">
            <thead><tr>
                <th class="details-control-header"></th>
                <th>#</th>
                <th>Project Name</th>
                <th>Client</th>
                <th>Stage</th>
                <th class="text-end">Actions</th>
            </tr></thead>
            <tbody>
                @foreach($projects as $i => $p)
                <tr data-details="{{ json_encode([
                    "manager" => $p->manager->name ?? "Unassigned",
                    "start_date" => $p->start_date ? $p->start_date->format("d M Y") : "—",
                    "end_date" => $p->end_date ? $p->end_date->format("d M Y") : "—",
                    "client" => $p->lead->customer_name ?? "N/A"
                 ]) }}">
                    <td class="details-control">
                        <div class="expand-icon"><i class="fa-solid fa-plus"></i></div>
                    </td>
                    <td><span class="text-muted fw-bold">#{{ $i + 1 }}</span></td>
                    <td>
                        <div class="fw-bold text-dark">{{ $p->name }}</div>
                        <div class="text-muted small d-none d-md-block">Manager: {{ $p->manager->name ?? 'N/A' }}</div>
                    </td>
                    <td>{{ $p->lead->customer_name ?? 'N/A' }}</td>
                    <td>{!! $p->stage_badge !!}</td>
                    <td>
                        <div class="action-btns justify-content-end">
                            <a href="{{ route('projects.show', $p) }}" class="btn-action view" title="View Details"><i class="fa-solid fa-eye"></i></a>
                            @role('Admin|Manager')
                                @if($p->stage === 'pending')
                                    <button class="btn-action approve" onclick="activateProject({{ $p->id }}, '{{ $p->name }}')" title="Activate Project"><i class="fa-solid fa-bolt"></i></button>
                                @endif
                                <button class="btn-action delete btn-delete" data-url="{{ route('projects.destroy', $p) }}" title="Delete"><i class="fa-solid fa-trash"></i></button>
                            @endrole
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
    function formatDetails(d) {
        return `
            <div class="expanded-row-details">
                <div class="row g-2">
                    <div class="col-md-6">
                        <div class="detail-item mb-2">
                            <label class="mb-1">Project Management</label>
                            <div class="detail-value"><strong>Manager:</strong> ${d.manager}</div>
                            <div class="detail-value"><strong>Client:</strong> ${d.client}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-item mb-2">
                            <label class="mb-1">Timeline</label>
                            <div class="detail-value"><strong>Start:</strong> ${d.start_date}</div>
                            <div class="detail-value"><strong>End:</strong> ${d.end_date}</div>
                        </div>
                    </div>
                </div>
            </div>`;
    }

    var table = $('#projectsTable').DataTable({
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search Projects...",
            lengthMenu: "Show _MENU_",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            paginate: {
                first: '<i class="fa-solid fa-angles-left"></i>',
                last: '<i class="fa-solid fa-angles-right"></i>',
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
            { targets: 0, orderable: false },
            { targets: 5, orderable: false }
        ],
        initComplete: function() {
            if ($(window).width() <= 767) {
                setTimeout(buildMobileCards, 100);
            }
        }
    });

    // Expand logic
    $('#projectsTable tbody').on('click', 'td.details-control', function() {
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

        $('#projectsTable tbody tr').each(function() {
            var $tr = $(this);
            var d = $tr.data('details');
            if (!d) return;

            var name = $tr.find('td:eq(2) .fw-bold').text().trim();
            var stage = $tr.find('td:eq(4)').html();
            var actions = $tr.find('td:eq(5)').html();

            var card = $(`
                <div class="lead-card">
                    <div class="lead-card-header">
                        <div class="lead-card-name">${name}</div>
                        <div class="lead-card-number">#${$tr.find('td:eq(1)').text().trim()}</div>
                    </div>
                    <div class="lead-card-meta">
                        <div class="lead-card-phone"><i class="fa-solid fa-user me-1 text-primary"></i> ${d.client}</div>
                    </div>
                    <div class="lead-card-footer">
                        <div class="d-flex align-items-center gap-2">
                            ${stage}
                            <div class="lead-card-expand-btn"><i class="fa-solid fa-plus"></i></div>
                        </div>
                        <div class="action-btns">${actions}</div>
                    </div>
                    <div class="lead-card-details">
                        <div class="detail-row"><span class="detail-label">Manager</span><span class="detail-val">${d.manager}</span></div>
                        <div class="detail-row"><span class="detail-label">Start Date</span><span class="detail-val">${d.start_date}</span></div>
                        <div class="detail-row"><span class="detail-label">End Date</span><span class="detail-val">${d.end_date}</span></div>
                    </div>
                </div>
            `);
            $container.append(card);
        });

        var $wrapper = $('#projectsTable_wrapper');
        if ($wrapper.length) {
            $wrapper.find('.table-controls').after($container);
        } else {
            $('.panel-table-wrapper').prepend($container);
        }
        $('#projectsTable').hide();
    }

    var drawTimer;
    table.on('draw', function() {
        if ($(window).width() <= 767) {
            clearTimeout(drawTimer);
            drawTimer = setTimeout(function() {
                $('#projectsTable').show();
                buildMobileCards();
            }, 50);
        }
    });

    var resizeTimer;
    $(window).on('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            if ($(window).width() > 767) {
                $('.lead-cards-mobile').remove();
                $('#projectsTable').show();
                $('.panel-table-wrapper').show();
            } else {
                $('#projectsTable').show();
                buildMobileCards();
            }
        }, 200);
    });

    // Card Expand toggle
    $(document).off('click', '.lead-card-expand-btn').on('click', '.lead-card-expand-btn', function() {
        var $btn = $(this);
        var $card = $btn.closest('.lead-card');
        var $details = $card.find('.lead-card-details');
        $btn.toggleClass('open');
        $details.toggleClass('open').slideToggle(200);
    });
});

function activateProject(id, name) {
    Swal.fire({
        title: 'Activate Project?',
        text: `Set start date for "${name}" to begin workflow?`,
        html: `<input type="date" id="projStartDate" class="form-control" value="{{ date('Y-m-d') }}">`,
        icon: 'info',
        showCancelButton: true,
        confirmButtonColor: '#6c3fc5',
        confirmButtonText: 'Activate Now',
        preConfirm: () => {
            const date = document.getElementById('projStartDate').value;
            if (!date) Swal.showValidationMessage('Please select a start date');
            return date;
        }
    }).then(result => {
        if (result.isConfirmed) {
            ajaxPost(`/projects/${id}/activate`, { start_date: result.value }, function(res) {
                showSuccess(res.message);
                setTimeout(() => location.reload(), 1500);
            });
        }
    });
}
</script>
@endpush
