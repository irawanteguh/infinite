(async () => {
    const page = document.getElementById('environment-page');
    if (!page) return;
    const select = document.getElementById('env-select');
    const name = document.getElementById('env-name');
    const rows = document.getElementById('env-rows');
    const status = document.getElementById('env-status');
    let environments = [];
    let active = '';
    let dirty = false;
    const message = (text, error = false) => {
        status.hidden = false;
        status.className = 'alert ' + (error ? 'alert-danger' : 'alert-info');
        status.textContent = text;
    };
    let storage;
    try {
        storage = await createRestStorage(page);
        let saved = storage.data;
        if (saved === null) {
            saved = JSON.parse(localStorage.getItem(page.dataset.storageKey) || '{}');
            if (Array.isArray(saved.environments)) await storage.save(saved);
        }
        environments = saved?.environments || [];
        active = saved?.active || '';
    } catch (error) {
        message(error.message, true);
        page.querySelectorAll('button, input, select').forEach(control => control.disabled = true);
        return;
    }
    const persist = () => storage.save({environments, active});
    function current() { return environments.find(item => item.id === active); }
    function addRow(value = {key: '', value: '', type: 'default', enabled: true}) {
        const row = document.createElement('tr');
        row.innerHTML = '<td><input class="form-check-input" type="checkbox" aria-label="Variabel aktif"></td><td><input class="form-control" aria-label="Nama variabel"></td><td><select class="form-select" aria-label="Tipe variabel"><option value="default">Text</option><option value="secret">Secret</option></select></td><td><div class="input-group"><input class="form-control" aria-label="Nilai variabel" autocomplete="off"><button type="button" class="btn btn-light" aria-label="Tampilkan nilai">Lihat</button></div></td><td class="text-end"><button type="button" class="btn btn-sm btn-light-danger">Hapus</button></td>';
        const inputs = row.querySelectorAll('input');
        inputs[0].checked = value.enabled !== false;
        inputs[1].value = value.key;
        inputs[2].value = value.value;
        const type = row.querySelector('select');
        type.value = value.type === 'secret' ? 'secret' : 'default';
        const toggle = row.querySelector('.input-group button');
        function updateType() {
            inputs[2].type = type.value === 'secret' ? 'password' : 'text';
            toggle.hidden = type.value !== 'secret';
            toggle.textContent = 'Lihat';
        }
        type.addEventListener('change', updateType);
        toggle.addEventListener('click', () => {
            inputs[2].type = inputs[2].type === 'password' ? 'text' : 'password';
            toggle.textContent = inputs[2].type === 'password' ? 'Lihat' : 'Sembunyikan';
        });
        row.lastElementChild.firstElementChild.addEventListener('click', () => { row.remove(); dirty = true; });
        updateType();
        rows.append(row);
    }
    function render() {
        select.replaceChildren();
        environments.forEach(item => select.add(new Option(item.name, item.id)));
        if (!current()) active = environments[0]?.id || '';
        select.value = active;
        name.value = current()?.name || '';
        rows.replaceChildren();
        (current()?.values || []).forEach(addRow);
        ['env-save', 'env-add', 'env-export', 'env-delete'].forEach(id => document.getElementById(id).disabled = !current());
        name.disabled = !current();
        dirty = false;
    }
    async function save() {
        if (!current()) return false;
        const values = Array.from(rows.children, row => {
            const inputs = row.querySelectorAll('input');
            return {key: inputs[1].value.trim(), value: inputs[2].value, type: row.querySelector('select').value, enabled: inputs[0].checked};
        });
        if (!name.value.trim() || values.some(value => !value.key) || new Set(values.map(value => value.key)).size !== values.length) {
            message('Isi nama environment dan nama variabel yang unik.', true);
            return false;
        }
        const previous = structuredClone(current());
        Object.assign(current(), {name: name.value.trim(), values});
        try { await persist(); render(); message('Environment berhasil disimpan.'); return true; }
        catch { Object.assign(current(), previous); message('Penyimpanan gagal. Muat ulang halaman sebelum mencoba kembali.', true); return false; }
    }
    function canLeave() { return !dirty || window.confirm('Abaikan perubahan yang belum disimpan?'); }
    page.addEventListener('input', event => { if (event.target.closest('#env-rows') || event.target === name) dirty = true; });
    document.getElementById('env-save').onclick = save;
    document.getElementById('env-add').onclick = () => { addRow(); dirty = true; };
    select.onchange = async () => {
        if (!canLeave()) { select.value = active; return; }
        active = select.value;
        render();
        try { await persist(); } catch { message('Pilihan environment belum tersimpan.', true); }
    };
    document.getElementById('env-new').onclick = () => {
        if (!canLeave()) return;
        const item = {id: crypto.randomUUID(), name: 'Environment baru', values: []};
        environments.push(item); active = item.id; render(); dirty = true; name.focus();
    };
    document.getElementById('env-delete').onclick = async () => {
        if (!current() || !window.confirm('Hapus environment ' + current().name + '?')) return;
        const previous = environments;
        environments = environments.filter(item => item.id !== active);
        try { await persist(); render(); message('Environment dihapus.'); }
        catch { environments = previous; message('Penghapusan gagal disimpan.', true); }
    };
    document.getElementById('env-export').onclick = async () => {
        if (!await save()) return;
        const item = current();
        const blob = new Blob([JSON.stringify({id: item.id, name: item.name, values: item.values, _postman_variable_scope: 'environment'}, null, 2)], {type: 'application/json'});
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a'); link.href = url;
        link.download = item.name.replace(/[^a-z0-9_-]/gi, '_') + '.postman_environment.json';
        link.click(); setTimeout(() => URL.revokeObjectURL(url), 1000);
    };
    document.getElementById('env-import').onchange = async event => {
        const file = event.target.files[0];
        if (!file || !canLeave()) return;
        try {
            if (file.size > 5 * 1024 * 1024) throw new Error('Ukuran file maksimal 5 MB.');
            const item = JSON.parse(await file.text());
            if (typeof item.name !== 'string' || !item.name.trim() || !Array.isArray(item.values) ||
                item.values.some(value => typeof value.key !== 'string' || !value.key.trim() || typeof value.value !== 'string') ||
                new Set(item.values.map(value => value.key.trim())).size !== item.values.length) throw new Error('Format environment Postman tidak valid.');
            const imported = {id: crypto.randomUUID(), name: item.name, values: item.values.map(value => ({key: value.key.trim(), value: value.value, type: value.type === 'secret' ? 'secret' : 'default', enabled: value.enabled !== false}))};
            environments.push(imported);
            active = imported.id;
            try { await persist(); } catch (error) { environments.pop(); throw error; }
            active = imported.id; render(); message('Environment berhasil diimpor.');
        } catch (error) { message(error.message || 'Impor gagal.', true); }
        finally { event.target.value = ''; }
    };
    window.addEventListener('beforeunload', event => { if (dirty) { event.preventDefault(); event.returnValue = ''; } });
    render();
})();
