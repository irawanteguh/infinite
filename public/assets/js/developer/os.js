document.addEventListener('DOMContentLoaded', () => {
    initDataTable('#os-log-table', '#os-log-search', 10, {
        order: [],
    });
    const previewModal = document.getElementById('os-module-preview');
    if (previewModal) {
        let previewTable;
        previewModal.addEventListener('shown.bs.modal', () => {
            if (!previewTable) previewTable = initDataTable('#os-module-table', '#os-module-search', 10, {order: []});
            previewTable?.columns.adjust();
        });
        bootstrap.Modal.getOrCreateInstance(previewModal).show();
    }
    const importForm = document.getElementById('os-modules-import');
    let importing = false;
    importForm?.addEventListener('submit', event => {
        event.preventDefault();
        if (importing) return;
        const file = document.getElementById('os-module-file').files[0];
        if (file && file.size > 2 * 1024 * 1024) {
            Swal.fire({icon: 'error', title: 'File terlalu besar', text: 'Ukuran file maksimal 2 MB.'});
            return;
        }
        importing = true;
        importForm.querySelector('[type="submit"]').disabled = true;
        Swal.fire({
            title: 'Import & Preview Master Modules',
            html: '<div class="report-progress" role="status" aria-live="polite">' +
                '<p class="app-loading-description">Mengunggah katalog, memvalidasi modul, dan menyiapkan preview perubahan.</p>' +
                '<div class="progress report-progress-meter app-loading-meter" aria-label="Memproses import dan preview">' +
                '<div class="progress-bar progress-bar-striped progress-bar-animated"></div></div>' +
                '<p class="app-loading-caption">Sedang memproses file…</p></div>',
            customClass: {popup: 'report-progress-popup app-loading-popup'},
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => HTMLFormElement.prototype.submit.call(importForm));
                });
            },
        });
    });
    const applyForm = document.getElementById('os-modules-apply');
    let applying = false;
    applyForm?.addEventListener('submit', async event => {
        event.preventDefault();
        if (applying) return;
        applying = true;
        const answer = await Swal.fire({
            icon: 'question', title: 'Terapkan sinkronisasi?',
            text: 'Perubahan pada preview akan diterapkan ke katalog menu seluruh organisasi.',
            showCancelButton: true, confirmButtonText: 'Terapkan Sinkronisasi', cancelButtonText: 'Cancel',
            buttonsStyling: false,
            customClass: {confirmButton: 'btn btn-primary', cancelButton: 'btn btn-light ms-3'},
            focusCancel: true,
        });
        if (!answer.isConfirmed) { applying = false; return; }
        applyForm.querySelector('[type="submit"]').disabled = true;
        HTMLFormElement.prototype.submit.call(applyForm);
    });

    document.querySelectorAll('.os-page .modal form:not(#os-modules-apply), .os-page [data-setup-table]').forEach(form => {
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
