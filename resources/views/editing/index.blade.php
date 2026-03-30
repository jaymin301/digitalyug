@extends('layouts.panel')
@section('title', 'Editing Tasks')
@section('breadcrumb')
    <li class="breadcrumb-item active">Editing</li>
@endsection
@section('content')
<div class="page-header">
    <h1 class="page-title">Video Editing <span class="page-subtitle">Manage post-production and approvals</span></h1>
    @role('Admin|Manager')
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#projectSelectModal">
            <i class="fa-solid fa-film me-2"></i>Assign Editing
        </button>
    {{-- <div class="dropdown">
        <button class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">Assign Editing</button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li class="dropdown-header">Select Project</li>
            @foreach(\App\Models\Project::whereNotIn('stage',['completed','pending'])->orderBy('created_at', 'desc')->get() as $p)
                <li><a class="dropdown-item" href="{{ route('editing.assign-form', $p) }}">{{ $p->name }}</a></li>
            @endforeach
        </ul>
    </div> --}}
    @endrole
</div>

<div class="panel-card">
    <div class="panel-card-header">
        <h5 class="panel-card-title"><i class="fa-solid fa-film"></i> Editing Queue</h5>
    </div>
    <div class="panel-table-wrapper">
        <table id="editingTable" class="table table-hover w-100">
            <thead><tr>
                <th class="details-control-header"></th>
                <th>Task Title</th>
                <th>Project</th>
                <th>Progress</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
            </tr></thead>
            <tbody>
                @foreach($tasks as $i => $t)
                <tr data-details="{{ json_encode([
                    'editor' => $t->assignedTo->name ?? "N/A",
                    'concept' => $t->concept->title ?? "General",
                    'approved' => $t->approved_videos,
                    'total' => $t->total_videos,
                    'project' => $t->project->name ?? "N/A"
                ]) }}">
                    <td class="details-control">
                        <div class="expand-icon"><i class="fa-solid fa-plus"></i></div>
                    </td>
                    <td>
                        <div class="fw-bold text-dark">{{ $t->title }}</div>
                        <div class="text-muted small d-none d-md-block">Editor: {{ $t->assignedTo->name ?? 'N/A' }}</div>
                    </td>
                    <td>{{ $t->project->name ?? 'N/A' }}</td>
                    <td style="min-width:120px;">
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height:6px;"><div class="progress-bar" style="width:{{ $t->progress_percent }}%"></div></div>
                            <span class="small fw-bold text-dark">{{ $t->approved_videos }}/{{ $t->total_videos }}</span>
                        </div>
                    </td>
                    <td>{!! $t->status_badge !!}</td>
                    <td>
                        <div class="action-btns justify-content-end">
                            <a href="{{ route('editing.show', $t) }}" class="btn-action view" title="View/Update"><i class="fa-solid fa-eye"></i></a>
                            @role('Admin|Manager')
                                <button class="btn-action delete btn-delete" data-url="{{ route('editing.destroy', $t) }}" title="Delete"><i class="fa-solid fa-trash"></i></button>
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
                        <i class="fa-solid fa-film me-2 text-primary"></i>Assign Editing Task
                    </h5>
                    <p class="text-muted small mb-0">Select a project to assign editing for</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4">

                {{-- Search --}}
                <div class="mb-3">
                    <input type="text" id="editProjectSearch" class="form-control"
                        placeholder="Search project..."
                        style="border-radius:10px;">
                </div>

                {{-- Project list --}}
                <div id="editProjectList" style="max-height:380px;overflow-y:auto;overflow-x:hidden;padding-right:4px;">
                    @forelse(\App\Models\Project::with('lead','approvedConcepts')->whereNotIn('stage', ['completed', 'pending'])->latest()->get() as $p)
                        <a href="{{ route('editing.assign-form', $p) }}"
                            class="edit-project-select-item d-flex align-items-center gap-3 p-3 rounded-3 mb-2 text-decoration-none"
                            style="border:1px solid #f0f2f8;transition:all 0.15s;overflow:hidden;"
                            data-name="{{ strtolower($p->name) }}">

                            {{-- Icon --}}
                            <div class="d-flex align-items-center justify-content-center rounded-3 flex-shrink-0"
                                style="width:40px;height:40px;background:rgba(108,63,197,0.08);">
                                <i class="fa-solid fa-folder-open" style="color:#6c3fc5;font-size:16px;"></i>
                            </div>

                            {{-- Info --}}
                            <div style="flex:1;min-width:0;overflow:hidden;">
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
                                        <i class="fa-solid fa-check me-1"></i>{{ $p->approvedConcepts->count() }} concepts
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
                            <label class="mb-1">Assignment Info</label>
                            <div class="detail-value"><strong>Editor:</strong> ${d.editor}</div>
                            <div class="detail-value"><strong>Concept:</strong> ${d.concept}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-item mb-2">
                            <label class="mb-1">Production Progress</label>
                            <div class="detail-value">Approved Videos: <span class="text-success fw-bold">${d.approved}</span> / ${d.total}</div>
                            <div class="progress mt-2" style="height:6px; background:#e2e8f0;"><div class="progress-bar" style="width:${(d.approved/d.total)*100}%"></div></div>
                        </div>
                    </div>
                </div>
            </div>`;
    }

    var table = $('#editingTable').DataTable({ 
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search Tasks...",
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
    $('#editingTable tbody').on('click', 'td.details-control', function() {
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

        $('#editingTable tbody tr').each(function() {
            var $tr = $(this);
            var d = $tr.data('details');
            if (!d) return;

            var title = $tr.find('td:eq(1) .fw-bold').text().trim();
            var project = $tr.find('td:eq(2)').text().trim();
            var progress = $tr.find('td:eq(3)').html();
            var status = $tr.find('td:eq(4)').html();
            var actions = $tr.find('td:eq(5)').html();

            var card = $(`
                <div class="lead-card">
                    <div class="lead-card-header">
                        <div class="lead-card-name text-truncate">${title}</div>
                        <div class="lead-card-number">Editing</div>
                    </div>
                    <div class="lead-card-meta">
                        <div class="lead-card-phone"><i class="fa-solid fa-folder me-1 text-primary"></i> ${project}</div>
                    </div>
                    <div class="lead-card-body py-2">
                        ${progress}
                    </div>
                    <div class="lead-card-footer">
                        <div class="d-flex align-items-center gap-2">
                            ${status}
                            <div class="lead-card-expand-btn"><i class="fa-solid fa-plus"></i></div>
                        </div>
                        <div class="action-btns">${actions}</div>
                    </div>
                    <div class="lead-card-details">
                        <div class="detail-row"><span class="detail-label">Editor</span><span class="detail-val">${d.editor}</span></div>
                        <div class="detail-row"><span class="detail-label">Concept</span><span class="detail-val">${d.concept}</span></div>
                    </div>
                </div>
            `);
            $container.append(card);
        });

        var $wrapper = $('#editingTable_wrapper');
        if ($wrapper.length) {
            $wrapper.find('.table-controls').after($container);
        } else {
            $('.panel-table-wrapper').prepend($container);
        }
        $('#editingTable').hide();
    }

    var drawTimer;
    table.on('draw', function() {
        if ($(window).width() <= 767) {
            clearTimeout(drawTimer);
            drawTimer = setTimeout(function() {
                $('#editingTable').show();
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
                $('#editingTable').show();
                $('.panel-table-wrapper').show();
            } else {
                $('#editingTable').show();
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

      // Client-side search
    $('#editProjectSearch').on('input', function() {
        const q = $(this).val().toLowerCase();
        $('.edit-project-select-item').each(function() {
            const name = $(this).data('name');
            if (name.includes(q)) {
                $(this).css('display', 'flex');
            } else {
                this.style.setProperty('display', 'none', 'important');
            }
        });
    });

    // Hover effects
    $(document).on('mouseenter', '.edit-project-select-item', function() {
        $(this).css({
            'background': '#fafbff',
            'border-color': '#6c3fc5',
            'transform': 'translateX(3px)'
        });
    }).on('mouseleave', '.edit-project-select-item', function() {
        $(this).css({
            'background': '',
            'border-color': '#f0f2f8',
            'transform': ''
        });
    });

    // Reset on close
    $('#projectSelectModal').on('hidden.bs.modal', function() {
        $('#editProjectSearch').val('');
        $('.edit-project-select-item').show();
    });
});
</script>
@endpush
