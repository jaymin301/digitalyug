@extends('layouts.panel')
@section('title', 'Concept Tasks')
@section('breadcrumb')
    <li class="breadcrumb-item active">Concepts</li>
@endsection
@section('content')
<div class="page-header">
    <h1 class="page-title">Concept Writing <span class="page-subtitle">Track and review creative work</span></h1>
</div>

<div class="panel-card">
    <div class="panel-card-header">
        <h5 class="panel-card-title"><i class="fa-solid fa-lightbulb"></i> Concept Pipeline</h5>
    </div>
    <div class="panel-table-wrapper">
        <table id="conceptsTable" class="table table-hover w-100">
            <thead><tr>
                <th class="details-control-header"></th>
                <th>#</th>
                <th>Project</th>
                <th>Due Date</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
            </tr></thead>
            <tbody>
                @foreach($tasks as $i => $t)
                <tr data-details="{{ json_encode([
                    "writer" => $t->assignedTo->name ?? "Unassigned",
                    "required" => $t->concepts_required,
                    "approved" => $t->concepts()->where("status","approved")->count(),
                    "project" => $t->project->name ?? "N/A"
                ]) }}">
                    <td class="details-control">
                        <div class="expand-icon"><i class="fa-solid fa-plus"></i></div>
                    </td>
                    <td><span class="text-muted fw-bold">#{{ $i + 1 }}</span></td>
                    <td>
                        <div class="fw-bold text-dark">{{ $t->project->name ?? 'N/A' }}</div>
                        <div class="text-muted small d-none d-md-block">Writer: {{ $t->assignedTo->name ?? 'Unassigned' }}</div>
                    </td>
                    <td><span class="fw-medium text-dark">{{ $t->due_date ? $t->due_date->format('d M Y') : '—' }}</span></td>
                    <td>{!! $t->status_badge !!}</td>
                    <td>
                        <div class="action-btns justify-content-end">
                            @role('Concept Writer')
                                <a href="{{ route('concepts.submit-form', $t) }}" class="btn-action edit" title="Submit Concepts"><i class="fa-solid fa-pen-nib"></i></a>
                            @endrole
                            <a href="{{ route('concepts.project', $t->project_id) }}" class="btn-action view" title="Review"><i class="fa-solid fa-magnifying-glass"></i></a>
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
                    <div class="col-md-5">
                        <div class="detail-item mb-2">
                            <label class="mb-1">Creative Assignment</label>
                            <div class="detail-value"><strong>Writer:</strong> ${d.writer}</div>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="detail-item mb-2">
                            <label class="mb-1">Concept Progress</label>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-soft-info text-info">Required: ${d.required}</span>
                                <span class="badge bg-soft-success text-success">Approved: ${d.approved}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;
    }

    var table = $('#conceptsTable').DataTable({
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search Concepts...",
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
    $('#conceptsTable tbody').on('click', 'td.details-control', function() {
        var tr = $(this).closest('tr');
        var row = table.row(tr);
        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
            $(this).find('i').removeClass('fa-minus').addClass('fa-plus');
        } else {
            row.child(formatDetails(tr.data('details'))).show();
            tr.addClass('shown');
            $(this).find('i').removeClass('fa-minus').addClass('fa-plus');
        }
    });

    // Mobile Cards
    function buildMobileCards() {
        if ($(window).width() > 767) return;
        $('.lead-cards-mobile').remove();
        var $container = $('<div class="lead-cards-mobile"></div>');

        $('#conceptsTable tbody tr').each(function() {
            var $tr = $(this);
            var d = $tr.data('details');
            if (!d) return;

            var project = $tr.find('td:eq(2) .fw-bold').text().trim();
            var dueDate = $tr.find('td:eq(3)').text().trim();
            var status = $tr.find('td:eq(4)').html();
            var actions = $tr.find('td:eq(5)').html();

            var card = $(`
                <div class="lead-card">
                    <div class="lead-card-header">
                        <div class="lead-card-name text-truncate">${project}</div>
                        <div class="lead-card-number">#${$tr.find('td:eq(1)').text().trim()}</div>
                    </div>
                    <div class="lead-card-meta">
                        <div class="lead-card-phone"><i class="fa-solid fa-calendar me-1 text-primary"></i> ${dueDate}</div>
                    </div>
                    <div class="lead-card-footer">
                        <div class="d-flex align-items-center gap-2">
                            ${status}
                            <div class="lead-card-expand-btn"><i class="fa-solid fa-plus"></i></div>
                        </div>
                        <div class="action-btns">${actions}</div>
                    </div>
                    <div class="lead-card-details">
                        <div class="detail-row"><span class="detail-label">Writer</span><span class="detail-val">${d.writer}</span></div>
                        <div class="detail-row"><span class="detail-label">Required</span><span class="detail-val">${d.required}</span></div>
                        <div class="detail-row"><span class="detail-label">Approved</span><span class="detail-val text-success fw-bold">${d.approved}</span></div>
                    </div>
                </div>
            `);
            $container.append(card);
        });

        var $wrapper = $('#conceptsTable_wrapper');
        if ($wrapper.length) {
            $wrapper.find('.table-controls').after($container);
        } else {
            $('.panel-table-wrapper').prepend($container);
        }
        $('#conceptsTable').hide();
    }

    var drawTimer;
    table.on('draw', function() {
        if ($(window).width() <= 767) {
            clearTimeout(drawTimer);
            drawTimer = setTimeout(function() {
                $('#conceptsTable').show();
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
                $('#conceptsTable').show();
                $('.panel-table-wrapper').show();
            } else {
                $('#conceptsTable').show();
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
</script>
@endpush
