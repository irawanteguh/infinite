<div id="patient-page" data-url="<?= esc(site_url('satusehat/patient'), 'attr') ?>" data-csrf="<?= esc(csrf_hash(), 'attr') ?>">
    <div id="patient-message" class="alert alert-danger" role="status" <?= empty($loadError) ? 'hidden' : '' ?>><?= esc($loadError ?? '') ?></div>
    <section class="card app-table-card">
        <div class="card-header app-table-heading">
            <div><h2 class="app-table-title">Daftar pasien</h2><p class="app-table-description">Master pasien pada organisasi aktif.</p></div>
            <div class="app-table-search"><input id="patient-search" class="form-control" type="search" placeholder="Cari nama, NIK, atau IHS" aria-label="Cari pasien"></div>
        </div>
        <div class="card-body">
            <table id="patient-table" class="table table-row-dashed align-middle w-100 fs-8 gy-2" data-datatable="manual">
                <thead>
                    <tr>
                        <th scope="col">NIK pasien</th>
                        <th scope="col">Nama pasien</th>
                        <th scope="col">Jenis kelamin</th>
                        <th scope="col">Tanggal lahir</th>
                        <th scope="col">Nomor IHS</th>
                        <th scope="col">NIK ibu</th>
                        <th scope="col">Data</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($records ?? []) as $row): ?>
                        <tr>
                            <?php foreach ([$row['nik'], $row['name'], ['male' => 'Laki-laki', 'female' => 'Perempuan'][$row['gender']] ?? $row['gender'], $row['birth_date'], $row['ihs_number'], $row['mother_nik'] ?? null, (string)$row['is_dummy'] === '1' ? 'Sandbox / dummy' : 'Pasien'] as $value): ?>
                                <td><?= esc($value ?: '—') ?></td>
                            <?php endforeach ?>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </section>
    <div class="modal fade" id="patient-modal" tabindex="-1" aria-labelledby="patient-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable"><form id="patient-form" class="modal-content">
            <div class="modal-header"><div><h2 id="patient-modal-title" class="modal-title fs-4">Tambah pasien bayi</h2><p class="text-muted mb-0 mt-2">Pendaftaran bayi baru lahir menggunakan NIK ibu.</p></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
            <div class="modal-body">
                <div id="patient-form-message" class="alert alert-danger" role="alert" hidden></div>
                <button id="patient-exchange-open" type="button" class="btn btn-sm btn-light-primary mb-5" hidden>Lihat request dan response</button>
                <div class="mb-6"><label for="patient-environment" class="form-label required">Environment</label><select id="patient-environment" name="environment_id" class="form-select" required><option value="">Pilih environment</option><?php foreach (($environments ?? []) as $env): ?><option value="<?= esc($env['id'], 'attr') ?>" data-sandbox="<?= $env['sandbox'] ? '1' : '0' ?>"><?= esc($env['name']) ?></option><?php endforeach ?></select></div>
                <h3 class="fs-5 mb-4">Identitas bayi</h3><div class="row g-5">
                <div class="col-md-6"><label for="patient-mother_nik" class="form-label required">NIK ibu</label><div class="input-group"><input id="patient-mother_nik" name="mother_nik" class="form-control" readonly required placeholder="Pilih pasien dari master"><button id="patient-mother-find" type="button" class="btn btn-light-primary"><i class="bi bi-search" aria-hidden="true"></i>Pilih pasien</button></div></div>
                <div class="col-md-6"><label for="patient-mother-name" class="form-label">Nama ibu</label><input id="patient-mother-name" name="mother_name" class="form-control" readonly></div>
                <?php foreach ([['name','Nama bayi','text',200],['birth_date','Tanggal lahir','date',10],['birth_order','Urutan kelahiran (0 untuk tunggal)','number',2]] as [$name,$label,$type,$max]): ?>
                    <div class="col-md-6"><label for="patient-<?= $name ?>" class="form-label required"><?= esc($label) ?></label><input id="patient-<?= $name ?>" name="<?= $name ?>" type="<?= $type ?>" class="form-control" maxlength="<?= $max ?>" <?= $name === 'mother_nik' ? 'pattern="[0-9]{16}" inputmode="numeric"' : '' ?> <?= $type === 'number' ? 'min="0" max="20" value="0"' : '' ?> required></div>
                <?php endforeach ?>
                    <div class="col-md-6"><label for="patient-gender" class="form-label required">Jenis kelamin</label><select id="patient-gender" name="gender" class="form-select" required><option value="">Pilih jenis kelamin</option><option value="male">Laki-laki</option><option value="female">Perempuan</option></select></div>
                </div>
                <div class="row g-5 mt-0">
                <?php foreach (['email' => 'Email', 'birth_place' => 'Tempat lahir', 'rt' => 'RT', 'rw' => 'RW', 'contact_phone' => 'Nomor telepon ibu'] as $key => $label): ?>
                    <div class="col-md-6"><label for="patient-<?= $key ?>" class="form-label"><?= esc($label) ?></label><input id="patient-<?= $key ?>" name="<?= $key ?>" class="form-control" maxlength="<?= $key === 'contact_phone' ? 30 : 200 ?>" <?= $key === 'email' ? 'type="email"' : '' ?>></div>
                <?php endforeach ?>
                </div>
                <div class="separator my-6"></div><h3 class="fs-5 mb-4">Alamat</h3><div class="row g-5">
                <div class="col-12"><label for="patient-address" class="form-label required">Alamat lengkap</label><input id="patient-address" name="address" class="form-control" maxlength="500" required></div>
                <?php foreach (['province' => 'Provinsi', 'city_code' => 'Kota / kabupaten', 'district' => 'Kecamatan', 'village' => 'Kelurahan / desa'] as $key => $label): ?>
                    <div class="col-md-6"><label for="patient-<?= $key ?>" class="form-label required"><?= esc($label) ?></label><select id="patient-<?= $key ?>" name="<?= $key ?>" class="form-select" required><option value="">Pilih <?= esc(strtolower($label)) ?></option><?php if ($key === 'province'): ?><?php foreach (($provinces ?? []) as $province): ?><option value="<?= esc($province['id'], 'attr') ?>"><?= esc($province['text'] . ' · ' . $province['id']) ?></option><?php endforeach ?><?php endif ?></select></div>
                <?php endforeach ?>
                <div class="col-md-6"><label for="patient-city" class="form-label required">Nama kota / kabupaten untuk alamat</label><input id="patient-city" name="city" class="form-control" maxlength="200" required></div>
                <div class="col-md-6"><label for="patient-postal_code" class="form-label">Kode pos</label><input id="patient-postal_code" name="postal_code" class="form-control" maxlength="10"></div>
                <div class="col-12"><p id="patient-region-status" class="text-muted mb-2" role="status">Pilih provinsi sesuai alamat pasien.</p><div class="d-flex gap-3"><button id="patient-region-retry" type="button" class="btn btn-sm btn-light-primary" hidden>Coba lagi</button></div></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button id="patient-preview" type="button" class="btn btn-light-primary">Lihat request body</button><button id="patient-submit" type="submit" class="btn btn-primary"><i class="bi bi-cloud-arrow-up" aria-hidden="true"></i>Simpan ke SATUSEHAT</button></div>
        </form></div>
    </div>
    <div class="modal fade" id="patient-mother-modal" tabindex="-1" aria-labelledby="patient-mother-title" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable"><div class="modal-content">
            <div class="modal-header"><h2 id="patient-mother-title" class="modal-title fs-4">Pilih pasien ibu</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
            <div class="modal-body">
                <div id="patient-mother-message" class="alert alert-danger" role="alert" hidden></div>
                <section class="card app-table-card">
                    <div class="card-header app-table-heading"><div><h3 class="app-table-title">Master pasien</h3><p class="app-table-description">Pilih pasien untuk mengisi NIK ibu.</p></div><div class="app-table-search"><input id="patient-mother-search" type="search" class="form-control" placeholder="Cari nama, NIK, atau IHS" aria-label="Cari pasien ibu"></div></div>
                    <div class="card-body"><table id="patient-mother-table" class="table table-row-dashed align-middle w-100 fs-8 gy-2" data-datatable="manual"><thead><tr><th scope="col">NIK</th><th scope="col">Nama pasien</th><th scope="col">Jenis kelamin</th><th scope="col">Tanggal lahir</th><th scope="col">Nomor IHS</th><th scope="col" class="text-end">Pilih</th></tr></thead><tbody></tbody></table></div>
                </section>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Kembali</button></div>
        </div></div>
    </div>
    <div class="modal fade" id="patient-exchange-modal" tabindex="-1" aria-labelledby="patient-exchange-title" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable"><div class="modal-content">
            <div class="modal-header">
                <h2 id="patient-exchange-title" class="modal-title fs-4">Detail penambahan pasien SATUSEHAT</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <p><code>POST /Patient</code> · Patient - Create by Mother's NIK (Newborn)</p>
                <?php foreach (['request' => 'Request body', 'response' => 'Response body'] as $key => $label): ?>
                    <h3 id="patient-json-<?= $key ?>-title" class="fs-6"><?= esc($label) ?></h3>
                    <div class="patient-json bg-light border rounded mb-4" tabindex="0" role="region" aria-labelledby="patient-json-<?= $key ?>-title">
                        <pre id="patient-json-<?= $key ?>-lines" class="patient-json-lines mb-0 py-3 px-4" aria-hidden="true"></pre>
                        <pre id="patient-json-<?= $key ?>" class="mb-0 p-3"></pre>
                    </div>
                <?php endforeach ?>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Kembali</button></div>
        </div></div>
    </div>
</div>
