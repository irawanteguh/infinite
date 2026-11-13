(function () {
    'use strict';
    function initPeSurvey() {
    const visitForm = document.getElementById('prem-visit-form');
    const clinic = document.getElementById('prem-clinic');
    if (clinic && typeof jQuery !== 'undefined' && jQuery.fn.select2 && !jQuery(clinic).data('select2')) {
        jQuery(clinic).select2({width: '100%', minimumResultsForSearch: 0, placeholder: 'Pilih poli'});
    }
    if (visitForm && document.getElementById('prem-occupation-other')) {
        const other = document.getElementById('prem-occupation-other');
        const syncOccupation = () => {
            const selected = visitForm.querySelector('input[name="occupation"]:checked');
            const needsOther = selected && selected.value === 'LAINNYA';
            document.getElementById('prem-occupation-other-wrap').classList.toggle('d-none', !needsOther);
            other.required = !!needsOther;
            other.disabled = !needsOther;
        };
        visitForm.querySelectorAll('input[name="occupation"]').forEach((radio) => radio.addEventListener('change', syncOccupation));
        syncOccupation();
    }
    const form = document.getElementById('prem-form');
    if (!form || form.dataset.initialized === '1') return;
    form.dataset.initialized = '1';
    const submitLabel = document.getElementById('prem-submit').innerHTML;
    const lifecycle = new AbortController();
    const byId = (id) => document.getElementById(id);
    const sections = Array.from(form.querySelectorAll('[data-section]'));
    const fields = Array.from(form.querySelectorAll('.prem-question'));
    const steps = Array.from(document.querySelectorAll('.prem-step'));
    const labels = ['Sangat tidak setuju', 'Tidak setuju', 'Ragu-ragu', 'Setuju', 'Sangat setuju'];
    let step = 0;
    let sending = false;
    let submitted = false;
    let editingRespondent = false;
    const revision = form.elements.namedItem('revision').value;
    const draftScope = new URL(form.action, window.location.href).searchParams;
    const draftKey = ['pe-survey-draft', draftScope.get('group_id'), draftScope.get('org_id'),
        form.elements.namedItem('instrument_id').value, form.elements.namedItem('submission_token').value].join(':');

    function clearDraft() {
        try { window.sessionStorage.removeItem(draftKey); } catch (_) {}
    }

    function saveDraft() {
        if (submitted) { clearDraft(); return; }
        const answers = {};
        fields.forEach((field) => {
            const selected = field.querySelector('input:checked');
            if (selected) answers[field.dataset.questionId] = selected.value;
        });
        const suggestions = document.getElementById('prem-suggestions');
        try {
            window.sessionStorage.setItem(draftKey, JSON.stringify({
                revision: revision, answers: answers, suggestions: suggestions ? suggestions.value : '', step: step,
            }));
        } catch (_) {}
    }

    function restoreDraft() {
        try {
            const draft = JSON.parse(window.sessionStorage.getItem(draftKey) || 'null');
            if (!draft) return 0;
            if (draft.revision !== revision) { clearDraft(); return 0; }
            fields.forEach((field) => {
                const value = draft.answers && draft.answers[field.dataset.questionId];
                const selected = Array.from(field.querySelectorAll('input[type="radio"]')).find((input) => input.value === value);
                if (selected) selected.checked = true;
            });
            const suggestions = document.getElementById('prem-suggestions');
            if (suggestions && typeof draft.suggestions === 'string') suggestions.value = draft.suggestions.slice(0, 4000);
            let restoredStep = Number.isInteger(draft.step) ? Math.max(0, Math.min(draft.step, sections.length)) : 0;
            if (restoredStep === sections.length && !fields.every((field) => field.querySelector('input:checked'))) restoredStep = 0;
            return restoredStep;
        } catch (_) { clearDraft(); return 0; }
    }

    function editRespondent() {
        saveDraft();
        editingRespondent = true;
        window.location.assign(form.dataset.visitUrl);
    }
    const escape = (value) => String(value).replace(/[&<>"']/g, (char) => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[char]));
    const answered = (field) => field.querySelector('input:checked');
    function startReturnTimer() {
        const deadline = Date.now() + 10000;
        const countdown = byId('prem-return-seconds');
        const destination = byId('prem-success').dataset.returnUrl;
        const timer = window.setInterval(() => {
            const remaining = Math.max(0, Math.ceil((deadline - Date.now()) / 1000));
            countdown.textContent = remaining;
            if (remaining === 0) {
                window.clearInterval(timer);
                window.location.replace(destination);
            }
        }, 1000);
    }

    function progress() {
        const count = fields.filter(answered).length;
        byId('prem-progress-label').textContent = count + '/' + fields.length;
        byId('prem-progress').style.width = (count / fields.length * 100) + '%';
        byId('prem-progress').setAttribute('aria-valuenow', count);
        steps.forEach((button, index) => {
            const complete = Array.from(sections[index].querySelectorAll('.prem-question')).every(answered);
            button.querySelector('.prem-step-check').classList.toggle('d-none', !complete);
        });
    }

    function validate(index) {

        const questions = index === undefined ? fields : Array.from(sections[index].querySelectorAll('.prem-question'));
        let first = null;
        questions.forEach((field) => {
            const missing = !answered(field);
            field.classList.toggle('is-invalid', missing);
            field.querySelector('.prem-question-error').classList.toggle('d-none', !missing);
            if (missing && !first) first = field;
        });
        if (first) {
            const target = Number(first.closest('[data-section]').dataset.section);
            showStep(target, false);
            first.querySelector('input').focus();
            first.scrollIntoView({block: 'center', behavior: 'auto'});
            return false;
        }
        const suggestions = byId('prem-suggestions');
        if (suggestions && !suggestions.checkValidity()) {
            showStep(sections.length - 1, false);
            suggestions.reportValidity();
            return false;
        }
        return true;
    }

    function renderReview() {

        const respondent = JSON.parse(form.dataset.respondent || 'null');
        const visitEdit = '<a href="' + escape(form.dataset.visitUrl) + '" class="prem-edit-section">' + (respondent ? 'Ubah data responden' : 'Ubah data kunjungan') + '</a>';
        const profileRows = [
            ['Jenis pelayanan', form.dataset.serviceLabel],
            ['Nomor rekam medis', form.dataset.medicalRecord],
            ...Object.entries(respondent || {}),
        ];
        const profileFields = profileRows.map(([label, value]) =>
            '<div class="prem-review-profile-field"><dt>' + escape(label) + '</dt><dd>' +
            (label === 'Jenis pelayanan' ? '<span class="prem-review-service">' + escape(value) + '</span>' : escape(value)) +
            '</dd></div>').join('');
        const visitSummary = '<section class="prem-review-profile" aria-label="' + (respondent ? 'Data responden' : 'Data kunjungan pasien') +
            '"><div class="prem-review-profile-header"><h3>' + (respondent ? 'DATA RESPONDEN' : 'DATA KUNJUNGAN PASIEN') +
            '</h3>' + visitEdit + '</div><dl class="prem-review-profile-grid">' + profileFields + '</dl></section>';
        byId('prem-review-answers').innerHTML = visitSummary + sections.map((section, index) => '<div class="prem-review-domain"><div class="d-flex justify-content-between gap-3 align-items-start"><h3>' + escape(section.querySelector('h2').textContent) + '</h3><button type="button" class="prem-edit-section" data-edit-step="' + index + '">Ubah</button></div>' + Array.from(section.querySelectorAll('.prem-question')).map((field) => {
            const value = Number(answered(field).value);
            return '<div class="prem-review-item"><p>' + escape(field.querySelector('.prem-question-number').textContent) + '. ' + escape(field.querySelector('.prem-question-text').textContent) + '</p><span class="prem-review-value">' + value + ' — ' + escape(answered(field).closest('label').querySelector('.prem-choice-label').textContent) + '</span></div>';
        }).join('') + '</div>').join('');
        const suggestions = byId('prem-suggestions');
        if (suggestions) byId('prem-review-answers').innerHTML += '<div class="prem-review-domain"><h3>Saran dan Masukan</h3><p class="prem-suggestion-review">' + escape(suggestions.value.trim() || 'Tidak ada saran dan masukan.') + '</p></div>';
    }

    function showStep(index, focus = true) {
        step = index;
        const review = index === sections.length;
        sections.forEach((section, i) => section.classList.toggle('d-none', i !== index));
        byId('prem-review').classList.toggle('d-none', !review);
        byId('prem-next').classList.toggle('d-none', review);
        byId('prem-submit').classList.toggle('d-none', !review);
        byId('prem-back').classList.toggle('d-none', index === 0 && form.dataset.fromSkm === '1');
        byId('prem-back').textContent = index === 0 ? 'Data kunjungan' : 'Kembali';
        byId('prem-next').innerHTML = (index === sections.length - 1 ? 'Tinjau jawaban' : 'Selanjutnya') + ' <i class="bi bi-arrow-right ms-2"></i>';
        byId('prem-step-label').textContent = review ? 'Tinjauan jawaban' : 'Bagian ' + (index + 1) + ' dari ' + sections.length;
        steps.forEach((button, i) => {
            button.classList.toggle('is-current', i === index);
            if (i === index) button.setAttribute('aria-current', 'step');
            else button.removeAttribute('aria-current');
        });
        if (review) renderReview();
        saveDraft();
        if (focus) {
            const title = review ? byId('prem-review-title') : sections[index].querySelector('h2');
            title.focus({preventScroll: true});
            form.scrollIntoView({block: 'start', behavior: 'auto'});
        }
    }

    byId('prem-start').addEventListener('click', () => {
        byId('prem-welcome').classList.add('d-none');
        byId('prem-survey').classList.remove('d-none');
        showStep(0);
    });
    form.addEventListener('change', (event) => {
        if (!event.target.matches('input[type="radio"]')) return;
        const field = event.target.closest('.prem-question');
        field.classList.remove('is-invalid');
        field.querySelector('.prem-question-error').classList.add('d-none');
        progress();
    });
    form.addEventListener('input', saveDraft);
    byId('prem-next').addEventListener('click', () => {
        if (sending || !validate(step)) return;
        if (step === sections.length - 1 && !validate()) return;
        showStep(step + 1);
    });
    byId('prem-back').addEventListener('click', () => {
        if (sending) return;
        if (step > 0) showStep(step - 1);
        else {
            editRespondent();
        }
    });
    steps.forEach((button, index) => button.addEventListener('click', () => {
        if (sending) return;
        if (index <= step || validate(step)) showStep(index);
    }));
    byId('prem-review-answers').addEventListener('click', (event) => {
        const edit = event.target.closest('a.prem-edit-section');
        if (edit && edit.href === new URL(form.dataset.visitUrl, window.location.href).href) {
            event.preventDefault();
            if (!sending) editRespondent();
            return;
        }
        const button = event.target.closest('[data-edit-step]');
        if (button && !sending) showStep(Number(button.dataset.editStep));
    });
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (sending || submitted || !validate()) return;
        if (step !== sections.length) { showStep(sections.length); return; }
        const errorBox = byId('prem-form-error');
        const button = byId('prem-submit');
        errorBox.classList.add('d-none');
        const payload = new FormData(form);
        sending = true;
        document.querySelectorAll('#prem-survey button, #prem-form input:not([type="hidden"]), #prem-form select').forEach((control) => { control.disabled = true; });
        button.textContent = 'Mengirim jawaban…';
        try {
            const response = await fetch(form.action, {method: 'POST', body: payload, credentials: 'same-origin', headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json'}});
            if (response.redirected || !(response.headers.get('content-type') || '').includes('application/json')) throw new Error('Jawaban belum dapat dikirim. Silakan coba kembali.');
            const result = await response.json();
            if (result.csrfHash) {
                const csrf = form.querySelector('input[data-prem-csrf]');
                if (csrf) csrf.value = result.csrfHash;
            }
            if (!response.ok || !result.success) throw new Error(result.message || 'Jawaban belum berhasil dikirim.');
            submitted = true;
            clearDraft();
            if (result.nextHtml) {
                const nextPage = new DOMParser().parseFromString(result.nextHtml, 'text/html');
                const nextRoot = nextPage.getElementById('prem-app');
                if (!nextRoot) throw new Error('Tahap PREM belum dapat ditampilkan. Muat ulang halaman ini untuk melanjutkan.');
                const currentRoot = document.getElementById('prem-app');
                lifecycle.abort();
                currentRoot.replaceWith(document.importNode(nextRoot, true));
                window.initPeSurvey();
                const title = document.querySelector('#prem-app h1');
                if (title) { title.tabIndex = -1; title.focus(); title.scrollIntoView({block: 'start'}); }
                return;
            }
            if (result.continuationMessage) byId('prem-success').querySelector('.prem-lead').textContent = result.continuationMessage;
            byId('prem-survey').classList.add('d-none');
            byId('prem-success').classList.remove('d-none');
            byId('prem-receipt').textContent = result.responseId.slice(-8).toUpperCase();
            byId('prem-success').focus();
            byId('prem-success').scrollIntoView({block: 'start', behavior: 'auto'});
            startReturnTimer();
        } catch (error) {
            errorBox.textContent = error.message || 'Koneksi terputus. Silakan coba kirim kembali.';
            errorBox.classList.remove('d-none');
            errorBox.focus();
            document.querySelectorAll('#prem-survey button, #prem-form input:not([type="hidden"]), #prem-form select').forEach((control) => { control.disabled = false; });
        } finally {
            sending = false;
            button.innerHTML = submitLabel;
        }
    });
    window.addEventListener('beforeunload', (event) => {
        saveDraft();
        if (!submitted && !editingRespondent && (fields.some(answered) || (byId('prem-suggestions') && byId('prem-suggestions').value.trim()))) { event.preventDefault(); event.returnValue = ''; }
    }, {signal: lifecycle.signal});
    // Mark the server-rendered CSRF field so regenerated tokens can be used on retries.
    form.querySelector('input[type="hidden"]').setAttribute('data-prem-csrf', '');
    window.addEventListener('pagehide', saveDraft, {signal: lifecycle.signal});
    const restoredStep = restoreDraft();
    showStep(restoredStep, false);
    progress();
    }
    window.initPeSurvey = initPeSurvey;
    initPeSurvey();
})();
