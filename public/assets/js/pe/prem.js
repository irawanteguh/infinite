(function () {
    'use strict';
    const form = document.getElementById('prem-form');
    if (!form) return;
    const byId = (id) => document.getElementById(id);
    const sections = Array.from(form.querySelectorAll('[data-section]'));
    const fields = Array.from(form.querySelectorAll('.prem-question'));
    const steps = Array.from(document.querySelectorAll('.prem-step'));
    const labels = ['Sangat tidak setuju', 'Tidak setuju', 'Ragu-ragu', 'Setuju', 'Sangat setuju'];
    let step = 0;
    let sending = false;
    let submitted = false;
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
        return true;
    }

    function renderReview() {
        byId('prem-review-answers').innerHTML = sections.map((section, index) => '<div class="prem-review-domain"><div class="d-flex justify-content-between gap-3 align-items-start"><h3>' + escape(section.querySelector('h2').textContent) + '</h3><button type="button" class="prem-edit-section" data-edit-step="' + index + '">Ubah</button></div>' + Array.from(section.querySelectorAll('.prem-question')).map((field) => {
            const value = Number(answered(field).value);
            return '<div class="prem-review-item"><p>' + escape(field.querySelector('.prem-question-number').textContent) + '. ' + escape(field.querySelector('.prem-question-text').textContent) + '</p><span class="prem-review-value">' + value + ' — ' + labels[value - 1] + '</span></div>';
        }).join('') + '</div>').join('');
    }

    function showStep(index, focus = true) {
        step = index;
        const review = index === sections.length;
        sections.forEach((section, i) => section.classList.toggle('d-none', i !== index));
        byId('prem-review').classList.toggle('d-none', !review);
        byId('prem-next').classList.toggle('d-none', review);
        byId('prem-submit').classList.toggle('d-none', !review);
        byId('prem-back').textContent = index === 0 ? 'Pengantar' : 'Kembali';
        byId('prem-next').innerHTML = (index === sections.length - 1 ? 'Tinjau jawaban' : 'Selanjutnya') + ' <i class="bi bi-arrow-right ms-2"></i>';
        byId('prem-step-label').textContent = review ? 'Tinjauan jawaban' : 'Bagian ' + (index + 1) + ' dari ' + sections.length;
        steps.forEach((button, i) => {
            button.classList.toggle('is-current', i === index);
            if (i === index) button.setAttribute('aria-current', 'step');
            else button.removeAttribute('aria-current');
        });
        if (review) renderReview();
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
    byId('prem-next').addEventListener('click', () => {
        if (sending || !validate(step)) return;
        if (step === sections.length - 1 && !validate()) return;
        showStep(step + 1);
    });
    byId('prem-back').addEventListener('click', () => {
        if (sending) return;
        if (step > 0) showStep(step - 1);
        else {
            byId('prem-survey').classList.add('d-none');
            byId('prem-welcome').classList.remove('d-none');
            byId('prem-start').focus();
        }
    });
    steps.forEach((button, index) => button.addEventListener('click', () => {
        if (sending) return;
        if (index <= step || validate(step)) showStep(index);
    }));
    byId('prem-review-answers').addEventListener('click', (event) => {
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
        document.querySelectorAll('#prem-survey button, #prem-form input[type="radio"]').forEach((control) => { control.disabled = true; });
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
            document.querySelectorAll('#prem-survey button, #prem-form input[type="radio"]').forEach((control) => { control.disabled = false; });
        } finally {
            sending = false;
            button.innerHTML = 'Kirim jawaban <i class="bi bi-send ms-2"></i>';
        }
    });
    window.addEventListener('beforeunload', (event) => {
        if (!submitted && fields.some(answered)) { event.preventDefault(); event.returnValue = ''; }
    });
    // Mark the server-rendered CSRF field so regenerated tokens can be used on retries.
    form.querySelector('input[type="hidden"]').setAttribute('data-prem-csrf', '');
    showStep(0, false);
    progress();
})();
