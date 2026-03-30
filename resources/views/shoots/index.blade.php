@extends('layouts.panel')
@section('title', 'Shoot Schedules')
@section('breadcrumb')
    <li class="breadcrumb-item active">Shoots</li>
@endsection
@section('content')
<div class="page-header">
    <h1 class="page-title">Shooting Schedules <span class="page-subtitle">Manage on-location production</span></h1>
    @role('Admin|Manager')
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#projectSelectModal">
        <i class="fa-solid fa-camera me-2"></i>Schedule Shoot
    </button>
    {{-- <div class="dropdown">
        <button class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">Schedule Shoot</button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li class="dropdown-header">Select Project</li>
            @foreach(\App\Models\Project::whereNotIn('stage',['completed','pending'])->get() as $p)
                <li><a class="dropdown-item" href="{{ route('shoots.create', $p) }}">{{ $p->name }}</a></li>
            @endforeach
        </ul>
    </div> --}}
    @endrole
</div>

<div class="panel-card">
    <div class="panel-card-header">
        <h5 class="panel-card-title"><i class="fa-solid fa-camera"></i> Shoot Pipeline</h5>
    </div>
    <div class="panel-table-wrapper">
        <table id="shootsTable" class="table table-hover w-100">
            <thead><tr>
                <th class="details-control-header"></th>
                <th>Date</th>
                <th>Project</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
            </tr></thead>
            <tbody>
                @foreach($shoots as $i => $s)
                <tr data-details="{{ json_encode([
                    "location" => $s->location,
                    "shooter" => $s->shootingPerson->name ?? "N/A",
                    "duration" => $s->duration ?? "—",
                    "reels" => $s->reels_shot,
                    "project" => $s->project->name ?? "N/A"
                ]) }}">
                    <td class="details-control">
                        <div class="expand-icon"><i class="fa-solid fa-plus"></i></div>
                    </td>
                    <td><strong>{{ $s->shoot_date->format('d M Y') }}</strong></td>
                    <td>
                        <div class="fw-bold text-dark">{{ $s->project->name ?? 'N/A' }}</div>
                        <div class="text-muted small d-none d-md-block">Shooter: {{ $s->shootingPerson->name ?? 'N/A' }}</div>
                    </td>
                    <td>{!! $s->status_badge !!}</td>
                    <td>
                        <div class="action-btns justify-content-end">
                            <a href="{{ route('shoots.show', $s) }}" class="btn-action view" title="View/Check-in"><i class="fa-solid fa-eye"></i></a>
                            @role('Admin|Manager')
                                <button class="btn-action delete btn-delete" data-url="{{ route('shoots.destroy', $s) }}" title="Delete"><i class="fa-solid fa-trash"></i></button>
                            @endrole
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@role('Admin|Manager')
    <div class="modal fade" id="projectSelectModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" style="max-width:560px;">
            <div class="modal-content" style="border-radius:16px;border:none;box-shadow:0 20px 60px rgba(0,0,0,0.15);">
                <div class="modal-header" style="border-bottom:1px solid #f0f2f8;padding:20px 24px;">
                    <div>
                        <h5 class="modal-title fw-bold mb-1">
                            <i class="fa-solid fa-camera me-2 text-primary"></i>Schedule a Shoot
                        </h5>
                        <p class="text-muted small mb-0">Select a project to schedule shoot for</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">

                    {{-- Search --}}
                    <div class="mb-3">
                        <input type="text" id="projectSearch" class="form-control"
                            placeholder="Search project..."
                            style="border-radius:10px;">
                    </div>

                    {{-- Project list --}}
                    <div id="projectList" style="max-height:380px;overflow-y:auto;overflow-x:hidden;padding-right:4px;"> {{-- ✅ overflow-x hidden --}}
                        @forelse(\App\Models\Project::with('lead','approvedConcepts')->whereNotIn('stage', ['completed', 'pending'])->latest()->get() as $p)
                            <a href="{{ route('shoots.create', $p) }}"
                            class="project-select-item d-flex align-items-center gap-3 p-3 rounded-3 mb-2 text-decoration-none"
                            style="border:1px solid #f0f2f8;transition:all 0.15s;overflow:hidden;" {{-- ✅ overflow hidden on item --}}
                            data-name="{{ strtolower($p->name) }}">

                                {{-- Icon --}}
                                <div class="d-flex align-items-center justify-content-center rounded-3 flex-shrink-0"
                                    style="width:40px;height:40px;background:rgba(108,63,197,0.08);">
                                    <i class="fa-solid fa-folder-open" style="color:#6c3fc5;font-size:16px;"></i>
                                </div>

                                {{-- Info --}}
                                <div style="flex:1;min-width:0;overflow:hidden;"> {{-- ✅ overflow hidden --}}
                                    <div class="fw-semibold text-dark text-truncate" style="font-size:14px;">{{ $p->name }}</div>
                                    <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                                        <span class="text-muted text-truncate" style="font-size:12px;max-width:140px;">
                                            <i class="fa-solid fa-user me-1"></i>{{ $p->lead->customer_name ?? 'N/A' }}
                                        </span>
                                        <span style="font-size:11px;padding:2px 8px;border-radius:10px;white-space:nowrap;
                                            @if($p->stage === 'shooting') background:rgba(247,183,49,0.12);color:#c67c00;
                                            @elseif($p->stage === 'concept') background:rgba(69,170,242,0.12);color:#1a6fa3;
                                            @elseif($p->stage === 'editing') background:rgba(108,63,197,0.12);color:#6c3fc5;
                                            @else background:#f0f2f8;color:#718096;
                                            @endif">
                                            {{ ucfirst($p->stage) }}
                                        </span>
                                        @if($p->approvedConcepts && $p->approvedConcepts->count() > 0)
                                        <span style="font-size:11px;padding:2px 8px;border-radius:10px;background:rgba(38,222,129,0.12);color:#1a8754;white-space:nowrap;">
                                            <i class="fa-solid fa-check me-1"></i>{{ $p->approvedConcepts->count() }} ready
                                        </span>
                                        @else
                                        <span style="font-size:11px;padding:2px 8px;border-radius:10px;background:rgba(235,77,75,0.1);color:#eb4d4b;white-space:nowrap;">
                                            <i class="fa-solid fa-triangle-exclamation me-1"></i>No concepts
                                        </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Arrow --}}
                                <i class="fa-solid fa-chevron-right text-muted flex-shrink-0" style="font-size:11px;"></i>
                            </a>
                        @empty
                            <div class="text-center py-5">
                                <i class="fa-solid fa-folder-open fa-3x text-muted opacity-25 mb-3 d-block"></i>
                                <p class="text-muted">No active projects available.</p>
                            </div>
                        @endforelse
                    </div>

                </div>
            </div>
        </div>
    </div>
@endrole
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
                            <label class="mb-1">Location & Team</label>
                            <div class="detail-value"><strong>Location:</strong> ${d.location}</div>
                            <div class="detail-value"><strong>Shooter:</strong> ${d.shooter}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-item mb-2">
                            <label class="mb-1">Production Stats</label>
                            <div class="detail-value"><strong>Duration:</strong> ${d.duration}</div>
                            <div class="detail-value text-primary fw-bold"><strong>Reels Shot:</strong> ${d.reels}</div>
                        </div>
                    </div>
                </div>
            </div>`;
    }

    var table = $('#shootsTable').DataTable({ 
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search Shoots...",
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
        order: [[1, 'desc']],
        columnDefs: [
            { targets: 0, orderable: false },
            { targets: 4, orderable: false }
        ],
        initComplete: function() {
            if ($(window).width() <= 767) {
                setTimeout(buildMobileCards, 100);
            }
        }
    });

    // Expand logic
    $('#shootsTable tbody').on('click', 'td.details-control', function() {
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

        $('#shootsTable tbody tr').each(function() {
            var $tr = $(this);
            var d = $tr.data('details');
            if (!d) return;

            var date = $tr.find('td:eq(1)').text().trim();
            var project = $tr.find('td:eq(2) .fw-bold').text().trim();
            var status = $tr.find('td:eq(3)').html();
            var actions = $tr.find('td:eq(4)').html();

            var card = $(`
                <div class="lead-card">
                    <div class="lead-card-header">
                        <div class="lead-card-name text-truncate">${project}</div>
                        <div class="lead-card-number">${date}</div>
                    </div>
                    <div class="lead-card-meta">
                        <div class="lead-card-phone"><i class="fa-solid fa-location-dot me-1 text-primary"></i> ${d.location}</div>
                    </div>
                    <div class="lead-card-footer">
                        <div class="d-flex align-items-center gap-2">
                            ${status}
                            <div class="lead-card-expand-btn"><i class="fa-solid fa-plus"></i></div>
                        </div>
                        <div class="action-btns">${actions}</div>
                    </div>
                    <div class="lead-card-details">
                        <div class="detail-row"><span class="detail-label">Shooter</span><span class="detail-val">${d.shooter}</span></div>
                        <div class="detail-row"><span class="detail-label">Duration</span><span class="detail-val">${d.duration}</span></div>
                        <div class="detail-row"><span class="detail-label">Reels Shot</span><span class="detail-val text-primary fw-bold">${d.reels}</span></div>
                    </div>
                </div>
            `);
            $container.append(card);
        });

        var $wrapper = $('#shootsTable_wrapper');
        if ($wrapper.length) {
            $wrapper.find('.table-controls').after($container);
        } else {
            $('.panel-table-wrapper').prepend($container);
        }
        $('#shootsTable').hide();
    }

    var drawTimer;
    table.on('draw', function() {
        if ($(window).width() <= 767) {
            clearTimeout(drawTimer);
            drawTimer = setTimeout(function() {
                $('#shootsTable').show();
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
                $('#shootsTable').show();
                $('.panel-table-wrapper').show();
            } else {
                $('#shootsTable').show();
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

    $('#projectSearch').on('input', function() {
        const q = $(this).val().toLowerCase();
        $('.project-select-item').each(function() {
            const name = $(this).data('name');
            if (name.includes(q)) {
                $(this).css('display', 'flex');
            } else {
                this.style.setProperty('display', 'none', 'important');
            }
        });
    });

    $(document).on('mouseenter', '.project-select-item', function() {
        $(this).css({
            'background': '#fafbff',
            'border-color': '#6c3fc5',
            'transform': 'translateX(3px)'
        });
    }).on('mouseleave', '.project-select-item', function() {
        $(this).css({
            'background': '',
            'border-color': '#f0f2f8',
            'transform': ''
        });
    });

    $('#projectSelectModal').on('hidden.bs.modal', function() {
        $('#projectSearch').val('');
        $('.project-select-item').show();
    });
});
</script>
@endpush
