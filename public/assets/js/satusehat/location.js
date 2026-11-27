(() => {
    const page = document.getElementById('location-page');
    if (!page) return;
    const el = key => document.getElementById('location-' + key);
    const modal = bootstrap.Modal.getOrCreateInstance(el('modal'));
    let busy = false;
    let records = [];
    let rootId = '';
    let organizations = [];
    function message(key, text, error = false) {
        el(key).hidden = !text;
        el(key).textContent = text;
        el(key).className = 'alert ' + (error ? 'alert-danger' : 'alert-success');
    }
    async function request(endpoint, body) {
        try {
            const result = await $.ajax({
                url: page.dataset.url + '/' + endpoint,
                method: body ? 'POST' : 'GET',
                data: body ? JSON.stringify(body) : {environment_id: el('environment').value},
                contentType: 'application/json', dataType: 'json', timeout: 220000,
                headers: {'X-CSRF-TOKEN': page.dataset.csrf}
            });
            if (result.csrfHash) page.dataset.csrf = result.csrfHash;
            if (!result.data) throw new Error('Respons tidak valid. Muat ulang halaman dan periksa sesi login.');
            return result.data;
        } catch (error) {
            if (error.responseJSON?.csrfHash) page.dataset.csrf = error.responseJSON.csrfHash;
            throw new Error(error.responseJSON?.error || error.message || 'Hasil belum pasti. Lakukan Sync sebelum mengulangi penambahan.');
        }
    }
    async function refresh() {
        const table = el('table');
        if ($.fn.DataTable.isDataTable(table)) $(table).DataTable().destroy();
        table.tBodies[0].replaceChildren();
        records = [];
        rootId = '';
        if (el('environment').value) {
            const data = await request('data');
            records = data.records;
            rootId = data.rootId;
            organizations = data.organizations;
            for (const record of records) {
                const row = table.tBodies[0].insertRow();
                const codes = (record.identifier || []).map(item => item.value).filter(Boolean).join(', ');
                const types = ([record.physicalType].filter(Boolean)).flatMap(item => (item.coding || []).map(coding => coding.display || coding.code)).join(', ');
                for (const value of [record.id, codes, record.name, types, record.managingOrganization?.reference, record.status === 'active' ? 'Aktif' : record.status || '—']) {
                    row.insertCell().textContent = value || '—';
                }
            }
        }
        initDataTable(table, '#location-search', 10);
    }
    async function work(action, form = false) {
        if (busy) return;
        if (!el('environment').value) {
            message('message', 'Pilih environment terlebih dahulu.', true);
            return;
        }
        busy = true;
        page.setAttribute('aria-busy', 'true');
        const controls = [...page.querySelectorAll('button, input, select, textarea'), el('sync'), el('refresh'), el('new')].filter(Boolean);
        controls.forEach(node => node.disabled = true);
        showDataLoading('Memproses Location', 'Mohon tunggu hingga proses selesai.');
        message(form ? 'form-message' : 'message', '');
        try {
            await action();
        } catch (error) {
            message(form ? 'form-message' : 'message', error.message, true);
        } finally {
            busy = false;
            controls.forEach(node => node.disabled = false);
            page.removeAttribute('aria-busy');
            Swal.close();
        }
    }
    el('modal').addEventListener('hide.bs.modal', event => { if (busy) event.preventDefault(); });
    $(el('environment')).select2({width: '100%', placeholder: 'Pilih environment'});
    $(el('part-of')).select2({width: '100%', dropdownParent: $(el('modal')), placeholder: 'Pilih organisasi pengelola'});
    $(el('type')).select2({width: '100%', dropdownParent: $(el('modal')), placeholder: 'Pilih jenis'});
    $(el('environment')).on('change', () => {
        if (!el('environment').value) { refresh(); return; }
        work(refresh);
    });
    el('refresh').onclick = () => work(refresh);
    el('sync').onclick = () => work(async () => {
        const result = await request('sync', {environment_id: el('environment').value});
        await refresh();
        message('message', result.count + ' Location berhasil disinkronkan.');
    });
    el('new').onclick = () => {
        if (busy) return;
        if (!el('environment').value) { message('message', 'Pilih environment terlebih dahulu.', true); return; }
        el('form').reset();
        el('part-of').replaceChildren(new Option('Pilih organisasi pengelola', ''));
        if (rootId) el('part-of').add(new Option('Organisasi induk · ' + rootId, rootId));
        for (const record of organizations) {
            if (record.id === rootId || record.active === false) continue;
            const parent = record.partOf?.reference || '';
            el('part-of').add(new Option((record.name || record.id) + ' · ' + record.id + (parent ? ' ← ' + parent : ''), record.id));
        }
        $(el('part-of')).val(rootId).trigger('change.select2');
        $(el('type')).trigger('change.select2');
        message('form-message', '');
        modal.show();
    };
    el('form').onsubmit = async event => {
        event.preventDefault();
        if (busy || !el('form').reportValidity()) return;
        const body = Object.fromEntries(new FormData(el('form')));
        body.environment_id = el('environment').value;
        let saved = false;
        await work(async () => {
            await request('create', body);
            saved = true;
        }, true);
        if (saved) {
            modal.hide();
            await work(async () => {
                await refresh();
                message('message', 'Location berhasil ditambahkan dan disimpan.');
            });
        }
    };
    initDataTable(el('table'), '#location-search', 10);
})();
