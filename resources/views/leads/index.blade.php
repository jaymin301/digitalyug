@extends('layouts.panel')
@section('title', 'Leads')
@section('breadcrumb')
    <li class="breadcrumb-item active">Leads</li>
@endsection
@section('content')
<div class="page-header">
    <h1 class="page-title">Leads Management <span class="page-subtitle">All client leads and inquiries</span></h1>
    @role('Admin|Manager|Sales Executive')
    <a href="{{ route('leads.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus me-2"></i>Add Lead</a>
    @endrole
</div>

<div class="panel-card">
    <div class="panel-card-header">
        <h5 class="panel-card-title"><i class="fa-solid fa-handshake"></i> All Leads</h5>
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <span class="badge rounded-pill" style="background:rgba(108,63,197,0.1);color:#6c3fc5;">{{ $leads->count() }} Total</span>
        </div>
    </div>
    <div class="panel-table-wrapper">
        <table id="leadsTable" class="table table-hover w-100">
            <thead><tr>
                <th class="details-control-header"></th>
                <th>No.</th>
                {{-- <th>Date</th> --}}
                <th>Customer Name</th>
                <th>Mobile Number</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
            </tr></thead>
            <tbody>
                @foreach($leads as $i => $lead)
                <tr data-details="{{ json_encode([
                    "day" => $lead->day,
                    "date" => $lead->date->format('d M Y'),
                    "phone" => $lead->contact_number,
                    "source" => $lead->source,
                    "service" => $lead->service_required,
                    "budget" => "₹" . number_format($lead->total_meta_budget),
                    "reels" => $lead->total_reels,
                    "posts" => $lead->total_posts,
                    "budget_total" => "₹" . number_format($lead->total_meta_budget),
                    "budget_client" => "₹" . number_format($lead->client_meta_budget),
                    "budget_dy" => "₹" . number_format($lead->dy_meta_budget),
                    "notes" => $lead->notes ?? "No notes available.",
                    "created_by" => $lead->createdBy?->name ?? "N/A"
                ]) }}">
                    <td class="details-control">
                        <div class="expand-icon"><i class="fa-solid fa-plus"></i></div>
                    </td>
                    <td><span class="text-muted fw-bold">#{{ $i + 1 }}</span></td>
                    {{-- <td><span class="fw-medium text-dark">{{ $lead->date->format('d M Y') }}</span></td> --}}
                    <td>
                        <div class="fw-bold text-dark">{{ $lead->customer_name }}</div>
                        @if($lead->project)
                            <span class="badge" style="background:rgba(38,222,129,0.1);color:#1a8754;font-size:10px;">Has Project</span>
                        @endif
                    </td>
                    <td><span class="text-muted">{{ $lead->contact_number }}</span></td>
                    <td>{!! $lead->status_badge !!}</td>
                    <td>
                        <div class="action-btns justify-content-end">
                            <a href="{{ route('leads.show', $lead) }}" class="btn-action view" title="View"><i class="fa-solid fa-eye"></i></a>
                            @role('Admin|Manager|Sales Executive')
                                <a href="{{ route('leads.edit', $lead) }}" class="btn-action edit" title="Edit"><i class="fa-solid fa-pen"></i></a>
                            @endrole
                            @role('Admin|Manager')
                                @if(!$lead->project && $lead->status === 'converted')
                                    <button class="btn-action approve" onclick="createProject({{ $lead->id }}, '{{ $lead->customer_name }}')" title="Create Project"><i class="fa-solid fa-folder-plus"></i></button>
                                @endif
                                <button class="btn-action delete btn-delete" data-url="{{ route('leads.destroy', $lead) }}" title="Delete"><i class="fa-solid fa-trash"></i></button>
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

    // ── Desktop expand format ──
    function formatDetails(d) {
        return `
            <div class="expanded-row-details">
                <div class="row g-2">
                    <div class="col-md-4">
                        <div class="detail-item mb-2">
                            <label class="mb-1">Contact & Source</label>
                            <div class="detail-value"><strong>Phone:</strong> ${d.phone}</div>
                            <div class="detail-value"><strong>Source:</strong> ${d.source}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="detail-item mb-2">
                            <label class="mb-1">Service & Production</label>
                            <div class="detail-value"><strong>Service:</strong> ${d.service}</div>
                            <div class="detail-value"><strong>Reels:</strong> ${d.reels}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="detail-item mb-2">
                            <label class="mb-1">Budget Details</label>
                            <div class="detail-value fw-bold text-primary">${d.budget}</div>
                        </div>
                    </div>
                    <div class="col-12 mt-0">
                        <div class="detail-item mb-0">
                            <label class="mb-1">Requirement Notes</label>
                            <div class="detail-notes p-2" style="font-size:12.5px; border-left:3px solid #6c3fc5; background:#f8faff;">
                                ${d.notes ? d.notes : '<span class="text-muted">No additional notes.</span>'}
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;
    }

    var table = $('#leadsTable').DataTable({
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search Leads...",
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
        columnDefs: [
            { orderable: false, targets: [0, 5] }
        ],
        initComplete: function() {
            if ($(window).width() <= 767) {
                setTimeout(buildMobileCards, 100);
            }
        }
    });

    // Desktop row expand
    $('#leadsTable tbody').on('click', 'td.details-control', function () {
        var tr = $(this).closest('tr');
        var row = table.row(tr);
        var detailsData = tr.data('details');
        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
            $(this).find('i').removeClass('fa-minus').addClass('fa-plus');
        } else {
            row.child(formatDetails(detailsData)).show();
            tr.addClass('shown');
            $(this).find('i').removeClass('fa-plus').addClass('fa-minus');
        }
    });

    // ── Mobile Cards: build from table rows ──
    function buildMobileCards() {
        if ($(window).width() > 767) return;

        // Remove old cards if already built
        $('.lead-cards-mobile').remove();

        var $container = $('<div class="lead-cards-mobile"></div>');

        $('#leadsTable tbody tr').each(function() {
            var $tr = $(this);
            var d = $tr.data('details');
            if (!d) return;

            var number   = $tr.find('td:eq(1)').text().trim();
            var name     = $tr.find('td:eq(2) .fw-bold').text().trim();
            var hasBadge = $tr.find('td:eq(2) .badge').length
                            ? $tr.find('td:eq(2) .badge').prop('outerHTML') : '';
            var phone    = $tr.find('td:eq(3)').text().trim();
            var status   = $tr.find('td:eq(4)').html();
            var actions  = $tr.find('td:eq(5) .action-btns').html();

            var card = `
            <div class="lead-card">
                <div class="lead-card-header">
                    <div>
                        <div class="lead-card-name">${name} ${hasBadge}</div>
                    </div>
                    <div class="lead-card-number">${number}</div>
                </div>
                <div class="lead-card-meta">
                    <span class="lead-card-phone"><i class="fa-solid fa-phone"></i>${phone}</span>
                </div>
                <div class="lead-card-footer">
                    <div>${status}</div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="lead-card-actions action-btns">${actions}</div>
                        <button class="lead-card-expand-btn" title="More details">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="lead-card-details">
                    <div class="detail-row">
                        <span class="detail-label">Date</span>
                        <span class="detail-val">${d.day}, ${d.date}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Content</span>
                        <span class="detail-val">${d.reels} Reels &bull; ${d.posts} Posts</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Total Budget</span>
                        <span class="detail-val fw-bold" style="color:#6c3fc5;">${d.budget_total}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Client / Agency</span>
                        <span class="detail-val">${d.budget_client} / ${d.budget_dy}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Created By</span>
                        <span class="detail-val">${d.created_by}</span>
                    </div>
                    ${d.notes ? `<div class="detail-notes">${d.notes}</div>` : ''}
                </div>
            </div>`;

            $container.append(card);
        });

        // ✅ Inject INSIDE panel-table-wrapper, specifically inside dataTables_wrapper after controls
        var $wrapper = $('#leadsTable_wrapper');
        if ($wrapper.length) {
            $wrapper.find('.table-controls').after($container);
        } else {
            $('.panel-table-wrapper').prepend($container);
        }

        // ✅ Hide ONLY the table, NOT the controls/wrapper
        $('#leadsTable').hide();
        $('.panel-table-wrapper').show();

        // Expand toggle
        $(document).off('click', '.lead-card-expand-btn').on('click', '.lead-card-expand-btn', function() {
            var $details = $(this).closest('.lead-card').find('.lead-card-details');
            var isOpen = $details.hasClass('open');
            $details.toggleClass('open');
            $(this).toggleClass('open');
            $(this).find('i')
                .toggleClass('fa-plus', isOpen)
                .toggleClass('fa-minus', !isOpen);
        });
    }

    // Build on draw (debounced to handle multiple draw events during init)
    var drawTimer;
    table.on('draw', function() {
        if ($(window).width() <= 767) {
            clearTimeout(drawTimer);
            drawTimer = setTimeout(function() {
                $('#leadsTable').show();
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
                $('#leadsTable').show();
                $('.panel-table-wrapper').show();
            } else {
                $('#leadsTable').show();
                buildMobileCards();
            }
        }, 200);
    });
});

function createProject(leadId, name) {
    Swal.fire({
        title: 'Create Project?',
        text: `Convert lead "${name}" into a project?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#6c3fc5',
        confirmButtonText: 'Yes, create!'
    }).then(r => {
        if (r.isConfirmed) {
            ajaxPost(`/projects/from-lead/${leadId}`, {}, function(res) {
                showSuccess(res.message);
                setTimeout(() => window.location.href = `/projects/${res.project_id}`, 1500);
            });
        }
    });
}
</script>
@endpush