<div id="organization-page" data-url="<?= esc(site_url('satusehat/organization'), 'attr') ?>" data-csrf="<?= esc(csrf_hash(), 'attr') ?>">
    <div id="organization-message" class="alert alert-danger" role="status" <?= $loadError === '' ? 'hidden' : '' ?>><?= esc($loadError) ?></div>
    <div class="card app-filter-card mb-5"><div class="card-body">
        <label for="organization-environment" class="form-label">Environment</label>
        <select id="organization-environment" class="form-select">
            <option value="">Pilih environment</option>
            <?php foreach ($environments as $env): ?><option value="<?= esc($env['id'], 'attr') ?>"><?= esc($env['name']) ?></option><?php endforeach ?>
        </select>
    </div></div>
    <section class="card app-table-card">
        <div class="card-header app-table-heading">
            <div><h2 class="app-table-title">Organization</h2><p class="app-table-description">Suborganisasi dari Org_id pada environment terpilih.</p></div>
            <div class="app-table-search"><input id="organization-search" class="form-control" type="search" placeholder="Cari Organization" aria-label="Cari Organization"></div>
        </div>
        <div class="card-body"><table id="organization-table" class="table table-row-dashed align-middle w-100 fs-8 gy-2" data-datatable="manual">
            <thead><tr><th>ID Organization</th><th>Kode</th><th>Nama</th><th>Jenis</th><th>Induk</th><th>Status</th></tr></thead><tbody></tbody>
        </table></div>
    </section>
    <div id="organization-modal" class="modal fade" tabindex="-1" aria-labelledby="organization-title" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable"><form id="organization-form" class="modal-content">
            <div class="modal-header"><h2 id="organization-title" class="modal-title fs-4">Tambah Organization</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
            <div class="modal-body">
                <div id="organization-form-message" class="alert alert-danger" role="alert" hidden></div>
                <div class="mb-5"><label for="organization-code" class="form-label required">Kode internal</label><input id="organization-code" name="code" class="form-control" maxlength="100" required></div>
                <div class="mb-5"><label for="organization-name" class="form-label required">Nama Organization</label><input id="organization-name" name="name" class="form-control" maxlength="200" required></div>
                <div class="mb-5"><label for="organization-part-of" class="form-label required">Part of · induk langsung</label><select id="organization-part-of" name="part_of" class="form-select" required><option value="">Pilih induk Organization</option></select><div class="form-text">Pilih organisasi induk atau departemen di atasnya.</div></div>
                <label for="organization-type"  class="form-label required">Jenis Organization</label>
                <select id="organization-type" name="type" class="form-select" required><option value="">Pilih jenis</option><option value="dept">Hospital Department</option><option value="team">Organizational team</option><option value="other">Other</option></select>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan ke SATUSEHAT</button></div>
        </form></div>
    </div>
</div>
