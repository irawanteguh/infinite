(() => {
    const modal = document.querySelector('#modal_change_password');
    const form = modal.querySelector('form');
    const message = form.querySelector('[data-password-message]');
    const submit = form.querySelector('[type="submit"]');
    const csrf = form.querySelector('input[type="hidden"]');
    const newPassword = form.elements.new_password;
    const toggles = form.querySelectorAll('[data-password-toggle]');
    const lengthRule = form.querySelector('[data-password-length]');
    const rules = Array.from(form.querySelectorAll('[data-password-rule]'), item => [
        item,
        new RegExp(item.dataset.passwordRule, 'u'),
    ]);
    const level = form.querySelector('[data-password-level]');
    const progress = form.querySelector('[data-password-progress]');
    const encoder = new TextEncoder();

    function updatePasswordPolicy() {
        const value = newPassword.value;
        const lengthOk = Array.from(value).length >= newPassword.minLength
            && encoder.encode(value).length <= Number(form.dataset.maxPasswordBytes) && !value.includes('\0');
        const results = [[lengthRule, lengthOk], ...rules.map(([item, pattern]) => [item, pattern.test(value)])];
        let passed = 0;
        for (const [item, valid] of results) {
            passed += Number(valid);
            item.classList.toggle('text-muted', !valid);
            item.classList.toggle('is-met', valid);
            const icon = item.querySelector('i');
            icon.classList.toggle('bi-circle', !valid);
            icon.classList.toggle('bi-check-circle-fill', valid);
        }
        const complete = passed === results.length;
        const label = !value ? 'Belum diisi' : complete ? 'Memenuhi syarat' : `${passed} dari ${results.length} syarat terpenuhi`;
        if (level.textContent !== label) level.textContent = label;
        progress.classList.remove('w-0');
        progress.style.width = `${passed / results.length * 100}%`;
        progress.classList.toggle('bg-success', complete);
        progress.classList.toggle('bg-warning', !complete);
        newPassword.setCustomValidity('');
        return complete;
    }

    function setPasswordVisibility(toggle, input, visible) {
        input.type = visible ? 'text' : 'password';
        toggle.setAttribute('aria-pressed', String(visible));
        toggle.querySelector('i').className = visible ? 'bi bi-eye-slash' : 'bi bi-eye';
    }

    function clearPasswords() {
        for (const toggle of toggles) {
            const input = form.elements[toggle.dataset.passwordToggle];
            input.value = '';
            setPasswordVisibility(toggle, input, false);
        }
        updatePasswordPolicy();
    }

    newPassword.addEventListener('input', updatePasswordPolicy);
    for (const toggle of toggles) {
        const input = form.elements[toggle.dataset.passwordToggle];
        toggle.addEventListener('click', () => setPasswordVisibility(toggle, input, input.type === 'password'));
    }
    modal.addEventListener('shown.bs.modal', () => form.elements.current_password.focus());
    modal.addEventListener('hidden.bs.modal', () => {
        clearPasswords();
        message.classList.add('d-none');
    });

    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (submit.disabled) return;
        if (!updatePasswordPolicy()) {
            newPassword.setCustomValidity('Lengkapi semua syarat kerumitan password.');
            newPassword.reportValidity();
            return;
        }

        submit.disabled = true;
        message.classList.add('d-none');
        let success = false;
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(form),
            });
            const result = await response.json();
            if (typeof result.csrfHash === 'string') csrf.value = result.csrfHash;
            success = response.ok;
            message.textContent = result.message;
            if (success) clearPasswords();
        } catch {
            message.textContent = 'Permintaan gagal. Silakan coba kembali.';
        } finally {
            message.classList.toggle('alert-success', success);
            message.classList.toggle('alert-danger', !success);
            message.classList.remove('d-none');
            submit.disabled = false;
        }
    });
})();
