document.addEventListener('DOMContentLoaded', () => {
    const success = document.getElementById('ca-success');
    if (success) {
        success.focus();
        const countdown = document.getElementById('ca-result-countdown');
        if (!countdown) return;
        const seconds = countdown.querySelector('[role="timer"]');
        const home = document.getElementById('ca-result-home');
        const deadline = Date.now() + 60_000;

        function updateCountdown() {
            const remaining = deadline - Date.now();
            if (remaining <= 0) {
                window.location.replace(home.href);
                return;
            }
            seconds.textContent = String(Math.ceil(remaining / 1000));
            window.setTimeout(updateCountdown, Math.min(remaining, 1000));
        }

        countdown.classList.remove('d-none');
        updateCountdown();
        return;
    }

    const form = document.getElementById('ca-screening-form');
    if (!form) return;

    const sections = Array.from(form.querySelectorAll('[data-ca-section]'));
    const steps = Array.from(document.querySelectorAll('[data-ca-step]'));
    const questions = Array.from(form.querySelectorAll('.prem-question'));
    const progress = document.getElementById('ca-progress');
    const progressLabel = document.getElementById('ca-progress-label');
    const review = document.getElementById('ca-review-answers');
    const back = document.getElementById('ca-screen-back');
    const next = document.getElementById('ca-screen-next');
    const submit = document.getElementById('ca-screen-submit');
    const last = sections.length - 1;
    let step = 0;

    function updateProgress() {
        const answered = questions.filter((question) => question.querySelector('input:checked')).length;
        progressLabel.textContent = answered + '/' + questions.length;
        progress.setAttribute('aria-valuenow', String(answered));
        progress.style.width = (answered / questions.length * 100) + '%';
    }

    function buildReview() {
        const items = questions.map((question) => {
            const row = document.createElement('div');
            row.className = 'prem-review-item';
            const title = document.createElement('p');
            title.textContent = question.querySelector('.prem-question-text').textContent;
            const answer = document.createElement('span');
            answer.className = 'prem-review-value';
            answer.textContent = question.querySelector('input:checked').closest('label').querySelector('.prem-choice-label').textContent;
            row.append(title, answer);
            return row;
        });
        review.replaceChildren(...items);
    }

    function show(index, focus = true) {
        step = index;
        sections.forEach((section, i) => {
            section.classList.toggle('d-none', i !== step);
            section.classList.remove('mt-6');
            steps[i].classList.toggle('is-current', i === step);
            if (i === step) {
                steps[i].setAttribute('aria-current', 'step');
            } else {
                steps[i].removeAttribute('aria-current');
            }
        });
        back.classList.toggle('d-none', step === 0);
        next.classList.toggle('d-none', step === last);
        submit.classList.toggle('d-none', step !== last);
        if (step === last) buildReview();
        if (focus) sections[step].querySelector('h2').focus();
    }

    function validateQuestions() {
        let firstMissing = null;
        questions.forEach((question) => {
            const missing = !question.querySelector('input:checked');
            question.classList.toggle('is-invalid', missing);
            question.querySelector('.prem-question-error').classList.toggle('d-none', !missing);
            question.querySelectorAll('input').forEach((input) => input.setAttribute('aria-invalid', String(missing)));
            if (missing && !firstMissing) firstMissing = question;
        });
        if (firstMissing) {
            firstMissing.scrollIntoView({behavior: 'smooth', block: 'center'});
            firstMissing.querySelector('input').focus({preventScroll: true});
        }
        return !firstMissing;
    }

    function advance() {
        if (step === 1 && !validateQuestions()) return;
        const invalid = sections[step].querySelector('input:invalid, select:invalid');
        if (invalid) {
            invalid.reportValidity();
            return;
        }
        show(step + 1);
    }

    back.addEventListener('click', () => show(step - 1));
    next.addEventListener('click', advance);
    form.addEventListener('change', (event) => {
        if (event.target.matches('input[data-ca-warning="1"]:checked') && window.Swal) {
            Swal.fire({
                icon: 'error',
                title: 'Tanda peringatan',
                text: 'Perlu dievaluasi dokter sesegera mungkin.',
                confirmButtonText: 'Tutup',
                buttonsStyling: false,
                customClass: {
                    popup: 'ca-warning-dialog',
                    icon: 'ca-warning-icon',
                    title: 'ca-warning-title',
                    htmlContainer: 'ca-warning-message',
                    confirmButton: 'btn ca-warning-close',
                },
                showCloseButton: true,
                allowOutsideClick: true,
            });
        }
        const question = event.target.closest('.prem-question');
        if (question) {
            question.classList.remove('is-invalid');
            question.querySelector('.prem-question-error').classList.add('d-none');
            question.querySelectorAll('input').forEach((input) => input.removeAttribute('aria-invalid'));
        }
        updateProgress();
    });
    form.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && step < last && event.target.matches('input:not([type=radio])')) {
            event.preventDefault();
            advance();
        }
    });
    form.addEventListener('submit', (event) => {
        if (step !== last) {
            event.preventDefault();
            return;
        }
        submit.disabled = true;
    });
    window.addEventListener('pageshow', () => {
        submit.disabled = false;
        updateProgress();
    });

    document.getElementById('ca-form-layout').classList.add('prem-layout');
    document.getElementById('ca-progress-panel').classList.remove('d-none');
    updateProgress();
    show(0, false);
});
