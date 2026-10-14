const chartInstances = {};

function renderchartarea(name, data, titleX, titleY, seriesName, fieldName, rightAxisIndex = null, rightAxisLabel = "", avgField = null, avgLabel = "Rata-rata", annotationValue = null) {

    if (chartInstances[name]) {
        chartInstances[name].destroy();
        chartInstances[name] = null;
    }

    let series = [];

    if (Array.isArray(seriesName) && Array.isArray(fieldName)) {
        series = seriesName.map((nm, index) => ({
            name: nm,
            data: data.map(item => parseFloat(item[fieldName[index]] || 0)),
            yAxisIndex: (rightAxisIndex !== null && index === rightAxisIndex) ? 1 : 0
        }));
    } else {
        series = [{
            name: seriesName,
            data: data.map(item => parseFloat(item[fieldName] || 0))
        }];
    }

    let avgValue = null;

    if (avgField) {
        let total = 0;
        let count = 0;

        data.forEach(item => {
            let val = parseFloat(item[avgField]);
            if (!isNaN(val) && val > 0) {
                total += val;
                count++;
            }
        });

        avgValue = count > 0 ? total / count : 0;
    }

    let finalAnnotation = null;
    let finalLabel = "";

    if (annotationValue !== null) {
        finalAnnotation = annotationValue;
        finalLabel      = avgLabel;
    } else if (avgValue !== null) {
        finalAnnotation = avgValue;
        finalLabel      = `${avgLabel} (${Math.round(avgValue).toLocaleString()})`;
    }

    const options = {
        chart : { type: "area", height: 350, toolbar: { show: true }, zoom: { enabled: false } },
        series: series,
        xaxis : {
            categories   : data.map(item => item.periode),
            title        : { text: titleX },
            tickPlacement: 'on',
            axisBorder   : { show: true },
            axisTicks    : { show: true }
        },
        yaxis: rightAxisIndex !== null ? [
            {
                title         : { text: titleY },
                min           : 0,
                forceNiceScale: true,
                labels        : { formatter: val => val.toLocaleString() }
            },
            {
                opposite: true,
                title   : { text: rightAxisLabel },
                labels  : { formatter: val => val.toLocaleString() }
            }
        ] : {
            title         : { text: titleY },
            min           : 0,
            forceNiceScale: true,
            labels        : { formatter: val => val.toLocaleString() }
        },
        stroke : { curve: 'smooth', width: 3 },
        markers: { size: 4 },
        fill   : {
            type    : 'gradient',
            gradient: { shadeIntensity: 1, opacityFrom: 0.75, opacityTo: 0.25, stops: [0, 100] }
        },
        dataLabels: { enabled: true, formatter: val => val.toLocaleString() },
        tooltip   : { y: { formatter: val => val.toLocaleString() } },
        grid      : { strokeDashArray: 4 },
        legend    : { position: 'top' },
        annotations: finalAnnotation !== null ? {
            yaxis: [{
                y: finalAnnotation,
                borderColor: '#FF0000',
                strokeDashArray: 3,
                label: {
                    text: finalLabel,
                    style: { background: '#FF0000', color: '#fff' }
                }
            }]
        } : {}
    };

    const chartEl = document.querySelector(`#${name}`);
    chartEl.innerHTML = "";
    chartInstances[name] = new ApexCharts(chartEl, options);
    chartInstances[name].render();
}

function renderChartStackedBar(elementId,categories,series,titleX = "",titleY = "",options = {}){

    const chartElement = document.getElementById(elementId);

    if (!chartElement) return;

    if (chartElement.chart) {
        chartElement.chart.destroy();
    }

    const config = {

        chart: {
            type: "bar",
            height: options.height || 350,
            stacked: true,
            toolbar: {
                show: false
            },
            fontFamily: "inherit"
        },

        series: series,

        xaxis: {
            categories: categories,
            title: {
                text: titleX
            }
        },

        yaxis: {
            title: {
                text: titleY
            },
            labels: {
                formatter: function (value) {
                    return value.toLocaleString('id-ID');
                }
            }
        },

        plotOptions: {
            bar: {
                horizontal             : options.horizontal || false,
                columnWidth            : options.columnWidth || "55%",
                borderRadius           : options.borderRadius || 4,
                borderRadiusApplication: "end"
            }
        },

        stroke: {
            width: 1,
            colors: ["#fff"]
        },

        fill: {
            opacity: 1
        },

        dataLabels: {
            enabled: options.dataLabels ?? false
        },

        legend: {
            position: options.legendPosition || "top",
            horizontalAlign: options.legendAlign || "left"
        },

        tooltip: {
            y: {
                formatter: function(val) {
                    return val.toLocaleString("id-ID");
                }
            }
        },

        grid: {
            borderColor: "#e5e5e5",
            strokeDashArray: 4
        },

        noData: {
            text: "Tidak ada data"
        }

    };

    Object.assign(config, options);

    chartElement.chart = new ApexCharts(chartElement, config);
    chartElement.chart.render();

}


function renderBarHorizontal(elementId,seriesConfig,data,categoryField,titleX){
    const chartElement = document.getElementById(elementId);

    if (!chartElement) return;

    if (chartElement.chart) {
        chartElement.chart.destroy();
    }

    data = Array.isArray(data) ? data : [];

    const chartData = data.map(item => ({
        ...item,
        [categoryField]: String(
            item[categoryField] ?? 'LAINNYA'
        ).trim().toUpperCase()
    }));

    chartData.sort((a, b) => {
        const totalA = seriesConfig.reduce(
            (sum, s) => sum + (Number(a[s.field]) || 0), 0
        );

        const totalB = seriesConfig.reduce(
            (sum, s) => sum + (Number(b[s.field]) || 0), 0
        );

        return totalB - totalA;
    });

    const categories = chartData.map(
        item => item[categoryField]
    );

    const series = seriesConfig.map(s => ({
        name: s.name,
        data: chartData.map(item => Number(item[s.field]) || 0)
    }));

    const options = {
        chart: {
            type: 'bar',
            stacked: true,
            height: Math.max(350, categories.length * 45),
            toolbar: {
                show: false
            },
            fontFamily: 'inherit'
        },
        series: series,
        plotOptions: {
            bar: {
                horizontal: true,
                borderRadius: 3,
                barHeight: '70%',
                dataLabels: {
                    total: {
                        enabled: true,
                        offsetX: 10,
                        style: {
                            fontSize: '12px',
                            fontWeight: 700
                        },
                        formatter: function (val) {
                            return Number(val).toLocaleString('id-ID');
                        }
                    }
                }
            }
        },
        stroke: {
            width: 1,
            colors: ['#ffffff']
        },
        xaxis: {
            categories: categories,
            title: {
                text: titleX
            },
            labels: {
                formatter: function (val) {
                    return Number(val).toLocaleString('id-ID');
                }
            }
        },
        yaxis: {
            title: {
                text: ''
            }
        },
        dataLabels: {
            enabled: true,
            formatter: function (val) {
                return val > 0 ? Number(val).toLocaleString('id-ID') : '';
            },
            style: {
                fontSize: '11px',
                fontWeight: 500
            }
        },
        tooltip: {
            shared: true,
            intersect: false,
            y: {
                formatter: function (val) {
                    return Number(val).toLocaleString('id-ID') + ' kasus';
                }
            }
        },
        legend: {
            position: 'top',
            horizontalAlign: 'left'
        },
        grid: {
            borderColor: '#e5e5e5',
            strokeDashArray: 4
        },
        noData: {
            text: 'Tidak ada data'
        }
    };

    const chartEl = document.querySelector(`#${elementId}`);
    chartEl.innerHTML = "";
    chartInstances[elementId] = new ApexCharts(chartEl, options);
    chartInstances[elementId].render();
}

function renderchartpie(name, data) {
    if (chartInstances[name]) {
        chartInstances[name].destroy();
        chartInstances[name] = null;
    }

    const labels = [];
    const series = [];

    data.forEach(item => {
        labels.push(
            item.category ??
            item.CATEGORY ??
            item.label ??
            item.LABEL ??
            item.PROVIDER ??
            "-"
        );

        series.push(
            Number(
                item.value ??
                item.VALUE ??
                item.total ??
                item.TOTAL ??
                0
            )
        );
    });

    const options = {
        chart: {
            type: "donut",
            height: 385
        },
        labels: labels,
        series: series,
        legend: {
            position: "bottom",
            show: false
        },
        dataLabels: {
            enabled: true,
            formatter: function(val) {
                return val.toFixed(1) + "%";
            }
        },
        tooltip: {
            y: {
                formatter: function(val) {
                    return val.toLocaleString("id-ID") + " Pasien";
                }
            }
        }
    };

    const chartContainer = document.querySelector(`#${name}`);
    if (!chartContainer) return;

    chartContainer.innerHTML = "";
    chartInstances[name] = new ApexCharts(chartContainer, options);
    chartInstances[name].render();
}