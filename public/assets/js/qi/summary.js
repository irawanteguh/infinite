const qualitySummaryMonths = [
    "Jan", "Feb", "Mar", "Apr", "Mei", "Jun",
    "Jul", "Agu", "Sep", "Okt", "Nov", "Des"
];
let qualitySummaryDepartmentData = [];

function qualitySummaryNumber(value) {
    const number = Number(value);
    return Number.isFinite(number) ? number : 0;
}

function qualitySummaryHasScore(row) {
    return row.rata_pencapaian != null
        && String(row.rata_pencapaian).trim() !== ""
        && Number.isFinite(Number(row.rata_pencapaian));
}

function qualitySummaryTargetRate(rows) {
    const assessed = rows.reduce((total, row) => total + qualitySummaryNumber(row.jumlah_dinilai_target), 0);
    const achieved = rows.reduce((total, row) => total + qualitySummaryNumber(row.jumlah_memenuhi_target), 0);
    return assessed ? achieved / assessed * 100 : null;
}

function qualitySummaryEscape(value) {
    return $("<div>").text(value == null || value === "" ? "-" : String(value)).html();
}

function summaryProgressState(value) {
    return value === null ? 'is-empty' : value <= 50 ? 'is-low' : value <= 80 ? 'is-medium' : 'is-complete';
}

function summaryProgressAgainstTarget(value, target, direction) {
    if (value === null || target == null || String(target).trim() === '') return null;
    const targetValue = Number(target);
    if (!Number.isFinite(targetValue) || targetValue < 0) return null;
    if (targetValue === 0) return value === 0 ? 100 : 0;
    const progress = direction === 'L'
        ? (value === 0 ? 100 : targetValue / value * 100)
        : value / targetValue * 100;
    return Math.max(0, Math.min(100, progress));
}

function renderSummaryProgress(value, progressValue = value) {
    const empty = value === null;
    const state = summaryProgressState(progressValue);
    const label = empty ? 'Belum dinilai' : value.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '%';
    return '<div class="department-rate ' + state + '"><strong>' + label + '</strong>'
        + '<div class="department-rate-track" aria-hidden="true"><span style="width:' + (progressValue === null ? 0 : Math.max(0, Math.min(100, progressValue))) + '%"></span></div></div>';
}

function aggregateQualityIndicators(rows, typeId) {
    const grouped = new Map();
    rows.filter(row => String(row.jenis_indikator_id || '').toLowerCase() === typeId).forEach(row => {
        const key = row.indikator_id || row.indikator_code || row.transaksi_id;
        if (!grouped.has(key)) grouped.set(key, {
            code: row.indikator_code, name: row.indikator, departments: new Set(), targets: new Set(),
            numerator: 0, denominator: 0, measurements: 0, rows: []
        });
        const item = grouped.get(key);
        if (row.department_id) item.departments.add(row.department_id);
        if (row.target != null && String(row.target).trim() !== '') item.targets.add(String(row.target));
        item.numerator += qualitySummaryNumber(row.total_numerator);
        item.denominator += qualitySummaryNumber(row.total_denominator);
        item.measurements += qualitySummaryNumber(row.jumlah_penilaian);
        item.rows.push(row);
    });
    return Array.from(grouped.values()).map(item => ({
        ...item,
        achievement: !item.measurements ? null : item.denominator === 0
            ? (item.numerator === 0 ? 100 : 0) : Math.min(100, item.numerator / item.denominator * 100),
        targetRate: qualitySummaryTargetRate(item.rows)
    }));
}

function renderQualityAggregateTables(rows) {
    const types = {
        global: '1f513f16-d546-11f1-91d6-202b2018cf5a',
        national: '25ae8766-7384-11f1-a5e6-e0be037258a4',
        priority: '25ae8926-7384-11f1-a5e6-e0be037258a4'
    };
    const percent = value => value === null ? 'Belum dinilai'
        : value.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '%';
    Object.entries(types).forEach(([key, typeId]) => {
        const table = '#aggregate-' + key + '-table';
        if ($.fn.DataTable.isDataTable(table)) $(table).DataTable().destroy();
        const items = aggregateQualityIndicators(rows, typeId);
        const html = items.map((item, index) => '<tr>'
            + '<td class="ps-4">' + (index + 1) + '</td>'
            + '<td><div>' + qualitySummaryEscape(item.code) + '</div><div>' + qualitySummaryEscape(item.name) + '</div></td>'
            + '<td class="text-center">' + item.departments.size + '</td>'
            + '<td class="text-center">' + qualitySummaryEscape(Array.from(item.targets).join('%; ') + (item.targets.size ? '%' : '')) + '</td>'
            + '<td class="text-center">' + item.measurements.toLocaleString('id-ID') + '</td>'
            + '<td class="text-center">' + item.numerator.toLocaleString('id-ID') + '</td>'
            + '<td class="text-center">' + item.denominator.toLocaleString('id-ID') + '</td>'
            + '<td class="text-center">' + percent(item.achievement) + '</td>'
            + '<td class="text-end">' + renderSummaryProgress(item.targetRate) + '</td>'
            + '<td class="text-end pe-4"><button type="button" class="btn btn-sm btn-light-primary aggregate-departments" data-index="' + index + '" aria-expanded="false">Lihat departemen</button></td></tr>').join('');
        $('#aggregate-' + key + '-rows').html(html);
        const dataTable = initDataTable(table, '#aggregate-' + key + '-search', 25);
        $(table).off('click.aggregate', '.aggregate-departments').on('click.aggregate', '.aggregate-departments', function () {
            const row = dataTable.row($(this).closest('tr'));
            const opened = row.child.isShown();
            if (opened) {
                row.child.hide();
            } else {
                Object.keys(types).forEach(typeKey => {
                    const selector = '#aggregate-' + typeKey + '-table';
                    if (!$.fn.DataTable.isDataTable(selector)) return;
                    $(selector).DataTable().rows().every(function () {
                        if (!this.child.isShown()) return;
                        this.child.hide();
                        $(this.node()).find('.aggregate-departments')
                            .attr('aria-expanded', 'false').text('Lihat departemen');
                    });
                });
                row.child(renderAggregateDepartments(items[Number(this.dataset.index)].rows)).show();
            }
            this.setAttribute('aria-expanded', String(!opened));
            this.textContent = opened ? 'Lihat departemen' : 'Tutup departemen';
        });
    });
}

function renderAggregateDepartments(rows) {
    const departments = new Map();
    rows.forEach(row => {
        const key = row.department_id || row.department || '';
        if (!departments.has(key)) departments.set(key, []);
        departments.get(key).push(row);
    });
    const percent = value => value === null ? 'Belum dinilai'
        : value.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '%';
    const body = Array.from(departments.values()).map((departmentRows, index) => {
        const numerator = departmentRows.reduce((sum, row) => sum + qualitySummaryNumber(row.total_numerator), 0);
        const denominator = departmentRows.reduce((sum, row) => sum + qualitySummaryNumber(row.total_denominator), 0);
        const measurements = departmentRows.reduce((sum, row) => sum + qualitySummaryNumber(row.jumlah_penilaian), 0);
        const targets = [...new Set(departmentRows.filter(row => row.target != null && String(row.target).trim() !== '').map(row => String(row.target)))];
        const achievement = !measurements ? null : denominator === 0 ? (numerator === 0 ? 100 : 0) : Math.min(100, numerator / denominator * 100);
        const rate = qualitySummaryTargetRate(departmentRows);
        const rateClass = summaryProgressState(rate);
        const achievedCount = departmentRows.reduce((sum, row) => sum + qualitySummaryNumber(row.jumlah_memenuhi_target), 0);
        const assessedCount = departmentRows.reduce((sum, row) => sum + qualitySummaryNumber(row.jumlah_dinilai_target), 0);
        return '<tr><td class="ps-4"><span class="department-row-number">' + (index + 1) + '</span></td>'
            + '<td><div class="department-name"><span class="department-icon"><i class="bi bi-building"></i></span><span>' + qualitySummaryEscape(departmentRows[0].department) + '</span></div></td>'
            + '<td class="text-center"><span class="department-target">' + qualitySummaryEscape(targets.map(target => target + '%').join('; ')) + '</span></td>'
            + '<td class="text-center"><span class="department-measurements">' + measurements.toLocaleString('id-ID') + '</span></td>'
            + '<td class="text-center">' + numerator.toLocaleString('id-ID') + '</td>'
            + '<td class="text-center">' + denominator.toLocaleString('id-ID') + '</td>'
            + '<td class="text-center fw-bold">' + percent(achievement) + '</td>'
            + '<td class="text-end pe-4"><div class="department-rate ' + rateClass + '"><strong>' + percent(rate) + '</strong>'
            + '<div class="department-rate-track" aria-hidden="true"><span style="width:' + (rate === null ? 0 : Math.max(0, Math.min(100, rate))) + '%"></span></div>'
            + '<small>' + (rate === null ? 'Belum ada penilaian' : achievedCount.toLocaleString('id-ID') + ' dari ' + assessedCount.toLocaleString('id-ID') + ' memenuhi target') + '</small></div></td></tr>';
    }).join('');
    return '<section class="department-detail my-3" aria-label="Rincian per departemen"><div class="department-detail-heading">'
        + '<div><span class="summary-eyebrow">KONTRIBUSI UNIT</span><h4>Rincian per departemen</h4><p>Capaian pengukuran dan pemenuhan target pada periode terpilih</p></div>'
        + '<span class="department-count"><i class="bi bi-buildings me-2"></i>' + departments.size + ' departemen</span></div>'
        + '<div class="table-responsive"><table class="table align-middle department-detail-table mb-0">'
        + '<thead><tr class="fw-bolder text-muted"><th class="ps-4">#</th><th>Departemen</th><th class="text-center">Target</th>'
        + '<th class="text-center">Penilaian</th><th class="text-center">Total numerator</th><th class="text-center">Total denominator</th>'
        + '<th class="text-center">Capaian gabungan</th><th class="text-end pe-4">Ketercapaian target</th></tr></thead>'
        + '<tbody>' + body + '</tbody></table></div></section>';
}

function renderIndicatorTypeSummary(rows) {
    const typeCards = document.querySelectorAll("#summaryIndicatorTypes [data-indicator-type]");

    typeCards.forEach(card => {
        const typeId = card.dataset.indicatorType;
        const typeRows = rows.filter(row => String(row.jenis_indikator_id || "").toLowerCase() === typeId);
        const indicatorKey = row => String(row.indikator_id || row.indikator_code || row.transaksi_id || "");
        const indicators = new Set(typeRows.map(indicatorKey));
        const assessedCount = new Set(typeRows.filter(qualitySummaryHasScore).map(indicatorKey)).size;
        const average = qualitySummaryTargetRate(typeRows);

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

            renderQualitySummary(response.responseResult || [], response.responseTrend || [], response.responseOverdue || []);
        },
        error: function () {
            Swal.fire({ icon: "error", title: "Gagal memuat data", text: "Ringkasan indikator tidak dapat dimuat." });
        },
        complete: function () {
            Swal.close();
        }
    });
}

function renderOverdueIndicators(rows) {
    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    const table = '#summary-overdue-table';
    if ($.fn.DataTable.isDataTable(table)) $(table).DataTable().destroy();
    $('#summary-overdue-count').text(rows.length.toLocaleString('id-ID'));
    $('#summary-overdue-total').text(rows.length.toLocaleString('id-ID'));
    const overdueMonths = rows.reduce((sum, row) => sum + (Array.isArray(row.overdue_months) ? row.overdue_months.length : 0), 0);
    $('#summary-overdue-caption').text(rows.length
        ? rows.length.toLocaleString('id-ID') + ' indikator/departemen dengan ' + overdueMonths.toLocaleString('id-ID') + ' pengisian bulanan melewati batas waktu.'
        : 'Tidak ada pengisian yang melewati batas waktu pada periode ini.');
    $('#summary-overdue-rows').html(rows.map((row, index) => {
        const missing = Array.isArray(row.overdue_months) ? row.overdue_months : [];
        return '<tr><td class="ps-4">' + (index + 1) + '</td>'
            + '<td>' + qualitySummaryEscape(row.department) + '</td>'
            + '<td><div>' + qualitySummaryEscape(row.indikator_code) + '</div><div>' + qualitySummaryEscape(row.indikator) + '</div></td>'
            + '<td>' + qualitySummaryEscape(row.jenis) + '</td>'
            + '<td>' + qualitySummaryEscape(missing.map(month => months[Number(month) - 1]).join(', ')) + '</td>'
            + '<td class="text-center">' + missing.length + '</td>'
            + '<td class="text-end pe-4"><span class="badge badge-light-danger">Batas waktu berakhir</span></td></tr>';
    }).join(''));
    initDataTable(table, '#summary-overdue-search', 25, {
        language: { emptyTable: 'Tidak ada indikator yang belum diisi melewati batas waktu pada periode ini.' }
    });
}

function renderQualitySummary(rows, monthlyTrend, overdueRows = []) {
    rows = Array.isArray(rows) ? rows : [];
    monthlyTrend = Array.isArray(monthlyTrend) ? monthlyTrend : [];
    renderIndicatorTypeSummary(rows);
    renderQualityAggregateTables(rows);
    renderOverdueIndicators(Array.isArray(overdueRows) ? overdueRows : []);

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
                && qualitySummaryHasScore(row));
            return { label, value: qualitySummaryTargetRate(assessed) };
        });
        const hasValues = dimensionValues.some(item => item.value !== null);
        $("#summaryDimensionRadarEmpty").toggleClass("d-none", hasValues);
        $(dimensionRadar).toggleClass("d-none", !hasValues);
        if (hasValues) {
            dimensionRadar.chart = new ApexCharts(dimensionRadar, {
                chart: { type: "radar", height: 380, toolbar: { show: false }, fontFamily: "inherit" },
                plotOptions: { radar: { size: 145 } },
                series: [{ name: "Ketercapaian target", data: dimensionValues.map(item => item.value) }],
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
    const scoredRows = rows.filter(qualitySummaryHasScore);
    const average = qualitySummaryTargetRate(rows);
    const departments = new Set(rows.map(row => row.department).filter(Boolean));

    $("#summary-total-indicators").text(rows.length.toLocaleString("id-ID"));
    $("#summary-national-indicators").text(nationalRows.length.toLocaleString("id-ID"));
    $("#summary-assessed-indicators").text(scoredRows.length.toLocaleString("id-ID"));
    $("#summary-average").text(average === null ? "Belum dinilai" : average.toLocaleString("id-ID", { maximumFractionDigits: 2 }) + "%");
    $('#summary-target-progress').css('width', (average === null ? 0 : Math.max(0, Math.min(100, average))) + '%');
    $('[data-summary-period]').text($('#selectsummaryperiode').val() || '—');
    $("#summary-departments").text(departments.size.toLocaleString("id-ID"));

    const tableRows = rows.map(function (row, index) {
        const categoryBadge = "<span class='badge badge-light-primary'>" + qualitySummaryEscape(row.jenis) + "</span>";
        const targetValue = row.target == null || row.target === "" ? "-" : qualitySummaryEscape(row.target);
        const targetDirection = row.target_capaian === "H" ? "Lebih tinggi lebih baik"
            : row.target_capaian === "L" ? "Lebih rendah lebih baik"
            : row.target_capaian === "R" ? "Rentang"
            : "";
        const unit = row.satuan ? " " + qualitySummaryEscape(row.satuan) : "";
        const score = qualitySummaryHasScore(row) ? qualitySummaryNumber(row.rata_pencapaian) : null;
        const achievement = renderSummaryProgress(score, summaryProgressAgainstTarget(score, row.target, row.target_capaian));
        return "<tr>"
            + "<td class='ps-4'>" + (index + 1) + "</td>"
            + "<td>" + qualitySummaryEscape(row.department) + "</td>"
            + "<td><div>"+qualitySummaryEscape(row.indikator_code)+"</div><div>" + qualitySummaryEscape(row.indikator) + "</div><div>"+categoryBadge+"</div></td>"
            + "<td class='text-center'><span class='fw-bold'>" + targetValue + "</span></td>"
            + "<td class='text-center'><span class='fw-bold'>" + unit + "</span>" + (targetDirection ? "<div class='text-muted fs-8'>" + targetDirection + "</div>" : "") + "</td>"
            + "<td class='text-center'>" + qualitySummaryNumber(row.jumlah_bulan).toLocaleString("id-ID") + " / 12</td>"
            + "<td class='text-center'>" + qualitySummaryNumber(row.jumlah_penilaian).toLocaleString("id-ID") + "</td>"
            + "<td class='text-end pe-4'>" + achievement + "</td>"
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
            department: department ? qualitySummaryTargetRate([department]) : null,
            national: national ? qualitySummaryTargetRate([national]) : null
        };
    });

    const trendChart = document.getElementById('summaryTrendChart');
    if (trendChart) {
        if (trendChart.chart) trendChart.chart.destroy();
        trendChart.chart = new ApexCharts(trendChart, {
            chart: { type: 'area', height: 380, toolbar: { show: false }, fontFamily: 'inherit' },
            series: [
                { name: 'Indikator Departemen', data: trendData.map(item => item.department) },
                { name: 'Indikator Nasional Mutu', data: trendData.map(item => item.national) }
            ],
            xaxis: { categories: qualitySummaryMonths, title: { text: 'Bulan' } },
            yaxis: {
                min: 0, max: 100, title: { text: 'Ketercapaian target (%)' },
                labels: { formatter: value => Number(value).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '%' }
            },
            stroke: { curve: 'straight', width: 3 },
            markers: { size: 4 },
            dataLabels: { enabled: false },
            tooltip: { y: { formatter: value => value == null ? 'Belum dinilai' : value.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '%' } }
        });
        trendChart.chart.render();
    }

    const departmentStats = {};
    rows.forEach(function (row) {
        if (!row.department) return;
        if (!departmentStats[row.department]) {
            departmentStats[row.department] = { department: row.department, total: 0, count: 0, indicators: 0, assessed: 0 };
        }
        departmentStats[row.department].indicators++;
        departmentStats[row.department].total += qualitySummaryNumber(row.jumlah_memenuhi_target);
        departmentStats[row.department].count += qualitySummaryNumber(row.jumlah_dinilai_target);
        if (qualitySummaryNumber(row.jumlah_dinilai_target) > 0) departmentStats[row.department].assessed++;
    });

    qualitySummaryDepartmentData = Object.values(departmentStats).map(function (item) {
        return {
            department: item.department,
            average: item.count ? Number((item.total / item.count * 100).toFixed(2)) : null,
            assessed: item.assessed,
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
        series: [{ name: "Ketercapaian target", data: visible.map(item => item.average) }],
        colors: visible.map(item => item.average < 50 ? "#f1416c" : (item.average < 80 ? "#ffc700" : "#50cd89")),
        plotOptions: { bar: { horizontal: false, borderRadius: 4, columnWidth: "52%", distributed: true } },
        dataLabels: { enabled: true, formatter: value => `${Number(value).toLocaleString("id-ID", { maximumFractionDigits: 2 })}%`, style: { fontSize: "11px" }, offsetY: -18 },
        legend: { show: false },
        xaxis: { categories: visible.map(item => item.department), labels: { rotate: -35, rotateAlways: visible.length > 5, trim: true, hideOverlappingLabels: true, style: { fontSize: "11px" } } },
        yaxis: { min: 0, max: 100, tickAmount: 5, title: { text: "Ketercapaian target (%)" }, labels: { formatter: value => `${Number(value).toLocaleString("id-ID", { maximumFractionDigits: 0 })}%` } },
        grid: { borderColor: "#eff2f5", strokeDashArray: 4 },
        tooltip: { custom: ({ dataPointIndex }) => {
            const item = visible[dataPointIndex];
            if (!item) return "";
            return `<div class="p-3"><div class="fw-bold mb-1">${qualitySummaryEscape(item.department)}</div><div>Ketercapaian target: ${item.average.toLocaleString("id-ID", { maximumFractionDigits: 2 })}%</div><div class="text-muted">Dinilai: ${item.assessed} dari ${item.indicators} indikator</div></div>`;
        } },
        noData: { text: "Tidak ada data capaian" }
    });
    chartElement.chart.render();
}

$(document).on("change", "#selectsummaryperiode", loadQualitySummary);
$(document).on('shown.bs.tab', '.summary-aggregate-tabs [data-bs-toggle="tab"]', function () {
    const panel = $(this.getAttribute('data-bs-target'));
    const table = panel.find('table').first();
    if (table.length && $.fn.DataTable.isDataTable(table[0])) table.DataTable().columns.adjust();
});
loadQualitySummary();
