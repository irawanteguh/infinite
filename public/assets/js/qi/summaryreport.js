$(function () {
    const esc = value => $('<div>').text(value == null || value === '' ? '-' : String(value)).html();
    const rowsTarget = $('#summaryReportRows');
    let generating = false;
    const formatDate = value => {
        if (!value) return '-';
        const date = new Date(String(value).replace(' ', 'T'));
        return Number.isNaN(date.getTime()) ? value : date.toLocaleString('id-ID', {
            day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
        });
    };

    function loadPeriods() {
        $('#summaryReportLoading').removeClass('d-none');
        $('#summaryReportEmpty, #summaryReportError').addClass('d-none');
        $.ajax({
            url: url + 'qi/summaryreport/periods',
            type: 'GET',
            dataType: 'JSON',
            success(response) {
                const periods = Array.isArray(response.responseResult) ? response.responseResult : [];
                $('#report-period-count').text(periods.length.toLocaleString('id-ID'));
                $('#report-pic-count').text(periods.filter(period => period.pic && String(period.pic).trim()).length.toLocaleString('id-ID'));
                const years = periods.map(period => String(period.tahun || '')).filter(Boolean).sort().reverse();
                $('#report-latest-period').text(years[0] || '—');
                $('#summaryReportEmpty').toggleClass('d-none', periods.length > 0);
                if ($.fn.DataTable.isDataTable('#summaryReportTable')) {
                    $('#summaryReportTable').DataTable().destroy();
                }
                rowsTarget.empty();
                periods.forEach((period, index) => {
                    const color = ['primary', 'success', 'info', 'warning', 'danger', 'secondary', 'dark'].includes(period.statuscolor) ? period.statuscolor : 'primary';
                    const status = period.status
                        ? `<span class="badge badge-light-${color}">${esc(period.status)}</span>`
                        : '<span class="badge badge-light-success">Aktif</span>';
                    const action = `<a class="btn btn-sm btn-light-primary report-pdf-button" target="_blank" rel="noopener" aria-label="Generate PDF laporan PMKP periode ${esc(period.tahun)}" href="${url}qi/summaryreport/generate?periode_id=${encodeURIComponent(period.periode_id)}&prepare=1${period.report_updated_at ? '&force=1' : ''}"><i class="bi bi-arrow-repeat me-1"></i>${period.report_updated_at ? 'Generate ulang' : 'Generate laporan'}<i class="bi bi-arrow-up-right ms-2 me-0"></i></a>`;
                    const savedReport = period.report_updated_at
                        ? `<a class="text-primary text-hover-primary fw-semibold" target="_blank" rel="noopener" aria-label="Buka PDF tersimpan periode ${esc(period.tahun)}" href="${url}qi/summaryreport/viewreport?periode_id=${encodeURIComponent(period.periode_id)}">${esc(formatDate(period.report_updated_at))}</a>`
                        : '<span class="text-muted fs-8">Belum digenerate</span>';
                    rowsTarget.append(`<tr><td class="ps-4 text-muted fs-7">${index + 1}</td><td><div class="report-period-cell"><span class="report-year-icon"><i class="bi bi-calendar3"></i></span><div><span class="report-year">${esc(period.tahun)}</span><span class="report-period-label">Laporan tahunan PMKP</span></div></div></td><td><span class="fw-bold text-gray-800">${esc(period.pic || '-')}</span></td><td><span class="report-team"><i class="bi bi-people"></i>${esc(period.komite ? period.komite.split(';').filter(Boolean).length : 0)} anggota</span></td><td>${status}</td><td class="text-muted fs-7 text-nowrap">${savedReport}</td><td class="text-end pe-4">${action}</td></tr>`);
                });
                if (periods.length) initDataTable('#summaryReportTable', '#summaryReportSearch', 10);
            },
            error() {
                $('#summaryReportError').removeClass('d-none');
                Swal.fire({ icon: 'error', title: 'Gagal memuat periode', text: 'Daftar periode pelaporan tidak dapat dimuat.' });
            },
            complete() {
                $('#summaryReportLoading').addClass('d-none');
            }
        });
    }

    rowsTarget.on('click', '.report-pdf-button', async function (event) {
        event.preventDefault();
        if (generating) return;
        const jobId = Array.from(crypto.getRandomValues(new Uint8Array(16)), byte => byte.toString(16).padStart(2, '0')).join('');
        const stages = ['loading', 'preparing', 'rendering', 'saving', 'saved'];
        const labels = [
            'Mengambil data indikator dan periode',
            'Menyusun analisis dan isi laporan',
            'Membuat halaman PDF',
            'Menyimpan file laporan'
        ];
        let currentStage = 0;
        let progressTimer;
        const showProgress = stage => {
            const index = stages.indexOf(stage);
            if (index < 0 || index < currentStage) return;
            currentStage = index;
            document.querySelectorAll('#report-progress .report-progress-step').forEach((step, stepIndex) => {
                const complete = stage === 'saved' || stepIndex < index;
                step.classList.toggle('is-complete', complete);
                step.classList.toggle('is-current', !complete && stepIndex === index);
                step.querySelector('.report-progress-icon').innerHTML = complete
                    ? '<i class="bi bi-check-lg"></i>'
                    : stepIndex === index ? '<span class="spinner-border spinner-border-sm"></span>' : '<span class="report-progress-dot"></span>';
            });
        };
        generating = true;
        const buttons = rowsTarget.find('.report-pdf-button');
        buttons.addClass('disabled').attr('aria-disabled', 'true');
        Swal.fire({
            title: 'Menyiapkan laporan PMKP',
            width: 'min(560px, calc(100vw - 32px))',
            customClass: { popup: 'report-progress-popup' },
            html: '<div id="report-progress" class="report-progress">' + labels.map((label, index) =>
                '<div class="report-progress-step' + (index === 0 ? ' is-current' : '') + '"><span class="report-progress-icon">'
                + (index === 0 ? '<span class="spinner-border spinner-border-sm"></span>' : '<span class="report-progress-dot"></span>')
                + '</span><span>' + label + '</span></div>').join('') + '</div>',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => showProgress('loading')
        });
        try {
            progressTimer = window.setInterval(async () => {
                try {
                    const statusResponse = await fetch(url + 'qi/summaryreport/progress?job=' + jobId, { credentials: 'same-origin', cache: 'no-store' });
                    if (statusResponse.ok) showProgress((await statusResponse.json()).stage);
                } catch (_) { /* Generation response remains authoritative. */ }
            }, 700);
            const generationUrl = new URL(this.href, window.location.href);
            generationUrl.searchParams.set('job', jobId);
            const response = await fetch(generationUrl.href, { credentials: 'same-origin' });
            if (!response.ok) throw new Error('Report generation failed');
            const result = await response.json();
            if (result.responseCode !== '00' || !result.viewUrl) throw new Error('Invalid report response');
            const viewUrl = new URL(result.viewUrl, window.location.href);
            if (viewUrl.origin !== window.location.origin) throw new Error('Invalid report URL');
            showProgress('saved');
            window.clearInterval(progressTimer);
            await new Promise(resolve => window.setTimeout(resolve, 350));
            Swal.close();
            const reportTab = window.open(viewUrl.href, '_blank');
            if (reportTab) {
                reportTab.opener = null;
            } else {
                Swal.fire({
                    icon: 'success',
                    title: 'Laporan selesai dibuat',
                    width: 'min(460px, calc(100vw - 32px))',
                    customClass: {
                        popup: 'report-ready-popup',
                        confirmButton: 'report-ready-close'
                    },
                    html: '<p class="report-ready-message">PDF telah disimpan. Browser menahan pembukaan tab otomatis; buka laporan melalui tombol berikut.</p>'
                        + '<a class="report-ready-open" href="' + viewUrl.href + '" target="_blank" rel="noopener">'
                        + '<i class="bi bi-file-earmark-pdf"></i><span>Buka laporan PDF</span><i class="bi bi-arrow-up-right"></i></a>',
                    confirmButtonText: 'Kembali ke daftar',
                    buttonsStyling: false
                });
            }
            loadPeriods();
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Laporan gagal dibuat',
                text: 'PDF tidak dapat disiapkan. Silakan coba kembali atau muat ulang halaman jika sesi telah berakhir.'
            });
        } finally {
            window.clearInterval(progressTimer);
            generating = false;
            buttons.removeClass('disabled').removeAttr('aria-disabled');
        }
    });
    $('#summaryReportRetry').on('click', loadPeriods);
    loadPeriods();
});
