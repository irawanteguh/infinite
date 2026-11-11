/* Matrix copied from the institution's PETA RISIKO 2026 workbook. */
document.addEventListener('DOMContentLoaded', function () {
    'use strict';
    if (typeof initDataTable === 'function') {
        if (document.getElementById('risk-register-table')) {
            initDataTable('#risk-register-table', '#risk-register-search', 10);
        }
    }
    var matrix = [[1, 3, 5, 8, 20], [2, 7, 11, 13, 21], [4, 10, 14, 17, 22], [6, 12, 16, 19, 24], [9, 15, 18, 23, 25]];
    document.querySelectorAll('[data-risk-assessment]').forEach(function (form) {
        var preview = form.querySelector('[data-score-preview]');
        if (!form.elements.likelihood || !form.elements.impact_level) { return; }
        function update() {
            var likelihood = Number(form.elements.likelihood.value);
            var impact = Number(form.elements.impact_level.value);
            var score = matrix[likelihood - 1][impact - 1];
            var color = score <= 5 ? 'Biru' : score <= 11 ? 'Hijau' : score <= 15 ? 'Kuning' : score <= 19 ? 'Oranye' : 'Merah';
            var text = 'Skor: ' + score + ' · ' + color + ' · ' + (score >= 12 ? 'Perlu penanganan' : 'Di bawah ambang penanganan');
            if (preview.dataset.baseline) {
                var baseline = Number(preview.dataset.baseline);
                text += '. Skor awal: ' + baseline + '. Tren: ' + (score < baseline ? 'Menurun' : score > baseline ? 'Meningkat' : 'Tetap');
            }
            preview.textContent = text;
            preview.className = 'alert mb-0 ' + (score >= 20 ? 'alert-danger' : score >= 12 ? 'alert-warning' : 'alert-success');
            form.querySelectorAll('[data-action-required]').forEach(function (field) { field.required = score >= 12; });
        }
        form.elements.likelihood.addEventListener('change', update);
        form.elements.impact_level.addEventListener('change', update);
        form.addEventListener('submit', function () {
            var button = form.querySelector('button[type="submit"], button:not([type])');
            if (button) { button.disabled = true; button.textContent = 'Menyimpan…'; }
        });
        update();
    });
    var print = document.getElementById('risk-print');
    if (print) { print.addEventListener('click', function () { window.print(); }); }
    document.querySelectorAll('.modal[data-open="1"]').forEach(function (modal) {
        if (window.bootstrap) { bootstrap.Modal.getOrCreateInstance(modal).show(); }
    });
    document.querySelectorAll('#risk-profile-modal').forEach(function (modal) {
        var form = modal.querySelector('form[data-risk-assessment]');
        var unit = form && form.elements.department_id;
        var code = form && form.elements.code;
        function updateDepartmentMetadata() {
            if (!unit || !code) { return; }
            var option = unit.options[unit.selectedIndex];
            var riskCode = option && option.dataset.riskCode ? option.dataset.riskCode : '';
            code.value = riskCode;
        }
        if (unit) { unit.addEventListener('change', updateDepartmentMetadata); }
        modal.addEventListener('shown.bs.modal', updateDepartmentMetadata);
        var steps = form ? form.querySelectorAll('[data-risk-step]') : [];
        var stepperElement = document.getElementById('risk-profile-stepper');
        var stepper = stepperElement && typeof KTStepper === 'function' ? new KTStepper(stepperElement) : null;
        var currentStep = 1;
        var prev = modal.querySelector('[data-risk-prev]');
        var next = modal.querySelector('[data-risk-next]');
        var submit = modal.querySelector('[data-risk-submit]');
        function renderStep() {
            Array.prototype.forEach.call(steps, function (step) {
                var active = Number(step.dataset.riskStep) === currentStep;
                step.classList.toggle('is-active', active);
                step.classList.toggle('current', active);
            });
            modal.querySelectorAll('[data-risk-step-indicator]').forEach(function (indicator) {
                var number = Number(indicator.dataset.riskStepIndicator);
                indicator.classList.toggle('is-active', number === currentStep);
                indicator.classList.toggle('is-done', number < currentStep);
                indicator.classList.toggle('current', number === currentStep);
                indicator.classList.toggle('completed', number < currentStep);
            });
            if (prev) { prev.disabled = currentStep === 1; }
            if (next) { next.classList.toggle('d-none', currentStep === steps.length); }
            if (submit) { submit.classList.toggle('d-none', currentStep !== steps.length); }
        }
        function validateStep() {
            var active = modal.querySelector('[data-risk-step="' + currentStep + '"]');
            if (!active) { return true; }
            var valid = true;
            var firstInvalid = null;
            active.querySelectorAll('input:not([type="hidden"]), select, textarea').forEach(function (field) {
                field.classList.remove('is-invalid');
                if (!field.checkValidity()) {
                    field.classList.add('is-invalid');
                    if (!firstInvalid) { firstInvalid = field; }
                    valid = false;
                }
            });
            if (!valid && firstInvalid) {
                if (firstInvalid.classList.contains('select2-hidden-accessible') && window.jQuery && window.jQuery.fn.select2) {
                    window.jQuery(firstInvalid).select2('open');
                } else {
                    firstInvalid.focus();
                    firstInvalid.reportValidity();
                }
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'warning', title: 'Lengkapi tahap ini', text: 'Isi semua kolom wajib sebelum melanjutkan.' });
                }
            }
            return valid;
        }
        if (stepper) {
            stepper.on('kt.stepper.changed', function (instance) {
                currentStep = instance.getCurrentStepIndex();
                renderStep();
            });
            stepper.on('kt.stepper.next', function (instance) {
                updateDepartmentMetadata();
                if (validateStep()) { instance.goNext(); }
            });
            stepper.on('kt.stepper.previous', function (instance) {
                instance.goPrevious();
            });
        } else {
            if (next) { next.addEventListener('click', function () { updateDepartmentMetadata(); if (validateStep() && currentStep < steps.length) { currentStep++; renderStep(); } }); }
            if (prev) { prev.addEventListener('click', function () { if (currentStep > 1) { currentStep--; renderStep(); } }); }
        }
        renderStep();
        modal.addEventListener('show.bs.modal', function (event) {
            var trigger = event.relatedTarget;
            if (!trigger || trigger.dataset.riskMode !== 'add' || !form) { return; }
            form.reset();
            currentStep = 1;
            if (stepper && stepper.getCurrentStepIndex() !== 1) { stepper.goFirst(); }
            renderStep();
            if (form.elements.risk_id) { form.elements.risk_id.value = ''; }
            if (form.elements.year) { form.elements.year.value = (document.getElementById('filter-year') || {}).value || new Date().getFullYear(); }
            if (form.elements.department_id) { form.elements.department_id.value = (document.getElementById('filter-unit') || {}).value || ''; }
            updateDepartmentMetadata();
            var title = document.getElementById('risk-profile-title');
            if (title) { title.textContent = 'Tambah profil risiko'; }
            if (form.elements.likelihood) { form.elements.likelihood.dispatchEvent(new Event('change')); }
        });
    });
});
