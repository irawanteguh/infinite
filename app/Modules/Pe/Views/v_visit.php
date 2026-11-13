<?php
$isProm = $surveyType === 'PROM';
$requiresRespondentData = \Modules\Pe\Config\GeneralQuestionnaires::service($instrument['code'] ?? '') !== null;
$path = $surveyPath ?? ($isProm ? 'pe/prom' : 'pe/prem');
$phase = $context['measurement_phase'] ?? null;
$tenant = $tenant ?? ['group_id' => '', 'org_id' => ''];
$publicUrl = site_url($path) . '?' . http_build_query($tenant);
$visitAction = site_url($path . '/visit') . '?' . http_build_query($tenant);
?>
<main class="prem-page">
    <header class="prem-header"><div class="prem-container"><img src="<?= base_url('assets/media/logos/rsudpasarminggu.png') ?>" alt="RSUD Pasar Minggu" class="prem-logo"></div></header>
    <div class="prem-container prem-main">
        <div class="prem-heading"><span class="prem-eyebrow">SEBELUM MENGISI KUESIONER <?= $surveyType ?></span><h1><?= $requiresRespondentData ? 'DATA RESPONDEN' : 'Data kunjungan pasien' ?></h1><p><?= $requiresRespondentData ? 'Lengkapi data responden untuk melanjutkan ke kuesioner ' . esc($instrument['name']) . '.' : (isset($context['fixed_service_type']) ? 'Masukkan nomor rekam medis untuk melanjutkan ke kuesioner.' : 'Pilih jenis pelayanan dan masukkan nomor rekam medis untuk melanjutkan ke kuesioner.') ?></p></div>
        <div class="prem-card prem-form prem-visit-page">
            <?php if ($error !== ''): ?><div class="alert alert-danger" role="alert"><?= esc($error) ?></div><?php endif; ?>
            <?php if ($instrument): ?>
                <div class="prem-section-heading"><h2><?= esc($instrument['name']) ?></h2><?php if ($phase): ?><p><?= $phase === 'T0' ? 'PRE / T0 · Sebelum atau awal perawatan' : 'POST / T1 · Saat pulang perawatan' ?></p><?php endif; ?></div>
                <form id="prem-visit-form" action="<?= esc($visitAction, 'attr') ?>" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="submission_token" value="<?= esc($token, 'attr') ?>">
                    <?php if (!isset($context['fixed_service_type'])): ?>
                    <fieldset class="border-0 p-0 mb-7">
                        <legend class="float-none fs-6 fw-bold required">Jenis pelayanan</legend>
                        <div class="prem-service-choices">
                            <?php foreach (['RJ' => 'Rawat Jalan', 'RI' => 'Rawat Inap', 'IGD' => 'IGD'] as $value => $label): if (isset($context['fixed_service_type']) && $context['fixed_service_type'] !== $value) continue; ?>
                                <label class="prem-choice"><input type="radio" name="service_type" value="<?= $value ?>" required<?= ($context['fixed_service_type'] ?? $context['service_type'] ?? '') === $value ? ' checked' : '' ?>><span class="prem-choice-body"><span><?= $label ?></span><i class="bi bi-check-circle-fill"></i></span></label>
                            <?php endforeach; ?>
                        </div>
                    </fieldset>
                    <?php endif; ?>
                    <div class="mb-7"><label for="prem-medical-record" class="form-label required"><?= $requiresRespondentData ? 'Medical Record' : 'Nomor medical record (rekam medis)' ?></label><input id="prem-medical-record" name="medical_record_number" type="text" class="form-control form-control-solid" maxlength="50" pattern="[A-Za-z0-9][A-Za-z0-9./-]{0,49}" autocomplete="off" placeholder="Masukkan nomor rekam medis" value="<?= esc($context['medical_record_number'] ?? '', 'attr') ?>" required><div class="form-text">Masukkan sesuai nomor rekam medis pasien, termasuk angka nol di depan.</div></div>
                    <?php if ($requiresRespondentData): ?><?= view('Modules\Pe\Views\v_inpatient_respondent', ['context' => $context]) ?><?php endif; ?>
                    <div class="prem-form-footer"><a href="<?= esc($publicUrl, 'attr') ?>" class="btn btn-light">Kembali</a><button type="submit" class="btn prem-btn-primary">Lanjut ke kuesioner <i class="bi bi-arrow-right ms-2"></i></button></div>
                </form>
            <?php else: ?><a href="<?= esc($publicUrl, 'attr') ?>" class="btn prem-btn-primary">Buka ulang kuesioner</a><?php endif; ?>
        </div>
        <footer class="prem-footer">RSUD Pasar Minggu <span>·</span> <?= $surveyType === 'SKM' ? 'Survei Kepuasan Masyarakat' : ($isProm ? 'Patient-Reported Outcome Measures' : 'Patient-Reported Experience Measures') ?></footer>
    </div>
</main>