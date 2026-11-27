window.createRestStorage = async function (page) {
    let revision = '';
    async function request(data) {
        const options = {headers: {'Accept': 'application/json'}, credentials: 'same-origin'};
        if (data !== undefined) {
            options.method = 'POST';
            options.headers['Content-Type'] = 'application/json';
            options.headers['X-CSRF-TOKEN'] = page.dataset.csrfHash;
            options.body = JSON.stringify({data, revision, [page.dataset.csrfName]: page.dataset.csrfHash});
            if (typeof CompressionStream !== 'undefined' && options.body.length > 1024 * 1024) {
                const stream = new Blob([options.body]).stream().pipeThrough(new CompressionStream('gzip'));
                options.body = await new Response(stream).blob();
                options.headers['Content-Type'] = 'application/octet-stream';
                options.headers['X-Workspace-Encoding'] = 'gzip';
            }
        }
        const response = await fetch(page.dataset.storageUrl, options);
        let payload;
        try { payload = await response.json(); } catch {
            throw Error(response.status === 413 ? 'Ukuran data melewati batas unggahan server.' : 'Respons penyimpanan tidak valid (HTTP ' + response.status + '). Muat ulang halaman dan coba kembali.');
        }
        if (!response.ok) throw Error(payload.error || 'Penyimpanan server gagal.');
        revision = payload.revision;
        if (payload.csrfHash) page.dataset.csrfHash = payload.csrfHash;
        return payload.data;
    }
    const data = await request();
    let pending = Promise.resolve();
    return {data, save(value) {
        const snapshot = structuredClone(value);
        const operation = pending.then(() => request(snapshot));
        pending = operation.catch(() => {});
        return operation;
    }};
};
