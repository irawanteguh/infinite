(function () {
    const element = document.getElementById("summaryCompareChart");
    const rows = Array.isArray(window.summaryCompareRows) ? window.summaryCompareRows : [];
    if (!element || rows.length === 0 || typeof ApexCharts === "undefined") return;

    const values = rows.map(row => row.rata_pencapaian == null ? null : Number(row.rata_pencapaian));
    new ApexCharts(element, {
        chart: { type: "bar", height: 360, toolbar: { show: false }, fontFamily: "inherit" },
        series: [{ name: "Rata-rata capaian", data: values }],
        colors: ["#009ef7"],
        plotOptions: { bar: { borderRadius: 5, columnWidth: "45%", dataLabels: { position: "top" } } },
        dataLabels: { enabled: true, formatter: value => value == null ? "-" : `${Number(value).toLocaleString("id-ID", { maximumFractionDigits: 2 })}%`, offsetY: -20 },
        xaxis: { categories: rows.map(row => String(row.tahun)), title: { text: "Periode" } },
        yaxis: { min: 0, max: 100, tickAmount: 5, title: { text: "Rata-rata capaian (%)" }, labels: { formatter: value => `${Number(value).toLocaleString("id-ID", { maximumFractionDigits: 0 })}%` } },
        grid: { borderColor: "#eff2f5", strokeDashArray: 4 },
        tooltip: { y: { formatter: value => value == null ? "Belum dinilai" : `${Number(value).toLocaleString("id-ID", { maximumFractionDigits: 2 })}%` } },
        noData: { text: "Belum ada data capaian" }
    }).render();
})();
