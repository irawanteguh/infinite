<script>
    const group_id = <?= json_encode($group_id, JSON_HEX_TAG | JSON_THROW_ON_ERROR) ?>;
    const org_id = <?= json_encode($org_id, JSON_HEX_TAG | JSON_THROW_ON_ERROR) ?>;
</script>
<main class="prem-page ca-screening">
    <header class="prem-header">
        <div class="prem-container d-flex flex-wrap align-items-center justify-content-between gap-3">
            <img src="<?= esc(base_url('assets/media/logos/rsudpasarminggu.png'), 'attr') ?>" alt="RSUD Pasar Minggu" class="prem-logo">
            <span class="prem-header-label"><span class="prem-dot"></span>Oncology centre</span>
        </div>
    </header>
    <div class="prem-container prem-main">
        <?php if ($error !== ''): ?>
            <div class="alert alert-danger mb-5" role="alert"><?= esc($error) ?></div>
        <?php endif; ?>

        <?php if ($result): ?>
            <?php
            $riskColors = ['Ringan' => 'success', 'Rata-rata' => 'success', 'Sedang' => 'warning', 'Berat' => 'danger', 'Tinggi' => 'danger', 'Average Risk (Risiko ringan)' => 'success', 'Moderate Risk (Risiko Sedang)' => 'warning', 'High Risk (Risiko Tinggi)' => 'danger'];
            $riskColor = $riskColors[$result['risk'] ?? ''] ?? 'secondary';
            ?>
            <section id="ca-success" class="prem-card prem-empty text-center" tabindex="-1" aria-live="polite">
                <span class="prem-success-icon"><i class="bi bi-check-lg" aria-hidden="true"></i></span>
                <span class="prem-eyebrow d-block mt-7">JAWABAN BERHASIL DIKIRIM</span>
                <h1 class="mt-3 mb-4">Terima kasih telah mengisi kuesioner.</h1>
                <p class="prem-lead mx-auto"><?= esc($result['name']) ?></p>
                <div class="ca-result-summary mx-auto my-5 ca-result-<?= $riskColor ?>">
                    <div>
                        <span class="d-block text-gray-600 fs-7 mb-2">Total skor</span>
                        <strong class="ca-result-value"><?= $result['total'] !== null ? esc((string) $result['total']) : '—' ?></strong>
                    </div>
                    <div>
                        <span class="d-block text-gray-600 fs-7 mb-2">Kategori risiko</span>
                        <strong class="ca-result-value"><?= esc($result['risk'] ?? 'Belum dapat ditentukan') ?></strong>
                    </div>
                </div>
                <?php if ($result['risk'] === null): ?>
                    <p class="text-muted">Kategori belum dapat ditentukan dari jawaban atau susunan kuesioner yang digunakan.</p>
                <?php endif; ?>
                <p class="text-muted mb-3 d-none" id="ca-result-countdown">
                    Kembali otomatis dalam <span role="timer" class="fw-semibold">60</span> detik.
                </p>
                <a id="ca-result-home" href="<?= esc($url . '&welcome=1', 'attr') ?>" class="btn btn-light">Kembali ke halaman awal</a>
            </section>
        <?php elseif (!$instrument): ?>
            <?php $choosingInstrument = request()->getGet('start') === '1'; ?>
            <?php if (!$choosingInstrument): ?>
            <section class="prem-card prem-welcome mb-5">
                <div class="prem-welcome-copy">
                    <span class="prem-eyebrow">Oncology centre</span>
                    <h1>Kepedulian untuk kesehatan Anda,<br><span>dimulai dari skrining.</span></h1>
                    <p class="prem-lead">Isi kuesioner sesuai kondisi dan riwayat kesehatan Anda untuk menilai risiko kanker paru atau kolorektal.</p>
                    <div class="prem-facts">
                        <span><i class="bi bi-list-check" aria-hidden="true"></i>2 pilihan kuesioner</span>
                        <span><i class="bi bi-person-check" aria-hidden="true"></i>Nama dan domisili opsional</span>
                    </div>
                    <a href="<?= esc($url . '&start=1', 'attr') ?>" class="btn prem-btn-primary">Mulai mengisi <i class="bi bi-arrow-right ms-2" aria-hidden="true"></i></a>
                </div>
                <aside class="prem-welcome-aside">
                    <span class="prem-hero-icon"><i class="bi bi-clipboard-heart" aria-hidden="true"></i></span>
                    <h2>AURA — A Place of Care, A Space for Hope</h2>
                    <p>Kenali risiko melalui skrining sesuai kondisi dan riwayat kesehatan Anda.</p>
                    <div class="prem-guide"><span>01</span><div>Pilih skrining kanker paru atau kolorektal.</div></div>
                    <div class="prem-guide"><span>02</span><div>Pilih satu jawaban pada setiap pertanyaan.</div></div>
                    <div class="prem-guide"><span>03</span><div>Periksa jawaban sebelum mengirim.</div></div>
                </aside>
            </section>
            <?php else: ?>
            <div class="prem-heading">
                <span class="prem-eyebrow">KUESIONER SKRINING KANKER</span>
                <h1>Penilaian risiko kanker</h1>
                <p>Pilih kuesioner skrining yang ingin Anda isi.</p>
            </div>
            <section id="ca-instrument-selection" class="prem-card prom-selection" aria-labelledby="ca-instrument-title">
                <div class="prem-section-heading">
                    <h2 id="ca-instrument-title">Pilih instrumen</h2>
                    <p>Kuesioner diisi sesuai kondisi dan riwayat kesehatan pasien.</p>
                </div>
                <?php if (!$instruments && $error === ''): ?>
                    <div class="alert alert-warning mb-0">Belum ada instrumen siap diisi. Simpan master awal dan aktifkan instrumen serta pertanyaannya di Master Kuesioner.</div>
                <?php endif; ?>
                <div class="prom-instrument-grid">
                    <?php foreach ($instruments as $item): ?>
                        <?php $activeCount = count(array_filter($item['questions'], static function ($question) {
                            return (string) $question['active'] === '1';
                        })); ?>
                        <?php
                        $enabled = $item['writable'] && (string) $item['active'] === '1' && $activeCount > 0;
                        $cardTitle = $item['code'] === 'IASTO' ? 'Skrining Kanker Paru' : 'Skrining Kanker Kolorektal';
                        ?>
                        <form method="post" action="<?= esc(site_url('ca/caskrining/start'), 'attr') ?>" class="h-100">
                            <?= csrf_field() ?>
                    <input type="hidden" name="group_id" value="<?= esc($group_id, 'attr') ?>">
                    <input type="hidden" name="org_id" value="<?= esc($org_id, 'attr') ?>">
                            <input type="hidden" name="code" value="<?= esc($item['code'], 'attr') ?>">
                            <button type="submit" class="prom-instrument-body ca-instrument-card w-100 text-start" <?= $enabled ? '' : 'disabled' ?>>
                                <span class="prem-hero-icon"><i class="bi bi-clipboard2-pulse" aria-hidden="true"></i></span>
                                <strong class="prom-instrument-name"><?= esc($cardTitle) ?></strong>
                                <span class="prom-respondent"><?= esc($item['name']) ?></span>
                                <span class="prom-measurement">Isi sesuai kondisi dan riwayat kesehatan pasien.</span>
                                <span class="prom-question-count"><i class="bi bi-list-check" aria-hidden="true"></i><?= $activeCount ?> pertanyaan</span>
                                <?php if (!$enabled): ?>
                                    <span class="text-muted"><?= (string) $item['active'] !== '1' ? 'Instrumen nonaktif' : 'Belum tersedia untuk pengisian' ?></span>
                                <?php endif; ?>
                            </button>
                        </form>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>
        <?php else: ?>
            <div class="prem-heading d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <span class="prem-eyebrow">KUESIONER <?= esc($instrument['code']) ?></span>
                    <h1><?= esc($instrument['name']) ?></h1>
                </div>
                <a href="<?= esc($url . '&start=1', 'attr') ?>" class="btn btn-light">Ganti instrumen</a>
            </div>
            <div id="ca-form-layout">
                <aside class="prem-sidebar d-none" id="ca-progress-panel">
                    <div class="prem-card prem-sidebar-inner">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-bold">Progres pengisian</span>
                            <span id="ca-progress-label" class="prem-progress-label"></span>
                        </div>
                        <div class="progress h-6px mb-6">
                            <div id="ca-progress" class="progress-bar" role="progressbar" aria-label="Pertanyaan terjawab" aria-valuemin="0" aria-valuemax="<?= count($instrument['questions']) ?>" aria-valuenow="0" style="width:0%"></div>
                        </div>
                        <ol class="prem-step-list list-unstyled mb-0">
                            <?php foreach (['Data pasien', 'Pertanyaan', 'Periksa jawaban'] as $index => $step): ?>
                                <li class="prem-step" data-ca-step><span class="prem-step-number"><?= $index + 1 ?></span><?= $step ?></li>
                            <?php endforeach; ?>
                        </ol>
                    </div>
                </aside>
                <form id="ca-screening-form" class="prem-card prem-form" action="<?= esc(site_url('ca/caskrining/submit'), 'attr') ?>" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="group_id" value="<?= esc($group_id, 'attr') ?>">
                    <input type="hidden" name="org_id" value="<?= esc($org_id, 'attr') ?>">
                    <input type="hidden" name="code" value="<?= esc($instrument['code'], 'attr') ?>">
                    <input type="hidden" name="form_token" value="<?= esc($token, 'attr') ?>">

                    <section data-ca-section aria-labelledby="ca-patient-title">
                        <div class="prem-section-heading">
                            <h2 id="ca-patient-title" tabindex="-1">Data pasien</h2>
                        </div>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="ca-patient" class="form-label">Nama pasien (opsional)</label>
                                <input id="ca-patient" name="respondent_name" class="form-control form-control-solid" maxlength="200" value="<?= esc($values['respondent_name'] ?? '', 'attr') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="ca-phone" class="form-label">No. HP (opsional)</label>
                                <div class="input-group">
                                    <span class="input-group-text" id="ca-phone-prefix">+62</span>
                                    <input id="ca-phone" name="phone_number" type="tel" inputmode="numeric" class="form-control form-control-solid" aria-describedby="ca-phone-prefix" placeholder="81234567890" pattern="[0-9]{8,13}" maxlength="13" value="<?= esc($values['phone_number'] ?? '', 'attr') ?>">
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-check form-check-custom form-check-solid">
                                    <input class="form-check-input" type="checkbox" name="contact_consent" value="1" <?= !empty($values['contact_consent']) ? 'checked' : '' ?>>
                                    <span class="form-check-label">Saya bersedia dihubungi terkait hasil skrining ini.</span>
                                </label>
                            </div>
                            <div class="col-12">
                                <fieldset>
                                    <legend class="form-label float-none">Domisili (opsional)</legend>
                                    <div class="prom-phase-grid">
                                        <?php foreach ($domiciles as $key => $label): ?>
                                            <label class="prom-phase-choice">
                                                <input type="radio" name="domicile" value="<?= esc($key, 'attr') ?>" <?= ($values['domicile'] ?? '') === $key ? 'checked' : '' ?>>
                                                <span><strong><?= esc($label) ?></strong><i class="bi bi-check-circle-fill prom-selected" aria-hidden="true"></i></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </fieldset>
                            </div>
                        </div>
                    </section>

                    <section data-ca-section class="mt-6" aria-labelledby="ca-questions-title">
                        <div class="prem-section-heading">
                            <h2 id="ca-questions-title" tabindex="-1">Pertanyaan skrining</h2>
                            <p>Pilih satu jawaban pada setiap pertanyaan.</p>

                        </div>
                        <?php foreach ($instrument['questions'] as $index => $question): ?>
                            <?php
                            $id = (string) $question['question_id'];
                            $selected = $values['answers'][$id] ?? null;
                            $options = $question['answer_options'];
                            ?>
                            <fieldset class="prem-question">
                                <legend>
                                    <span class="prem-question-number"><?= $index + 1 ?></span>
                                    <span class="prem-question-text"><?= esc($question['question']) ?></span>
                                </legend>
                                <div class="prem-choices">
                                    <?php foreach ($options as $key => [$label]): ?>
                                        <label class="prem-choice">
                                            <input type="radio" name="answers[<?= esc($id, 'attr') ?>]" value="<?= esc((string) $key, 'attr') ?>" <?= $selected === (string) $key ? 'checked' : '' ?> required>
                                            <span class="prem-choice-body"><span class="prem-choice-label"><?= esc($label) ?></span><i class="bi bi-check-circle-fill" aria-hidden="true"></i></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                                <p class="prem-question-error d-none" role="alert">Silakan pilih satu jawaban.</p>
                            </fieldset>
                        <?php endforeach; ?>
                    </section>

                    <section data-ca-section class="d-none" aria-labelledby="ca-review-title">
                        <div class="prem-section-heading">
                            <h2 id="ca-review-title" tabindex="-1">Periksa jawaban</h2>
                            <p>Pastikan jawaban sesuai sebelum menyimpan skrining.</p>
                        </div>
                        <div id="ca-review-answers"></div>
                    </section>

                    <div class="prem-form-footer justify-content-end">
                        <button type="button" id="ca-screen-back" class="btn btn-light d-none me-auto">Kembali</button>
                        <button type="button" id="ca-screen-next" class="btn prem-btn-primary d-none">Selanjutnya <i class="bi bi-arrow-right ms-2" aria-hidden="true"></i></button>
                        <button type="submit" id="ca-screen-submit" class="btn prem-btn-primary">Simpan skrining <i class="bi bi-send ms-2" aria-hidden="true"></i></button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
        <footer class="prem-footer">RSUD Pasar Minggu <span>·</span> AURA — A Place of Care, A Space for Hope</footer>
    </div>
</main>
