import './bootstrap';

// jQuery
import $ from 'jquery';
window.$ = window.jQuery = $;

// Bootstrap JS
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

// Select2
import select2 from 'select2';
select2(); // Auto-attaches to window.jQuery

// Flatpickr
import flatpickr from 'flatpickr';
window.flatpickr = flatpickr;

// SweetAlert2
import Swal from 'sweetalert2';
window.Swal = Swal;

// DataTables
import DataTable from 'datatables.net-bs5';
DataTable(window, $);
window.DataTable = DataTable;

// Chart.js
import Chart from 'chart.js/auto';
window.Chart = Chart;

// ─── AJAX Global CSRF Setup ─────────────────────────────────────
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

// ─── Global AJAX Loading Indicator ──────────────────────────────
$(document).ajaxStart(function () {
    $('#global-loader').fadeIn(150);
}).ajaxStop(function () {
    $('#global-loader').fadeOut(150);
});

// ─── Auto-init Components ────────────────────────────────────────
$(document).ready(function () {

    // Select2
    $('.select2').select2({ width: '100%' });
    $('.select2-multiple').select2({ width: '100%', multiple: true });

    // Flatpickr date
    flatpickr('.datepicker', {
        dateFormat: 'Y-m-d',
        allowInput: true,
    });

    // Flatpickr datetime
    flatpickr('.datetimepicker', {
        enableTime: true,
        dateFormat: 'Y-m-d H:i',
        allowInput: true,
    });

    // Flatpickr time only
    flatpickr('.timepicker', {
        enableTime: true,
        noCalendar: true,
        dateFormat: 'H:i',
    });

    // Sidebar submenu toggle
    $('.nav-item.has-submenu > .nav-link').on('click', function (e) {
        e.preventDefault();
        const $parent = $(this).closest('.nav-item');
        $parent.toggleClass('open');
    });

    // Sidebar toggle (collapse/expand)
    $('#sidebarToggle').on('click', function () {
        $('.content-area').toggleClass('sidebar-collapsed');
        $('.topbar').toggleClass('sidebar-collapsed');
        $('.sidebar').toggleClass('collapsed');
    });

    // Mobile sidebar
    $('#mobileSidebarToggle').on('click', function () {
        $('.sidebar').toggleClass('mobile-open');
    });

    // Auto-dismiss alerts
    setTimeout(() => {
        $('.alert-auto-dismiss').fadeOut(400);
    }, 4000);

    // Tooltip init
    $('[data-bs-toggle="tooltip"]').each(function () {
        new bootstrap.Tooltip(this);
    });

    // Confirm delete via SweetAlert
    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        const url = $(this).data('url');
        Swal.fire({
            title: 'Are you sure?',
            text: "This action cannot be undone.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#6c3fc5',
            cancelButtonColor: '#ea5455',
            confirmButtonText: 'Yes, delete it!',
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(url, { _method: 'DELETE' }, function () {
                    Swal.fire('Deleted!', 'Record has been removed.', 'success')
                        .then(() => location.reload());
                });
            }
        });
    });
});

// ─── Global AJAX Helper ──────────────────────────────────────────
window.ajaxPost = function (url, data, onSuccess, onError) {
    $.ajax({
        url: url,
        method: 'POST',
        data: data,
        success: function (res) {
            if (typeof onSuccess === 'function') onSuccess(res);
        },
        error: function (xhr) {
            const msg = xhr.responseJSON?.message || 'Something went wrong.';
            Swal.fire('Error', msg, 'error');
            if (typeof onError === 'function') onError(xhr);
        }
    });
};

window.showSuccess = function (msg) {
    Swal.fire({ icon: 'success', title: 'Success', text: msg, timer: 2000, showConfirmButton: false });
};

window.showError = function (msg) {
    Swal.fire({ icon: 'error', title: 'Error', text: msg });
};