(() => {
    const filterForm = document.getElementById('ca-dashboard-filter');
    for (const id of ['ca-year', 'ca-instrument', 'ca-domicile']) {
        const select = document.getElementById(id);
        if (!select || !filterForm) continue;
        const applyFilter = () => filterForm.requestSubmit();
        if (window.jQuery) {
            window.jQuery(select).on('change', applyFilter);
        } else {
            select.addEventListener('change', applyFilter);
        }
    }
    const source = document.getElementById('ca-dashboard-data');
    if (!source) return;
    const data = JSON.parse(source.textContent);
    if (!data) return;
    if (typeof ApexCharts === 'undefined') {
        document.getElementById('ca-monthly-chart').textContent = 'Lihat jumlah pengisian pada tabel bulanan.';
        return;
    }
    const base = {
        chart: {height: 300, toolbar: {show: false}, fontFamily: getComputedStyle(source.parentElement).fontFamily},
        colors: ['#087e82', '#7fbea9'],
        dataLabels: {enabled: false},
        noData: {text: 'Belum ada data pada periode ini'},
        grid: {borderColor: '#edf2f3'},
        tooltip: {y: {formatter: (value) => value.toLocaleString('id-ID') + ' pengisian'}},
    };
    const monthly = new ApexCharts(document.getElementById('ca-monthly-chart'), {
        ...base,
        chart: {...base.chart, type: 'line'},
        colors: ['#087e82', '#7fbea9', '#c7838e', '#dfc384', '#7199b4', '#967fa8'],
        series: data.series,
        xaxis: {categories: data.months.map((month) => new Date(Number(month.slice(0, 4)), Number(month.slice(5, 7)) - 1, 1).toLocaleDateString('id-ID', {month: 'long'}))},
        yaxis: {min: 0, forceNiceScale: true, labels: {formatter: (value) => Number.isInteger(value) ? String(value) : ''}},
        stroke: {curve: 'straight', width: 3},
        markers: {size: 4, hover: {size: 6}},
        legend: {position: 'top'},
    });
    monthly.render();
    const riskDefinitions = [
        {name: 'Average Risk', labels: ['Ringan', 'Rata-rata', 'Average Risk (Risiko ringan)']},
        {name: 'Moderate Risk', labels: ['Sedang', 'Moderate Risk (Risiko Sedang)']},
        {name: 'High Risk', labels: ['Berat', 'Tinggi', 'High Risk (Risiko Tinggi)']},
    ];
    const codes = [...new Set(data.risks.map((row) => row.code))];
    new ApexCharts(document.getElementById('ca-risk-chart'), {
        ...base,
        chart: {...base.chart, type: 'bar', stacked: true, height: 260},
        colors: ['#7fbea9', '#dfc384', '#c7838e'],
        series: riskDefinitions.map((definition) => ({
            name: definition.name,
            data: codes.map((code) => data.risks
                .filter((row) => row.code === code && definition.labels.includes(row.risk_level))
                .reduce((sum, row) => sum + Number(row.total), 0)),
        })),
        xaxis: {categories: codes, min: 0, labels: {formatter: (value) => Number.isInteger(Number(value)) ? String(value) : ''}},
        plotOptions: {bar: {horizontal: true, borderRadius: 4, barHeight: '45%'}},
        legend: {position: 'bottom'},
    }).render();
    if (!data.domiciles.length) {
        document.getElementById('ca-domicile-chart').textContent = 'Belum ada data domisili pada filter ini.';
    }
    if (data.domiciles.length) {
        new ApexCharts(document.getElementById('ca-domicile-chart'), {
            ...base,
            chart: {...base.chart, type: 'donut', height: 250},
            colors: ['#087e82', '#7fbea9', '#b5c5cd'],
            series: data.domiciles.map((row) => row.total),
            labels: data.domiciles.map((row) => row.label),
            stroke: {width: 4, colors: ['#fff']},
            plotOptions: {pie: {donut: {
                size: '77%',
                labels: {
                    show: true,
                    name: {fontSize: '12px', color: '#8aa0a6'},
                    value: {fontSize: '36px', fontWeight: 700, color: '#15373e'},
                    total: {
                        show: true, showAlways: true, label: 'Total pengisian',
                        fontSize: '12px', color: '#8aa0a6',
                        formatter: ({globals}) => globals.seriesTotals.reduce((sum, value) => sum + value, 0).toLocaleString('id-ID'),
                    },
                },
            }}},
            responsive: [{breakpoint: 576, options: {
                plotOptions: {pie: {donut: {labels: {value: {fontSize: '30px'}}}}},
            }}],
            legend: {show: false},
        }).render();
    }
})();
