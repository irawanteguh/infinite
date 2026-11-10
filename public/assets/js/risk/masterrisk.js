document.addEventListener('DOMContentLoaded', function () {
    'use strict';
    var masterTables = [];
    if (typeof initDataTable === 'function') {
        if (document.getElementById('datamasterrisk_table')) {
            var codeTable = initDataTable('#datamasterrisk_table', '#mr-search', 10);
            masterTables.push(codeTable);
        }
        if (document.getElementById('datamasterriskcategory_table')) {
            masterTables.push(initDataTable('#datamasterriskcategory_table', '#mr-category-search', 10));
        }
        if (document.getElementById('datamasterriskmatrix_table')) {
            masterTables.push(initDataTable('#datamasterriskmatrix_table', null, 5, {
                paging: false, searching: false, info: false, lengthChange: false, ordering: false, dom: 't'
            }));
        }
    }
    function showSection(section) {
        if (!['codes', 'categories', 'matrix'].includes(section) || !window.bootstrap) { return; }
        var button = document.getElementById('master-' + section + '-tab');
        if (button) { bootstrap.Tab.getOrCreateInstance(button).show(); }
    }
    function sectionFromHash() {
        showSection(window.location.hash.replace('#master-', ''));
    }
    document.querySelectorAll('[data-master-section]').forEach(function (link) {
        link.addEventListener('click', function (event) {
            event.preventDefault();
            showSection(link.dataset.masterSection);
        });
    });
    document.querySelectorAll('[id^="master-"][data-bs-toggle="tab"]').forEach(function (button) {
        button.addEventListener('shown.bs.tab', function () {
            masterTables.forEach(function (table) { table.columns.adjust(); });
            var section = button.id.replace('master-', '').replace('-tab', '');
            window.history.replaceState(null, '', window.location.pathname + window.location.search + '#master-' + section);
        });
    });
    window.addEventListener('hashchange', sectionFromHash);
    sectionFromHash();
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
            showSection(modal.id === 'modal_add_riskcategory' ? 'categories' : 'codes');
        });
    });
    document.querySelectorAll('.modal[data-reopen="1"]').forEach(function (modal) {
        if (window.bootstrap) {
            showSection(modal.id === 'modal_add_riskcategory' ? 'categories' : 'codes');
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
