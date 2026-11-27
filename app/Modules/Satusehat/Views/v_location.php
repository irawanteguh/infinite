<div id="location-page" data-url="<?= esc(site_url('satusehat/location'), 'attr') ?>" data-csrf="<?= esc(csrf_hash(), 'attr') ?>">
    <div id="location-message" class="alert alert-danger" role="status" <?= $loadError === '' ? 'hidden' : '' ?>><?= esc($loadError) ?></div>
    <div class="card app-filter-card mb-5"><div class="card-body">
        <label for="location-environment" class="form-label">Environment</label>
        <select id="location-environment" class="form-select">
            <option value="">Pilih environment</option>
            <?php foreach ($environments as $env): ?><option value="<?= esc($env['id'], 'attr') ?>"><?= esc($env['name']) ?></option><?php endforeach ?>
        </select>
    </div></div>
    <section class="card app-table-card">
        <div class="card-header app-table-heading">
            <div><h2 class="app-table-title">Location</h2><p class="app-table-description">Lokasi berdasarkan Org_id pada environment terpilih.</p></div>
            <div class="app-table-search"><input id="location-search" class="form-control" type="search" placeholder="Cari Location" aria-label="Cari Location"></div>
        </div>
        <div class="card-body"><table id="location-table" class="table table-row-dashed align-middle w-100" data-datatable="manual">
            <thead><tr><th>ID Location</th><th>Kode</th><th>Nama</th><th>Jenis</th><th>Organisasi pengelola</th><th>Status</th></tr></thead><tbody></tbody>
        </table></div>
    </section>
    <div id="location-modal" class="modal fade" tabindex="-1" aria-labelledby="location-title" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable"><form id="location-form" class="modal-content">
            <div class="modal-header"><h2 id="location-title" class="modal-title fs-4">Tambah Location</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
            <div class="modal-body">
                <div id="location-form-message" class="alert alert-danger" role="alert" hidden></div>
                <div class="mb-5"><label for="location-code" class="form-label required">Kode internal</label><input id="location-code" name="code" class="form-control" maxlength="100" required></div>
                <div class="mb-5"><label for="location-name" class="form-label required">Nama Location</label><input id="location-name" name="name" class="form-control" maxlength="200" required></div>
                <div class="mb-5"><label for="location-part-of" class="form-label required">Managing organization</label><select id="location-part-of" name="part_of" class="form-select" required><option value="">Pilih organisasi pengelola</option></select><div class="form-text">Pilih organisasi induk atau departemen pengelola dari master Organization.</div></div>
<div class="mb-5"><label for="location-description" class="form-label">Deskripsi</label><textarea id="location-description" name="description" class="form-control" maxlength="1000"></textarea></div>
                <label for="location-type"  class="form-label required">Tipe fisik lokasi</label>
                <select id="location-type" name="type" class="form-select" required><option value="">Pilih jenis</option><option value="si">Site</option><option value="bu">Building</option><option value="lvl">Level</option><option value="ro">Room</option><option value="bd">Bed</option><option value="wa">Ward</option><option value="area">Area</option></select>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan ke SATUSEHAT</button></div>
        </form></div>
    </div>
</div>
