<?php
$phases = [
    'Plan' => ['title' => 'PLAN', 'subtitle' => 'Rencana & prediksi', 'icon' => 'bi-clipboard2-check', 'color' => 'primary'],
    'Do' => ['title' => 'DO', 'subtitle' => 'Pelaksanaan', 'icon' => 'bi-play-circle', 'color' => 'success'],
    'Study' => ['title' => 'STUDY', 'subtitle' => 'Hasil & pembelajaran', 'icon' => 'bi-graph-up-arrow', 'color' => 'warning'],
    'Act' => ['title' => 'ACT', 'subtitle' => 'Tindak lanjut', 'icon' => 'bi-check2-circle', 'color' => 'danger'],
];
$phaseItems = [];
foreach ($phases as $phase => $config) {
    $phaseItems[$phase] = array_values(array_filter($items, static function ($item) use ($phase) {
        return $item->phase === $phase;
    }));
}
?>

<div id="pdsaDiagramRoot" data-endpoint="<?= esc($endpoint) ?>" data-can-manage="<?= $canManage ? '1' : '0' ?>"></div>
<div id="pdsaItemStatus" class="visually-hidden" role="status" aria-live="polite"></div>

<section class="card border-0 shadow-sm mb-7">
    <div class="card-body p-6 p-lg-8">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-5">
            <div class="d-flex align-items-center gap-4">
                <span class="symbol symbol-55px symbol-circle bg-light-primary"><span class="symbol-label"><i class="bi bi-arrow-repeat text-primary fs-2"></i></span></span>
                <div>
                    <span class="badge badge-light-primary mb-2">QUALITY IMPROVEMENT</span>
                    <h2 class="text-gray-900 fw-bolder mb-1">Siklus PDSA</h2>
                    <div class="text-gray-600 fs-7"><?= esc($cycle->department ?? 'Unit') ?><?= !empty($cycle->tahun) ? ' · ' . esc($cycle->tahun) : '' ?></div>
                </div>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-3">
                <?php if (!empty($cycle->indikator)): ?><span class="badge badge-light-info py-3 px-4"><i class="bi bi-activity me-2"></i><?= esc($cycle->indikator) ?></span><?php endif; ?>
                <?php if (!empty($cycle->status)): ?><span class="badge badge-light-success py-3 px-4"><i class="bi bi-check-circle me-2"></i><?= esc($cycle->status) ?></span><?php endif; ?>
            </div>
        </div>
    </div>
</section>

<section class="card border-0 shadow-sm overflow-hidden">
    <div class="card-header border-0 pt-6 px-6 px-lg-8">
        <div>
            <h3 class="card-title fw-bolder text-gray-900">Diagram perbaikan</h3>
            <div class="text-gray-600 fs-7">Ikuti alur Plan, Do, Study, Act dan lihat item pada setiap tahap.</div>
        </div>
        <a href="<?= esc(site_url('qi/pdsa')) ?>" class="btn btn-sm btn-light-primary"><i class="bi bi-list-ul me-2"></i>Daftar PDSA</a>
    </div>
    <div class="card-body p-4 p-lg-8">
        <div class="table-responsive pdsa-board-scroll">
            <div class="pdsa-board mx-auto" role="group" aria-label="Diagram siklus PDSA">
                <svg class="pdsa-board-arrows" viewBox="0 0 1000 1000" aria-hidden="true">
                    <defs>
                        <marker id="pdsaPlanArrow" markerWidth="9" markerHeight="9" refX="7" refY="3.5" orient="auto"><path d="M0 0 L0 7 L8 3.5Z" fill="#009ef7"/></marker>
                        <marker id="pdsaDoArrow" markerWidth="9" markerHeight="9" refX="7" refY="3.5" orient="auto"><path d="M0 0 L0 7 L8 3.5Z" fill="#50cd89"/></marker>
                        <marker id="pdsaStudyArrow" markerWidth="9" markerHeight="9" refX="7" refY="3.5" orient="auto"><path d="M0 0 L0 7 L8 3.5Z" fill="#e6b400"/></marker>
                        <marker id="pdsaActArrow" markerWidth="9" markerHeight="9" refX="7" refY="3.5" orient="auto"><path d="M0 0 L0 7 L8 3.5Z" fill="#f1416c"/></marker>
                        <linearGradient id="pdsaRingGradient" x1="0" x2="1" y1="0" y2="1"><stop offset="0" stop-color="#009ef7"/><stop offset=".36" stop-color="#50cd89"/><stop offset=".68" stop-color="#ffc700"/><stop offset="1" stop-color="#f1416c"/></linearGradient>
                    </defs>
                    <circle cx="500" cy="500" r="356" fill="none" stroke="url(#pdsaRingGradient)" stroke-width="6" stroke-dasharray="3 10" opacity=".4"/>
                    <path d="M720 219 Q784 262 805 317" fill="none" stroke="#009ef7" stroke-width="5.5" stroke-linecap="round" marker-end="url(#pdsaPlanArrow)"/>
                    <path d="M781 720 Q738 784 683 805" fill="none" stroke="#50cd89" stroke-width="5.5" stroke-linecap="round" marker-end="url(#pdsaDoArrow)"/>
                    <path d="M280 781 Q216 738 195 683" fill="none" stroke="#e6b400" stroke-width="5.5" stroke-linecap="round" marker-end="url(#pdsaStudyArrow)"/>
                    <path d="M219 280 Q262 216 317 195" fill="none" stroke="#f1416c" stroke-width="5.5" stroke-linecap="round" marker-end="url(#pdsaActArrow)"/>
                </svg>

                <?php foreach ($phases as $phase => $config): ?>
                    <?php $completedCount = count(array_filter($phaseItems[$phase], static function ($item) { return (int) ($item->completed ?? 0) === 1; })); ?>
                    <article class="pdsa-stage pdsa-stage--<?= esc(strtolower($phase)) ?>">
                        <div class="card border border-<?= esc($config['color']) ?> border-2 shadow-sm h-100 overflow-hidden">
                            <div class="card-header border-0 bg-light-<?= esc($config['color']) ?> min-h-65px py-3 px-4">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="symbol symbol-35px symbol-circle bg-white"><span class="symbol-label"><i class="bi <?= esc($config['icon']) ?> text-<?= esc($config['color']) ?> fs-5"></i></span></span>
                                    <div><div class="fw-bolder text-<?= esc($config['color']) ?> fs-5"><?= esc($config['title']) ?></div><div class="text-gray-600 fs-9"><?= esc($config['subtitle']) ?></div></div>
                                </div>
                                <span class="badge badge-light-<?= esc($config['color']) ?> ms-auto pdsa-stage-count"><?= $completedCount ?>/<?= count($phaseItems[$phase]) ?></span>
                            </div>
                            <div class="card-body p-4 pdsa-stage-items">
                                <?php if ($phaseItems[$phase]): ?>
                                    <ul class="list-group list-group-flush">
                                        <?php foreach ($phaseItems[$phase] as $index => $item): ?>
                                            <?php $itemInputId = 'pdsa_item_done_' . preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $item->pdsa_item_id); ?>
                                            <li class="list-group-item d-flex align-items-start border-0 border-bottom px-0 py-3">
                                                <input class="form-check-input mt-1 flex-shrink-0 pdsa-item-completed" type="checkbox" id="<?= esc($itemInputId) ?>" data-item-id="<?= esc($item->pdsa_item_id) ?>" <?= (int) ($item->completed ?? 0) === 1 ? 'checked' : '' ?> <?= !$canManage ? 'disabled title="Unit ini hanya dapat melihat diagram"' : '' ?> aria-label="Tandai item <?= $index + 1 ?> sebagai selesai">
                                                <label class="form-check-label ms-3 fs-7 <?= (int) ($item->completed ?? 0) === 1 ? 'text-decoration-line-through text-gray-500' : 'text-gray-700' ?>" for="<?= esc($itemInputId) ?>"><?= nl2br(esc($item->item_text)) ?></label>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <div class="h-100 d-flex flex-column align-items-center justify-content-center text-gray-500 py-5">
                                        <i class="bi <?= esc($config['icon']) ?> fs-2x text-<?= esc($config['color']) ?> opacity-50 mb-3"></i>
                                        <span class="fs-8">Belum ada item pada tahap ini</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>

                <div class="pdsa-center d-flex align-items-center justify-content-center text-center">
                    <div class="px-4">
                        <span class="badge badge-light-primary rounded-pill mb-3">PERBAIKAN</span>
                        <h4 class="fw-bolder text-gray-900 fs-6 mb-3"><?= esc($cycle->title) ?></h4>
                        <div class="text-gray-600 fs-8 pdsa-center-problem" title="<?= esc($cycle->problem) ?>"><?= esc($cycle->problem) ?></div>
                        <?php if (!empty($cycle->aim)): ?><div class="text-primary fs-9 fw-semibold mt-3"><i class="bi bi-bullseye me-1"></i><?= esc($cycle->aim) ?></div><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="d-flex flex-wrap justify-content-center gap-3 mt-7">
            <?php foreach ($phases as $config): ?>
                <span class="badge badge-light-<?= esc($config['color']) ?> py-3 px-4"><i class="bi <?= esc($config['icon']) ?> me-2"></i><?= esc($config['title']) ?></span>
            <?php endforeach; ?>
        </div>
    </div>
</section>
