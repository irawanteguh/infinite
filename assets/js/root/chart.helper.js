let apexChartInstances = {};

function renderAreaChart(config) {

    const defaults = {
        selector      : "",
        title         : "",
        categories    : [],
        series        : [],
        target        : null,
        height        : 350,
        min           : 0,
        max           : 100,
        ySuffix       : "%",
        colors        : ["#009EF7"],
        showToolbar   : false,
        showDataLabel : true
    };

    config = $.extend(true, {}, defaults, config);

    if ($(config.selector).length === 0) {
        return;
    }

    if (apexChartInstances[config.selector]) {
        apexChartInstances[config.selector].destroy();
    }

    let annotations = {};

    if (config.target !== null) {
        annotations = {
            yaxis: [{
                y: parseFloat(config.target),
                borderColor: "#F1416C",
                strokeDashArray: 6,
                label: {
                    borderColor: "#F1416C",
                    style: {
                        color: "#fff",
                        background: "#F1416C"
                    },
                    text: "Target : " + config.target + config.ySuffix
                }
            }]
        };
    }

    const options = {

        series: config.series,

        chart: {
            type: "area",
            height: config.height,
            toolbar: {
                show: config.showToolbar
            },
            zoom: {
                enabled: false
            }
        },

        colors: config.colors,

        stroke: {
            curve: "smooth",
            width: 3
        },

        fill: {
            type: "gradient",
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.45,
                opacityTo: 0.05,
                stops: [0,90,100]
            }
        },

        markers: {
            size: 4
        },

        dataLabels: {
            enabled: config.showDataLabel,
            formatter: function(val){
                return Number(val).toFixed(2) + config.ySuffix;
            }
        },

        xaxis: {
            categories: config.categories
        },

        yaxis: {
            min: config.min,
            max: config.max,
            labels: {
                formatter: function(val){
                    return Number(val).toFixed(0) + config.ySuffix;
                }
            }
        },

        tooltip: {
            shared: true,
            intersect: false,
            y: {
                formatter: function(val){
                    return Number(val).toFixed(2) + config.ySuffix;
                }
            }
        },

        grid: {
            borderColor: "#EFF2F5"
        },

        annotations: annotations

    };

    apexChartInstances[config.selector] = new ApexCharts(
        document.querySelector(config.selector),
        options
    );

    apexChartInstances[config.selector].render();
}