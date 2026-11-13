(function () {
    'use strict';
    document.querySelectorAll('.pe-filter select').forEach((select) => {
        const control = jQuery(select);
        if (!control.data('select2')) control.select2({width: '100%', minimumResultsForSearch: 0});
    });
    const questionSelect = document.getElementById('pe-distribution-question');
    if (questionSelect) {
        const syncQuestion = () => document.querySelectorAll('[data-distribution-index]').forEach(panel => panel.classList.toggle('d-none', panel.dataset.distributionIndex !== questionSelect.value));
        if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
            const control = jQuery(questionSelect);
            if (!control.data('select2')) control.select2({width: '100%', minimumResultsForSearch: 0});
            control.on('change', syncQuestion);
        } else questionSelect.addEventListener('change', syncQuestion);
        syncQuestion();
    }
    const source = document.getElementById('pe-chart-data');
    if (!source) return;
    const data = JSON.parse(source.textContent);
    if (!data) return;
    if (typeof ApexCharts === 'undefined') {
        ['pe-skm-count-chart', 'pe-skm-score-chart', 'pe-score-chart', 'pe-trend-chart', 'pe-service-chart'].forEach((id) => { const node = document.getElementById(id); if (node) node.textContent = 'Grafik belum dapat dimuat. Lihat ringkasan data pada kartu dan tabel.'; });
        return;
    }
    const colors = ['#087e82', '#93b6c3', '#7fbea9'];
    const skmOptions = {
        chart: {height: 290, toolbar: {show: false}, fontFamily: 'Poppins, sans-serif', animations: {enabled: false}, foreColor: '#8a9fa5'},
        colors: ['#087e82', '#75b5a0', '#709db8'], dataLabels: {enabled: false},
        grid: {borderColor: '#edf2f3', strokeDashArray: 4},
        noData: {text: 'Belum ada respons SKM pada periode ini'},
    };
    if (data.skm && document.getElementById('pe-skm-count-chart')) {
        new ApexCharts(document.getElementById('pe-skm-count-chart'), {
            ...skmOptions, chart: {...skmOptions.chart, type: 'bar'},
            series: [{name: 'Respons SKM', data: data.skm.counts}],
            plotOptions: {bar: {distributed: true, borderRadius: 5, columnWidth: '42%'}},
            legend: {show: false}, xaxis: {categories: data.skm.labels},
            yaxis: {min: 0, forceNiceScale: true, labels: {formatter: value => Number.isInteger(value) ? value.toString() : ''}},
            tooltip: {y: {formatter: value => value + ' respons'}},
        }).render();
        new ApexCharts(document.getElementById('pe-skm-score-chart'), {
            ...skmOptions, chart: {...skmOptions.chart, type: 'line'},
            series: data.skm.series, stroke: {width: 3, curve: 'straight'}, markers: {size: 4},
            legend: {position: 'top', horizontalAlign: 'left', fontSize: '10px'},
            xaxis: {categories: data.skm.months.map(month => new Date(Number(month.slice(0, 4)), Number(month.slice(5, 7)) - 1, 1).toLocaleDateString('id-ID', {month: 'short', year: 'numeric'})), labels: {rotate: -35, hideOverlappingLabels: true}},
            yaxis: {min: 1, max: 4, tickAmount: 3, labels: {formatter: value => value.toFixed(0)}},
            tooltip: {shared: true, intersect: false, y: {formatter: value => value == null ? 'Belum ada jawaban' : value.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' / 4'}},
        }).render();
    }
    if (!document.getElementById('pe-trend-chart')) return;
    new ApexCharts(document.getElementById('pe-score-chart'), {
        chart: {type: 'line', height: 300, toolbar: {show: false}, fontFamily: 'Poppins, sans-serif', animations: {enabled: false}, foreColor: '#8a9fa5'},
        series: data.scoreSeries, colors: ['#087e82', '#527fbc', '#cb8b37'],
        stroke: {width: 3, curve: 'straight'},
        markers: {size: 4, hover: {sizeOffset: 2}},
        dataLabels: {enabled: false},
        grid: {borderColor: '#edf2f3', strokeDashArray: 4},
        xaxis: {categories: data.labels, axisBorder: {show: false}, axisTicks: {show: false}, labels: {rotate: -35, hideOverlappingLabels: true}},
        yaxis: {min: 1, max: 5, tickAmount: 4, labels: {formatter: (value) => value.toFixed(0)}},
        legend: {position: 'top', horizontalAlign: 'left', fontSize: '11px'},
        tooltip: {shared: true, intersect: false, y: {formatter: (value) => value == null ? 'Belum ada jawaban' : value.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' / 5'}},
        noData: {text: 'Belum ada nilai pada periode ini'},
        responsive: [{breakpoint: 576, options: {chart: {height: 280}, legend: {fontSize: '9px'}}}],
    }).render();
    new ApexCharts(document.getElementById('pe-trend-chart'), {
        chart: {type: 'bar', height: 285, toolbar: {show: false}, fontFamily: 'Poppins, sans-serif', animations: {enabled: false}, foreColor: '#8a9fa5'},
        series: data.series, colors: colors,
        plotOptions: {bar: {borderRadius: 3, columnWidth: '60%'}},
        dataLabels: {enabled: false},
        stroke: {show: true, width: 2, colors: ['transparent']},
        grid: {borderColor: '#edf2f3', strokeDashArray: 4, padding: {left: 0, right: 0}},
        xaxis: {categories: data.labels, axisBorder: {show: false}, axisTicks: {show: false}, labels: {style: {fontSize: '9px'}, rotate: -35, hideOverlappingLabels: true}},
        yaxis: {min: 0, forceNiceScale: true, labels: {formatter: (value) => Number.isInteger(value) ? value.toString() : '', style: {fontSize: '9px'}}},
        legend: {position: 'top', horizontalAlign: 'left', fontSize: '10px', markers: {radius: 3}, itemMargin: {horizontal: 12, vertical: 8}},
        tooltip: {shared: true, intersect: false, y: {formatter: (value) => value + ' respons'}},
        responsive: [{breakpoint: 576, options: {chart: {height: 265}, legend: {fontSize: '9px', itemMargin: {horizontal: 6, vertical: 6}}}}],
    }).render();
    new ApexCharts(document.getElementById('pe-service-chart'), {
        chart: {type: 'donut', height: 220, fontFamily: 'Poppins, sans-serif', animations: {enabled: false}},
        series: data.services, labels: data.serviceLabels, colors: ['#087e82', '#75bdb5', '#c0d8e3', '#d6dde0'],
        stroke: {width: 4, colors: ['#fff']}, dataLabels: {enabled: false}, legend: {show: false},
        plotOptions: {pie: {donut: {size: '77%', labels: {show: true, name: {fontSize: '10px', color: '#8aa0a6'},
            value: {fontSize: '28px', fontWeight: 600, color: '#15373e'},
            total: {show: true, showAlways: true, label: 'Total respons', fontSize: '9px', color: '#8aa0a6', formatter: (chart) => chart.globals.seriesTotals.reduce((sum, value) => sum + value, 0).toString()},
        }}}},
        tooltip: {y: {formatter: (value) => value + ' respons'}},
        responsive: [{breakpoint: 576, options: {chart: {height: 185}, plotOptions: {pie: {donut: {labels: {value: {fontSize: '24px'}}}}}}}],
    }).render();
})();