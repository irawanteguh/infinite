jmlkasus();

function jmlkasus(){
    let selectperiode = $("select[name='selectperiode']").val();
    $.ajax({
        url      : url + "ur/analystsl/jmlkasus",
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

            const result = Array.isArray(response.responseResult)
                ? response.responseResult
                : [];

            const namaBulan = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];

            const dataChart = namaBulan.map((nama, index) => {
                const bulan = index + 1;
                const item = result.find(row => Number(row.bulan) === bulan) || {};

                return {
                    periode: nama,

                    kasus_i_real: Number(item.kasus_i_real || 0),
                    kasus_i_simulasi: Number(item.kasus_i_simulasi || 0),
                    kasus_ii_real: Number(item.kasus_ii_real || 0),
                    kasus_ii_simulasi: Number(item.kasus_ii_simulasi || 0),
                    kasus_iii_real: Number(item.kasus_iii_real || 0),
                    kasus_iii_simulasi: Number(item.kasus_iii_simulasi || 0),

                    pendapatan_i_real: Number(item.pendapatan_i_real || 0),
                    pendapatan_i_simulasi: Number(item.pendapatan_i_simulasi || 0),
                    pendapatan_ii_real: Number(item.pendapatan_ii_real || 0),
                    pendapatan_ii_simulasi: Number(item.pendapatan_ii_simulasi || 0),
                    pendapatan_iii_real: Number(item.pendapatan_iii_real || 0),
                    pendapatan_iii_simulasi: Number(item.pendapatan_iii_simulasi || 0)
                };
            });

            // ========================================
            // INSERT VALUE CARD - DATA AKTUAL
            // ========================================

            // SL I
            $("#jmlkasussl1").text(
                todesimal(result.reduce((sum, row) =>
                    sum + Number(row.kasus_i_real || 0), 0))
            );

            $("#pendapatansl1").text(
                todesimal(result.reduce((sum, row) =>
                    sum + Number(row.pendapatan_i_real || 0), 0))
            );

            // SL II
            $("#jmlkasussl2").text(
                todesimal(result.reduce((sum, row) =>
                    sum + Number(row.kasus_ii_real || 0), 0))
            );

            $("#pendapatansl2").text(
                todesimal(result.reduce((sum, row) =>
                    sum + Number(row.pendapatan_ii_real || 0), 0))
            );

            // SL III
            $("#jmlkasussl3").text(
                todesimal(result.reduce((sum, row) =>
                    sum + Number(row.kasus_iii_real || 0), 0))
            );

            $("#pendapatansl3").text(
                todesimal(result.reduce((sum, row) =>
                    sum + Number(row.pendapatan_iii_real || 0), 0))
            );


            // ========================================
            // INSERT VALUE CARD - SIMULASI
            // ========================================

            // SL I
            $("#simawaljmlkasussl1").text(
                todesimal(result.reduce((sum, row) =>
                    sum + Number(row.kasus_i_real || 0), 0))
            );

            $("#simpindahkasussl1").text(
                todesimal(result.reduce((sum, row) =>
                    sum + Number(row.pindah_i_ii || 0), 0)) +
                " | AVG Monthly " +
                todesimal(result.reduce((sum, row) =>
                    sum + Number(row.pindah_i_ii || 0), 0) / 9)
            );

            $("#simjmlkasussl1").text(
                todesimal(result.reduce((sum, row) =>
                    sum + Number(row.kasus_i_simulasi || 0), 0))
            );

            $("#simpendapatansl1").text(
                todesimal(result.reduce((sum, row) =>
                    sum + Number(row.pendapatan_i_simulasi || 0), 0))
            );


            // SL II
            $("#simawaljmlkasussl2").text(
                todesimal(result.reduce((sum, row) =>
                    sum + Number(row.kasus_ii_real || 0), 0))
            );

            $("#simmasukkasussl2").text(
                todesimal(result.reduce((sum, row) =>
                    sum + Number(row.pindah_i_ii || 0), 0))
            );

            $("#simpindahkasussl2").text(
                todesimal(result.reduce((sum, row) =>
                    sum + Number(row.pindah_ii_iii || 0), 0)) +
                " | AVG Monthly " +
                todesimal(result.reduce((sum, row) =>
                    sum + Number(row.pindah_ii_iii || 0), 0) / 9)
            );

            $("#simjmlkasussl2").text(
                todesimal(result.reduce((sum, row) =>
                    sum + Number(row.kasus_ii_simulasi || 0), 0))
            );

            $("#simpendapatansl2").text(
                todesimal(result.reduce((sum, row) =>
                    sum + Number(row.pendapatan_ii_simulasi || 0), 0))
            );


            // SL III
            $("#simawaljmlkasussl3").text(
                todesimal(result.reduce((sum, row) =>
                    sum + Number(row.kasus_iii_real || 0), 0))
            );

            $("#simmasukkasussl3").text(
                todesimal(result.reduce((sum, row) =>
                    sum + Number(row.pindah_ii_iii || 0), 0))
            );

            $("#simjmlkasussl3").text(
                todesimal(result.reduce((sum, row) =>
                    sum + Number(row.kasus_iii_simulasi || 0), 0))
            );

            $("#simpendapatansl3").text(
                todesimal(result.reduce((sum, row) =>
                    sum + Number(row.pendapatan_iii_simulasi || 0), 0))
            );

            const sumField = (field) => {
                return result.reduce((sum, row) => {
                    return sum + Number(row[field] || 0);
                }, 0);
            };

            const formatPercent = (value) => {
                return value.toLocaleString('id-ID', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }) + '%';
            };

            // ==========================================
            // 1. SUMMARY PERUBAHAN JUMLAH KASUS
            // ==========================================
            const kasusAktual =
                sumField('kasus_i_real') +
                sumField('kasus_ii_real') +
                sumField('kasus_iii_real');

            const kasusSimulasi =
                sumField('kasus_i_simulasi') +
                sumField('kasus_ii_simulasi') +
                sumField('kasus_iii_simulasi');

            const selisihKasus = kasusSimulasi - kasusAktual;

            const persenKasus = kasusAktual > 0
                ? (selisihKasus / kasusAktual) * 100
                : 0;

            $('#summarykasusaktual')
                .text(todesimal(kasusAktual));

            $('#summarykasussimulasi')
                .text(todesimal(kasusSimulasi));

            $('#summaryperubahankasus')
                .text(
                    `${selisihKasus > 0 ? '+' : ''}${todesimal(selisihKasus)} (${persenKasus > 0 ? '+' : ''}${formatPercent(persenKasus)})`
                )
                .removeClass('text-success text-danger text-gray-700')
                .addClass(
                    selisihKasus > 0 ? 'text-success' :
                    selisihKasus < 0 ? 'text-danger' :
                    'text-gray-700'
                );

            // ==========================================
            // 2. SUMMARY PERUBAHAN PENDAPATAN
            // ==========================================
            const pendapatanAktual =
                sumField('pendapatan_i_real') +
                sumField('pendapatan_ii_real') +
                sumField('pendapatan_iii_real');

            const pendapatanSimulasi =
                sumField('pendapatan_i_simulasi') +
                sumField('pendapatan_ii_simulasi') +
                sumField('pendapatan_iii_simulasi');

            const selisihPendapatan =
                pendapatanSimulasi - pendapatanAktual;

            const persenPendapatan = pendapatanAktual > 0
                ? (selisihPendapatan / pendapatanAktual) * 100
                : 0;

            $('#summarypendapatanaktual')
                .text(todesimal(pendapatanAktual));

            $("#summarypendapatansimulasi").text(
                todesimal(pendapatanSimulasi) +
                " | AVG Monthly " +
                todesimal(pendapatanSimulasi / 9)
            );

            $('#summaryselisihpendapatan')
            .text(
                `${selisihPendapatan > 0 ? '+' : selisihPendapatan < 0 ? '-' : ''}Rp ${todesimal(Math.abs(selisihPendapatan))} (${persenPendapatan > 0 ? '+' : ''}${formatPercent(persenPendapatan)})` +
                ' | AVG Monthly ' +
                `${selisihPendapatan > 0 ? '+' : selisihPendapatan < 0 ? '-' : ''}Rp ${todesimal(Math.abs(selisihPendapatan / 9))}`
            )
            .removeClass('text-success text-danger text-gray-700')
            .addClass(
                selisihPendapatan > 0 ? 'text-success' :
                selisihPendapatan < 0 ? 'text-danger' :
                'text-gray-700'
            );

            // ==========================================
            // 3. SUMMARY RATA-RATA PENDAPATAN PER KASUS
            // ==========================================
            const rataAktual = kasusAktual > 0
                ? pendapatanAktual / kasusAktual
                : 0;

            const rataSimulasi = kasusSimulasi > 0
                ? pendapatanSimulasi / kasusSimulasi
                : 0;

            const selisihRata = rataSimulasi - rataAktual;

            const persenRata = rataAktual > 0
                ? (selisihRata / rataAktual) * 100
                : 0;

            $('#summaryrataaktual')
                .text(todesimal(rataAktual));

            $('#summaryratasimulasi')
                .text(todesimal(rataSimulasi));

            $('#summarypersenrata')
                .text(
                    `${persenRata > 0 ? '+' : ''}${formatPercent(persenRata)}`
                )
                .removeClass('text-info text-success text-danger text-gray-700')
                .addClass(
                    persenRata > 0 ? 'text-success' :
                    persenRata < 0 ? 'text-danger' :
                    'text-gray-700'
                );

            // Chart jumlah kunjungan
            renderchartarea(
                "severitylevelmountly",
                dataChart,
                "Bulan",
                "Jumlah Kunjungan",
                [
                    "SL I Real", "SL I Simulasi",
                    "SL II Real", "SL II Simulasi",
                    "SL III Real", "SL III Simulasi"
                ],
                [
                    "kasus_i_real", "kasus_i_simulasi",
                    "kasus_ii_real", "kasus_ii_simulasi",
                    "kasus_iii_real", "kasus_iii_simulasi"
                ]
            );

            // Chart pendapatan
            renderchartarea(
                "severitylevelmountlypendapatan",
                dataChart,
                "Bulan",
                "Pendapatan",
                [
                    "SL I Real", "SL I Simulasi",
                    "SL II Real", "SL II Simulasi",
                    "SL III Real", "SL III Simulasi"
                ],
                [
                    "pendapatan_i_real", "pendapatan_i_simulasi",
                    "pendapatan_ii_real", "pendapatan_ii_simulasi",
                    "pendapatan_iii_real", "pendapatan_iii_simulasi"
                ]
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