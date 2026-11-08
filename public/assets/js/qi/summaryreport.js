$(function () {
    const esc = value => $('<div>').text(value == null || value === '' ? '-' : String(value)).html();
    const rowsTarget = $('#summaryReportRows');

    function loadPeriods() {
        $.ajax({
            url: url + 'qi/summaryreport/periods',
            type: 'GET',
            dataType: 'JSON',
            success(response) {
                const periods = Array.isArray(response.responseResult) ? response.responseResult : [];
                $('#summaryReportEmpty').toggleClass('d-none', periods.length > 0);
                if ($.fn.DataTable.isDataTable('#summaryReportTable')) {
                    $('#summaryReportTable').DataTable().destroy();
                }
                rowsTarget.empty();
                periods.forEach((period, index) => {
                    const status = period.status
                        ? `<span class="badge badge-light-${esc(period.statuscolor || 'primary')}">${esc(period.status)}</span>`
                        : '<span class="badge badge-light-success">Aktif</span>';
                    const action = `<a class="btn btn-sm btn-primary" target="_blank" rel="noopener" href="${url}qi/summaryreport/generate?periode_id=${encodeURIComponent(period.periode_id)}"><i class="bi bi-file-earmark-pdf me-1"></i>Laporan PMKP</a>`;
                    rowsTarget.append(`<tr><td class="ps-4">${index + 1}</td><td><span class="fw-bolder text-gray-900">${esc(period.tahun)}</span></td><td>${esc(period.pic || '-')}</td><td>${esc(period.komite ? period.komite.split(';').filter(Boolean).length : 0)} anggota</td><td>${status}</td><td>${esc(period.dibuattgl || '-')}</td><td class="text-end pe-4">${action}</td></tr>`);
                });
                if (periods.length) initDataTable('#summaryReportTable', '#summaryReportSearch', 10);
            },
            error() {
                Swal.fire({ icon: 'error', title: 'Gagal memuat periode', text: 'Daftar periode pelaporan tidak dapat dimuat.' });
            }
        });
    }

    loadPeriods();
});
