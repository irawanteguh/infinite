document.addEventListener('DOMContentLoaded', () => {
    initDataTable('#os-log-table', '#os-log-search', 10, {
        order: [],
    });

    document.querySelectorAll('.os-page .modal form, .os-page [data-setup-table]').forEach(form => {
        const setup = form.hasAttribute('data-setup-table');
        const title = setup ? 'Menyiapkan tabel aktivitas' : 'Menghapus log aktivitas';
        const prepared = setup ? 'Permintaan penyiapan tabel diterima' : 'Organisasi dan pilihan penghapusan dipilih';
        const processing = setup ? 'Membuat atau melengkapi tabel aktivitas' : 'Memproses penghapusan log';
        let submitting = false;
        form.addEventListener('submit', event => {
            event.preventDefault();
            if (submitting) return;
            submitting = true;
            form.querySelector('[type="submit"]').disabled = true;
            Swal.fire({
                title,
                html: '<div class="report-progress" role="status" aria-live="polite">' +
                    '<div class="report-progress-step is-complete">' +
                    '<span class="report-progress-icon"><i class="bi bi-check-lg" aria-hidden="true"></i></span>' +
                    '<span>' + prepared + '</span></div>' +
                    '<div class="report-progress-step is-current" aria-current="step">' +
                    '<span class="report-progress-icon"><span class="spinner-border spinner-border-sm" aria-hidden="true"></span></span>' +
                    '<span>' + processing + '</span></div>' +
                    '<div class="progress report-progress-meter os-delete-progress" role="status" aria-label="' + title + '">' +
                    '<div class="progress-bar progress-bar-striped progress-bar-animated"></div></div></div>',
                customClass: { popup: 'report-progress-popup' },
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    requestAnimationFrame(() => {
                        requestAnimationFrame(() => HTMLFormElement.prototype.submit.call(form));
                    });
                },
            });
        });
    });
});
