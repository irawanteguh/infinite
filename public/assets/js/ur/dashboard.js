dataraweklaim();

$('#selectperiode').on('change', function () {
    dataraweklaim();
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

            // Total keseluruhan
            let totalKunjungan = 0;
            let totalTarifRS   = 0;
            let totalINACBG    = 0;
            let totalIDRG      = 0;

            // Total InPatient (PTD = 1)
            let kunjunganRI = 0;
            let tarifRSRI   = 0;
            let inaCBGRI    = 0;
            let idrgRI      = 0;

            // Total OutPatient (PTD = 2)
            let kunjunganRJ = 0;
            let tarifRSRJ   = 0;
            let inaCBGRJ    = 0;
            let idrgRJ      = 0;

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

            const mapKunjunganRI      = {};
            const mapKunjunganRJ      = {};
            const mapPendapatanRI     = {};
            const mapPendapatanRJ     = {};
            const mapPendapatanInacbg = {};
            const mapPendapatanIdrg   = {};
            const mapSeverityLevelI   = {};
            const mapSeverityLevelII  = {};
            const mapSeverityLevelIII = {};
            const mapCostDistribution = {};

            costComponents.forEach(function (item) {
                mapCostDistribution[item.field] = {};
            });

            for (const item of result) {
                const periode = String(item.periode ?? "").trim();
                const ptd     = String(item.ptd ?? "").trim();
                const sl      = String(item.sl ?? "").trim();
                const jumlah  = parseInt(item.jumlah_kasus, 10) || 0;
                const tarifRS = parseFloat(item.tarif_rs) || 0;
                const inaCBG  = parseFloat(item.total_tarif) || 0;
                const idrg    = parseFloat(item.idrg_total_tarif) || 0;

                // Total keseluruhan
                totalKunjungan += jumlah;
                totalTarifRS   += tarifRS;
                totalINACBG    += inaCBG;
                totalIDRG      += idrg;

                mapPendapatanInacbg[periode] = (mapPendapatanInacbg[periode] || 0) + inaCBG;
                mapPendapatanIdrg[periode]   = (mapPendapatanIdrg[periode] || 0) + idrg;

                if (ptd === "1") {
                    // InPatient
                    kunjunganRI += jumlah;
                    tarifRSRI   += tarifRS;
                    inaCBGRI    += inaCBG;
                    idrgRI      += idrg;

                    mapKunjunganRI[periode] = (mapKunjunganRI[periode] || 0) + jumlah;
                    mapPendapatanRI[periode] = (mapPendapatanRI[periode] || 0) + inaCBG;

                    if (sl === "I") {
                        mapSeverityLevelI[periode] =
                            (mapSeverityLevelI[periode] || 0) + jumlah;

                    } else if (sl === "II") {
                        mapSeverityLevelII[periode] =
                            (mapSeverityLevelII[periode] || 0) + jumlah;

                    } else if (sl === "III") {
                        mapSeverityLevelIII[periode] =
                            (mapSeverityLevelIII[periode] || 0) + jumlah;
                    }

                    costComponents.forEach(function (component) {
                        const nilaiBiaya = parseFloat(item[component.field]) || 0;

                        mapCostDistribution[component.field][periode] =
                            (mapCostDistribution[component.field][periode] || 0) + nilaiBiaya;
                    });

                } else if (ptd === "2") {
                    // OutPatient
                    kunjunganRJ += jumlah;
                    tarifRSRJ   += tarifRS;
                    inaCBGRJ    += inaCBG;
                    idrgRJ      += idrg;

                    mapKunjunganRJ[periode] = (mapKunjunganRJ[periode] || 0) + jumlah;
                    mapPendapatanRJ[periode] = (mapPendapatanRJ[periode] || 0) + inaCBG;
                }   
                
                
            }

            // Selisih total
            const selisihRSINACBG = totalINACBG - totalTarifRS;
            const selisihRSIDRG   = totalIDRG - totalTarifRS;

            // Selisih InPatient
            const selisihINACBGRI = inaCBGRI - tarifRSRI;
            const selisihIDRGRI   = idrgRI - tarifRSRI;

            // Selisih OutPatient
            const selisihINACBGRJ = inaCBGRJ - tarifRSRJ;
            const selisihIDRGRJ   = idrgRJ - tarifRSRJ;

            const chartkunjungan = bulanLengkap.map(function (bulan, index) {
                return {
                    periode: namaBulan[index],
                    value1 : mapKunjunganRI[bulan] || 0,
                    value2 : mapKunjunganRJ[bulan] || 0
                };
            });

            const chartpendapatan = bulanLengkap.map(function (bulan, index) {
                return {
                    periode: namaBulan[index],
                    value1 : mapPendapatanRI[bulan] || 0,
                    value2 : mapPendapatanRJ[bulan] || 0
                };
            });

            const chartpendapatancompare = bulanLengkap.map(function (bulan, index) {
                return {
                    periode: namaBulan[index],
                    value1 : mapPendapatanInacbg[bulan] || 0,
                    value2 : mapPendapatanIdrg[bulan] || 0
                };
            });

            const chartseveritylevel = bulanLengkap.map(function (bulan, index) {
                return {
                    periode: namaBulan[index],
                    value1: mapSeverityLevelI[bulan] || 0,
                    value2: mapSeverityLevelII[bulan] || 0,
                    value3: mapSeverityLevelIII[bulan] || 0
                };
            });

            const chartseveritylevelyear = [
                {
                    label: "Severity Level I",
                    value: Object.values(mapSeverityLevelI).reduce((total, jumlah) => total + jumlah, 0)
                },
                {
                    label: "Severity Level II",
                    value: Object.values(mapSeverityLevelII).reduce((total, jumlah) => total + jumlah, 0)
                },
                {
                    label: "Severity Level III",
                    value: Object.values(mapSeverityLevelIII).reduce((total, jumlah) => total + jumlah, 0)
                }
            ];

            const chartcostdistribution = bulanLengkap.map(function (bulan, index) {
                const data = {
                    periode: namaBulan[index]
                };

                costComponents.forEach(function (component) {
                    data[component.field] =
                        mapCostDistribution[component.field][bulan] || 0;
                });

                return data;
            });

            const chartcostcomponentyear = costComponents.map(function (component) {
                const total = Object.values(
                    mapCostDistribution[component.field]
                ).reduce(function (sum, value) {
                    return sum + value;
                }, 0);

                return {
                    label: component.name,
                    value: total
                };
            });

            // ==========================================
            // TOTAL KUNJUNGAN
            // ==========================================
            $("#jmlkunjungan").text(todesimal(totalKunjungan));
            $("#jmlkunjungan_inpatient").text(todesimal(kunjunganRI));
            $("#jmlkunjungan_outpatient").text(todesimal(kunjunganRJ));

            // ==========================================
            // TOTAL TARIF RS
            // ==========================================
            $("#jmltarifrs").text(todesimal(totalTarifRS));
            $("#jmltarifrs_inpatient").text(todesimal(tarifRSRI));
            $("#jmltarifrs_outpatient").text(todesimal(tarifRSRJ));

            // ==========================================
            // TOTAL INA-CBG
            // ==========================================
            $("#jmlinacbg").text(todesimal(totalINACBG));
            $("#jmlinacbg_inpatient").text(todesimal(inaCBGRI));
            $("#jmlinacbg_outpatient").text(todesimal(inaCBGRJ));

            // ==========================================
            // TOTAL iDRG
            // ==========================================
            $("#jmlidrg").text(todesimal(totalIDRG));
            $("#jmlidrg_inpatient").text(todesimal(idrgRI));
            $("#jmlidrg_outpatient").text(todesimal(idrgRJ));

            // ==========================================
            // SELISIH RS VS INA-CBG
            // ==========================================
            $("#selisihinacbg").text(todesimal(selisihRSINACBG));
            $("#selisihinacbg_inpatient").text(todesimal(selisihINACBGRI));
            $("#selisihinacbg_outpatient").text(todesimal(selisihINACBGRJ));

            // ==========================================
            // SELISIH RS VS iDRG
            // ==========================================
            $("#selisihidrg").text(todesimal(selisihRSIDRG));
            $("#selisihidrg_inpatient").text(todesimal(selisihIDRGRI));
            $("#selisihidrg_outpatient").text(todesimal(selisihIDRGRJ));

            // Status selisih total
            setStatusSelisih("#selisihinacbg", selisihRSINACBG);
            setStatusSelisih("#selisihidrg", selisihRSIDRG);

            renderchartarea("trenvisit",chartkunjungan,"Discharge Date","InPatient",["InPatient", "OutPatient"],["value1", "value2"],true,"OutPatient","","",null);
            renderchartarea("trenrevenue",chartpendapatan,"Discharge Date","InPatient",["InPatient", "OutPatient"],["value1", "value2"],null,"","","",null);
            renderchartarea("trenrevenuecompare",chartpendapatancompare,"Discharge Date","Revenue",["Ina-Cbg","IDRG"],["value1","value2"],null,"","","",null);
            renderchartarea("severitylevelmountly",chartseveritylevel,"Discharge Date","Case",["Severity Level I", "Severity Level II", "Severity Level III"],["value1", "value2", "value3"],null,"","","",null);
            renderchartarea("costdistribution",chartseveritylevel,"Discharge Date","Case",["Severity Level I", "Severity Level II", "Severity Level III"],["value1", "value2", "value3"],null,"","","",null);
            renderchartarea("costdistribution",chartcostdistribution,"Discharge Date","Cost Distribution",costComponents.map(item => item.name),costComponents.map(item => item.field),null,"","","",null);
            renderchartpie("severitylevelyear", chartseveritylevelyear);
            renderchartpie("costcomponentyear", chartcostcomponentyear);
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