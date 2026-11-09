const qualitySummaryMonths = [
    "Jan", "Feb", "Mar", "Apr", "Mei", "Jun",
    "Jul", "Agu", "Sep", "Okt", "Nov", "Des"
];
let qualitySummaryDepartmentData = [];

function qualitySummaryNumber(value) {
    const number = Number(value);
    return Number.isFinite(number) ? number : 0;
}

function qualitySummaryEscape(value) {
    return $("<div>").text(value == null || value === "" ? "-" : String(value)).html();
}

function loadQualitySummary() {
    const year = $("#selectsummaryperiode").val();

    $.ajax({
        url: url + "qi/summary/summarydata",
        type: "POST",
        dataType: "JSON",
        data: { tahun: year },
        beforeSend: function () {
            Swal.fire({
                title: "Memuat ringkasan",
                text: "Mengambil data penilaian indikator...",
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => Swal.showLoading()
            });
        },
        success: function (response) {
            if (response.responseCode !== "00") {
                $("#qualitySummaryRows").empty();
                renderQualitySummary([], []);
                Swal.fire({ icon: "info", title: "Data tidak tersedia", text: response.responseDesc || "Tidak ada data untuk periode ini." });
                return;
            }

            renderQualitySummary(response.responseResult || [], response.responseTrend || []);
            $("#summary-period-label").text(year || "-");
        },
        error: function () {
            Swal.fire({ icon: "error", title: "Gagal memuat data", text: "Ringkasan indikator tidak dapat dimuat." });
        },
        complete: function () {
            Swal.close();
        }
    });
}

function renderQualitySummary(rows, monthlyTrend) {
    rows = Array.isArray(rows) ? rows : [];
    monthlyTrend = Array.isArray(monthlyTrend) ? monthlyTrend : [];

    const nationalRows = rows.filter(row => row.kategori === "Nasional Mutu");
    const scoredRows = rows.filter(row => row.rata_pencapaian !== null && row.rata_pencapaian !== "");
    const average = scoredRows.length
        ? scoredRows.reduce((total, row) => total + qualitySummaryNumber(row.rata_pencapaian), 0) / scoredRows.length
        : 0;
    const departments = new Set(rows.map(row => row.department).filter(Boolean));

    $("#summary-total-indicators").text(rows.length.toLocaleString("id-ID"));
    $("#summary-national-indicators").text(nationalRows.length.toLocaleString("id-ID"));
    $("#summary-assessed-indicators").text(scoredRows.length.toLocaleString("id-ID"));
    $("#summary-average").text(average.toLocaleString("id-ID", { maximumFractionDigits: 2 }) + "%");
    $("#summary-departments").text(departments.size.toLocaleString("id-ID"));
    $("#summary-period-label").text($("#selectsummaryperiode").val() || "-");

    const tableRows = rows.map(function (row, index) {
        const categoryBadge = row.kategori === "Nasional Mutu"
            ? "<span class='badge badge-light-primary'>Indikator Nasional Mutu</span>"
            : "<span class='badge badge-light-info'>Indikator Departemen</span>";
        const targetValue = row.target == null || row.target === "" ? "-" : qualitySummaryEscape(row.target);
        const targetDirection = row.target_capaian === "H" ? "Lebih tinggi lebih baik"
            : row.target_capaian === "L" ? "Lebih rendah lebih baik"
            : row.target_capaian === "R" ? "Rentang"
            : "";
        const unit = row.satuan ? " " + qualitySummaryEscape(row.satuan) : "";
        const achievement = row.rata_pencapaian == null
            ? "<span class='text-muted'>Belum dinilai</span>"
            : "<span class='badge badge-light-success'>" + qualitySummaryNumber(row.rata_pencapaian).toLocaleString("id-ID", { maximumFractionDigits: 2 }) + "%</span>";
        return "<tr>"
            + "<td class='ps-4'>" + (index + 1) + "</td>"
            + "<td>" + qualitySummaryEscape(row.department) + "</td>"
            + "<td><div>"+qualitySummaryEscape(row.indikator_code)+"</div><div>" + qualitySummaryEscape(row.indikator) + "</div><div>"+categoryBadge+"</div></td>"
            + "<td class='text-center'><span class='fw-bold'>" + targetValue + "</span></td>"
            + "<td class='text-center'><span class='fw-bold'>" + unit + "</span>" + (targetDirection ? "<div class='text-muted fs-8'>" + targetDirection + "</div>" : "") + "</td>"
            + "<td class='text-center'>" + qualitySummaryNumber(row.jumlah_bulan).toLocaleString("id-ID") + " / 12</td>"
            + "<td class='text-center'>" + qualitySummaryNumber(row.jumlah_penilaian).toLocaleString("id-ID") + "</td>"
            + "<td class='text-center pe-4'>" + achievement + "</td>"
            + "</tr>";
    }).join("");

    if ($.fn.DataTable.isDataTable("#qualitySummaryTable")) {
        $("#qualitySummaryTable").DataTable().destroy();
    }
    $("#qualitySummaryRows").html(tableRows);
    initDataTable("#qualitySummaryTable", "#qualitySummarySearch", 25);

    const trendData = qualitySummaryMonths.map(function (month, index) {
        const monthKey = String(index + 1).padStart(2, "0");
        const department = monthlyTrend.find(item => String(item.bulan).padStart(2, "0") === monthKey && item.kategori === "Departemen");
        const national = monthlyTrend.find(item => String(item.bulan).padStart(2, "0") === monthKey && item.kategori === "Nasional Mutu");
        return {
            periode: month,
            department: department ? qualitySummaryNumber(department.rata_pencapaian) : 0,
            national: national ? qualitySummaryNumber(national.rata_pencapaian) : 0
        };
    });

    renderchartarea(
        "summaryTrendChart",
        trendData,
        "Bulan",
        "Rata-rata capaian (%)",
        ["Indikator Departemen", "Indikator Nasional Mutu"],
        ["department", "national"]
    );

    const departmentStats = {};
    rows.forEach(function (row) {
        if (!row.department) return;
        if (!departmentStats[row.department]) {
            departmentStats[row.department] = { department: row.department, total: 0, count: 0, indicators: 0 };
        }
        departmentStats[row.department].indicators++;
        if (row.rata_pencapaian !== null && row.rata_pencapaian !== "") {
            departmentStats[row.department].total += qualitySummaryNumber(row.rata_pencapaian);
            departmentStats[row.department].count++;
        }
    });

    qualitySummaryDepartmentData = Object.values(departmentStats).map(function (item) {
        return {
            department: item.department,
            average: item.count ? Number((item.total / item.count).toFixed(2)) : null,
            assessed: item.count,
            indicators: item.indicators
        };
    }).filter(item => item.average !== null);
    drawQualitySummaryDepartmentChart();
}

function drawQualitySummaryDepartmentChart() {
    const chartElement = document.getElementById("summaryDepartmentChart");
    if (!chartElement) return;
    if (chartElement.chart) chartElement.chart.destroy();

    const attention = $("#summaryDepartmentView").val() === "attention";
    const ranked = [...qualitySummaryDepartmentData].sort((a, b) => attention ? a.average - b.average : b.average - a.average);
    const visible = ranked.slice(0, 10);
    const empty = visible.length === 0;
    $("#summaryDepartmentEmpty").toggleClass("d-none", !empty);
    $(chartElement).toggleClass("d-none", empty);
    $("#summaryDepartmentCaption").text(`${visible.length} dari ${qualitySummaryDepartmentData.length} departemen · ${attention ? "capaian terendah" : "capaian tertinggi"} ditampilkan`);
    if (empty) return;

    chartElement.chart = new ApexCharts(chartElement, {
        chart: { type: "bar", height: 330, toolbar: { show: false }, fontFamily: "inherit" },
        series: [{ name: "Rata-rata capaian", data: visible.map(item => item.average) }],
        colors: [attention ? "#f1416c" : "#50cd89"],
        plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: "58%", distributed: true } },
        dataLabels: { enabled: true, formatter: value => `${Number(value).toLocaleString("id-ID", { maximumFractionDigits: 1 })}%`, style: { fontSize: "11px" } },
        xaxis: { categories: visible.map(item => item.department), min: 0, max: 100, tickAmount: 4, labels: { formatter: value => `${Number(value).toLocaleString("id-ID")}%` } },
        yaxis: { labels: { maxWidth: 145, style: { fontSize: "11px" } } },
        grid: { borderColor: "#eff2f5", strokeDashArray: 4 },
        tooltip: { custom: ({ dataPointIndex }) => {
            const item = visible[dataPointIndex];
            if (!item) return "";
            return `<div class="p-3"><div class="fw-bold mb-1">${qualitySummaryEscape(item.department)}</div><div>Rata-rata: ${item.average.toLocaleString("id-ID", { maximumFractionDigits: 2 })}%</div><div class="text-muted">Dinilai: ${item.assessed} dari ${item.indicators} indikator</div></div>`;
        } },
        noData: { text: "Tidak ada data capaian" }
    });
    chartElement.chart.render();
}

$(document).on("change", "#selectsummaryperiode", loadQualitySummary);
$(document).on("change", "#summaryDepartmentView", drawQualitySummaryDepartmentChart);
loadQualitySummary();
