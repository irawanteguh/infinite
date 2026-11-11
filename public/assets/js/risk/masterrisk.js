document.addEventListener('DOMContentLoaded', function () {
    'use strict';
    if (typeof initDataTable === 'function') {
        if (document.getElementById('datamasterrisk_table')) {
            initDataTable('#datamasterrisk_table', '#mr-search', 10);
        }
        if (document.getElementById('datamasterriskcategory_table')) {
            initDataTable('#datamasterriskcategory_table', '#mr-category-search', 10);
        }
        if (document.getElementById('datamasterriskmatrix_table')) {
            initDataTable('#datamasterriskmatrix_table', null, 5, {
                paging: false, searching: false, info: false, lengthChange: false, ordering: false, dom: 't'
            });
        }
    }
    document.querySelectorAll('#modal_add_masterrisk, #modal_add_riskcategory').forEach(function (modal) {
        modal.addEventListener('show.bs.modal', function (event) {
            if (modal.id === 'modal_add_masterrisk') {
                var trigger = event.relatedTarget;
                var codeForm = document.getElementById('mr-form');
                if (trigger) {
                    codeForm.elements.code_id.value = trigger.dataset.codeId || '';
                    codeForm.elements.code.value = trigger.dataset.code || '';
                    codeForm.elements.department_id.value = trigger.dataset.departmentId || '';
                }
                var editing = codeForm.elements.code_id.value !== '';
                document.getElementById('mr-modal-title').textContent = editing ? 'Edit Kode Risiko' : 'Tambah Kode Risiko';
                codeForm.querySelector('[type="submit"]').textContent = editing ? 'Simpan Perubahan' : 'Simpan Kode Risiko';
            }
        });
    });
    document.querySelectorAll('.modal[data-reopen="1"]').forEach(function (modal) {
        if (window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modal).show();
        }
    });
    document.querySelectorAll('#mr-form, #risk-category-form').forEach(function (form) {
        form.addEventListener('submit', function () {
            var button = form.querySelector('[type="submit"]');
            button.disabled = true;
            button.textContent = 'Menyimpan…';
        });
    });
});
