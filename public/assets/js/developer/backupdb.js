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

    async function request(action, method = 'GET') {
        const data = method === 'GET' ? {} : {[config.dataset.csrfName]: config.dataset.csrfHash};
        try {
            const payload = await $.ajax({url: base + action, method, data, dataType: 'json', timeout: 930000});
            updateToken(payload);
            if (payload.responseCode !== '00') throw new Error(payload.responseDesc || 'Permintaan gagal diproses.');
            return payload.responseResult;
        } catch (error) {
            updateToken(error.responseJSON);
            throw new Error(error.responseJSON?.responseDesc || error.message || (error.status === 0 ? 'Koneksi terputus atau proses melebihi batas waktu. Muat ulang daftar untuk memeriksa hasil backup.' : 'Permintaan gagal. Pastikan sesi login masih aktif.'));
        }
    }

    function setBusy(value, message) {
        busy = value;
        $('#btnBackupDatabase, #btnBackupDatabaseICS, #btnRefreshBackups').prop('disabled', value);
        $('#backup_table button').prop('disabled', value);
        status.textContent = message;
    }

    async function loadList() {
        const rows = await request('listbackup');
        if ($.fn.DataTable.isDataTable('#backup_table')) $('#backup_table').DataTable().destroy();
        const body = $('#resultbackup').empty();
        (Array.isArray(rows) ? rows : []).forEach((row, index) => {
            const tr = $('<tr>');
            $('<td>').addClass('ps-4').text(index + 1).appendTo(tr);
            $('<td>').text(row.filename).appendTo(tr);
            $('<td>').text(row.scope).appendTo(tr);
            $('<td>').text(row.sizeFormatted).appendTo(tr);
            $('<td>').text(row.created).appendTo(tr);
            const actions = $('<td>').addClass('text-end pe-4');
            $('<a>').addClass('btn btn-sm btn-light-primary me-2').attr({href: base + 'download/' + encodeURIComponent(row.filename), target: '_blank', rel: 'noopener'}).text('Unduh SQL').appendTo(actions);
            $('<button>').attr('type', 'button').addClass('btn btn-sm btn-light-danger backup-delete').data('filename', row.filename).prop('disabled', busy).text('Hapus').appendTo(actions);
            tr.append(actions).appendTo(body);
        });
        initDataTable('#backup_table', '#searchtable', 10, {language: {emptyTable: 'Belum ada file backup SQL.', search: 'Cari:', info: 'Menampilkan _START_–_END_ dari _TOTAL_ file', infoEmpty: 'Belum ada file backup', zeroRecords: 'File tidak ditemukan', lengthMenu: 'Tampilkan _MENU_ file'}});
        return Array.isArray(rows) ? rows.length : 0;
    }

    async function refresh() {
        if (busy) return;
        setBusy(true, 'Memuat daftar backup...');
        try { const count = await loadList(); setBusy(false, count + ' file backup tersedia.'); }
        catch (error) { setBusy(false, error.message); }
    }

    async function create(scope) {
        if (busy) return;
        const label = scope === 'ics' ? 'ICS' : 'seluruh database';
        const confirm = await Swal.fire({title: 'Buat dump ' + label + '?', text: scope === 'ics' ? 'Struktur dan data tabel referensi ICS akan disimpan sebagai file SQL.' : 'Seluruh database aktif, termasuk seluruh organisasi, akan disimpan sebagai file SQL.', icon: 'question', showCancelButton: true, confirmButtonText: 'Buat dump', cancelButtonText: 'Batal'});
        if (!confirm.isConfirmed || busy) return;
        setBusy(true, 'Membuat dump ' + label + '... Tunggu hingga proses selesai.');
        let result;
        try {
            result = await request(scope === 'ics' ? 'backupics' : 'backup', 'POST');

        } catch (error) {
            setBusy(false, error.message);
            await Swal.fire({icon: 'error', title: 'Dump belum berhasil', text: error.message});
            return;
        }
        let refreshNote = '';
        try { await loadList(); } catch (error) { refreshNote = ' Daftar belum diperbarui: ' + error.message; }
        setBusy(false, 'Dump selesai: ' + result.filename + ' (' + result.tables + ' tabel/objek, ' + result.durationSeconds + ' detik).' + refreshNote);
        const answer = await Swal.fire({icon: 'success', title: 'Dump database selesai', text: result.filename, showCancelButton: true, confirmButtonText: 'Unduh SQL', cancelButtonText: 'Tutup'});
        if (answer.isConfirmed) window.location.assign(base + 'download/' + encodeURIComponent(result.filename));
    }

    $('#btnBackupDatabase').on('click', () => create('full'));
    $('#btnBackupDatabaseICS').on('click', () => create('ics'));
    $('#btnRefreshBackups').on('click', refresh);
    $('#resultbackup').on('click', '.backup-delete', async function () {
        if (busy) return;
        const filename = $(this).data('filename');
        const answer = await Swal.fire({title: 'Hapus file backup?', text: filename, icon: 'warning', showCancelButton: true, confirmButtonText: 'Hapus file', cancelButtonText: 'Batal'});
        if (!answer.isConfirmed || busy) return;
        setBusy(true, 'Menghapus file backup...');
        try { await request('delete/' + encodeURIComponent(filename), 'POST'); const count = await loadList(); setBusy(false, 'File backup dihapus. ' + count + ' file tersedia.'); }
        catch (error) { setBusy(false, error.message); await Swal.fire({icon: 'error', title: 'File belum terhapus', text: error.message}); }
    });
    refresh();
})();
