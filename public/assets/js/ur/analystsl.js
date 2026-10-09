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