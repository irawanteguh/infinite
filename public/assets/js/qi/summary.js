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

function renderIndicatorTypeSummary(rows) {
    const departmentType = "25ae899b-7384-11f1-a5e6-e0be037258a4";
    const typeCards = document.querySelectorAll("#summaryIndicatorTypes [data-indicator-type]");

    typeCards.forEach(card => {
        const typeId = card.dataset.indicatorType;
        const typeRows = rows.filter(row => String(row.jenis_indikator_id || "").toLowerCase() === typeId);
        const indicatorKey = row => String(row.indikator_id || row.indikator_code || row.transaksi_id || "");
        const indicators = new Set(typeRows.map(indicatorKey));
        let assessedCount = 0;
        let average = null;

        if (typeId === departmentType) {
            const aggregated = new Map();
            typeRows.forEach(row => {
                const key = indicatorKey(row);
                if (!aggregated.has(key)) aggregated.set(key, { numerator: 0, denominator: 0, assessed: false });
                const item = aggregated.get(key);
                item.numerator += qualitySummaryNumber(row.total_numerator);
                item.denominator += qualitySummaryNumber(row.total_denominator);
                item.assessed = item.assessed || qualitySummaryNumber(row.jumlah_penilaian) > 0;
            });

            const scores = [];
            aggregated.forEach(item => {
                if (!item.assessed) return;
                assessedCount++;
                const score = item.denominator === 0
                    ? (item.numerator === 0 ? 100 : 0)
                    : Math.min(100, (item.numerator / item.denominator) * 100);
                scores.push(score);
            });
            average = scores.length ? scores.reduce((sum, score) => sum + score, 0) / scores.length : null;
        } else {
            const scored = typeRows.filter(row => row.rata_pencapaian !== null && row.rata_pencapaian !== "");
            assessedCount = new Set(scored.map(indicatorKey)).size;
            average = scored.length
                ? scored.reduce((sum, row) => sum + qualitySummaryNumber(row.rata_pencapaian), 0) / scored.length
                : null;
        }

        card.querySelector("[data-type-count]").textContent = indicators.size.toLocaleString("id-ID");
        card.querySelector("[data-type-assessed]").textContent = assessedCount.toLocaleString("id-ID");
        card.querySelector("[data-type-average]").textContent = average === null
            ? "—"
            : average.toLocaleString("id-ID", { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + "%";
    });
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
    renderIndicatorTypeSummary(rows);

    const dimensionRadar = document.getElementById("summaryDimensionRadar");
    if (dimensionRadar) {
        if (dimensionRadar.chart) dimensionRadar.chart.destroy();
        const dimensions = [
            ["Keselamatan", "dimensi_mutu_keselamatan"],
            ["Tepat waktu", "dimensi_mutu_waktu"],
            ["Efektif", "dimensi_mutu_efektif"],
            ["Efisien", "dimensi_mutu_efesien"],
            ["Berfokus pada pasien", "dimensi_mutu_pasien"],
            ["Terintegrasi", "dimensi_mutu_integrasi"]
        ];
        const dimensionValues = dimensions.map(([label, field]) => {
            const assessed = rows.filter(row => (row[field] === "Y" || row[field] === 1 || row[field] === "1")
                && row.rata_pencapaian !== null && row.rata_pencapaian !== "");
            return { label, value: assessed.length
                ? assessed.reduce((sum, row) => sum + qualitySummaryNumber(row.rata_pencapaian), 0) / assessed.length
                : null };
        });
        const hasValues = dimensionValues.some(item => item.value !== null);
        $("#summaryDimensionRadarEmpty").toggleClass("d-none", hasValues);
        $(dimensionRadar).toggleClass("d-none", !hasValues);
        if (hasValues) {
            dimensionRadar.chart = new ApexCharts(dimensionRadar, {
                chart: { type: "radar", height: 380, toolbar: { show: false }, fontFamily: "inherit" },
                plotOptions: { radar: { size: 145 } },
                series: [{ name: "Rata-rata capaian", data: dimensionValues.map(item => item.value ?? 0) }],
                xaxis: { categories: dimensionValues.map(item => item.label), labels: { style: { fontSize: "12px" } } },
                yaxis: { min: 0, max: 100, tickAmount: 5, labels: { formatter: value => `${Number(value).toLocaleString("id-ID", { maximumFractionDigits: 0 })}%` } },
                colors: ["#009ef7"],
                fill: { opacity: 0.22 },
                stroke: { width: 3 },
                markers: { size: 4 },
                dataLabels: { enabled: true, formatter: value => `${Number(value).toLocaleString("id-ID", { maximumFractionDigits: 2 })}%` },
                tooltip: { y: { formatter: value => `${Number(value).toLocaleString("id-ID", { maximumFractionDigits: 2 })}%` } }
            });
            dimensionRadar.chart.render();
        }
    }

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

    const tableRows = rows.map(function (row, index) {
        const categoryBadge = "<span class='badge badge-light-primary'>" + qualitySummaryEscape(row.jenis) + "</span>";
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
            + "<td class='text-center'><a class='btn btn-sm btn-light-primary' href='" + url + "qi/summarycompare?indikator_id=" + encodeURIComponent(row.indikator_id || "") + "&department_id=" + encodeURIComponent(row.department_id || "") + "'><i class='bi bi-bar-chart-line me-1'></i>Compare</a></td>"
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

    const visible = [...qualitySummaryDepartmentData].sort((a, b) => b.average - a.average);
    const empty = visible.length === 0;
    $("#summaryDepartmentEmpty").toggleClass("d-none", !empty);
    $(chartElement).toggleClass("d-none", empty);
    $("#summaryDepartmentCaption").text(`${visible.length} departemen · diurutkan dari capaian tertinggi`);
    if (empty) return;

    chartElement.chart = new ApexCharts(chartElement, {
        chart: { type: "bar", height: 380, toolbar: { show: false }, fontFamily: "inherit" },
        series: [{ name: "Rata-rata capaian", data: visible.map(item => item.average) }],
        colors: visible.map(item => item.average < 50 ? "#f1416c" : (item.average < 80 ? "#ffc700" : "#50cd89")),
        plotOptions: { bar: { horizontal: false, borderRadius: 4, columnWidth: "52%", distributed: true } },
        dataLabels: { enabled: true, formatter: value => `${Number(value).toLocaleString("id-ID", { maximumFractionDigits: 2 })}%`, style: { fontSize: "11px" }, offsetY: -18 },
        legend: { show: false },
        xaxis: { categories: visible.map(item => item.department), labels: { rotate: -35, rotateAlways: visible.length > 5, trim: true, hideOverlappingLabels: true, style: { fontSize: "11px" } } },
        yaxis: { min: 0, max: 100, tickAmount: 5, title: { text: "Rata-rata capaian (%)" }, labels: { formatter: value => `${Number(value).toLocaleString("id-ID", { maximumFractionDigits: 0 })}%` } },
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
loadQualitySummary();
