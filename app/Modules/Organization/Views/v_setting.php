<div class="card">
    <div class="card-header"><h2 class="card-title">Pengaturan pengisian indikator</h2></div>
    <div class="card-body">
        <?php if ($message = session()->getFlashdata('indicator_setting_message')): ?>
            <div class="alert alert-info"><?= esc($message) ?></div>
        <?php endif; ?>
        <?php if (!$ready): ?>
            <div class="alert alert-warning">Pengaturan belum tersedia. Jalankan migrasi database untuk mengaktifkan fitur ini.</div>
        <?php else: ?>
            <form method="post" action="<?= site_url('organization/setting/save') ?>" class="mb-10">
                <input type="hidden" name="token" value="<?= esc(session()->get('indicator_setting_token'), 'attr') ?>">
                <input type="hidden" name="action" value="setting">
                <label class="form-label" for="deadline-mode">Batas pengisian</label>
                <select class="form-select mb-4" data-control="select2" name="mode" id="deadline-mode">
                    <option value="unlimited" <?= !$setting || $setting->deadline_day === null ? 'selected' : '' ?>>Tanpa batas tanggal</option>
                    <option value="limited" <?= $setting && $setting->deadline_day !== null ? 'selected' : '' ?>>Dengan batas tanggal bulan berikutnya</option>
                </select>
                <?php $limited = $setting && $setting->deadline_day !== null; ?>
                <div id="deadline-day-fields" <?= $limited ? '' : 'hidden' ?>>
                    <label class="form-label" for="deadline-day">Tanggal batas (default 5)</label>
                    <input class="form-control mb-3" type="number" min="1" max="31" name="deadline_day" id="deadline-day" value="<?= esc($limited ? $setting->deadline_day : 5, 'attr') ?>" <?= $limited ? 'required' : 'disabled' ?>>
                    <p class="text-muted">Berlaku sampai akhir hari pada tanggal tersebut di bulan berikutnya. Jika tanggal tidak ada, digunakan hari terakhir bulan. Zona waktu: <?= esc(config('App')->appTimezone) ?>.</p>
                </div>
                <button class="btn btn-primary" type="submit">Simpan pengaturan</button>
            </form>
            <h3>Izin khusus departemen</h3>
            <p>Izin berlaku untuk pengisian dan perubahan nilai indikator departemen yang dipilih, selama 1 × 24 jam sejak diberikan. Izin aktif tidak diperpanjang saat tombol ditekan kembali.</p>
            <form method="post" action="<?= site_url('organization/setting/save') ?>" class="mb-7">
                <input type="hidden" name="token" value="<?= esc(session()->get('indicator_setting_token'), 'attr') ?>">
                <input type="hidden" name="action" value="grant">
                <label class="form-label" for="grant-department">Departemen</label>
                <select class="form-select mb-4" data-control="select2" data-placeholder="Pilih departemen" data-minimum-results-for-search="0" name="department_id" id="grant-department" required>
                    <option value="">Pilih departemen</option>
                    <?php $names = []; foreach ($departments as $department): $names[$department->department_id] = $department->department; ?>
                        <option value="<?= esc($department->department_id, 'attr') ?>"><?= esc($department->department) ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-warning" type="submit">Berikan izin 24 jam</button>
            </form>
            <h3>Riwayat izin (100 terakhir)</h3>
            <?php if (!$canDeactivate): ?>
                <div class="alert alert-warning">Jalankan migrasi database terbaru untuk mengaktifkan aksi nonaktifkan izin.</div>
            <?php endif; ?>
            <div class="d-flex align-items-center position-relative my-5">
                <i class="bi bi-search position-absolute ms-6 text-muted"></i>
                <input type="search" class="form-control form-control-solid w-250px ps-15" placeholder="Cari riwayat izin" aria-label="Cari riwayat izin" id="grant-history-search">
            </div>
            <div class="table-responsive"><table class="table align-middle table-row-dashed gy-2" id="grant-history-table">
                <thead><tr class="fw-bolder text-muted bg-light">
                    <th class="text-start ps-4 rounded-start">#</th>
                    <th>Departemen</th><th>Diberikan</th><th>Berakhir</th><th>Status</th><th>Active</th>
                    <th class="text-end pe-4 rounded-end">Actions</th>
                </tr></thead>
                <tbody class="fw-bold text-gray-600">
                <?php foreach ($grants as $index => $grant): ?>
                    <?php $active = (string) $grant->active === '1'; $expired = $grant->expires_at <= date('Y-m-d H:i:s'); ?>
                    <tr>
                        <td class="ps-4"><?= $index + 1 ?></td>
                        <td><?= esc($names[$grant->department_id] ?? $grant->department_id) ?></td>
                        <td class="text-nowrap"><?= esc(date('d.m.Y H:i:s', strtotime($grant->granted_at))) ?></td>
                        <td class="text-nowrap"><?= esc(date('d.m.Y H:i:s', strtotime($grant->expires_at))) ?></td>
                        <td><span class="badge <?= !$active ? 'badge-light-secondary' : ($expired ? 'badge-light-danger' : 'badge-light-success') ?>"><?= !$active ? 'Dinonaktifkan' : ($expired ? 'Berakhir' : 'Berlaku') ?></span></td>
                        <td><span class="badge <?= $active ? 'badge-light-success' : 'badge-light-danger' ?>"><?= $active ? 'Aktif' : 'Nonaktif' ?></span></td>
                        <td class="text-end pe-4">
                            <?php if ($active && $canDeactivate): ?>
                                <form method="post" action="<?= site_url('organization/setting/save') ?>" class="d-inline">
                                    <input type="hidden" name="token" value="<?= esc(session()->get('indicator_setting_token'), 'attr') ?>">
                                    <input type="hidden" name="action" value="deactivate">
                                    <input type="hidden" name="grant_id" value="<?= esc($grant->grant_id, 'attr') ?>">
                                    <button type="submit" class="btn btn-sm btn-light-danger"><i class="bi bi-x-circle me-1"></i>Nonaktifkan</button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$grants): ?><tr><td colspan="7" class="text-center text-muted py-10">Belum ada riwayat izin.</td></tr><?php endif; ?>
                </tbody>
            </table></div>
        <?php endif; ?>
    </div>
</div>
