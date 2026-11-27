(async () => {
    const page = document.getElementById('patient-page');
    if (!page) return;
    const el = key => document.getElementById('patient-' + key);
    const modal = bootstrap.Modal.getOrCreateInstance(el('modal'));
    let busy = false;
    let dummyForm = false;
    const regionCache = new Map();
    let retryRegion = null;
    function message(key, text, error = false) {
        const node = el(key); node.hidden = !text; node.textContent = text;
        node.className = 'alert ' + (error ? 'alert-danger' : 'alert-success');
    }
    async function request(endpoint, data, loadingTitle = '') {
        return new Promise((resolve, reject) => $.ajax({
            url: page.dataset.url + '/' + endpoint, method: data ? 'POST' : 'GET', data: data ? JSON.stringify(data) : undefined,
            contentType: 'application/json', dataType: 'json', timeout: 220000, headers: {'X-CSRF-TOKEN': page.dataset.csrf},
            beforeSend: () => { if (loadingTitle) showDataLoading(loadingTitle, 'Mohon tunggu hingga proses selesai.'); },
            complete: () => { if (loadingTitle) Swal.close(); }
        }).done(result => { if (result.csrfHash) page.dataset.csrf = result.csrfHash; resolve(result.data); })
            .fail(xhr => { if (xhr.responseJSON?.csrfHash) page.dataset.csrf = xhr.responseJSON.csrfHash;
                const error = new Error(xhr.responseJSON?.error || xhr.responseJSON?.responseDesc || (xhr.status === 503 ? 'Koneksi database atau pemeriksaan akses gagal. Klik Perbarui untuk mencoba kembali.' : '') || 'Hasil belum dapat dibaca. Perbarui daftar dan periksa log bridging sebelum mengulangi.');
                error.exchange = xhr.responseJSON?.exchange;
                reject(error);
            }));
    }
    async function refresh() {
        const data = await request('data');
        if (!Array.isArray(data?.records)) throw new Error('Respons master pasien tidak valid. Muat ulang halaman dan periksa sesi login.');
        const table = el('table');
        if ($.fn.DataTable.isDataTable(table)) $(table).DataTable().destroy();
        table.tBodies[0].replaceChildren();
        for (const row of data.records) {
            const tr = document.createElement('tr');
            for (const value of [row.nik, row.name, {male: 'Laki-laki', female: 'Perempuan'}[row.gender] || row.gender, row.birth_date, row.ihs_number, row.mother_nik, row.is_dummy === '1' ? 'Sandbox / dummy' : 'Pasien']) {
                tr.insertCell().textContent = value || '—';
            }
            table.tBodies[0].append(tr);
        }
        initDataTable(table, '#patient-search', 10);
        const selected = el('environment').value;
        el('environment').replaceChildren(new Option('Pilih environment', ''));
        for (const env of data.environments) {
            const option = new Option(env.name, env.id);
            option.dataset.sandbox = env.sandbox ? '1' : '0';
            el('environment').add(option);
        }
        $(el('environment')).val(data.environments.some(env => env.id === selected) ? selected : '').trigger('change.select2');
        message('message', '');
    }
    async function work(action, title, form = false, loadingModal = true) {
        if (busy) return;
        busy = true;
        page.setAttribute('aria-busy', 'true');
        el('refresh').disabled = true; el('new').disabled = true;
        el('form').querySelectorAll('button, input, select').forEach(node => node.disabled = true);
        el('modal').addEventListener('hide.bs.modal', preventHide);
        if (loadingModal) showDataLoading(title, 'Mohon tunggu hingga proses selesai.');
        try { await action(); }
        catch (error) { message(form ? 'form-message' : 'message', error.message, true); }
        finally {
            if (loadingModal) Swal.close();
            busy = false; page.removeAttribute('aria-busy');
            el('refresh').disabled = false; el('new').disabled = false;
            el('form').querySelectorAll('button, input, select').forEach(node => node.disabled = false);
            el('modal').removeEventListener('hide.bs.modal', preventHide);
        }
    }
    function preventHide(event) { if (busy) event.preventDefault(); }
    const regionFields = ['province', 'city_code', 'district', 'village'];
    const regionLevels = ['provinces', 'cities', 'districts', 'sub-districts'];
    const initialProvinces = Array.from(el('province').options).filter(option => option.value).map(option => ({id: option.value, text: option.textContent.replace(/ · [0-9]+$/, '')}));
    if (initialProvinces.length) regionCache.set('0|', initialProvinces);
    function resetRegions(from = 0) {
        for (const field of regionFields.slice(from)) {
            el(field).replaceChildren(new Option('Pilih wilayah', ''));
            $(el(field)).trigger('change.select2');
        }
        if (from <= 1) el('city').value = '';
    }
    async function loadRegions(index) {
        const parent = index ? el(regionFields[index - 1]).value : '';
        resetRegions(index);
        if (index && !parent) return;
        retryRegion = index; el('region-retry').hidden = true;
        const key = [index, parent].join('|');
        let rows = regionCache.get(key);
        try {
            if (!rows) {
                const query = new URLSearchParams({level: regionLevels[index], parent});
                const data = await request('regions?' + query, undefined, 'Memuat master wilayah');
                rows = data.results;
                regionCache.set(key, rows);
            }
            const unique = new Map(rows.map(row => [row.id, row]));
            for (const row of unique.values()) el(regionFields[index]).add(new Option(row.text === row.id ? row.id : row.text + ' · ' + row.id, row.id));
            $(el(regionFields[index])).trigger('change.select2');
            el('region-status').textContent = rows.length ? 'Pilih wilayah untuk melanjutkan.' : 'Wilayah belum tersedia untuk pilihan ini.';
            retryRegion = null;
        } catch (error) {
            el('region-status').textContent = error.message;
            el('region-retry').hidden = false;
            throw error;
        }
    }
    for (const [index, field] of regionFields.entries()) {
        $(el(field)).select2({width: '100%', dropdownParent: $(el('modal')), placeholder: 'Pilih wilayah'});
        $(el(field)).on('change', () => {
            if (busy) return;
            if (index === 1) el('city').value = el(field).selectedOptions[0]?.textContent.split(' · ')[0] || '';
            if (index < 3) work(() => loadRegions(index + 1), 'Memuat master wilayah', true, false);
        });
    }
    const motherModal = bootstrap.Modal.getOrCreateInstance(el('mother-modal'));
    for (const field of ['environment', 'gender']) {
        $(el(field)).select2({
            width: '100%',
            dropdownParent: $(el('modal')),
            placeholder: el(field).options[0].text,
            minimumResultsForSearch: Infinity
        });
    }
    const environment = $(el('environment'));
    const isSandbox = option => option?.dataset.sandbox === '1';
    const pick = options => options[Math.floor(Math.random() * options.length)];
    const randomDigits = length => Array.from({length}, () => Math.floor(Math.random() * 10)).join('');
    function setMother(row) {
        el('mother_nik').value = row?.nik || '';
        el('mother-name').value = row?.name || '';
    }
    async function fillDummy() {
        const sandbox = pick(Array.from(el('environment').options).filter(isSandbox));
        if (!sandbox) {
            await loadRegions(0);
            throw new Error('Environment Sandbox belum tersedia untuk mengisi data dummy.');
        }

        dummyForm = true;
        environment.val(sandbox.value).trigger('change.select2');
        const sample = randomDigits(8);
        const birthDate = new Date();
        birthDate.setDate(birthDate.getDate() - Math.floor(Math.random() * 7));
        el('name').value = 'Bayi Dummy ' + sample;
        el('birth_date').value = [
            birthDate.getFullYear(), String(birthDate.getMonth() + 1).padStart(2, '0'), String(birthDate.getDate()).padStart(2, '0')
        ].join('-');
        el('birth_order').value = pick([0, 1, 2]);
        $(el('gender')).val(pick(['male', 'female'])).trigger('change.select2');
        el('address').value = 'Alamat Dummy ' + sample;
        el('postal_code').value = randomDigits(5);
        el('email').value = 'dummy' + sample + '@example.com';
        el('birth_place').value = pick(['Bandung', 'Jakarta', 'Surabaya']);
        el('rt').value = String(1 + Math.floor(Math.random() * 20));
        el('rw').value = String(1 + Math.floor(Math.random() * 20));
        el('contact_phone').value = '000' + randomDigits(8);

        for (const [index, field] of regionFields.entries()) {
            await loadRegions(index);
            const option = pick(Array.from(el(field).options).filter(candidate => candidate.value));
            if (!option) throw new Error('Wilayah dummy belum lengkap. Pilih wilayah yang tersedia.');
            $(el(field)).val(option.value).trigger('change.select2');
            if (index === 1) el('city').value = option.textContent.split(' · ')[0];
        }
        el('region-status').textContent = 'Wilayah dummy terisi.';

        const query = new URLSearchParams({environment_id: sandbox.value, dummy_only: '1'});
        const {records} = await request('mothers?' + query);
        const mother = pick(records);
        if (!mother) throw new Error('Master pasien Sandbox belum memiliki NIK ibu dummy.');
        setMother(mother);
    }
    const exchangeModal = bootstrap.Modal.getOrCreateInstance(el('exchange-modal'));
    const exchangePanels = {request: 'requestBody', response: 'responseBody'};
    function showExchange(exchange) {
        for (const [panel, key] of Object.entries(exchangePanels)) {
            el('json-' + panel).parentElement.hidden = !!exchange && !(key in exchange);
            el('json-' + panel + '-title').hidden = !!exchange && !(key in exchange);
            const json = exchange ? JSON.stringify(exchange[key], null, 2) : '';
            el('json-' + panel).textContent = json;
            el('json-' + panel + '-lines').textContent = json
                ? json.split('\n').map((line, index) => index + 1).join('\n')
                : '';
        }
        el('exchange-open').hidden = !exchange;
    }
    el('exchange-open').onclick = () => {
        el('modal').addEventListener('hidden.bs.modal', () => exchangeModal.show(), {once: true});
        modal.hide();
    };
    el('exchange-modal').addEventListener('hidden.bs.modal', () => {
        el('modal').addEventListener('shown.bs.modal', () => el('preview').focus(), {once: true});
        modal.show();
    });
    el('mother-modal').addEventListener('hidden.bs.modal', () => modal.show());
    el('mother-modal').addEventListener('shown.bs.modal', () => {
        if ($.fn.DataTable.isDataTable(el('mother-table'))) $(el('mother-table')).DataTable().columns.adjust();
    });
    el('mother-find').onclick = () => work(async () => {
        message('form-message', '');
        if (!el('environment').value) throw new Error('Pilih environment sebelum mencari NIK ibu.');
        const query = new URLSearchParams({environment_id: el('environment').value, dummy_only: dummyForm ? '1' : '0'});
        const data = await request('mothers?' + query, undefined, 'Memuat master pasien');
        const table = el('mother-table');
        if ($.fn.DataTable.isDataTable(table)) $(table).DataTable().destroy();
        table.tBodies[0].replaceChildren();
        el('mother-search').value = '';
        for (const row of data.records) {
            const tr = document.createElement('tr');
            for (const value of [row.nik, row.name, {male: 'Laki-laki', female: 'Perempuan'}[row.gender] || row.gender, row.birth_date, row.ihs_number]) tr.insertCell().textContent = value || '—';
            const cell = tr.insertCell(); cell.className = 'text-end';
            const choose = document.createElement('button'); choose.type = 'button'; choose.className = 'btn btn-sm btn-light-primary'; choose.textContent = 'Pilih';
            choose.onclick = () => { setMother(row); motherModal.hide(); };
            cell.append(choose); table.tBodies[0].append(tr);
        }
        initDataTable(table, '#patient-mother-search', 10, {columnDefs: [{targets: -1, searchable: false}]});
        el('modal').removeEventListener('hide.bs.modal', preventHide);
        el('modal').addEventListener('hidden.bs.modal', () => motherModal.show(), {once: true});
        modal.hide();
    }, 'Memuat master pasien', true, false);
    el('mother_nik').onclick = () => el('mother-find').click();
    environment.on('change', () => {
        setMother(null);
        if (!dummyForm || isSandbox(el('environment').selectedOptions[0])) return;
        const selected = el('environment').value;
        dummyForm = false;
        el('form').reset();
        environment.val(selected);
        $([el('environment'), el('gender')]).trigger('change.select2');
        showExchange(null);
        message('form-message', '');
        work(() => loadRegions(0), 'Memuat master wilayah', true, false);
    });
    el('region-retry').onclick = () => { if (retryRegion !== null) work(() => loadRegions(retryRegion), 'Memuat master wilayah', true, false); };
    el('new').onclick = () => {
        dummyForm = false;
        el('form').reset(); resetRegions(); retryRegion = null; el('region-retry').hidden = true;
        $([el('environment'), el('gender')]).trigger('change.select2');
        showExchange(null);
        el('region-status').textContent = 'Pilih provinsi sesuai alamat pasien.';
        el('mother-name').value = '';
        message('form-message', ''); modal.show();
        work(fillDummy, 'Mengisi data dummy', true, false);
    };
    el('refresh').onclick = () => work(refresh, 'Memuat master pasien');
    el('preview').onclick = async () => {
        if (busy || !el('form').reportValidity()) return;
        const data = Object.fromEntries(new FormData(el('form')));
        data.birth_order = Number(data.birth_order);
        let preview;
        await work(async () => {
            preview = await request('preview', data);
        }, 'Menyiapkan request body', true);
        if (preview) {
            showExchange(preview);
            el('exchange-open').click();
        }
    };
    el('form').onsubmit = event => {
        event.preventDefault();
        showExchange(null);
        if (!el('mother_nik').value) { message('form-message', 'Pilih NIK ibu dari master pasien.', true); return; }
        if (!el('form').reportValidity()) return;
        const data = Object.fromEntries(new FormData(el('form'))); data.birth_order = Number(data.birth_order);
        message('form-message', '');
        work(async () => {
            let result;
            try {
                result = await request('create', data);
            } catch (error) {
                showExchange(error.exchange);
                throw error;
            }
            el('modal').removeEventListener('hide.bs.modal', preventHide); modal.hide();
            await refresh();
            message('message', 'Pasien tersimpan. Nomor IHS ' + result.ihs + '.');
        }, 'Mendaftarkan pasien bayi', true);
    };
    initDataTable(el('table'), '#patient-search', 10);

})();
