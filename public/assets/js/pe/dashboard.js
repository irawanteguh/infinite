(function () {
    'use strict';
    document.querySelectorAll('.pe-filter select').forEach((select) => {
        const control = jQuery(select);
        if (!control.data('select2')) control.select2({width: '100%', minimumResultsForSearch: 0});
    });
    const source = document.getElementById('pe-chart-data');
    if (!source) return;
    const data = JSON.parse(source.textContent);
    if (!data || !document.getElementById('pe-trend-chart')) return;
    if (typeof ApexCharts === 'undefined') {
        document.getElementById('pe-trend-chart').textContent = 'Grafik belum dapat dimuat. Lihat data bulanan di bawah.';
        document.getElementById('pe-service-chart').textContent = 'Rincian pelayanan tersedia di sebelah.';
        return;
    }
    const colors = ['#087e82', '#93b6c3', '#7fbea9'];
    const motion = !window.matchMedia('(prefers-reduced-motion: reduce)').matches;
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