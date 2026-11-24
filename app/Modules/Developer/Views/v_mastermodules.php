<main class="prem-page pe-dashboard mastermodules-page">
    <div class="ur-hero app-page-heading">
        <div>
            <h1>Master Modules</h1>
            <p>Kelola nama, ikon, dan urutan modul pada navigasi aplikasi.</p>
        </div>
    </div>

    <?php if ($message = session()->getFlashdata('mastermodules_success')): ?>
        <div class="alert alert-success" role="status"><?= esc($message) ?></div>
    <?php endif ?>
    <?php if ($message = session()->getFlashdata('mastermodules_error')): ?>
        <div class="alert alert-warning" role="alert"><?= esc($message) ?></div>
    <?php endif ?>

    <section class="prem-card pe-panel" aria-labelledby="mastermodules-tree-title">
        <div class="pe-panel-heading">
            <div>
                <h2 id="mastermodules-tree-title"><i class="bi bi-diagram-3 me-2" aria-hidden="true"></i>Struktur modul</h2>
                <p>Seret ikon pegangan atau gunakan tombol panah untuk mengurutkan modul dalam satu induk. Urutan disimpan otomatis.</p>
                <p class="text-muted mb-0" role="status" data-order-status></p>
            </div>
        </div>
        <?php if (!$groups): ?>
            <p class="text-muted mb-0">Belum ada modul.</p>
        <?php else: ?>
            <?php
            $rendered = [];
            $renderGroup = static function (string $parent) use (&$renderGroup, &$rendered, $groups): void {
                if (!isset($groups[$parent]) || isset($rendered[$parent])) {
                    return;
                }
                $formId = 'module-tree-order-' . count($rendered);
                $rendered[$parent] = true;
                $rows = $groups[$parent];
                $canMove = count($rows) > 1;
                ?>
                <?php if ($canMove): ?>
                    <form id="<?= $formId ?>" method="post" action="<?= esc(site_url('developer/mastermodules/reorder'), 'attr') ?>" hidden>
                        <?= csrf_field() ?>
                        <input type="hidden" name="group" value="<?= esc($parent, 'attr') ?>">
                        <input type="hidden" name="revision" value="<?= esc(\Modules\Developer\Models\MastermodulesModel::revision($rows), 'attr') ?>">
                    </form>
                <?php endif ?>
                <ul class="module-tree-list list-unstyled mb-0" <?= $canMove ? 'data-order-form="' . $formId . '"' : '' ?>>
                    <?php foreach ($rows as $index => $module): ?>
                        <?php
                        $id = (string) $module['modules_id'];
                        $name = $module['modules_name'];
                        $route = $module['def_controller'] ? $module['package'] . '/' . $module['def_controller'] : '';
                        $icon = preg_match('/\Abi bi-[a-z0-9-]+\z/D', $module['icon'] ?? '') ? $module['icon'] : 'bi bi-grid';
                        $active = (string) $module['active'] === '1';
                        ?>
                        <li class="module-tree-node" data-order-id="<?= esc($id, 'attr') ?>">
                            <div class="module-tree-row rounded p-2 d-flex align-items-center flex-wrap gap-2">
                                <?php if ($canMove): ?>
                                    <span class="module-drag-handle px-1" draggable="true" title="Seret untuk pindah"><i class="bi bi-grip-vertical" aria-hidden="true"></i></span>
                                <?php endif ?>
                                <span class="module-tree-position text-muted fs-8 text-center" data-position><?= $index + 1 ?></span>
                                <i class="<?= esc($icon, 'attr') ?> module-tree-icon fs-5" aria-hidden="true"></i>
                                <div class="module-tree-name flex-grow-1">
                                    <div class="fw-semibold fs-5 text-break"><?= esc($name) ?></div>
                                    <?php if ($route !== ''): ?>
                                        <div class="text-muted fs-7 text-break"><?= esc($route) ?></div>
                                    <?php endif ?>
                                </div>
                                <span class="badge <?= $active ? 'module-badge-active' : 'badge-light-danger' ?>"><?= $active ? 'Aktif' : 'Nonaktif' ?></span>
                                <div class="module-tree-actions d-flex flex-wrap gap-1">
                                    <?php if ($canMove): ?>
                                        <button type="button" class="btn btn-icon btn-sm btn-light" data-move="-1" aria-label="Naikkan <?= esc($name, 'attr') ?>" <?= $index === 0 ? 'disabled' : '' ?>>
                                            <i class="bi bi-chevron-up" aria-hidden="true"></i>
                                        </button>
                                        <button type="button" class="btn btn-icon btn-sm btn-light" data-move="1" aria-label="Turunkan <?= esc($name, 'attr') ?>" <?= $index === count($rows) - 1 ? 'disabled' : '' ?>>
                                            <i class="bi bi-chevron-down" aria-hidden="true"></i>
                                        </button>
                                    <?php endif ?>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-light-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                            Actions
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <button type="button" class="dropdown-item btn btn-sm text-primary" data-bs-toggle="modal" data-bs-target="#mastermodules-edit"
                                                data-module-id="<?= esc($id, 'attr') ?>" data-module-name="<?= esc($name, 'attr') ?>"
                                                data-module-icon="<?= esc((string) $module['icon'], 'attr') ?>"
                                                data-module-package="<?= esc((string) $module['package'], 'attr') ?>"
                                                data-module-controller="<?= esc((string) $module['def_controller'], 'attr') ?>"
                                                data-module-status="<?= esc((string) $module['status'], 'attr') ?>"
                                                data-module-version="<?= esc((string) $module['version'], 'attr') ?>"
                                                data-module-revision="<?= esc(\Modules\Developer\Models\MastermodulesModel::editRevision($module), 'attr') ?>">
                                                <i class="bi bi-pencil me-4" aria-hidden="true"></i>Edit
                                            </button>
                                            <button type="button" class="dropdown-item btn btn-sm text-primary" data-bs-toggle="modal" data-bs-target="#mastermodules-edit"
                                                data-add-child data-module-id="<?= esc($id, 'attr') ?>" data-module-name="<?= esc($name, 'attr') ?>">
                                                <i class="bi bi-plus-lg me-4" aria-hidden="true"></i>Tambah Submodul
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php $renderGroup($id); ?>
                        </li>
                    <?php endforeach ?>
                </ul>
                <?php
            };

            $renderGroup('');
            $remaining = array_diff_key($groups, $rendered);
            if ($remaining): ?>
                <h3 class="fs-6 mt-5 mb-3">Cabang terpisah</h3>
                <?php foreach (array_diff_key($remaining, $modulesById) + $remaining as $parent => $rows) {
                    $renderGroup((string) $parent);
                } ?>
            <?php endif ?>
        <?php endif ?>
    </section>

    <div class="modal fade" id="mastermodules-edit" tabindex="-1" aria-hidden="true" aria-labelledby="mastermodules-edit-title"
        data-add-url="<?= esc(site_url('developer/mastermodules/addchild'), 'attr') ?>">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form class="modal-content" method="post" action="<?= esc(site_url('developer/mastermodules/update'), 'attr') ?>">
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title fs-4" id="mastermodules-edit-title">Edit modul</h2>
                        <p class="text-muted fs-7 mb-0" data-module-parent hidden></p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <?= csrf_field() ?>
                    <input type="hidden" name="module_id">
                    <input type="hidden" name="revision">
                    <div class="mb-4">
                        <label class="form-label" for="mastermodules-edit-name">Nama modul</label>
                        <input class="form-control form-control-solid" id="mastermodules-edit-name" name="module_name" maxlength="100" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label" for="mastermodules-edit-icon">Kelas ikon Bootstrap</label>
                        <input class="form-control form-control-solid" id="mastermodules-edit-icon" name="icon" maxlength="500" placeholder="bi bi-grid" aria-describedby="mastermodules-icon-hint">
                        <div class="form-text" id="mastermodules-icon-hint">Contoh kelas ikon <code>bi bi-grid-3x3-gap</code>.</div>
                    </div>
                    <div class="row g-3">
                        <div class="col-12 col-sm-6">
                            <label class="form-label" for="mastermodules-edit-package">Package</label>
                            <input class="form-control form-control-solid" id="mastermodules-edit-package" name="package" maxlength="100" spellcheck="false" aria-describedby="mastermodules-route-hint">
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label" for="mastermodules-edit-controller">Def Controller</label>
                            <input class="form-control form-control-solid" id="mastermodules-edit-controller" name="def_controller" maxlength="100" spellcheck="false" aria-describedby="mastermodules-route-hint">
                        </div>
                    </div>
                    <div class="form-text" id="mastermodules-route-hint">Rute dipakai oleh navigasi dan hak akses. Untuk modul pengelompokan, kosongkan kedua kolom.</div>
                    <fieldset class="mt-4" data-module-edit-fields>
                        <div class="row g-3">
                            <div class="col-12 col-sm-6">
                                <label class="form-label" for="mastermodules-edit-status">Kode status</label>
                                <input class="form-control form-control-solid" id="mastermodules-edit-status" name="status" maxlength="1" required>
                            </div>
                            <div class="col-12 col-sm-6">
                                <label class="form-label" for="mastermodules-edit-version">Versi</label>
                                <input class="form-control form-control-solid" id="mastermodules-edit-version" name="version" maxlength="100">
                            </div>
                        </div>
                    </fieldset>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan perubahan</button>
                </div>
            </form>
        </div>
    </div>
</main>
