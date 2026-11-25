window.showDataLoading = function () {
    const owner = {};
    window.showDataLoading.owner = owner;
    Swal.fire({
        title: 'Menyiapkan data',
        html: '<div class="report-progress" role="status" aria-live="polite">' +
            '<p class="app-loading-description">Memuat data pada periode terpilih.</p>' +
            '<div class="progress report-progress-meter app-loading-meter" aria-label="Data sedang dimuat"><div class="progress-bar"></div></div>' +
            '<p class="app-loading-caption">Sedang memproses data…</p></div>',
        customClass: {popup: 'report-progress-popup app-loading-popup'},
        allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false
    });
    $(document).off('ajaxSend.dataLoading').one('ajaxSend.dataLoading', function (event, xhr) {
        xhr.always(function () {
            if (window.showDataLoading.owner === owner) Swal.close();
        });
    });
};
