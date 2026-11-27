(function () {
    'use strict';
    const config = document.getElementById('backup-config');
    if (!config) return;
    const base = config.dataset.baseUrl.replace(/\/$/, '') + '/';
    const status = document.getElementById('backup-status');
    let busy = false;

    function updateToken(payload) {
        if (payload && payload.csrfHash) {
            config.dataset.csrfHash = payload.csrfHash;
            config.dataset.csrfName = payload.csrfName || config.dataset.csrfName;
        }
    }

    async function request(action, method = 'GET', loading = null) {
        const data = method === 'GET' ? {} : {[config.dataset.csrfName]: config.dataset.csrfHash};
        try {
            const payload = await $.ajax({
                url: base + action, method, data, dataType: 'json', timeout: 930000,
                beforeSend: () => {
                    if (loading) showDataLoading(...loading);
                }
            });
            updateToken(payload);
            if (payload.responseCode !== '00') throw new Error(payload.responseDesc || 'Permintaan gagal diproses.');
            return payload.responseResult;
        } catch (error) {
            updateToken(error.responseJSON);
            const fallback = error.status
                ? 'Respons server tidak dapat diproses (HTTP ' + error.status + ').'
                : 'Koneksi terputus atau proses melebihi batas waktu. Muat ulang daftar untuk memeriksa hasil backup.';
            throw new Error(error.responseJSON?.responseDesc || error.message || fallback);
        }
    }

    function setBusy(value, message) {
        busy = value;
        $('#btnBackupDatabase, #btnRefreshBackups').prop('disabled', value);
        $('#backup_table button').prop('disabled', value);
        status.textContent = message;
        status.hidden = !message;
    }

    async function loadList(loading = null) {
        const rows = await request('listbackup', 'GET', loading);
        if ($.fn.DataTable.isDataTable('#backup_table')) $('#backup_table').DataTable().destroy();
        document.querySelectorAll('#backup_table [data-bs-toggle="dropdown"]').forEach(toggle => {
            bootstrap.Dropdown.getInstance(toggle)?.dispose();
        });
        const body = $('#resultbackup').empty();
        (Array.isArray(rows) ? rows : []).forEach((row, index) => {
            const tr = $('<tr>');
            $('<td>').addClass('text-end').text(index + 1).appendTo(tr);
            $('<td>').text(row.filename).appendTo(tr);
            $('<td>').text(row.scope).appendTo(tr);
            $('<td>').addClass('text-end').text(row.sizeFormatted).appendTo(tr);
            $('<td>').text(row.created).appendTo(tr);
            const actions = $('<td>').addClass('text-end');
            const group = $('<div>').addClass('btn-group').attr({
                role: 'group', 'aria-label': 'Actions ' + row.filename
            }).appendTo(actions);
            const toggle = $('<button>').attr({
                type: 'button', 'data-bs-toggle': 'dropdown', 'aria-expanded': 'false'
            }).addClass('btn btn-sm btn-light-primary dropdown-toggle').prop('disabled', busy).text('Actions').appendTo(group);
            const menu = $('<div>').addClass('dropdown-menu dropdown-menu-end').appendTo(group);
            new bootstrap.Dropdown(toggle[0], {
                popperConfig: options => ({...options, strategy: 'fixed'}),
            });
            const download = $('<a>').addClass('dropdown-item').attr({
                href: base + 'download/' + encodeURIComponent(row.filename), target: '_blank', rel: 'noopener'
            }).appendTo(menu);
            $('<i>').addClass('bi bi-download me-2').attr('aria-hidden', 'true').appendTo(download);
            download.append(document.createTextNode('Unduh SQL'));
            const remove = $('<button>').attr('type', 'button').addClass('dropdown-item text-danger backup-delete')
                .data('filename', row.filename).prop('disabled', busy).appendTo(menu);
            $('<i>').addClass('bi bi-trash me-2').attr('aria-hidden', 'true').appendTo(remove);
            remove.append(document.createTextNode('Hapus'));
            tr.append(actions).appendTo(body);
        });
        initDataTable('#backup_table', '#searchtable', 10, {
            columnDefs: [{targets: -1, searchable: false}],
            language: {emptyTable: 'Belum ada file backup yang tersimpan.'}
        });
        return Array.isArray(rows) ? rows.length : 0;
    }

    async function refresh() {
        if (busy) return;
        setBusy(true, '');
        try { await loadList(['Menyiapkan daftar backup', 'Memuat file SQL yang tersimpan.']); setBusy(false, ''); }
        catch (error) { setBusy(false, error.message); }
    }

    async function create() {
        if (busy) return;
        const label = 'seluruh database';
        const confirm = await Swal.fire({title: 'Buat dump ' + label + '?', text: 'Seluruh database aktif, termasuk seluruh organisasi, akan disimpan sebagai file SQL. Data dt01_bpjs_pending_klaim dan dt01_bpjs_ur_dt tidak disertakan.', icon: 'question', showCancelButton: true, confirmButtonText: 'Buat dump', cancelButtonText: 'Batal'});
        if (!confirm.isConfirmed || busy) return;
        setBusy(true, '');
        let result;
        try {
            result = await request('backup', 'POST', ['Membuat backup database', 'Menyiapkan salinan database dalam format SQL.']);

        } catch (error) {
            setBusy(false, error.message);
            await Swal.fire({icon: 'error', title: 'Dump belum berhasil', text: error.message});
            return;
        }
        let refreshNote = '';
        try { await loadList(); } catch (error) { refreshNote = ' Daftar belum diperbarui: ' + error.message; }
        setBusy(false, refreshNote.trim());
        const answer = await Swal.fire({icon: 'success', title: 'Dump database selesai', text: result.filename, showCancelButton: true, confirmButtonText: 'Unduh SQL', cancelButtonText: 'Tutup'});
        if (answer.isConfirmed) window.location.assign(base + 'download/' + encodeURIComponent(result.filename));
    }

    $('#btnBackupDatabase').on('click', create);
    $('#btnRefreshBackups').on('click', refresh);
    $('#resultbackup').on('click', '.backup-delete', async function () {
        if (busy) return;
        const filename = $(this).data('filename');
        const answer = await Swal.fire({title: 'Hapus file backup?', text: filename, icon: 'warning', showCancelButton: true, confirmButtonText: 'Hapus file', cancelButtonText: 'Batal'});
        if (!answer.isConfirmed || busy) return;
        setBusy(true, '');
        try {
            await request('delete/' + encodeURIComponent(filename), 'POST', ['Menghapus file backup', 'Menghapus file SQL yang dipilih.']);
        } catch (error) {
            setBusy(false, error.message);
            await Swal.fire({icon: 'error', title: 'Penghapusan gagal dikonfirmasi', text: error.message});
            return;
        }

        try {
            await loadList();
            setBusy(false, '');
        } catch (error) {
            setBusy(false, 'File sudah dihapus. Daftar belum diperbarui. ' + error.message);
        }
    });
    refresh();
})();
