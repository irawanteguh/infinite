(async () => {
    const page = document.getElementById('collection-page');
    if (!page) return;
    const element = id => document.getElementById(id);
    let environmentData = {};
    const urlInput = element('request-url');
    const urlHighlight = element('request-url-highlight');
    function variableAvailable(name) {
        const environment = (environmentData.environments || []).find(item => item.id === element('request-environment').value);
        return (environment?.values || []).some(variable => variable.key === name && variable.enabled !== false);
    }
    function refreshVariableColors() {
        page.querySelectorAll('.collection-url-variable').forEach(span => {
            span.classList.toggle('is-missing', !variableAvailable(span.textContent.slice(2, -2)));
        });
    }
    function highlightUrl() {
        const style = getComputedStyle(urlInput);
        for (const property of ['fontFamily', 'fontSize', 'fontWeight', 'fontStyle', 'lineHeight', 'letterSpacing', 'wordSpacing', 'paddingTop', 'paddingBottom', 'paddingLeft', 'paddingRight', 'textIndent', 'textAlign']) {
            urlHighlight.style[property] = style[property];
        }
        urlHighlight.replaceChildren();
        const value = urlInput.value;
        let offset = 0;
        for (const match of value.matchAll(/{{[^{}]+}}/g)) {
            urlHighlight.append(document.createTextNode(value.slice(offset, match.index)));
            const variable = document.createElement('span');
            variable.className = 'collection-url-variable' + (variableAvailable(match[0].slice(2, -2)) ? '' : ' is-missing');
            variable.textContent = match[0];
            urlHighlight.append(variable);
            offset = match.index + match[0].length;
        }
        urlHighlight.append(document.createTextNode(value.slice(offset)));
        urlHighlight.scrollLeft = urlInput.scrollLeft;
    }
    urlInput.addEventListener('input', highlightUrl);
    urlInput.addEventListener('scroll', () => { urlHighlight.scrollLeft = urlInput.scrollLeft; });
    urlInput.addEventListener('focus', highlightUrl);
    urlInput.addEventListener('blur', highlightUrl);
    new ResizeObserver(highlightUrl).observe(urlInput);
    function readPathVariables() {
        return Array.from(element('request-path-fields').children, row => {
            const [enabled, key, value] = row.querySelectorAll('input');
            return {...row.bodyField, key: key.value, value: value.value, disabled: !enabled.checked};
        });
    }
    function syncPathVariables(imported = null) {
        const previous = new Map((imported || readPathVariables()).map(item => [item.key, item]));
        const path = urlInput.value.split(/[?#]/)[0];
        const keys = [...new Set(Array.from(path.matchAll(/(?:^|\/):([A-Za-z_][\w-]*)(?=\/|$)/g), match => match[1]))];
        const container = element('request-path-fields');
        container.replaceChildren();
        element('request-path-section').hidden = !keys.length;
        keys.forEach(key => {
            addBodyField(previous.get(key) || {key, value: '', disabled: false}, 'request-path-fields');
        });
    }
    urlInput.addEventListener('input', () => syncPathVariables());
    function decodeQuery(value) {
        try { return decodeURIComponent(value.replace(/\+/g, ' ')); } catch { return value; }
    }
    function syncParams(fields = null) {
        const query = urlInput.value.split('#')[0].split('?').slice(1).join('?');
        const values = fields || query.split('&').filter(Boolean).map(pair => {
            const separator = pair.indexOf('=');
            return {key: decodeQuery(separator < 0 ? pair : pair.slice(0, separator)), value: decodeQuery(separator < 0 ? '' : pair.slice(separator + 1))};
        });
        element('request-param-fields').replaceChildren();
        values.forEach(field => addBodyField(field, 'request-param-fields'));
    }
    function updateParamUrl() {
        const encode = value => encodeURIComponent(value).replace(/%7B%7B(.*?)%7D%7D/g, (_, key) => '{{' + decodeURIComponent(key) + '}}');
        const query = Array.from(element('request-param-fields').children).flatMap(row => {
            const [active, key, value] = row.querySelectorAll('input');
            return active.checked && key.value ? [encode(key.value) + '=' + encode(value.value)] : [];
        }).join('&');
        const [beforeHash, ...fragment] = urlInput.value.split('#');
        urlInput.value = beforeHash.split('?')[0] + (query ? '?' + query : '') + (fragment.length ? '#' + fragment.join('#') : '');
        highlightUrl();
    }
    urlInput.addEventListener('input', () => syncParams());
    element('request-param-fields').addEventListener('input', updateParamUrl);
    element('request-param-add').onclick = () => addBodyField(undefined, 'request-param-fields');
    const key = 'infinite.collections.' + page.dataset.scope;
    const tree = element('collection-tree');
    let collections = [], collection = null, selected = null, parent = null, sending = null;
    const notify = text => { element('collection-status').hidden = false; element('collection-status').textContent = text; };
    let storage;
    const persist = () => storage.save(collections);
    try {
        storage = await createRestStorage(page);
        if (storage.data !== null) {
            collections = storage.data;
        } else {
            const legacy = await new Promise((resolve, reject) => {
                const request = indexedDB.open('infinite-restapi', 1);
                request.onupgradeneeded = () => request.result.createObjectStore('collections');
                request.onerror = () => reject(request.error);
                request.onsuccess = () => {
                    const db = request.result;
                    const tx = db.transaction('collections', 'readonly');
                    const read = tx.objectStore('collections').get(key);
                    tx.oncomplete = () => { db.close(); resolve(read.result); };
                    tx.onerror = () => { db.close(); reject(tx.error); };
                };
            });
            collections = legacy || JSON.parse(localStorage.getItem(key) || '[]');
            if (collections.length) await persist();
        }
        if (!Array.isArray(collections)) throw Error('Data collection tidak valid.');
    } catch (error) {
        notify(error.message);
        page.querySelectorAll('button, input, select, textarea').forEach(control => control.disabled = true);
        return;
    }
    try {
        const response = await fetch(page.dataset.storageUrl.replace('/collection/', '/environment/'), {headers: {Accept: 'application/json'}});
        if (!response.ok) throw Error('Environment belum dapat dimuat.');
        environmentData = (await response.json()).data || {};
    } catch (error) { notify(error.message); }
    let modalEnvironmentStorage, modalEnvironmentData, modalEnvironmentId;
    element('collection-environment-modal').addEventListener('show.bs.modal', async () => {
        const body = element('collection-environment-values');
        const status = element('collection-environment-status');
        body.replaceChildren();
        element('save-modal-environment').disabled = true;
        element('add-modal-environment-variable').disabled = true;
        status.className = 'alert alert-warning';
        status.hidden = false;
        status.textContent = 'Memuat environment…';
        const environmentId = element('request-environment').value;
        try {
            modalEnvironmentStorage = await createRestStorage({dataset: {...page.dataset, storageUrl: page.dataset.storageUrl.replace('/collection/', '/environment/')}});
            modalEnvironmentData = modalEnvironmentStorage.data;
            modalEnvironmentId = environmentId;
            environmentData = modalEnvironmentData || {};
            refreshVariableColors();
            const env = (environmentData.environments || []).find(item => item.id === environmentId);
            element('collection-environment-title').textContent = env ? 'Environment · ' + env.name : 'Environment';
            status.textContent = env ? (env.values.length ? '' : 'Belum ada variabel pada environment ini.') : 'Pilih environment pada toolbar terlebih dahulu.';
            status.hidden = Boolean(env?.values.length);
            element('save-modal-environment').disabled = !env;
            element('add-modal-environment-variable').disabled = !env;
            for (const variable of env?.values || []) {
                const row = document.createElement('tr');
                const activeCell = document.createElement('td');
                const active = document.createElement('input');
                active.type = 'checkbox';
                active.className = 'form-check-input';
                active.checked = variable.enabled !== false;
                active.disabled = true;
                active.setAttribute('aria-label', 'Variabel aktif');
                activeCell.append(active);
                row.append(activeCell);
                for (const text of [variable.key, variable.type === 'secret' ? 'Secret' : 'Text']) {
                    const cell = document.createElement('td');
                    cell.className = 'text-break';
                    cell.textContent = text;
                    row.append(cell);
                }
                const cell = document.createElement('td');
                cell.className = 'text-break';
                const value = document.createElement('input');
                value.className = 'form-control';
                value.type = variable.type === 'secret' ? 'password' : 'text';
                value.value = variable.value;
                value.autocomplete = 'off';
                value.setAttribute('aria-label', 'Nilai ' + variable.key);
                row.environmentValue = value;
                cell.append(value);
                if (variable.type === 'secret') {
                    const toggle = document.createElement('button');
                    toggle.type = 'button'; toggle.className = 'btn btn-sm btn-light-primary ms-2';
                    toggle.textContent = 'Lihat';
                    let visible = false;
                    toggle.onclick = () => {
                        visible = !visible;
                        value.type = visible ? 'text' : 'password';
                        toggle.textContent = visible ? 'Sembunyikan' : 'Lihat';
                    };
                    cell.append(toggle);
                }
                row.append(cell); body.append(row);
            }
        } catch (error) {
            status.hidden = false; status.className = 'alert alert-danger'; status.textContent = error.message;
        }
    });
    element('add-modal-environment-variable').onclick = () => {
        const row = document.createElement('tr');
        const activeCell = document.createElement('td');
        const active = document.createElement('input');
        active.type = 'checkbox'; active.className = 'form-check-input'; active.checked = true;
        active.setAttribute('aria-label', 'Variabel aktif');
        activeCell.append(active);
        const keyCell = document.createElement('td');
        const key = document.createElement('input');
        key.className = 'form-control'; key.placeholder = 'Nama variabel'; key.setAttribute('aria-label', 'Nama variabel baru');
        keyCell.append(key);
        const typeCell = document.createElement('td');
        const type = document.createElement('select');
        type.className = 'form-select'; type.setAttribute('aria-label', 'Tipe variabel');
        type.add(new Option('Text', 'default')); type.add(new Option('Secret', 'secret'));
        typeCell.append(type);
        const valueCell = document.createElement('td');
        const value = document.createElement('input');
        value.className = 'form-control'; value.autocomplete = 'off'; value.setAttribute('aria-label', 'Nilai variabel baru');
        const remove = document.createElement('button');
        remove.type = 'button'; remove.className = 'btn btn-sm btn-light-danger mt-2'; remove.textContent = 'Hapus';
        remove.onclick = () => row.remove();
        type.onchange = () => { value.type = type.value === 'secret' ? 'password' : 'text'; };
        valueCell.append(value, remove);
        row.append(activeCell, keyCell, typeCell, valueCell);
        row.environmentValue = value;
        row.newVariable = {key, type, active};
        element('collection-environment-values').append(row);
        key.focus();
    };
    element('save-modal-environment').onclick = async () => {
        const button = element('save-modal-environment');
        const status = element('collection-environment-status');
        const env = modalEnvironmentData?.environments?.find(item => item.id === modalEnvironmentId);
        if (!env) return;
        button.disabled = true;
        button.textContent = 'Menyimpan…';
        try {
            const data = structuredClone(modalEnvironmentData);
            const target = data.environments.find(item => item.id === modalEnvironmentId);
            Array.from(element('collection-environment-values').rows).forEach((row, index) => {
                if (row.newVariable) {
                    const {key, type, active} = row.newVariable;
                    const name = key.value.trim();
                    if (!name || target.values.some(variable => variable.key === name)) throw Error('Isi nama variabel yang unik.');
                    target.values.push({key: name, value: row.environmentValue.value, type: type.value, enabled: active.checked});
                } else {
                    target.values[index].value = row.environmentValue.value;
                }
            });
            await modalEnvironmentStorage.save(data);
            element('collection-environment-values').querySelectorAll('tr').forEach(row => {
                if (row.newVariable) {
                    row.newVariable.key.disabled = true;
                    row.newVariable.type.disabled = true;
                    row.newVariable.active.disabled = true;
                    row.querySelector('button')?.remove();
                }
                delete row.newVariable;
            });
            modalEnvironmentData = data;
            environmentData = data;
            refreshVariableColors();
            status.className = 'alert alert-success';
            status.textContent = 'Environment berhasil disimpan.';
            status.hidden = false;
        } catch (error) {
            status.className = 'alert alert-danger';
            status.textContent = error.message;
            status.hidden = false;
        } finally {
            button.disabled = false;
            button.textContent = 'Simpan';
        }
    };
    const variableTooltip = document.createElement('div');
    variableTooltip.className = 'collection-variable-tooltip';
    variableTooltip.setAttribute('role', 'tooltip');
    variableTooltip.id = 'collection-variable-tooltip';
    variableTooltip.hidden = true;
    document.body.append(variableTooltip);
    let tooltipInput = null;
    function hideVariableTooltip() {
        variableTooltip.hidden = true;
        tooltipInput?.removeAttribute('aria-describedby');
        tooltipInput = null;
    }
    page.addEventListener('mousemove', event => {
        const input = event.target.closest('input, textarea');
        const editor = input?.closest('.collection-url, .collection-variable-editor');
        if (!editor) { hideVariableTooltip(); return; }
        const variable = Array.from(editor.querySelectorAll('.collection-url-variable')).find(span =>
            Array.from(span.getClientRects()).some(rect =>
                event.clientX >= rect.left && event.clientX <= rect.right &&
                event.clientY >= rect.top && event.clientY <= rect.bottom));
        if (!variable) { hideVariableTooltip(); return; }
        const key = variable.textContent.slice(2, -2);
        const env = (environmentData.environments || []).find(item => item.id === element('request-environment').value);
        const value = (env?.values || []).find(item => item.key === key && item.enabled !== false)
            || (collection?.variable || []).find(item => item.key === key);
        variableTooltip.textContent = key + ' :: ' + (value ? String(value.value ?? '') : 'Variabel belum tersedia');
        hideVariableTooltip();
        tooltipInput = input;
        input.setAttribute('aria-describedby', variableTooltip.id);
        variableTooltip.hidden = false;
        variableTooltip.style.left = '0px';
        variableTooltip.style.top = '0px';
        const rect = variableTooltip.getBoundingClientRect();
        variableTooltip.style.left = Math.max(8, Math.min(event.clientX + 12, window.innerWidth - rect.width - 8)) + 'px';
        variableTooltip.style.top = Math.max(8, Math.min(event.clientY + 18, window.innerHeight - rect.height - 8)) + 'px';
    });
    page.addEventListener('mouseleave', hideVariableTooltip);
    page.addEventListener('input', hideVariableTooltip);
    page.addEventListener('click', hideVariableTooltip);
    document.addEventListener('scroll', hideVariableTooltip, true);
    document.addEventListener('keydown', event => { if (event.key === 'Escape') hideVariableTooltip(); });
    element('request-environment').addEventListener('change', () => {
        hideVariableTooltip();
        refreshVariableColors();
    });
    function normalize(items) {
        if (!Array.isArray(items)) throw Error('Daftar item collection tidak valid.');
        items.forEach(item => {
            if (!item || typeof item.name !== 'string') throw Error('Nama item tidak valid.');
            if (item.item) normalize(item.item);
            else if (!item.request || typeof item.request !== 'object') throw Error('Request tidak valid.');
        });
    }
    const expandedFolders = new WeakMap();
    function render() {
        for (let index = requestTabs.length - 1; index >= 0; index--) {
            const tab = requestTabs[index];
            if (!collections.includes(tab.owner) || !tab.items.includes(tab.item)) requestTabs.splice(index, 1);
        }
        renderRequestTabs();
        const scroll = tree.scrollTop;
        tree.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(toggle => bootstrap.Dropdown.getInstance(toggle)?.dispose());
        tree.replaceChildren();
        const search = element('collection-search').value.trim().toLowerCase();
        function label(text, className) {
            const span = document.createElement('span');
            span.className = className;
            span.textContent = text;
            return span;
        }
        function icon(className) {
            const node = document.createElement('i');
            node.className = 'bi ' + className;
            node.setAttribute('aria-hidden', 'true');
            return node;
        }
        function nodes(items, container, owner, inheritedMatch = false) {
            items.forEach(item => {
                const matches = inheritedMatch || (search && item.name.toLowerCase().includes(search));
                if (item.item) {
                    const child = document.createElement('div');
                    child.className = 'collection-tree-children';
                    nodes(item.item, child, owner, matches);
                    if (!child.childElementCount) return;
                    const details = document.createElement('details');
                    details.className = 'collection-tree-folder';
                    details.open = Boolean(search) || expandedFolders.get(item) === true;
                    const summary = document.createElement('summary');
                    summary.append(icon('bi-chevron-right collection-tree-chevron'), icon('bi-folder2'), label(item.name, 'collection-tree-label'));
                    details.append(summary, child);
                    details.addEventListener('toggle', () => {
                        if (!search) expandedFolders.set(item, details.open);
                    });
                    container.append(details);
                } else if (!search || matches) {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'collection-tree-request' + (selected === item ? ' is-active' : '');
                    if (selected === item) button.setAttribute('aria-current', 'true');
                    const method = item.request.method || 'GET';
                    const badge = label(method, 'collection-tree-method');
                    badge.dataset.method = method;
                    button.append(badge, label(item.name, 'collection-tree-label'));
                    button.title = item.name;
                    button.onclick = () => {
                        open(owner, item, items);
                    };
                    const row = document.createElement('div');
                    row.className = 'collection-tree-request-row d-flex align-items-center gap-1';
                    button.classList.add('flex-grow-1');
                    const actions = document.createElement('div');
                    actions.className = 'btn-group flex-shrink-0';
                    const toggle = document.createElement('button');
                    toggle.type = 'button';
                    toggle.className = 'btn btn-sm btn-icon btn-light-primary';
                    toggle.dataset.bsToggle = 'dropdown';
                    toggle.setAttribute('aria-expanded', 'false');
                    toggle.setAttribute('aria-label', 'Pengaturan ' + item.name);
                    toggle.append(icon('bi-three-dots-vertical'));
                    const menu = document.createElement('div');
                    menu.className = 'dropdown-menu dropdown-menu-end';
                    const duplicate = document.createElement('button');
                    duplicate.type = 'button'; duplicate.className = 'dropdown-item';
                    duplicate.append(icon('bi-copy'), document.createTextNode('Duplicate'));
                    duplicate.onclick = () => duplicateRequest(owner, item, items, toggle);
                    const rename = document.createElement('button');
                    rename.type = 'button'; rename.className = 'dropdown-item';
                    rename.append(icon('bi-pencil'), document.createTextNode('Rename'));
                    rename.onclick = () => renameRequest(owner, item, items, toggle);
                    const remove = document.createElement('button');
                    remove.type = 'button'; remove.className = 'dropdown-item text-danger';
                    remove.append(icon('bi-trash'), document.createTextNode('Delete'));
                    remove.onclick = () => deleteRequest(owner, item, items, toggle);
                    menu.append(duplicate, rename, remove);
                    actions.append(toggle, menu);
                    row.append(button, actions);
                    new bootstrap.Dropdown(toggle, {popperConfig: options => ({...options, strategy: 'fixed'})});
                    container.append(row);
                }
            });
        }
        collections.forEach(owner => {
            const section = document.createElement('div');
            section.className = 'collection-tree-group';
            const child = document.createElement('div');
            nodes(owner.item, child, owner, Boolean(search && owner.info.name.toLowerCase().includes(search)));
            if (search && !child.childElementCount) return;
            const title = document.createElement('button');
            title.type = 'button';
            title.className = 'collection-tree-title' + (collection === owner ? ' is-active' : '');
            title.append(icon('bi-collection'), label(owner.info.name, 'collection-tree-label'));
            title.onclick = () => {
                collection = owner; selected = null; parent = null; fill(); render();
            };
            const heading = document.createElement('div');
            heading.className = 'd-flex align-items-center gap-1 mb-2';
            title.classList.add('mb-0');
            const group = document.createElement('div');
            group.className = 'btn-group flex-shrink-0';
            const settings = document.createElement('button');
            settings.type = 'button';
            settings.className = 'btn btn-sm btn-icon btn-light-primary';
            settings.dataset.bsToggle = 'dropdown';
            settings.setAttribute('aria-expanded', 'false');
            settings.setAttribute('aria-label', 'Pengaturan ' + owner.info.name);
            settings.append(icon('bi-gear'));
            const menu = document.createElement('div');
            menu.className = 'dropdown-menu dropdown-menu-end';
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'dropdown-item text-danger';
            remove.append(icon('bi-trash'), document.createTextNode('Delete'));
            remove.onclick = () => deleteCollection(owner, settings);
            menu.append(remove);
            group.append(settings, menu);
            heading.append(title, group);
            section.append(heading, child);
            new bootstrap.Dropdown(settings, {
                popperConfig: options => ({...options, strategy: 'fixed'})
            });
            tree.append(section);
        });
        if (!tree.childElementCount) {
            const empty = document.createElement('p');
            empty.className = 'text-muted text-center py-6 mb-0';
            empty.textContent = search ? 'Request tidak ditemukan.' : 'Tambah atau impor collection untuk mulai.';
            tree.append(empty);
        }
        tree.scrollTop = scroll;
    }
    function highlightBodyField(input) {
        const wrapper = document.createElement('div');
        wrapper.className = 'collection-variable-editor';
        if (input.tagName === 'TEXTAREA') wrapper.classList.add('is-multiline');
        input.replaceWith(wrapper);
        const mirror = document.createElement('div');
        mirror.className = 'collection-variable-mirror';
        mirror.setAttribute('aria-hidden', 'true');
        wrapper.append(mirror, input);
        let gutter, lineNumbers;
        if (input.id === 'request-body') {
            wrapper.classList.add('has-line-numbers');
            input.wrap = 'off';
            gutter = document.createElement('div');
            gutter.className = 'collection-editor-gutter';
            gutter.setAttribute('aria-hidden', 'true');
            lineNumbers = document.createElement('div');
            gutter.append(lineNumbers);
            wrapper.append(gutter);
        }
        function sync() {
            if (input.tagName === 'TEXTAREA') {
                const style = getComputedStyle(input);
                for (const property of ['fontFamily', 'fontSize', 'fontWeight', 'fontStyle', 'lineHeight', 'letterSpacing', 'wordSpacing', 'tabSize', 'paddingTop', 'paddingBottom', 'paddingLeft', 'paddingRight', 'textIndent', 'textAlign']) {
                    mirror.style[property] = style[property];
                }
                const border = parseFloat(style.borderLeftWidth) + parseFloat(style.borderRightWidth);
                mirror.style.width = (input.clientWidth + border) + 'px';
                if (lineNumbers) {
                    lineNumbers.style.font = style.font;
                    lineNumbers.style.lineHeight = style.lineHeight;
                    lineNumbers.style.paddingTop = style.paddingTop;
                    lineNumbers.style.paddingBottom = style.paddingBottom;
                    lineNumbers.textContent = Array.from({length: input.value.split(String.fromCharCode(10)).length}, (_, index) => index + 1).join(String.fromCharCode(10));
                    lineNumbers.style.transform = 'translateY(-' + input.scrollTop + 'px)';
                }
            }
            mirror.replaceChildren();
            function appendVariables(parent, text) {
                let offset = 0;
                for (const match of text.matchAll(/{{[^{}]+}}/g)) {
                    parent.append(document.createTextNode(text.slice(offset, match.index)));
                    const variable = document.createElement('span');
                    variable.className = 'collection-url-variable' + (variableAvailable(match[0].slice(2, -2)) ? '' : ' is-missing');
                    variable.textContent = match[0];
                    parent.append(variable);
                    offset = match.index + match[0].length;
                }
                parent.append(document.createTextNode(text.slice(offset)));
            }
            if (input.id === 'request-body') {
                const tokens = /"(?:\\.|[^"\\])*"(?:\s*:)?|\b(?:true|false|null)\b|-?\d+(?:\.\d+)?(?:[eE][+-]?\d+)?/g;
                let offset = 0;
                for (const match of input.value.matchAll(tokens)) {
                    appendVariables(mirror, input.value.slice(offset, match.index));
                    const token = match[0];
                    const span = document.createElement('span');
                    const type = token.startsWith('"') ? (token.endsWith(':') ? 'key' : 'string')
                        : token === 'null' ? 'null' : /^(true|false)$/.test(token) ? 'boolean' : 'number';
                    span.className = 'collection-json-' + type;
                    appendVariables(span, token);
                    mirror.append(span);
                    offset = match.index + token.length;
                }
                appendVariables(mirror, input.value.slice(offset));
                mirror.append(document.createTextNode(String.fromCharCode(10)));
            } else {
                appendVariables(mirror, input.value);
            }
            mirror.scrollTop = input.scrollTop;
            mirror.scrollLeft = input.scrollLeft;
        }
        input.addEventListener('input', sync);
        input.addEventListener('scroll', sync);
        if (input.tagName === 'TEXTAREA') {
            new ResizeObserver(sync).observe(input);
        }
        sync();
        return {wrapper, sync};
    }
    const rawBodyEditor = highlightBodyField(element('request-body'));
    function addBodyField(field = {key: '', value: '', disabled: false}, containerId = 'request-body-fields') {
        const row = document.createElement('div');
        row.className = 'd-flex gap-2 align-items-center mb-2';
        const enabled = document.createElement('input');
        enabled.type = 'checkbox'; enabled.className = 'form-check-input';
        enabled.checked = !field.disabled; enabled.setAttribute('aria-label', 'Parameter aktif');
        const key = document.createElement('input');
        key.className = 'form-control'; key.value = field.key || ''; key.placeholder = 'Key'; key.setAttribute('aria-label', 'Key parameter');
        const value = document.createElement('input');
        value.className = 'form-control'; value.value = field.value || ''; value.placeholder = 'Value'; value.setAttribute('aria-label', 'Value parameter');
        const remove = document.createElement('button');
        remove.type = 'button'; remove.className = 'btn btn-sm btn-light-danger'; remove.textContent = 'Hapus';
        remove.onclick = () => { row.remove(); if (containerId === 'request-param-fields') updateParamUrl(); };
        row.append(enabled, key, value, remove);
        highlightBodyField(key);
        highlightBodyField(value);
        row.bodyField = field;
        element(containerId).append(row);
    }
    function readBody() {
        const mode = element('request-body-mode').value;
        if (!mode) throw Error('Pilih mode body yang didukung sebelum menyimpan atau mengirim.');
        if (mode === 'none') return undefined;
        if (mode === 'urlencoded') return {mode, urlencoded: Array.from(element('request-body-fields').children, row => {
            const [enabled, key, value] = row.querySelectorAll('input');
            return {...row.bodyField, key: key.value, value: value.value, disabled: !enabled.checked};
        })};
        return {...(selected?.request.body || {}), mode: 'raw', raw: element('request-body').value};
    }
    function updateBodyMode() {
        const mode = element('request-body-mode').value;
        rawBodyEditor.wrapper.hidden = mode !== 'raw';
        element('request-form-body').hidden = mode !== 'urlencoded';
    }
    element('request-body-mode').onchange = () => { updateBodyMode(); };
    element('request-body-add').onclick = () => { addBodyField(); };
    let requestGeneration = 0;
    let switchAnimation;
    function fill() {
        requestGeneration++;
        sending?.abort();
        sending = null;
        element('request-cancel').disabled = true;
        element('request-send').removeAttribute('aria-busy');
        element('request-send-label').hidden = false;
        element('request-send-progress').hidden = true;
        element('response-body').replaceChildren();
        element('response-headers').replaceChildren();
        element('response-meta').textContent = selected ? 'Belum ada respons.' : 'Pilih request untuk mulai.';
        element('response-meta').dataset.tone = 'waiting';
        element('collection-status').hidden = true;
        hideVariableTooltip();
        element('response-body').scrollTop = 0;
        page.querySelector('.collection-response-headers').scrollTop = 0;
        switchAnimation?.cancel();
        if (selected && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            switchAnimation = element('collection-request-panel').animate([
                {opacity: .45, transform: 'translateY(6px)'},
                {opacity: 1, transform: 'translateY(0)'}
            ], {duration: 240, easing: 'ease-out'});
        }
        const request = selected?.request || {};
        element('request-method').value = request.method || 'GET';
        let url = typeof request.url === 'string' ? request.url : request.url?.raw || '';
        element('request-url').value = url;
        highlightUrl();
        syncParams(typeof request.url === 'object' ? request.url.query : null);
        syncPathVariables(typeof request.url === 'object' ? request.url.variable || [] : []);
        updateParamUrl();
        const scripts = [...(collection?.event || []), ...(selected?.event || [])];
        for (const [id, type] of [['request-prerequest', 'prerequest'], ['request-tests', 'test']]) {
            element(id).value = scripts.filter(event => event.listen === type).map(event => {
                const exec = event.script?.exec;
                return Array.isArray(exec) ? exec.join('\n') : exec || '';
            }).filter(script => script.trim()).join('\n\n');
        }
        const headers = {};
        (request.header || []).filter(header => !header.disabled).forEach(header => { headers[header.key] = header.value; });
        element('request-headers').value = JSON.stringify(headers, null, 2);
        const body = request.body || {};
        element('request-body').value = body.raw || '';
        rawBodyEditor.sync();
        element('request-body-mode').value = body.mode || 'none';
        element('request-body-fields').replaceChildren();
        (body.urlencoded || []).forEach(field => addBodyField(field));
        updateBodyMode();
        const auth = request.auth || collection?.auth;
        element('request-token').value = auth?.type === 'bearer' ? auth.bearer?.find(value => value.key === 'token')?.value || '' : '';
        element('request-send').disabled = !selected;
        element('request-save-toggle').disabled = !selected;
        if (body.mode && !['raw','urlencoded'].includes(body.mode)) notify('Tipe body ini perlu dikonfigurasi menjadi raw sebelum dikirim.');
        if (auth && !['bearer','noauth'].includes(auth.type)) notify('Atur token atau headers untuk autentikasi request ini.');
    }
    const requestTabs = [];
    const drafts = new WeakMap();
    function captureRequest() {
        if (!selected) return;
        const fields = {};
        for (const id of ['request-method', 'request-url', 'request-headers', 'request-body-mode', 'request-body', 'request-token']) fields[id] = element(id).value;
        const pairs = id => Array.from(element(id).children, row => {
            const [active, key, value] = row.querySelectorAll('input');
            return {...row.bodyField, key: key.value, value: value.value, disabled: !active.checked};
        });
        drafts.set(selected, {
            fields, query: pairs('request-param-fields'), path: readPathVariables(),
            body: pairs('request-body-fields'),
            response: Array.from(element('response-body').childNodes, node => node.cloneNode(true)),
            headers: Array.from(element('response-headers').childNodes, node => node.cloneNode(true)),
            meta: sending ? 'Request dibatalkan saat berpindah tab.' : element('response-meta').textContent, tone: sending ? 'waiting' : element('response-meta').dataset.tone
        });
    }
    function renderRequestTabs() {
        const container = element('collection-request-tabs');
        container.replaceChildren();
        for (const tab of requestTabs) {
            const group = document.createElement('div');
            group.className = 'collection-open-tab d-flex align-items-center flex-shrink-0' + (tab.item === selected ? ' is-active' : '');
            const button = document.createElement('button');
            button.type = 'button'; button.className = 'btn btn-sm';
            button.textContent = tab.item.name;
            button.setAttribute('role', 'tab');
            button.setAttribute('aria-selected', String(tab.item === selected));
            button.onclick = () => open(tab.owner, tab.item, tab.items);
            const close = document.createElement('button');
            close.type = 'button'; close.className = 'btn btn-sm btn-icon';
            close.textContent = '×'; close.setAttribute('aria-label', 'Tutup ' + tab.item.name);
            close.onclick = () => {
                const index = requestTabs.indexOf(tab);
                requestTabs.splice(index, 1); drafts.delete(tab.item);
                if (selected === tab.item) {
                    selected = null;
                    const next = requestTabs[Math.min(index, requestTabs.length - 1)];
                    if (next) open(next.owner, next.item, next.items);
                    else { parent = null; fill(); render(); }
                }
                renderRequestTabs();
            };
            group.append(button, close); container.append(group);
        }
    }
    function open(owner, item, items) {
        if (selected === item) return;
        captureRequest();
        collection = owner; selected = item; parent = items;
        if (!requestTabs.some(tab => tab.item === item)) requestTabs.push({owner, item, items});
        fill();
        const draft = drafts.get(item);
        if (draft) {
            Object.entries(draft.fields).forEach(([id, value]) => { element(id).value = value; });
            syncParams(draft.query); syncPathVariables(draft.path);
            element('request-body-fields').replaceChildren();
            draft.body.forEach(field => addBodyField(field));
            updateBodyMode(); highlightUrl(); rawBodyEditor.sync();
            element('response-body').replaceChildren(...draft.response.map(node => node.cloneNode(true)));
            element('response-headers').replaceChildren(...draft.headers.map(node => node.cloneNode(true)));
            element('response-meta').textContent = draft.meta;
            element('response-meta').dataset.tone = draft.tone;
        }
        render(); renderRequestTabs();
    }
    function environments() { return environmentData; }
    function loadEnvironments() {
        const data = environments(), select = element('request-environment');
        select.replaceChildren(new Option('Tanpa environment', ''));
        (data.environments || []).forEach(item => select.add(new Option(item.name, item.id)));
        select.value = data.active || '';
    }
    element('collection-search').oninput = render;
    async function deleteCollection(target, button) {
        if (!target) return;
        const answer = await Swal.fire({
            title: 'Hapus collection?',
            text: target.info.name + ' beserta seluruh folder dan request akan dihapus dari organisasi ini.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Hapus collection',
            cancelButtonText: 'Batal',
            buttonsStyling: false,
            customClass: {confirmButton: 'btn btn-danger', cancelButton: 'btn btn-light ms-3'}
        });
        if (!answer.isConfirmed) return;
        button.disabled = true;
        element('collection-new').disabled = true;
        element('collection-import').disabled = true;
        try {
            const remaining = collections.filter(item => item !== target);
            await storage.save(remaining);
            collections = remaining;
            if (collection === target) {
                collection = null; selected = null; parent = null;
                fill();
            }
            render();
            notify('Collection berhasil dihapus.');
        } catch (error) {
            notify('Collection belum dihapus. ' + error.message);
        } finally {
            button.disabled = false;
            element('collection-new').disabled = false;
            element('collection-import').disabled = false;
        }
    };
    element('collection-new').onclick = async () => {
        const name = prompt('Nama collection'); if (!name?.trim()) return;
        const owner = {info: {name: name.trim(), schema: 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json'}, item: []};
        collections.push(owner);
        try { await persist(); collection = owner; selected = null; fill(); render(); } catch (error) { collections.pop(); notify(error.message); }
    };
    element('collection-import').onchange = async event => {
        const file = event.target.files[0];
        try {
            if (!file) return;
            if (file.size > 10 * 1024 * 1024) throw Error('Ukuran maksimal 10 MB.');
            const owner = JSON.parse(await file.text());
            if (!owner.info?.name) throw Error('Format collection Postman tidak valid.');
            normalize(owner.item); collections.push(owner);
            try { await persist(); } catch (error) { collections.pop(); throw error; }
            collection = owner; selected = null; fill(); render(); notify('Collection berhasil diimpor.');
        } catch (error) { notify(error.message); } finally { event.target.value = ''; }
    };
    const responseBody = element('response-body');
    const responseLines = element('response-line-numbers');
    function syncResponseLines() {
        responseLines.textContent = Array.from({length: responseBody.textContent.split(String.fromCharCode(10)).length}, (_, index) => index + 1).join(String.fromCharCode(10));
        responseLines.style.transform = 'translateY(-' + responseBody.scrollTop + 'px)';
    }
    responseBody.addEventListener('scroll', syncResponseLines);
    new MutationObserver(syncResponseLines).observe(responseBody, {childList: true, characterData: true, subtree: true});
    syncResponseLines();
    function renderJsonResponse(value) {
        const output = element('response-body');
        const formatted = JSON.stringify(value, null, 2);
        output.replaceChildren();
        const tokens = /"(?:\\.|[^"\\])*"(?:\s*:)?|\b(?:true|false|null)\b|-?\d+(?:\.\d+)?(?:[eE][+-]?\d+)?/g;
        let offset = 0;
        for (const match of formatted.matchAll(tokens)) {
            output.append(document.createTextNode(formatted.slice(offset, match.index)));
            const span = document.createElement('span');
            const token = match[0];
            const type = token.startsWith('"') ? (token.endsWith(':') ? 'key' : 'string')
                : token === 'null' ? 'null' : /^(true|false)$/.test(token) ? 'boolean' : 'number';
            span.className = 'collection-json-' + type;
            span.textContent = token;
            output.append(span);
            offset = match.index + token.length;
        }
        output.append(document.createTextNode(formatted.slice(offset)));
    }
    async function runAfterResponse(script, text, environmentId) {
        if (!script.trim()) return;
        const declarations = new Map();
        const updates = [];
        for (const statement of script.split(';').map(value => value.trim()).filter(Boolean)) {
            const declaration = statement.match(/^(?:var|let|const)\s+([A-Za-z_$][\w$]*)\s*=\s*pm\.response\.json\(\s*\)$/);
            if (declaration) {
                declarations.set(declaration[1], JSON.parse(text));
                continue;
            }
            const setter = statement.match(/^pm\.environment\.set\(\s*["']([^"']+)["']\s*,\s*([A-Za-z_$][\w$]*)(\.[A-Za-z_$][\w$]*)*\s*\)$/);
            if (!setter) throw Error('After Response memuat perintah yang belum didukung.');
            const expression = statement.slice(statement.indexOf(',') + 1, statement.lastIndexOf(')')).trim().split('.');
            let value = declarations.get(expression.shift());
            for (const property of expression) {
                if (value === null || typeof value !== 'object' || !Object.hasOwn(value, property)) throw Error('Properti response untuk environment tidak tersedia.');
                value = value[property];
            }
            if (value === undefined) throw Error('Nilai response untuk environment tidak tersedia.');
            updates.push({key: setter[1], value: typeof value === 'object' ? JSON.stringify(value) : String(value)});
        }
        if (!updates.length) return;
        if (!environmentId) throw Error('Pilih environment untuk menyimpan hasil After Response.');
        const context = {dataset: {...page.dataset, storageUrl: page.dataset.storageUrl.replace('/collection/', '/environment/')}};
        const envStorage = await createRestStorage(context);
        const data = envStorage.data;
        const env = data?.environments?.find(item => item.id === environmentId);
        if (!env) throw Error('Environment tujuan tidak tersedia.');
        for (const update of updates) {
            const existing = env.values.find(item => item.key === update.key);
            if (existing) existing.value = update.value;
            else env.values.push({...update, type: 'default', enabled: true});
        }
        await envStorage.save(data);
        page.dataset.csrfHash = context.dataset.csrfHash;
        environmentData = data;
        refreshVariableColors();
    }
    function buildRequest() {
    const variables = new Map((collection.variable || []).map(value => [value.key, value.value]));
    const env = (environments().environments || []).find(item => item.id === element('request-environment').value);
    (env?.values || []).filter(value => value.enabled !== false).forEach(value => variables.set(value.key, value.value));
    const resolve = text => String(text).replace(/{{([^{}]+)}}/g, (_, key) => {
        if (!variables.has(key)) throw Error('Variabel belum tersedia: ' + key + '. Pilih environment dan periksa nama variabel, termasuk huruf besar/kecil.');
        return variables.get(key);
    });
    const pathValues = new Map(readPathVariables().filter(item => !item.disabled).map(item => [item.key, item.value]));
    const pathUrl = element('request-url').value.replace(/(^|\/):([A-Za-z_][\w-]*)(?=\/|[?#]|$)/g, (_, prefix, key) => {
        const value = pathValues.get(key);
        if (!value) throw Error('Isi Path Variable: ' + key);
        return prefix + encodeURIComponent(resolve(value));
    });
    const url = new URL(resolve(pathUrl));
    if (!['https:', 'http:'].includes(url.protocol)) throw Error('Gunakan URL HTTP atau HTTPS.');
    const headers = new Headers(JSON.parse(resolve(element('request-headers').value || '{}')));
    const token = element('request-token').value;
    if (token) headers.set('Authorization', 'Bearer ' + resolve(token));
    const method = element('request-method').value;
    const bodyConfig = readBody();
    let body;
    if (bodyConfig?.mode === 'urlencoded') {
        body = new URLSearchParams();
        bodyConfig.urlencoded.filter(field => !field.disabled).forEach(field => {
            body.append(resolve(field.key), resolve(field.value));
        });
        if (!headers.has('Content-Type')) headers.set('Content-Type', 'application/x-www-form-urlencoded');
    } else if (bodyConfig?.mode === 'raw') {
        body = resolve(bodyConfig.raw);
        if (!headers.has('Content-Type') && bodyConfig.options?.raw?.language === 'json') headers.set('Content-Type', 'application/json');
    }
        return {url, headers, method, body};
    }
    element('request-snippet').onclick = () => {
        if (!selected) { notify('Pilih request terlebih dahulu.'); return; }
        try {
            const {url, headers, method, body} = buildRequest();
            const quote = value => {
                const slash = String.fromCharCode(92);
                return "'" + String(value).split(slash).join(slash + slash).split("'").join(slash + "'") + "'";
            };
            const options = [
                '    CURLOPT_URL => ' + quote(url.href),
                '    CURLOPT_RETURNTRANSFER => true',
                '    CURLOPT_FOLLOWLOCATION => false',
                '    CURLOPT_CONNECTTIMEOUT => 15',
                '    CURLOPT_TIMEOUT => 90',
                '    CURLOPT_CUSTOMREQUEST => ' + quote(method),
                '    CURLOPT_HTTPHEADER => [' + String.fromCharCode(10) + Array.from(headers, ([key, value]) => '        ' + quote(key + ': ' + value)).join(',' + String.fromCharCode(10)) + String.fromCharCode(10) + '    ]'
            ];
            if (method === 'HEAD') options.push('    CURLOPT_NOBODY => true');
            if (!['GET', 'HEAD'].includes(method) && body !== undefined) options.push('    CURLOPT_POSTFIELDS => ' + quote(body instanceof URLSearchParams ? body.toString() : body));
            element('request-snippet-code').value = ["<?php", "", "$curl = curl_init();", "curl_setopt_array($curl, ["] .concat([options.join("," + String.fromCharCode(10))], ["]);", "", "$response = curl_exec($curl);", "if ($response === false) {", "    $error = curl_error($curl);", "    curl_close($curl);", "    throw new RuntimeException($error);", "}", "$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);", "curl_close($curl);", "", "echo $response;", ""]).join(String.fromCharCode(10));
            element('request-snippet-copy').textContent = 'Salin';
            bootstrap.Modal.getOrCreateInstance(element('request-snippet-modal')).show();
        } catch (error) { notify(error.message); }
    };
    element('request-snippet-copy').onclick = async () => {
        const code = element('request-snippet-code');
        try {
            await navigator.clipboard.writeText(code.value);
            element('request-snippet-copy').textContent = 'Tersalin';
        } catch {
            code.focus(); code.select();
            element('request-snippet-copy').textContent = 'Tekan Ctrl/Cmd+C';
        }
    };
    let requestMutationBusy = false;
    async function deleteRequest(owner, target, siblings, button) {
        if (requestMutationBusy) return;
        const answer = await Swal.fire({
            title: 'Hapus request?', text: target.name,
            icon: 'warning', showCancelButton: true,
            confirmButtonText: 'Hapus', cancelButtonText: 'Batal',
            buttonsStyling: false,
            customClass: {confirmButton: 'btn btn-danger', cancelButton: 'btn btn-light ms-3'}
        });
        if (!answer.isConfirmed || requestMutationBusy) return;
        requestMutationBusy = true;
        button.disabled = true;
        try {
            const snapshot = structuredClone(collections);
            function remove(items, copies) {
                const index = items.indexOf(target);
                if (index >= 0) {
                    copies.splice(index, 1);
                    return;
                }
                items.forEach((item, position) => {
                    if (item.item) remove(item.item, copies[position].item);
                });
            }
            remove(owner.item, snapshot[collections.indexOf(owner)].item);
            await storage.save(snapshot);
            siblings.splice(siblings.indexOf(target), 1);
            if (selected === target) {
                selected = null; parent = null;
                fill();
            }
            render();
            notify('Request berhasil dihapus.');
        } catch (error) {
            notify('Request belum dihapus. ' + error.message);
        } finally {
            requestMutationBusy = false;
            button.disabled = false;
        }
    }
    async function renameRequest(owner, target, siblings, button) {
        if (requestMutationBusy) return;
        const answer = await Swal.fire({
            title: 'Rename request', input: 'text', inputValue: target.name,
            inputLabel: 'Nama request', showCancelButton: true,
            confirmButtonText: 'Simpan', cancelButtonText: 'Batal',
            buttonsStyling: false,
            customClass: {confirmButton: 'btn btn-primary', cancelButton: 'btn btn-light ms-3'},
            inputValidator: value => {
                if (!value.trim()) return 'Isi nama request.';
                if (siblings.some(item => item !== target && item.name === value.trim())) return 'Nama request sudah digunakan di folder ini.';
            }
        });
        if (!answer.isConfirmed || requestMutationBusy) return;
        requestMutationBusy = true;
        button.disabled = true;
        try {
            const name = answer.value.trim();
            const snapshot = structuredClone(collections);
            function rename(items, copies) {
                items.forEach((item, index) => {
                    if (item === target) copies[index].name = name;
                    else if (item.item) rename(item.item, copies[index].item);
                });
            }
            rename(owner.item, snapshot[collections.indexOf(owner)].item);
            await storage.save(snapshot);
            target.name = name;
            render();
            notify('Nama request berhasil disimpan.');
        } catch (error) {
            notify('Nama request belum disimpan. ' + error.message);
        } finally {
            requestMutationBusy = false;
            button.disabled = false;
        }
    }
    async function duplicateRequest(owner, target, siblings, button) {
        if (requestMutationBusy) return;
        requestMutationBusy = true;
        button.disabled = true;
        element('request-save').disabled = true;
        try {
            const duplicate = structuredClone(target);
            let suffix = 1;
            let name = target.name + ' (Copy)';
            while (siblings.some(item => item.name === name)) {
                suffix++;
                name = target.name + ' (Copy ' + suffix + ')';
            }
            duplicate.name = name;
            if (duplicate.id) duplicate.id = crypto.randomUUID();
            const snapshot = structuredClone(collections);
            function insert(items, copies) {
                const index = items.indexOf(target);
                if (index >= 0) {
                    copies.splice(index + 1, 0, duplicate);
                    return;
                }
                items.forEach((item, position) => {
                    if (item.item) insert(item.item, copies[position].item);
                });
            }
            insert(owner.item, snapshot[collections.indexOf(owner)].item);
            await storage.save(snapshot);
            siblings.splice(siblings.indexOf(target) + 1, 0, duplicate);
            open(owner, duplicate, siblings);
            notify('Request berhasil diduplikasi.');
        } catch (error) {
            notify('Request belum diduplikasi. ' + error.message);
        } finally {
            button.disabled = false;
            element('request-save').disabled = false;
            requestMutationBusy = false;
        }
    }
    element('request-format-json').onclick = () => {
        if (!selected) return;
        if (element('request-body-mode').value !== 'raw') {
            notify('Formatter JSON tersedia untuk Body raw.');
            return;
        }
        try {
            const input = element('request-body');
            const formatted = JSON.stringify(JSON.parse(input.value), null, 4);
            input.value = formatted;
            input.scrollTop = 0;
            input.scrollLeft = 0;
            rawBodyEditor.sync();
            bootstrap.Tab.getOrCreateInstance(element('request-tab-body')).show();
            element('collection-status').hidden = true;
        } catch (error) {
            notify('JSON belum valid. ' + error.message);
            bootstrap.Tab.getOrCreateInstance(element('request-tab-body')).show();
        }
    };
    element('request-save').onclick = async () => {
        if (!selected || requestMutationBusy) return;
        requestMutationBusy = true;
        const target = selected;
        const owner = collection;
        const button = element('request-save');
        button.disabled = true;
        try {
            const headers = JSON.parse(element('request-headers').value || '{}');
            if (!headers || Array.isArray(headers) || typeof headers !== 'object' || Object.values(headers).some(value => typeof value !== 'string')) {
                throw Error('Headers harus berupa JSON object dengan nilai teks.');
            }
            const raw = urlInput.value.trim();
            if (!raw) throw Error('Isi URL request.');
            const query = Array.from(element('request-param-fields').children, row => {
                const [enabled, key, value] = row.querySelectorAll('input');
                return {...row.bodyField, key: key.value, value: value.value, disabled: !enabled.checked};
            });
            const url = {raw, query, variable: readPathVariables()};
            const request = {...target.request, method: element('request-method').value, url,
                header: [
                    ...(target.request.header || []).filter(header => header.disabled),
                    ...Object.entries(headers).map(([key, value]) => ({key, value}))
                ], body: readBody()};
            const token = element('request-token').value;
            if (token) request.auth = {type: 'bearer', bearer: [{key: 'token', value: token, type: 'string'}]};
            else request.auth = {type: 'noauth'};
            const snapshot = structuredClone(collections);
            function replace(items, copies) {
                items.forEach((item, index) => {
                    if (item === target) copies[index].request = request;
                    else if (item.item) replace(item.item, copies[index].item);
                });
            }
            replace(owner.item, snapshot[collections.indexOf(owner)].item);
            await storage.save(snapshot);
            target.request = request;
            notify('Request berhasil disimpan.');
            render();
        } catch (error) {
            notify('Request belum disimpan. ' + error.message);
        } finally {
            button.disabled = false;
            requestMutationBusy = false;
        }
    };
    element('request-cancel').onclick = () => sending?.abort();
    element('request-send').onclick = async () => {
        if (sending || !selected) return;
        const generation = requestGeneration;
        try {
            const {url, headers, method, body} = buildRequest();
            sending = new AbortController();
            element('request-send').disabled = true; element('request-cancel').disabled = false;
            element('request-send').setAttribute('aria-busy', 'true');
            element('request-send-label').hidden = true;
            element('request-send-progress').hidden = false;
            element('response-meta').textContent = 'Mengirim request…';
            element('response-meta').dataset.tone = 'waiting';
            element('response-body').textContent = ''; element('response-headers').textContent = '';
            const afterResponseScript = element('request-tests').value;
            const targetEnvironment = element('request-environment').value;
            const start = performance.now();
            const transport = await fetch(page.dataset.storageUrl.replace('/storage', '/send'), {
                method: 'POST', credentials: 'same-origin', signal: sending.signal,
                headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': page.dataset.csrfHash},
                body: JSON.stringify({url: url.href, method, headers: Object.fromEntries(headers), body: body instanceof URLSearchParams ? body.toString() : body})
            });
            let payload;
            try { payload = await transport.json(); } catch { throw Error('Respons server Infinite tidak valid. Periksa sesi login.'); }
            if (!transport.ok) throw Error(payload.error || 'Pengiriman server gagal.');
            if (payload.csrfHash) page.dataset.csrfHash = payload.csrfHash;
            const response = {status: payload.status, statusText: '', ok: payload.status >= 200 && payload.status < 300, headers: new Headers(payload.headers)};
            const text = payload.body;
            if (generation !== requestGeneration) return;
            element('response-meta').textContent = response.status + ' ' + response.statusText + ' · ' + Math.round(performance.now() - start) + ' ms';
            element('response-meta').dataset.tone = response.ok ? 'success' : 'error';
            const headerRows = element('response-headers');
            headerRows.replaceChildren();
            response.headers.forEach((value, key) => {
                const row = document.createElement('tr');
                const name = document.createElement('th');
                name.scope = 'row';
                name.className = 'fw-semibold text-break';
                name.textContent = key;
                const content = document.createElement('td');
                content.className = 'text-break';
                content.textContent = value;
                row.append(name, content);
                headerRows.append(row);
            });
            try { renderJsonResponse(JSON.parse(text)); } catch { element('response-body').textContent = text; }
            if (response.ok) {
                try { await runAfterResponse(afterResponseScript, text, targetEnvironment); }
                catch (error) { if (generation === requestGeneration) notify('Response diterima, tetapi After Response gagal. ' + error.message); }
            }
        } catch (error) {
            if (generation !== requestGeneration) return;
            element('response-meta').textContent = error.name === 'AbortError' ? 'Request dibatalkan.' : 'Request gagal. ' + error.message + (error instanceof TypeError ? ' Periksa URL, izin CORS, dan koneksi.' : '');
            element('response-meta').dataset.tone = error.name === 'AbortError' ? 'waiting' : 'error';
        }
        finally {
            if (generation !== requestGeneration) return;
            sending = null;
            element('request-send').disabled = !selected;
            element('request-cancel').disabled = true;
            element('request-send').removeAttribute('aria-busy');
            element('request-send-label').hidden = false;
            element('request-send-progress').hidden = true;
        }
    };
    window.addEventListener('focus', loadEnvironments);
    loadEnvironments(); fill(); render();
})();
