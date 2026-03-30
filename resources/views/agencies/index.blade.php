@extends('layouts.panel')

@section('title', 'Agencies')

@section('breadcrumb')
    <li class="breadcrumb-item active">Agencies</li>
@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">Agencies <span class="page-subtitle">Manage partner agencies</span></h1>
    <a href="{{ route('agencies.create') }}" class="btn btn-primary">
        <i class="fa-solid fa-plus me-2"></i>Create Agency
    </a>
</div>

<div class="panel-card">
    <div class="panel-card-header">
        <h5 class="panel-card-title"><i class="fa-solid fa-building"></i> All Agencies</h5>
        <span class="badge rounded-pill" style="background:rgba(108,63,197,0.1);color:#6c3fc5;">{{ $agencies->count() }} Total</span>
    </div>
    <div class="panel-table-wrapper">
        <table class="table table-hover w-100" id="agenciesTable">
                <thead>
                    <tr>
                        <th class="details-control-header"></th>
                        <th>#</th>
                        <th>Agency Name</th>
                        <th>Owner Name</th>
                        <th>Contact</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($agencies as $i => $agency)
                    <tr data-details="{{ json_encode([
                        'name' => $agency->name,
                        'owner' => $agency->owner_name,
                        'contact' => $agency->contact,
                        'remark' => $agency->remark ?: '—'
                    ]) }}">
                        <td class="details-control">
                            <div class="expand-icon"><i class="fa-solid fa-plus"></i></div>
                        </td>
                        <td><span class="text-muted fw-bold">#{{ $i + 1 }}</span></td>
                        <td>
                            <div class="fw-bold text-dark">{{ $agency->name }}</div>
                        </td>
                        <td>{{ $agency->owner_name }}</td>
                        <td>{{ $agency->contact }}</td>
                        <td class="text-end pe-3">
                            <div class="action-btns justify-content-end">
                                <a href="{{ route('agencies.edit', $agency) }}" class="btn-action edit" title="Edit">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <button type="button" class="btn-action delete btn-delete" 
                                        data-url="{{ route('agencies.destroy', $agency) }}" title="Delete">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
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
                    <div class="col-12">
                        <div class="detail-item mb-0">
                            <label class="mb-1">Agency Remarks</label>
                            <div class="detail-notes p-2" style="font-size:12.5px; border-left:3px solid #6c3fc5; background:#f8faff;">
                                ${d.remark}
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;
    }

    var table = $('#agenciesTable').DataTable({
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search Agencies...",
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
            { orderable: false, targets: [0, 5] }
        ],
        initComplete: function() {
            if ($(window).width() <= 767) {
                setTimeout(buildMobileCards, 100);
            }
        }
    });

    // Expand logic
    $('#agenciesTable tbody').on('click', 'td.details-control', function () {
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

        $('#agenciesTable tbody tr').each(function() {
            var $tr = $(this);
            var d = $tr.data('details');
            if (!d) return;

            var name = $tr.find('td:eq(2)').text().trim();
            var owner = $tr.find('td:eq(3)').text().trim();
            var contact = $tr.find('td:eq(4)').text().trim();
            var actions = $tr.find('td:eq(5) .action-btns').html();

            var card = `
            <div class="lead-card">
                <div class="lead-card-header">
                    <div class="lead-card-name">${name}</div>
                    <div class="lead-card-number">#${$tr.find('td:eq(1)').text().trim().replace('#','')}</div>
                </div>
                <div class="lead-card-meta">
                    <span class="lead-card-phone"><i class="fa-solid fa-user me-1"></i>${owner}</span>
                </div>
                <div class="lead-card-footer">
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted small"><i class="fa-solid fa-phone me-1"></i>${contact}</span>
                        <div class="lead-card-expand-btn"><i class="fa-solid fa-plus"></i></div>
                    </div>
                    <div class="action-btns">${actions}</div>
                </div>
                <div class="lead-card-details">
                    <div class="detail-notes p-2" style="font-size:12px; border-left:3px solid #6c3fc5; background:#f8faff;">
                        ${d.remark}
                    </div>
                </div>
            </div>`;
            $container.append(card);
        });

        var $wrapper = $('#agenciesTable_wrapper');
        if ($wrapper.length) {
            $wrapper.find('.table-controls').after($container);
        } else {
            $('.panel-table-wrapper').prepend($container);
        }
        $('#agenciesTable').hide();

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
            $('#agenciesTable').show();
        } else {
            buildMobileCards();
        }
    });

    if ($(window).width() <= 767) buildMobileCards();
});
</script>
@endpush
