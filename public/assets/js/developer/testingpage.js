document.addEventListener('DOMContentLoaded', () => {
    const page = document.getElementById('testing-page');
    if (!page) return;

    const year = document.getElementById('testing-year');
    const form = page.querySelector('#testing-filter-form');
    const service = page.querySelector('#testing-service');
    const start = page.querySelector('#testing-date-start');
    const end = page.querySelector('#testing-date-end');
    const message = page.querySelector('#testing-filter-message');
    const tableElement = page.querySelector('#testing-table');
    const rows = Array.from(tableElement.tBodies[0].rows, (row) => {
        const monthStart = row.dataset.date;
        const year = Number(monthStart.slice(0, 4));
        const month = Number(monthStart.slice(5, 7));
        const lastDay = new Date(year, month, 0).getDate();
        return {
            row,
            monthStart,
            monthEnd: monthStart.slice(0, -2) + lastDay,
            period: row.cells[0].textContent,
            service: row.dataset.service,
            count: Number(row.cells[2].textContent),
        };
    });
    const totals = ['testing-inpatient', 'testing-outpatient'].map(id => page.querySelector('#' + id));
    const summary = page.querySelector('#testing-total');
    const modalTotal = page.querySelector('#testing-modal-total');
    // Use fixed positioning so the menu can extend beyond the table scroll area.
    tableElement.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(toggle => {
        new bootstrap.Dropdown(toggle, {
            popperConfig: options => ({...options, strategy: 'fixed'}),
        });
    });

    const table = initDataTable(tableElement, '#testing-search', 10, {
        columnDefs: [{targets: -1, searchable: false}],
    });

    const actionStatus = page.querySelector('#testing-action-status');
    const modalSummary = page.querySelector('#testing-modal-summary');
    const modalRow = page.querySelector('#testing-modal-row');

    function rowDescription(row) {
        return [row.cells[0].textContent, row.cells[1].textContent, row.cells[2].textContent + ' kunjungan'].join(' · ');
    }

    page.querySelector('#testing-modal').addEventListener('show.bs.modal', (event) => {
        const row = event.relatedTarget?.closest('#testing-table tbody tr');
        modalSummary.hidden = Boolean(row);
        modalRow.hidden = !row;
        modalRow.textContent = row ? rowDescription(row) : '';
    });

    tableElement.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-testing-copy]');
        if (!button) return;

        const description = rowDescription(button.closest('tr'));
        try {
            await navigator.clipboard.writeText(description);
            actionStatus.dataset.testingTone = 'teal';
            actionStatus.textContent = 'Disalin: ' + description;
        } catch {
            actionStatus.dataset.testingTone = 'red';
            actionStatus.textContent = 'Penyalinan gagal. ' + description;
        }
    });

    const colors = ['#087e82', '#75bdb5'];
    const chartOptions = {
        colors,
        chart: {height: 320, toolbar: {show: false}},
        noData: {text: 'Tidak ada data pada periode ini'},
        series: [],
    };
    const cartesianOptions = {
        ...chartOptions,
        dataLabels: {enabled: false},
        xaxis: {type: 'category'},
        yaxis: {min: 0, title: {text: 'Kunjungan'}},
    };
    const stackedColors = ['#087e82', '#93b6c3'];
    const barChart = new ApexCharts(page.querySelector('#testing-chart'), {
        ...cartesianOptions,
        chart: {...chartOptions.chart, type: 'bar', stacked: true},
        colors: stackedColors,
        plotOptions: {bar: {columnWidth: '45%', borderRadius: 3}},
        legend: {position: 'top', horizontalAlign: 'left'},
        grid: {borderColor: '#edf2f4', strokeDashArray: 3},
        tooltip: {shared: true, intersect: false},
    });
    const lineChart = new ApexCharts(page.querySelector('#testing-line-chart'), {
        ...cartesianOptions,
        chart: {...chartOptions.chart, type: 'line'},
        stroke: {curve: 'straight', width: 3},
        markers: {size: 4},
    });
    const donutLegend = page.querySelector('#testing-donut-legend');
    const percentage = new Intl.NumberFormat('id-ID', {style: 'percent', maximumFractionDigits: 1});
    const donutChart = new ApexCharts(page.querySelector('#testing-donut-chart'), {
        ...chartOptions,
        chart: {type: 'donut', height: 300, fontFamily: 'Poppins, sans-serif', animations: {enabled: false}},
        labels: [],
        stroke: {width: 4, colors: ['#fff']},
        dataLabels: {enabled: false},
        legend: {show: false},
        plotOptions: {
            pie: {
                donut: {
                    size: '77%',
                    labels: {
                        show: true,
                        name: {fontSize: '12px', color: '#8aa0a6'},
                        value: {fontSize: '36px', fontWeight: 700, color: '#15373e'},
                        total: {
                            show: true,
                            showAlways: true,
                            label: 'Total kunjungan',
                            fontSize: '12px',
                            color: '#8aa0a6',
                            formatter: ({globals}) => globals.seriesTotals.reduce((sum, value) => sum + value, 0).toLocaleString('id-ID'),
                        },
                    },
                },
            },
        },
        tooltip: {y: {formatter: value => value.toLocaleString('id-ID') + ' kunjungan'}},
        responsive: [{
            breakpoint: 576,
            options: {chart: {height: 260}, plotOptions: {pie: {donut: {labels: {value: {fontSize: '30px'}}}}}},
        }],
    });
    const referenceChart = new ApexCharts(page.querySelector('#testing-reference-chart'), {
        ...cartesianOptions,
        chart: {...chartOptions.chart, type: 'line', height: 300, animations: {enabled: false}},
        yaxis: {...cartesianOptions.yaxis, max: 40},
        stroke: {curve: 'straight', width: 3},
        markers: {size: 4, hover: {size: 6}},
        grid: {borderColor: '#edf2f4', strokeDashArray: 3},
        legend: {position: 'top', horizontalAlign: 'left'},
        tooltip: {shared: true, intersect: false},
        annotations: {yaxis: [{
            y: 30, borderColor: '#f1aeb5', strokeDashArray: 5,
            label: {text: 'Acuan 30', style: {background: '#fde9e9', color: '#a63c45'}},
        }]},
    });
    const chartsReady = Promise.all([barChart.render(), lineChart.render(), donutChart.render(), referenceChart.render()]);

    function renderContributors(visibleRows, total) {
        const monthlyCounts = new Map();
        visibleRows.forEach(({period, count}) => {
            monthlyCounts.set(period, (monthlyCounts.get(period) ?? 0) + count);
        });
        const ranked = Array.from(monthlyCounts).filter(([, count]) => count > 0)
            .sort((a, b) => b[1] - a[1]).slice(0, 6);
        const list = page.querySelector('#testing-contributors');
        const template = page.querySelector('#testing-contributor-row');
        const items = ranked.map(([period, count]) => {
            const item = template.content.firstElementChild.cloneNode(true);
            const share = count / total;
            item.querySelector('.testing-contributor-name').textContent = period;
            item.querySelector('.testing-contributor-value').textContent = count.toLocaleString('id-ID') + ' kunjungan';
            item.querySelector('.progress-bar').style.width = (share * 100) + '%';
            item.querySelector('.testing-contributor-share').textContent = percentage.format(share) + ' dari total kunjungan';
            return item;
        });
        list.replaceChildren(...items);
        page.querySelector('#testing-contributors-empty').hidden = items.length > 0;
    }

    function applyFilters() {
        if (start.value && end.value && start.value > end.value) {
            message.dataset.testingTone = 'red';
            message.textContent = 'Tanggal mulai harus sebelum atau sama dengan tanggal akhir.';
            return;
        }

        const visibleRows = rows.filter(item =>
            item.monthStart.startsWith(year.value + '-') &&
            (service.value === '' || item.service === service.value) &&
            (!start.value || item.monthEnd >= start.value) &&
            (!end.value || item.monthStart <= end.value)
        );
        const filters = [
            'Tahun ' + year.value,
            service.selectedOptions[0].textContent.trim(),
            start.value && 'dari ' + start.value,
            end.value && 'sampai ' + end.value,
        ].filter(Boolean);
        page.querySelector('#testing-print-period').textContent = 'Data simulasi · ' + filters.join(' · ');

        const counts = [0, 0];
        visibleRows.forEach(item => { counts[item.service] += item.count; });
        const total = counts[0] + counts[1];
        totals.forEach((element, index) => { element.textContent = counts[index]; });
        summary.textContent = total;
        modalTotal.textContent = total;
        renderContributors(visibleRows, total);

        table.clear();
        table.rows.add(visibleRows.map(item => item.row)).draw();

        const services = Array.from(service.options).filter(option =>
            option.value !== '' && (service.value === '' || option.value === service.value)
        );
        const series = visibleRows.length ? services.map(option => ({
            name: option.textContent.trim(),
            data: visibleRows.filter(item => item.service === option.value)
                .map(item => ({x: item.period, y: item.count})),
        })) : [];
        const activeColors = services.map(option => colors[Number(option.value)]);
        donutLegend.hidden = total === 0;
        donutLegend.querySelectorAll('[data-service]').forEach(item => {
            const index = Number(item.dataset.service);
            const count = counts[index];
            item.hidden = count === 0;
            if (!count) return;
            item.querySelector('.testing-donut-dot').style.backgroundColor = colors[index];
            item.querySelector('.testing-donut-count').textContent = count.toLocaleString('id-ID');
            item.querySelector('.testing-donut-percent').textContent = percentage.format(count / total);
        });

        chartsReady.then(() => {
            referenceChart.updateOptions({series, colors: activeColors});
            barChart.updateOptions({series, colors: services.map(option => stackedColors[Number(option.value)])});
            lineChart.updateOptions({series, colors: activeColors});
            donutChart.updateOptions({
                series: visibleRows.length ? services.map(option => counts[option.value]) : [],
                labels: visibleRows.length ? services.map(option => option.textContent.trim()) : [],
                colors: activeColors,
            });
        });
        message.dataset.testingTone = visibleRows.length ? 'teal' : 'yellow';
        message.textContent = visibleRows.length
            ? 'Rentang tanggal mencakup seluruh bulan yang bersinggungan.'
            : 'Tidak ada kunjungan simulasi pada periode yang dipilih.';
    }

    $(year).on('change', applyFilters);
    document.getElementById('testing-refresh').addEventListener('click', applyFilters);
    document.getElementById('testing-print').addEventListener('click', () => window.print());

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        applyFilters();
    });

    page.querySelector('#testing-reset').addEventListener('click', () => {
        $(service).val('').trigger('change');
        [start, end].forEach((input) => {
            if (input._flatpickr) input._flatpickr.clear();
            else input.value = '';
        });
        page.querySelector('#testing-search').value = '';
        table.search('');
        applyFilters();
    });

    applyFilters();
});

document.addEventListener('DOMContentLoaded', () => {
    const filter = document.getElementById('testing-question-filter');
    if (!filter) return;

    const options = filter.querySelectorAll('[data-question-count]');
    const count = document.getElementById('testing-question-filter-count');

    filter.addEventListener('click', event => {
        const selected = event.target.closest('[data-question-count]');
        if (!selected) return;

        options.forEach(option => option.setAttribute('aria-pressed', String(option === selected)));

        const questionCount = Number(selected.dataset.questionCount);
        count.dataset.testingTone = questionCount ? 'teal' : 'yellow';
        count.textContent = questionCount ? questionCount + ' pertanyaan dummy sesuai filter.' : 'Tidak ada pertanyaan pada status ini.';
    });
});

function showTestingProgress({title, labels, completion, showBar = false}) {
    let stage = 0;
    let progressTimer;

    Swal.fire({
        title,
        width: 'min(560px, calc(100vw - 32px))',
        buttonsStyling: false,
        customClass: {popup: 'report-progress-popup', confirmButton: 'btn btn-primary'},
        html: '<div class="report-progress">' + labels.map(label =>
            '<div class="report-progress-step"><span class="report-progress-icon" aria-hidden="true"></span><span>'
            + label + '</span></div>'
        ).join('') + '</div>' + (showBar
            ? '<div class="progress report-progress-meter mt-3"><div class="progress-bar" role="progressbar" aria-label="Progres simulasi impor" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" data-demo-progress-bar></div></div>'
            : '') + '<p class="text-muted mt-3 mb-0" role="status" data-demo-report-status></p>',
        confirmButtonText: 'Tutup',
        allowOutsideClick: false,
        allowEscapeKey: () => stage === labels.length,
        showConfirmButton: false,
        didOpen: popup => {
            const steps = popup.querySelectorAll('.report-progress-step');
            const status = popup.querySelector('[data-demo-report-status]');
            const bar = popup.querySelector('[data-demo-progress-bar]');

            function render() {
                steps.forEach((step, index) => {
                    const complete = index < stage;
                    const current = index === stage;
                    step.classList.toggle('is-complete', complete);
                    step.classList.toggle('is-current', current);
                    if (current) step.setAttribute('aria-current', 'step');
                    else step.removeAttribute('aria-current');
                    step.querySelector('.report-progress-icon').innerHTML = complete
                        ? '<i class="bi bi-check-lg"></i>'
                        : current ? '<span class="spinner-border spinner-border-sm"></span>' : '<span class="report-progress-dot"></span>';
                });
                if (bar) {
                    const percent = Math.round(stage / labels.length * 100);
                    bar.style.width = percent + '%';
                    bar.setAttribute('aria-valuenow', percent);
                }
                status.textContent = stage < labels.length
                    ? 'Simulasi · Tahap ' + (stage + 1) + ' dari ' + labels.length + ' · ' + labels[stage]
                    : completion;
            }

            render();
            progressTimer = window.setInterval(() => {
                stage++;
                render();
                if (stage === labels.length) {
                    window.clearInterval(progressTimer);
                    Swal.update({showConfirmButton: true});
                    Swal.getConfirmButton()?.focus();
                }
            }, 650);
        },
        willClose: () => window.clearInterval(progressTimer),
    });
}

document.getElementById('testing-report-progress')?.addEventListener('click', () => {
    showTestingProgress({
        title: 'Menyiapkan laporan',
        labels: ['Mengambil data contoh', 'Menyusun isi laporan', 'Membuat halaman PDF', 'Menyimpan laporan'],
        completion: 'Simulasi selesai. Tidak ada berkas yang dibuat.',
    });
});

document.getElementById('testing-import-progress')?.addEventListener('click', () => {
    showTestingProgress({
        title: 'Simulasi impor E-Klaim',
        labels: ['Periksa berkas dan kolom wajib', 'Kirim dan proses data per batch', 'Siapkan ringkasan hasil impor'],
        completion: 'Simulasi selesai. Tidak ada data yang diimpor.',
        showBar: true,
    });
});
