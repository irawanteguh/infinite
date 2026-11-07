(function () {
    const root = document.getElementById('pdsaRoot');
    const form = document.getElementById('pdsaForm');
    const itemsForm = document.getElementById('pdsaStagesForm');
    if (!root || !form || !itemsForm) return;

    const endpoint = root.dataset.endpoint;
    const linkedIndicator = String(root.dataset.indicatorRef || '');
    const linkedDepartment = String(root.dataset.department || '');
    const linkedPeriod = String(root.dataset.period || '');
    const rows = document.getElementById('pdsaRows');
    const empty = document.getElementById('pdsaEmpty');
    const headerMessage = document.getElementById('pdsaMessage');
    const itemsMessage = document.getElementById('pdsaStagesMessage');
    const archiveButton = document.getElementById('pdsaArchive');
    const headerModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('pdsaModal'));
    const itemsModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('pdsaStagesModal'));
    const diagramModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('pdsaDiagramModal'));
    const phases = [
        { key: 'Plan', color: '#009ef7', fill: '#f1faff', cx: 400, cy: 125 },
        { key: 'Do', color: '#50cd89', fill: '#e8fff3', cx: 675, cy: 400 },
        { key: 'Study', color: '#ffc700', fill: '#fff8dd', cx: 400, cy: 675 },
        { key: 'Act', color: '#f1416c', fill: '#fff5f8', cx: 125, cy: 400 },
    ];
    let headers = [];

    const esc = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[char]));
    const field = (targetForm, name) => targetForm.elements.namedItem(name);

    async function post(action, payload) {
        const body = new URLSearchParams(payload);
        const response = await fetch(`${endpoint}/${action}`, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }, body });
        return response.json();
    }

    async function load(selectId) {
        const dataUrl = `${endpoint}/data${linkedIndicator ? `?indikator_ref_id=${encodeURIComponent(linkedIndicator)}` : ''}`;
        const result = await (await fetch(dataUrl, { headers: { 'Accept': 'application/json' } })).json();
        headers = result.responseResult || [];
        if (selectId) {
            const row = rows.querySelector(`[data-id="${CSS.escape(String(selectId))}"]`);
            if (row) row.classList.add('bg-light-primary');
        }
        render();
    }

    function render() {
        rows.innerHTML = headers.map((header) => `<tr><td><div class="fw-bold text-gray-800">${esc(header.title)}</div><div class="text-muted fs-8">${esc(header.indikator || 'Perbaikan unit')}</div></td><td>${esc(header.department || '-')}<div class="text-muted fs-8">${esc(header.tahun || '-')}</div></td><td><span class="badge badge-light-primary">${esc(header.item_count || 0)} item</span></td><td class="text-end text-nowrap"><button type="button" class="btn btn-sm btn-light-primary me-1" data-action="diagram" data-id="${esc(header.pdsa_id)}"><i class="bi bi-diagram-3 me-1"></i>Lihat Diagram</button><button type="button" class="btn btn-sm btn-light-success me-1" data-action="items" data-id="${esc(header.pdsa_id)}"><i class="bi bi-list-check me-1"></i>Item PDSA</button><button type="button" class="btn btn-sm btn-icon btn-light" title="Ubah header" data-action="header" data-id="${esc(header.pdsa_id)}"><i class="bi bi-gear"></i></button></td></tr>`).join('');
        empty.classList.toggle('d-none', headers.length > 0);
    }

    function resetHeader() {
        form.reset();
        field(form, 'pdsa_id').value = '';
        if (linkedIndicator) {
            field(form, 'indikator_ref_id').value = linkedIndicator;
            field(form, 'department_id').value = linkedDepartment;
            field(form, 'periode_id').value = linkedPeriod;
        }
        document.getElementById('pdsaFormTitle').textContent = 'Buat header PDSA';
        archiveButton.classList.add('d-none');
        headerMessage.textContent = '';
    }

    function editHeader(header) {
        ['pdsa_id', 'title', 'department_id', 'periode_id', 'indikator_ref_id', 'problem', 'aim'].forEach((name) => {
            field(form, name).value = header[name] ?? '';
        });
        document.getElementById('pdsaFormTitle').textContent = 'Perbarui header PDSA';
        archiveButton.classList.remove('d-none');
        headerMessage.textContent = '';
        headerModal.show();
    }

    function addItemField(phase, value) {
        const list = itemsForm.querySelector(`[data-item-list="${phase}"]`);
        const row = document.createElement('div');
        row.className = 'd-flex align-items-start gap-2 mb-2';
        row.dataset.itemPhase = phase;
        const textarea = document.createElement('textarea');
        textarea.className = 'form-control';
        textarea.rows = 2;
        textarea.maxLength = 5000;
        textarea.placeholder = `Tulis item ${phase}...`;
        textarea.value = value || '';
        textarea.dataset.itemText = '1';
        const remove = document.createElement('button');
        remove.type = 'button';
        remove.className = 'btn btn-icon btn-light-danger';
        remove.title = 'Hapus item';
        remove.innerHTML = '<i class="bi bi-trash"></i>';
        remove.addEventListener('click', () => row.remove());
        row.append(textarea, remove);
        list.appendChild(row);
    }

    function populateItems(items) {
        phases.forEach((phase) => {
            const list = itemsForm.querySelector(`[data-item-list="${phase.key}"]`);
            list.replaceChildren();
            (items || []).filter((item) => item.phase === phase.key).forEach((item) => addItemField(phase.key, item.item_text));
            if (!list.children.length) addItemField(phase.key, '');
        });
    }

    async function editItems(id) {
        const response = await (await fetch(`${endpoint}/itemsdata?pdsa_id=${encodeURIComponent(id)}`, { headers: { 'Accept': 'application/json' } })).json();
        if (response.responseCode !== '00') {
            window.alert(response.responseDesc || 'Item PDSA tidak dapat dimuat.');
            return;
        }
        field(itemsForm, 'pdsa_id').value = id;
        document.getElementById('pdsaStagesSubtitle').textContent = response.responseCycle.title;
        itemsMessage.textContent = '';
        populateItems(response.responseItems || []);
        itemsModal.show();
    }

    function renderDiagram(header, items) {
        const centerTitle = esc(header.title || 'PDSA');
        let svg = `<svg viewBox="0 0 800 800" role="img" aria-label="Diagram PDSA berbentuk lingkaran" style="display:block;width:min(100%,760px);height:auto;margin:0 auto"><defs><marker id="pdsaRoundArrow" markerWidth="12" markerHeight="12" refX="9" refY="4" orient="auto"><path d="M0,0 L0,8 L10,4 z" fill="#7e8299"/></marker></defs><circle cx="400" cy="400" r="350" fill="#fff" stroke="#d7d9e3" stroke-width="6"/>`;
        svg += '<path d="M492 98 Q610 145 675 260" fill="none" stroke="#7e8299" stroke-width="4" marker-end="url(#pdsaRoundArrow)"/><path d="M702 492 Q655 610 540 675" fill="none" stroke="#7e8299" stroke-width="4" marker-end="url(#pdsaRoundArrow)"/><path d="M308 702 Q190 655 125 540" fill="none" stroke="#7e8299" stroke-width="4" marker-end="url(#pdsaRoundArrow)"/><path d="M98 308 Q145 190 260 125" fill="none" stroke="#7e8299" stroke-width="4" marker-end="url(#pdsaRoundArrow)"/>';
        phases.forEach((phase) => {
            const phaseItems = (items || []).filter((item) => item.phase === phase.key);
            const itemMarkup = phaseItems.length ? phaseItems.map((item, index) => `<div style="text-align:left;border-bottom:1px solid #e4e6ef;padding:3px 0"><b>${index + 1}.</b> ${esc(item.item_text)}</div>`).join('') : '<div style="color:#a1a5b7;font-style:italic">Belum ada item</div>';
            const x = phase.cx - 94;
            const y = phase.cy - 94;
            svg += `<circle cx="${phase.cx}" cy="${phase.cy}" r="104" fill="${phase.fill}" stroke="${phase.color}" stroke-width="4"/><foreignObject x="${x}" y="${y}" width="188" height="188"><div xmlns="http://www.w3.org/1999/xhtml" style="box-sizing:border-box;width:188px;height:188px;padding:23px 15px 12px;font-family:Arial,sans-serif;text-align:center;display:flex;flex-direction:column"><strong style="font-size:18px;color:${phase.color};margin-bottom:8px">${phase.key.toUpperCase()}</strong><div style="font-size:11px;line-height:1.3;color:#5e6278;overflow:auto;max-height:130px">${itemMarkup}</div></div></foreignObject>`;
        });
        svg += `<circle cx="400" cy="400" r="94" fill="#fff" stroke="#e4e6ef" stroke-width="3"/><foreignObject x="322" y="355" width="156" height="90"><div xmlns="http://www.w3.org/1999/xhtml" title="${centerTitle}" style="height:90px;display:flex;align-items:center;justify-content:center;text-align:center;padding:8px;overflow:hidden;word-break:break-word;font:700 14px Arial;color:#181c32">${centerTitle}</div></foreignObject></svg>`;
        document.getElementById('pdsaDiagram').innerHTML = svg;
    }

    async function showDiagram(id) {
        const response = await (await fetch(`${endpoint}/itemsdata?pdsa_id=${encodeURIComponent(id)}`, { headers: { 'Accept': 'application/json' } })).json();
        if (response.responseCode !== '00') {
            window.alert(response.responseDesc || 'Diagram tidak dapat dimuat.');
            return;
        }
        const header = response.responseCycle;
        document.getElementById('pdsaDiagramTitle').textContent = header.title;
        renderDiagram(header, response.responseItems || []);
        diagramModal.show();
    }

    rows.addEventListener('click', (event) => {
        const button = event.target.closest('[data-action][data-id]');
        if (!button) return;
        const header = headers.find((item) => String(item.pdsa_id) === button.dataset.id);
        if (!header) return;
        if (button.dataset.action === 'diagram') showDiagram(header.pdsa_id);
        if (button.dataset.action === 'items') editItems(header.pdsa_id);
        if (button.dataset.action === 'header') editHeader(header);
    });

    document.querySelectorAll('[data-add-item]').forEach((button) => {
        button.addEventListener('click', () => addItemField(button.dataset.addItem, ''));
    });
    document.getElementById('pdsaNew').addEventListener('click', () => { resetHeader(); headerModal.show(); });
    document.getElementById('pdsaReset').addEventListener('click', resetHeader);

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        headerMessage.textContent = 'Menyimpan header…';
        try {
            const payload = Object.fromEntries(new FormData(form).entries());
            if (linkedIndicator && !payload.pdsa_id) {
                payload.indikator_ref_id = linkedIndicator;
                payload.department_id = linkedDepartment;
                payload.periode_id = linkedPeriod;
            }
            const result = await post('save', payload);
            headerMessage.textContent = result.responseDesc || '';
            headerMessage.className = `small mt-3 ${result.responseCode === '00' ? 'text-success' : 'text-danger'}`;
            if (result.responseCode === '00') { headerModal.hide(); await load(result.responseResult); }
        } catch (error) {
            headerMessage.textContent = 'Tidak dapat menyimpan header. Periksa koneksi lalu coba lagi.';
            headerMessage.className = 'small mt-3 text-danger';
        }
    });

    itemsForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        itemsMessage.textContent = 'Menyimpan item…';
        const items = Array.from(itemsForm.querySelectorAll('[data-item-phase]')).map((row) => ({
            phase: row.dataset.itemPhase,
            item_text: row.querySelector('[data-item-text]').value.trim(),
        })).filter((item) => item.item_text !== '');
        try {
            const result = await post('saveitems', { pdsa_id: field(itemsForm, 'pdsa_id').value, items: JSON.stringify(items) });
            itemsMessage.textContent = result.responseDesc || '';
            itemsMessage.className = `small mt-3 ${result.responseCode === '00' ? 'text-success' : 'text-danger'}`;
            if (result.responseCode === '00') { itemsModal.hide(); await load(result.responseResult); }
        } catch (error) {
            itemsMessage.textContent = 'Item PDSA gagal disimpan. Periksa koneksi lalu coba lagi.';
            itemsMessage.className = 'small mt-3 text-danger';
        }
    });

    archiveButton.addEventListener('click', async () => {
        if (!field(form, 'pdsa_id').value) return;
        if (!window.confirm('Arsipkan header PDSA ini beserta seluruh itemnya?')) return;
        const result = await post('archive', { pdsa_id: field(form, 'pdsa_id').value });
        headerMessage.textContent = result.responseDesc || '';
        if (result.responseCode === '00') { headerModal.hide(); await load(); }
    });

    load().catch(() => { empty.textContent = 'Gagal memuat daftar PDSA.'; });
})();
