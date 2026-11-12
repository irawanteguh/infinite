<?php
$isProm = ($surveyType ?? 'PREM') === 'PROM';
$phaseLabel = ($phase ?? '') === 'T0' ? 'PRE / T0 · Sebelum atau awal perawatan' : 'POST / T1 · Saat pulang perawatan';
$labels = [1 => 'Sangat tidak setuju', 2 => 'Tidak setuju', 3 => 'Ragu-ragu', 4 => 'Setuju', 5 => 'Sangat setuju'];
$sections = [];
foreach ($questions as $question) {
    $key = $question['domain_id'];
    if (!isset($sections[$key])) $sections[$key] = ['name' => $question['domain'], 'questions' => []];
    $sections[$key]['questions'][] = $question;
}
$sections = array_values($sections);
?>
<main class="prem-page" id="prem-app">
    <header class="prem-header">
        <div class="prem-container d-flex align-items-center justify-content-between gap-3">
            <img src="<?= base_url('assets/media/logos/rsudpasarminggu.png') ?>" alt="RSUD Pasar Minggu" class="prem-logo">
        </div>
    </header>
    <div class="prem-container prem-main">
        <?php if ($error !== ''): ?>
            <section class="prem-card prem-empty text-center">
                <span class="prem-hero-icon"><i class="bi bi-clipboard-heart"></i></span>
                <h1 class="mt-6 mb-4">Kuesioner belum tersedia</h1>
                <p class="text-muted mb-0"><?= esc($error) ?></p>
            </section>
        <?php else: ?>
            <section id="prem-welcome" class="prem-card prem-welcome">
                <div class="prem-welcome-copy">
                    <span class="prem-eyebrow"><?= $isProm ? 'HASIL PERAWATAN PASIEN' : 'PENGALAMAN PELAYANAN PASIEN' ?></span>
                    <?php if ($isProm): ?>
                        <h1>Bagaimana kondisi<br><span>kesehatan pasien saat ini?</span></h1>
                        <p class="prem-lead">Isi penilaian kondisi dan kemampuan pasien sesuai tahap pengukuran yang dipilih.</p>
                        <h2 class="fs-5 mb-3"><?= esc($instrument['name']) ?></h2>
                        <div class="badge badge-light-primary mb-3"><?= esc($phaseLabel) ?></div>
                        <p class="text-muted fs-7 mb-2">Responden: <?= esc($instrument['respondent']) ?></p>
                        <p class="text-muted fs-7"><?= esc($instrument['measurement']) ?></p>
                    <?php else: ?>
                        <h1>Pelayanan yang lebih baik,<br><span>dimulai dari pengalaman Anda.</span></h1>
                        <p class="prem-lead">Ceritakan pengalaman Anda selama menerima pelayanan di rumah sakit. Penilaian Anda membantu kami memahami hal yang sudah baik dan yang perlu diperbaiki.</p>
                    <?php endif; ?>
                    <div class="prem-facts"><span><i class="bi bi-list-check"></i> <?= count($questions) ?> pertanyaan</span><span><i class="bi bi-person-check"></i> Siapkan nomor rekam medis pasien</span></div>
                    <button type="button" id="prem-start" class="btn prem-btn-primary">Mulai mengisi <i class="bi bi-arrow-right ms-2"></i></button>
                    <?php if ($isProm): ?><a href="<?= site_url('pe/prom') ?>" class="btn btn-light ms-2">Ganti pilihan</a><?php endif; ?>
                </div>
                <aside class="prem-welcome-aside">
                    <span class="prem-hero-icon"><i class="bi bi-chat-heart"></i></span>
                    <h2><?= $isProm ? 'Kenali perkembangan kondisi pasien.' : 'Pengalaman Anda berarti.' ?></h2>
                    <p><?= $isProm ? 'Tidak ada jawaban benar atau salah. Pilih jawaban yang paling sesuai dengan kondisi pasien saat ini.' : 'Berikan penilaian sesuai pengalaman pelayanan yang Anda terima hari ini.' ?></p>
                    <div class="prem-guide"><span>01</span><div>Pilih satu jawaban untuk setiap pernyataan.</div></div>
                    <div class="prem-guide"><span>02</span><div><?= $isProm ? 'Gunakan skala 1 sampai 5. Nilai lebih tinggi menunjukkan kondisi atau kemampuan yang lebih baik.' : 'Gunakan skala 1 sampai 5 sesuai pengalaman Anda.' ?></div></div>
                    <div class="prem-guide"><span>03</span><div>Periksa jawaban sebelum mengirim.</div></div>
                </aside>
            </section>

            <div id="prem-survey" class="d-none">
                <div class="prem-heading"><span class="prem-eyebrow">KUESIONER <?= $isProm ? 'PROM' : 'PREM' ?></span><h1><?= esc($instrument['name']) ?></h1><p><?= $isProm ? esc($phaseLabel) . ' — Berikan penilaian berdasarkan kondisi pasien saat ini.' : 'Mohon berikan penilaian berdasarkan pengalaman pelayanan Anda hari ini.' ?></p></div>
                <div class="prem-layout">
                    <aside class="prem-sidebar">
                        <div class="prem-card prem-sidebar-inner">
                            <div class="d-flex justify-content-between align-items-center mb-3"><span class="fw-bold">Progres pengisian</span><span id="prem-progress-label" class="prem-progress-label">0/<?= count($questions) ?></span></div>
                            <div class="progress h-6px mb-6"><div id="prem-progress" class="progress-bar" role="progressbar" aria-label="Pertanyaan terjawab" aria-valuemin="0" aria-valuemax="<?= count($questions) ?>" aria-valuenow="0" style="width:0%"></div></div>
                            <nav aria-label="Bagian kuesioner" class="prem-step-list">
                                <?php foreach ($sections as $index => $section): ?>
                                    <button type="button" class="prem-step" data-step="<?= $index ?>"><span class="prem-step-number"><?= $index + 1 ?></span><span><?= esc($section['name']) ?></span><i class="bi bi-check-circle-fill prem-step-check d-none"></i></button>
                                <?php endforeach; ?>
                            </nav>
                            <div class="prem-sidebar-note"><i class="bi bi-info-circle"></i> <?= $isProm ? 'Pilih jawaban yang paling sesuai dengan kondisi pasien saat ini.' : 'Pilih jawaban yang paling sesuai dengan pengalaman Anda.' ?></div>
                        </div>
                    </aside>
                    <form id="prem-form" class="prem-card prem-form" action="<?= esc(site_url($isProm ? 'pe/prom/submit' : 'pe/prem/submit'), 'attr') ?>" method="post" novalidate data-total="<?= count($questions) ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="instrument_id" value="<?= esc($instrument['instrument_id'], 'attr') ?>">
                        <input type="hidden" name="submission_token" value="<?= esc($submissionToken, 'attr') ?>">
                        <input type="hidden" name="revision" value="<?= esc($revision, 'attr') ?>">
                        <div id="prem-form-error" class="alert alert-danger d-none" role="alert" tabindex="-1"></div>
                        <div id="prem-visit" class="mb-8">
                            <div class="prem-section-heading"><h2>Data kunjungan pasien</h2><p>Pilih jenis pelayanan dan masukkan nomor rekam medis pasien sebelum mengisi kuesioner.</p></div>
                            <div class="row g-5">
                                <div class="col-md-6">
                                    <label for="prem-service-type" class="form-label required">Jenis pelayanan</label>
                                    <select id="prem-service-type" name="service_type" class="form-select form-select-solid" data-control="select2" data-placeholder="Pilih jenis pelayanan" required>
                                        <option value=""></option>
                                        <option value="RJ">Rawat Jalan</option>
                                        <option value="RI">Rawat Inap</option>
                                        <option value="IGD">IGD</option>
                                    </select>
                                    <p id="prem-service-error" class="prem-question-error d-none" role="alert">Pilih jenis pelayanan.</p>
                                </div>
                                <div class="col-md-6">
                                    <label for="prem-medical-record" class="form-label required">Nomor medical record (rekam medis)</label>
                                    <input id="prem-medical-record" name="medical_record_number" type="text" class="form-control form-control-solid" maxlength="50" pattern="[A-Za-z0-9][A-Za-z0-9./-]{0,49}" autocomplete="off" placeholder="Masukkan nomor rekam medis" required>
                                    <div class="form-text">Masukkan sesuai nomor rekam medis pasien, termasuk angka nol di depan.</div>
                                </div>
                            </div>
                        </div>
                        <?php foreach ($sections as $index => $section): ?>
                            <section class="prem-section<?= $index !== 0 ? ' d-none' : '' ?>" data-section="<?= $index ?>" aria-labelledby="prem-section-title-<?= $index ?>">
                                <div class="prem-section-heading"><span class="prem-eyebrow">BAGIAN <?= $index + 1 ?> DARI <?= count($sections) ?></span><h2 id="prem-section-title-<?= $index ?>" tabindex="-1"><?= esc($section['name']) ?></h2><p>Pilih satu jawaban pada setiap pernyataan di bawah ini.</p></div>
                                <?php foreach ($section['questions'] as $question): ?>
                                    <fieldset class="prem-question" data-question-id="<?= esc($question['question_id'], 'attr') ?>">
                                        <legend><span class="prem-question-number"><?= esc($question['sort_order']) ?></span><span class="prem-question-text"><?= esc($question['question']) ?></span></legend>
                                        <div class="prem-choices">
                                            <?php foreach ($labels as $score => $label): ?>
                                                <label class="prem-choice"><input type="radio" name="answers[<?= esc($question['question_id'], 'attr') ?>]" value="<?= $score ?>"><span class="prem-choice-body"><span class="prem-score"><?= $score ?></span><span><?= esc($label) ?></span><i class="bi bi-check-circle-fill"></i></span></label>
                                            <?php endforeach; ?>
                                        </div>
                                        <p class="prem-question-error d-none">Silakan pilih satu jawaban.</p>
                                    </fieldset>
                                <?php endforeach; ?>
                            </section>
                        <?php endforeach; ?>
                        <section id="prem-review" class="d-none" aria-labelledby="prem-review-title">
                            <div class="prem-section-heading"><span class="prem-eyebrow">SATU LANGKAH LAGI</span><h2 id="prem-review-title" tabindex="-1">Periksa jawaban Anda</h2><p>Pastikan semua jawaban sudah sesuai sebelum dikirim.</p></div>
                            <div id="prem-review-answers"></div>
                        </section>
                        <div class="prem-form-footer"><button type="button" id="prem-back" class="btn btn-light">Kembali</button><span id="prem-step-label" class="text-muted"></span><button type="button" id="prem-next" class="btn prem-btn-primary">Selanjutnya <i class="bi bi-arrow-right ms-2"></i></button><button type="submit" id="prem-submit" class="btn prem-btn-primary d-none">Kirim jawaban <i class="bi bi-send ms-2"></i></button></div>
                    </form>
                </div>
            </div>

            <section id="prem-success" class="prem-card prem-empty text-center d-none" tabindex="-1" aria-live="polite" data-return-url="<?= esc(site_url($isProm ? 'pe/prom' : 'pe/prem'), 'attr') ?>">
                <span class="prem-success-icon"><i class="bi bi-check-lg"></i></span>
                <span class="prem-eyebrow d-block mt-7">JAWABAN BERHASIL DIKIRIM</span>
                <h1 class="mt-3 mb-4">Terima kasih telah mengisi kuesioner.</h1>
                <p class="prem-lead mx-auto">Masukan Anda menjadi bagian dari upaya kami memberikan pelayanan yang lebih baik.</p>
                <div class="prem-receipt">Referensi pengisian: <strong id="prem-receipt"></strong></div>
                <p class="text-muted mt-6 mb-4">Kembali ke halaman awal dalam <strong id="prem-return-seconds">10</strong> detik.</p>
                <a href="<?= esc(site_url($isProm ? 'pe/prom' : 'pe/prem'), 'attr') ?>" class="btn btn-light">Kembali sekarang</a>
            </section>
        <?php endif; ?>
        <footer class="prem-footer">RSUD Pasar Minggu <span>·</span> <?= $isProm ? 'Patient-Reported Outcome Measures' : 'Patient-Reported Experience Measures' ?></footer>
    </div>
</main>
