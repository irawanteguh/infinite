dataraweklaim();
detailtarif();

$('#selectperiode').on('change', function () {
    dataraweklaim();
    detailtarif();
});

function dataraweklaim(){
    let selectperiode = $("select[name='selectperiode']").val();
    $.ajax({
        url      : url + "ur/dashboard/dataraweklaim",
        data     : {selectperiode:selectperiode},
        type     : "POST",
        dataType : "JSON",
        beforeSend: function () {
            Swal.fire({
                title            : 'Processing',
                html             : 'Please wait while the system displays the requested data.',
                allowOutsideClick: false,
                allowEscapeKey   : false,
                showConfirmButton: false,
                didOpen          : () => Swal.showLoading()
            });
        },
        success: function (response) {

            if (!response || response.responseCode !== "00") {
                Swal.fire({
                    icon             : 'warning',
                    title            : 'No Records Found',
                    text             : 'No records are available for the selected period.',
                    showConfirmButton: false,
                    timer            : 2000
                });
                return;
            }

            const result       = Array.isArray(response.responseResult) ? response.responseResult : [];
            const bulanLengkap = ["01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12"];
            const namaBulan    = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];

            const jmlkunjungan  = aggregate(result, "count", "periode",["sep"]);
            const jmlpendapatan = aggregate(result,"sum","periode",["total_tarif", "idrg_total_tarif"]);

            const severityMap = {
                // "0"  : "Rawat Jalan",
                "I"  : "Severity I",
                "II" : "Severity II",
                "III": "Severity III"
            };

            const mapKunjungan     = {};
            const mapPendapatan    = {};
            const severityCount    = {};
            const severityMonthly  = {};

            let totalKunjungan = 0;
            let totalTarifRS   = 0;
            let totalINACBG    = 0;
            let totalIDRG      = 0;

            result.forEach(function (item) {
                totalKunjungan += 1;
                totalTarifRS   += parseFloat(item.tarif_rs) || 0;
                totalINACBG    += parseFloat(item.total_tarif) || 0;
                totalIDRG      += parseFloat(item.idrg_total_tarif) || 0;

                const periode = String(item.periode ?? "").trim();
                const sl      = String(item.sl ?? "").trim();
                const ptd     = String(item.ptd ?? "").trim();

                // Hitung kunjungan berdasarkan jenis pelayanan
                if (!mapKunjungan[periode]) {
                    mapKunjungan[periode] = {
                        rawatInap : 0,
                        rawatJalan: 0
                    };
                }

                if (ptd === "1") {
                    mapKunjungan[periode].rawatInap++;
                } else if (ptd === "2") {
                    mapKunjungan[periode].rawatJalan++;
                }

                // Proses severity tetap seperti sebelumnya
                if (!severityMap[sl]) return;

                severityCount[sl] = (severityCount[sl] || 0) + 1;

                if (!severityMonthly[periode]) {
                    severityMonthly[periode] = {
                        "0"  : 0,
                        "I"  : 0,
                        "II" : 0,
                        "III": 0
                    };
                }

                severityMonthly[periode][sl] += 1;
            });

            jmlpendapatan.forEach(function (item) {
                mapPendapatan[item.periode] = item;
            });


            const chartdatakunjungan = bulanLengkap.map(function (bulan, index) {
                const data = mapKunjungan[bulan] || {
                    rawatInap : 0,
                    rawatJalan: 0
                };

                return {
                    periode: namaBulan[index],
                    value1 : data.rawatInap,
                    value2 : data.rawatJalan
                };
            });
            
            const chartpendapatan = bulanLengkap.map(function (bulan, index) {
                return {
                    periode: namaBulan[index],
                    value1 : mapPendapatan[bulan]?.total_tarif || 0,
                    value2 : mapPendapatan[bulan]?.idrg_total_tarif || 0
                };
            });

            const chartseveritylevel = Object.keys(severityMap).filter(function (sl) {return severityCount[sl] > 0;}).map(function (sl) {
                return {
                    category: severityMap[sl],
                    value   : severityCount[sl]
                };
            });

            const chartDataSeverityMonthly = bulanLengkap.map(function(bulan, index) {
                const data = severityMonthly[bulan] || {
                    "0"  : 0,
                    "I"  : 0,
                    "II" : 0,
                    "III": 0
                };

                return {
                    periode: namaBulan[index],
                    value_1: data["0"],
                    value_2: data["I"],
                    value_3: data["II"],
                    value_4: data["III"]
                };
            });

            const selisihRSINACBG = totalINACBG - totalTarifRS;
            const selisihRSIDRG   = totalIDRG - totalTarifRS;

            $("#jmlkunjungan").text(new Intl.NumberFormat("id-ID").format(totalKunjungan));
            $("#jmltarifrs").text(todesimal(totalTarifRS));
            $("#jmlinacbg").text(todesimal(totalINACBG));
            $("#selisihinacbg").text(todesimal(selisihRSINACBG));
            $("#jmlidrg").text(todesimal(totalIDRG));
            $("#selisihidrg").text(todesimal(selisihRSIDRG));

            setStatusSelisih("#selisihinacbg", selisihRSINACBG);
            setStatusSelisih("#selisihidrg", selisihRSIDRG);

            renderchartarea("trenkunjungan",chartdatakunjungan,"Discharge Date","InPatient",["Rawat Inap", "Rawat Jalan"],["value1", "value2"],true,"OutPatient","","",null);
            renderchartarea("trenpendapatan",chartpendapatan,"Discharge Date","INACBG",["Tarif Ina-Cbg","Tarif IDRG"],["value1","value2"],true,"IDRG","","",null);
            renderchartpie("severitylevelyear", chartseveritylevel);

            renderChartStackedBar(
                "severitylevelmountly",
                chartDataSeverityMonthly.map(item => item.periode),
                [
                    // {
                    //     name: "Rawat Jalan",
                    //     data: chartDataSeverityMonthly.map(item => item.value_1)
                    // },
                    {
                        name: "Severity I",
                        data: chartDataSeverityMonthly.map(item => item.value_2)
                    },
                    {
                        name: "Severity II",
                        data: chartDataSeverityMonthly.map(item => item.value_3)
                    },
                    {
                        name: "Severity III",
                        data: chartDataSeverityMonthly.map(item => item.value_4)
                    }
                ],
                "Discharge Date",
                "Jumlah Kasus"
            );
        },
        complete: function () {
            Swal.close();
        },
        error: function () {
            Swal.fire({
                icon             : "error",
                title            : "Request Failed",
                text             : "An error occurred while processing your request.",
                confirmButtonText: "OK"
            });
        }
    });
};

function detailtarif(){
    let selectperiode = $("select[name='selectperiode']").val();
    $.ajax({
        url      : url + "ur/dashboard/detailtarif",
        data     : {selectperiode:selectperiode},
        type     : "POST",
        dataType : "JSON",
        beforeSend: function () {
            Swal.fire({
                title            : 'Processing',
                html             : 'Please wait while the system displays the requested data.',
                allowOutsideClick: false,
                allowEscapeKey   : false,
                showConfirmButton: false,
                didOpen          : () => Swal.showLoading()
            });
        },
        success: function (response) {

            if (!response || response.responseCode !== "00") {
                Swal.fire({
                    icon             : 'warning',
                    title            : 'No Records Found',
                    text             : 'No records are available for the selected period.',
                    showConfirmButton: false,
                    timer            : 2000
                });
                return;
            }

            const result       = Array.isArray(response.responseResult) ? response.responseResult : [];
            const bulanLengkap = ["01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12"];
            const namaBulan    = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];

            const costMonthly = {};

            const costComponents = [
                { field: "prosedurenonbedah", name: "Prosedur Non-Bedah" },
                { field: "prosedurbedah",     name: "Prosedur Bedah" },
                { field: "konsultasi",        name: "Konsultasi" },
                { field: "tenagaahli",        name: "Tenaga Ahli" },
                { field: "keperawatan",       name: "Keperawatan" },
                { field: "penunjang",         name: "Penunjang" },
                { field: "radiologi",         name: "Radiologi" },
                { field: "laboratorium",      name: "Laboratorium" },
                { field: "pelayanandarah",    name: "Pelayanan Darah" },
                { field: "rehabilitasi",      name: "Rehabilitasi" },
                { field: "kamarakomodasi",    name: "Kamar Akomodasi" },
                { field: "rawatintensif",     name: "Rawat Intensif" },
                { field: "obat",             name: "Obat" },
                { field: "alkes",            name: "Alkes" },
                { field: "bmhp",             name: "BMHP" },
                { field: "sewaalat",         name: "Sewa Alat" },
                { field: "obatkronis",       name: "Obat Kronis" },
                { field: "obatkemo",         name: "Obat Kemo" }
            ];

            result.forEach(function (item) {
                const periode = String(item.periode ?? "").trim();

                if (!costMonthly[periode]) {
                    costMonthly[periode] = {};

                    costComponents.forEach(function (component) {
                        costMonthly[periode][component.field] = 0;
                    });
                }

                costComponents.forEach(function (component) {
                    costMonthly[periode][component.field] +=
                        parseFloat(item[component.field]) || 0;
                });
            });

            const chartDataCostMonthly = bulanLengkap.map(function (bulan, index) {
                const data = costMonthly[bulan] || {};

                const row = {
                    periode: namaBulan[index]
                };

                costComponents.forEach(function (component) {
                    row[component.field] = data[component.field] || 0;
                });

                return row;
            });

            const chartDataCostYear = costComponents.map(function (component) {
                return {
                    category: component.name,
                    value: chartDataCostMonthly.reduce(function (total, item) {
                        return total + (parseFloat(item[component.field]) || 0);
                    }, 0)
                };
            });

            renderchartpie("costcomponentyear", chartDataCostYear);

            renderChartStackedBar(
                "costdistribution",
                chartDataCostMonthly.map(item => item.periode),
                costComponents.map(function (component) {
                    return {
                        name: component.name,
                        data: chartDataCostMonthly.map(item => item[component.field])
                    };
                }),
                "Discharge Date",
                "Total Cost (Rp)"
            );
        },
        complete: function () {
            Swal.close();
        },
        error: function () {
            Swal.fire({
                icon             : "error",
                title            : "Request Failed",
                text             : "An error occurred while processing your request.",
                confirmButtonText: "OK"
            });
        }
    });
};

function setStatusSelisih(element, value) {
    const card = $(element).closest(".card");
    const textElements = card.find(".text-dark, .text-success, .text-danger");
    card.removeClass("border-dark border-success border-danger bg-light-dark bg-light-success bg-light-danger");
    textElements.removeClass("text-dark text-success text-danger");
    if (value >= 0) {
        card.addClass("border-success bg-light-success");
        textElements.addClass("text-success");
    } else {
        card.addClass("border-danger bg-light-danger");
        textElements.addClass("text-danger");
    }
}
