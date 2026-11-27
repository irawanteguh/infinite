<?php
$services = ['Rawat inap', 'Rawat jalan'];
$periods = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni'];
$counts = [[18, 24, 21, 27, 29, 31], [25, 28, 26, 32, 34, 36]];
$palette = [
    ['Primer', '#087e82', ''],
    ['Hover primer', '#066b6e', ''],
    ['Aksen lembut', '#eaf6f4', 'text-dark'],
    ['Teks utama', '#234a54', ''],
    ['Merah soft · latar', '#fde9e9', 'text-dark'],
    ['Merah · teks', '#a63c45', ''],
    ['Kuning soft · latar', '#fff4d6', 'text-dark'],
    ['Kuning · teks', '#805800', ''],
];
?>

<div id="testing-page">
    <header class="mb-6">
        <h1>Contoh komponen Infinite</h1>
        <p class="text-muted mb-0">Contoh komponen dan interaksi antarmuka dengan data dummy.</p>
    </header>

    <section id="testing-controls" class="card mb-6" aria-labelledby="testing-controls-title">
        <div class="card-header">
            <div>
                <h2 id="testing-controls-title">Input, select, dan button</h2>
                <p class="text-muted mb-0">Padding 6px vertikal dan 10px horizontal, tinggi minimum 34px. Select menyediakan ruang 32px di kanan untuk panah.</p>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-4 align-items-end">
                <div class="col-md-4">
                    <label for="testing-control-input" class="form-label">Input</label>
                    <input id="testing-control-input" type="text" class="form-control" placeholder="Masukkan nilai">
                </div>
                <div class="col-md-4">
                    <label for="testing-control-select" class="form-label">Select</label>
                    <select id="testing-control-select" class="form-select">
                        <option>Text</option>
                        <option>Secret</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="testing-control-select2" class="form-label">Select2</label>
                    <select id="testing-control-select2" class="form-select" data-control="select2" data-hide-search="true" data-width="100%">
                        <option>Text</option>
                        <option>Secret</option>
                    </select>
                </div>
                <div class="col-12 d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-primary">Simpan</button>
                    <button type="button" class="btn btn-light-primary">Tutup</button>
                    <button type="button" class="btn btn-primary" disabled>Menyimpan…</button>
                </div>
            </div>
        </div>
    </section>

    <div class="row g-5 mb-6">
        <?php foreach ([
            'chaotic' => 'Pola abstrak',
            'triangles' => 'Geometri segitiga',
            'squares' => 'Pola persegi',
            'floral' => 'Ornamen floral',
            'waves' => 'Gelombang organik',
            'diagonal' => 'Geometri diagonal',
        ] as $pattern => $label): ?>
            <div class="col-12">
                <section class="app-geometric-banner testing-banner testing-banner-<?= esc($pattern, 'attr') ?>" aria-labelledby="testing-banner-<?= esc($pattern, 'attr') ?>">
                    <h2 id="testing-banner-<?= esc($pattern, 'attr') ?>"><?= esc($label) ?></h2>
                    <p>Ringkasan informasi dan aktivitas organisasi dalam satu tempat.</p>
                </section>
            </div>
        <?php endforeach ?>
    </div>

    <section id="testing-filters" class="app-filter-card" aria-labelledby="testing-filter-title">
        <div class="app-filter-heading">
            <h2 id="testing-filter-title"><i class="bi bi-funnel me-2" aria-hidden="true"></i>Filter data</h2>
            <p>Pilih layanan dan rentang tanggal untuk melihat perubahan ringkasan, grafik, dan tabel.</p>
        </div>
        <form id="testing-filter-form" class="app-filter-fields">
            <div>
                <label for="testing-service">Jenis layanan</label>
                <select id="testing-service" class="form-select" data-control="select2" data-hide-search="true">
                    <option value="">Semua layanan</option>
                    <?php foreach ($services as $index => $service): ?>
                        <option value="<?= $index ?>"><?= esc($service) ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <div>
                <label for="testing-date-start">Dari tanggal</label>
                <input id="testing-date-start" class="form-control" type="date">
            </div>
            <div>
                <label for="testing-date-end">Sampai tanggal</label>
                <input id="testing-date-end" class="form-control" type="date">
            </div>
            <button type="submit" class="btn btn-primary">Terapkan</button>
            <button type="button" id="testing-reset" class="btn btn-light-primary">Atur ulang</button>
        </form>
        <div id="testing-filter-message" class="app-filter-scope" data-testing-tone="teal" aria-live="polite">Periode simulasi Januari–Juni 2026.</div>
    </section>

    <section id="testing-print-summary" class="mb-5" aria-labelledby="testing-summary">
        <h2 id="testing-summary" class="mb-3">Ringkasan data</h2>
        <p id="testing-print-period" class="d-none d-print-block"></p>
        <div class="row g-4">
            <?php foreach ([
                ['testing-total', 'Total kunjungan', 'bi-clipboard-data', array_sum($counts[0]) + array_sum($counts[1])],
                ['testing-inpatient', 'Rawat inap', 'bi-building', array_sum($counts[0])],
                ['testing-outpatient', 'Rawat jalan', 'bi-people', array_sum($counts[1])],
            ] as [$id, $label, $icon, $value]): ?>
                <div class="col-md-4">
                    <div class="card border h-100 testing-summary-card<?= $id === 'testing-total' ? ' testing-summary-card-primary' : '' ?>">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                                <div class="fw-semibold testing-summary-label"><?= esc($label) ?></div>
                                <span class="d-inline-flex align-items-center justify-content-center rounded-3 flex-shrink-0 testing-summary-icon" data-testing-tone="teal">
                                    <i class="bi <?= $icon ?> fs-4" aria-hidden="true"></i>
                                </span>
                            </div>
                            <div class="d-flex align-items-baseline flex-wrap gap-2">
                                <span id="<?= $id ?>" class="fw-bold testing-summary-value"><?= $value ?></span>
                                <span class="text-muted">kunjungan</span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach ?>
        </div>
    </section>

    <div class="row g-4 mb-5">
        <div class="col-xl-6">
            <section class="card h-100" aria-labelledby="testing-chart-title">
                <div class="card-header">
                    <div>
                        <h2 id="testing-chart-title" class="mb-1">Grafik batang bertumpuk</h2>
                        <p class="text-muted mb-0">Jumlah kunjungan per bulan dan jenis layanan.</p>
                    </div>
                </div>
                <div class="card-body">
                    <div id="testing-chart"></div>
                </div>
            </section>
        </div>
        <div class="col-xl-6">
            <section class="card h-100" aria-labelledby="testing-line-title">
                <div class="card-header">
                    <div>
                        <h2 id="testing-line-title" class="mb-1">Grafik garis</h2>
                        <p class="text-muted mb-0">Perubahan jumlah kunjungan dari bulan ke bulan.</p>
                    </div>
                </div>
                <div class="card-body"><div id="testing-line-chart"></div></div>
            </section>
        </div>
        <div class="col-xl-6">
            <section class="card h-100" aria-labelledby="testing-reference-title">
                <div class="card-header">
                    <div>
                        <h2 id="testing-reference-title" class="mb-1">Grafik dengan garis acuan</h2>
                        <p class="text-muted mb-0">Nilai bulanan per layanan dengan garis acuan.</p>
                    </div>
                </div>
                <div class="card-body">
                    <div id="testing-reference-chart" role="img" aria-label="Nilai bulanan per jenis layanan dan garis acuan"></div>
                    <div class="alert mb-0 mt-3" data-testing-tone="teal" role="note">
                        <i class="bi bi-info-circle me-2 testing-status-icon" aria-hidden="true"></i>
                        Garis acuan menunjukkan 30 kunjungan per bulan.
                    </div>
                </div>
            </section>
        </div>
        <div class="col-xl-6">
            <section class="card h-100" aria-labelledby="testing-donut-title">
                <div class="card-header">
                    <div>
                        <h2 id="testing-donut-title" class="mb-1">Grafik donut</h2>
                        <p class="text-muted mb-0">Proporsi kunjungan menurut jenis layanan.</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-center gap-4">
                        <div id="testing-donut-chart" role="img" aria-label="Komposisi kunjungan menurut jenis layanan"></div>
                        <ul id="testing-donut-legend" class="list-unstyled flex-grow-1 mb-0" aria-label="Rincian komposisi kunjungan">
                            <?php foreach ($services as $index => $service): ?>
                                <li data-service="<?= $index ?>" class="border-bottom py-3" hidden>
                                    <div class="d-flex justify-content-between align-items-center gap-3">
                                        <span class="d-flex align-items-center gap-2 text-muted">
                                            <span class="testing-donut-dot rounded-circle" aria-hidden="true"></span><?= esc($service) ?>
                                        </span>
                                        <span class="text-end">
                                            <strong class="testing-donut-count d-block"></strong>
                                            <small class="testing-donut-percent text-muted"></small>
                                        </span>
                                    </div>
                                </li>
                            <?php endforeach ?>
                        </ul>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <section class="card mb-5" aria-labelledby="testing-contributors-title">
        <div class="card-header">
            <div>
                <h2 id="testing-contributors-title" class="mb-1">Grafik peringkat</h2>
                <p class="text-muted mb-0">Enam bulan dengan jumlah kunjungan tertinggi pada filter aktif.</p>
            </div>
        </div>
        <div class="card-body">
            <ul id="testing-contributors" class="list-unstyled mb-0"></ul>
            <p id="testing-contributors-empty" class="text-muted mb-0" hidden>Belum ada kunjungan pada periode ini.</p>
            <template id="testing-contributor-row">
                <li class="mb-4">
                    <div class="d-flex justify-content-between align-items-baseline gap-3 mb-2">
                        <span class="text-muted testing-contributor-name"></span>
                        <strong class="text-end testing-contributor-value"></strong>
                    </div>
                    <div class="progress testing-contributor-progress" aria-hidden="true">
                        <div class="progress-bar"></div>
                    </div>
                    <small class="text-muted testing-contributor-share"></small>
                </li>
            </template>
        </div>
    </section>

    <section class="card app-table-card mb-5" aria-labelledby="testing-table-title">
        <div class="card-header app-table-heading">
            <div>
                <h2 id="testing-table-title" class="app-table-title">Tabel data</h2>
                <p class="app-table-description">Rincian kunjungan bulanan per jenis layanan.</p>
            </div>
            <div class="app-table-search">
                <input id="testing-search" type="search" class="form-control" aria-label="Cari bulan atau layanan" placeholder="Cari bulan atau layanan">
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="testing-table" class="table table-row-dashed align-middle w-100 fs-8 gy-2" data-datatable="manual">
                    <thead>
                        <tr>
                            <th scope="col">Bulan</th>
                            <th scope="col">Jenis layanan</th>
                            <th scope="col" class="text-end">Kunjungan</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($periods as $month => $period): ?>
                            <?php foreach ($services as $index => $service): ?>
                                <tr data-date="2026-<?= sprintf('%02d', $month + 1) ?>-01" data-service="<?= $index ?>">
                                    <td><?= esc($period) ?></td>
                                    <td><?= esc($service) ?></td>
                                    <td class="text-end"><?= $counts[$index][$month] ?></td>
                                    <td class="text-end">
                                        <div class="btn-group" role="group" aria-label="<?= esc('Actions ' . $service . ' ' . $period . ' 2026', 'attr') ?>">
                                            <button type="button" class="btn btn-light-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                Actions
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#testing-modal">
                                                    <i class="bi bi-eye me-2" aria-hidden="true"></i>Detail
                                                </button>
                                                <button type="button" class="dropdown-item" data-testing-copy>
                                                    <i class="bi bi-clipboard me-2" aria-hidden="true"></i>Salin
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
            <div id="testing-action-status" class="rounded" data-testing-tone="teal" role="status"></div>
        </div>
    </section>

    <section class="card mb-5 testing-reading-panel" aria-labelledby="testing-reading-title">
        <div class="card-body">
            <header class="mb-4">
                <div class="testing-reading-eyebrow fw-semibold text-uppercase mb-1">Panduan membaca data</div>
                <h2 id="testing-reading-title" class="mb-1"><i class="bi bi-info-circle me-2" aria-hidden="true"></i>Dasar pembacaan</h2>
                <p class="mb-0">Pahami sumber nilai dan rumus sebelum menafsirkan hasil dashboard.</p>
            </header>
            <div class="row gx-6 gy-3 testing-reading-grid">
                <div class="col-sm-6">
                    <h3>Nilai bulanan</h3>
                    <p>Grafik menampilkan jumlah kunjungan simulasi per bulan untuk setiap layanan.</p>
                </div>
                <div class="col-sm-6">
                    <h3>Garis acuan</h3>
                    <p>Bandingkan setiap nilai bulanan dengan <strong>acuan 30 kunjungan</strong>.</p>
                </div>
                <div class="col-sm-6">
                    <h3>Total per layanan</h3>
                    <p>Grafik donut menjumlahkan nilai tiap layanan untuk periode yang dipilih.</p>
                </div>
                <div class="col-sm-6">
                    <h3>Peringkat bulanan</h3>
                    <p>Grafik peringkat mengurutkan bulan berdasarkan jumlah kunjungan.</p>
                </div>
            </div>
            <div class="testing-reading-period mt-3 pt-3" role="note">
                <i class="bi bi-calendar3 me-2" aria-hidden="true"></i>Bulan yang bersinggungan dengan rentang tanggal ikut dihitung.
            </div>
        </div>
    </section>

    <section class="card mb-5" aria-labelledby="testing-foundations">
        <div class="card-header"><h2 id="testing-foundations" class="card-title">Elemen antarmuka</h2></div>
        <div class="card-body">
            <div class="row g-5">
                <div class="col-lg-6">
                    <h3>Warna</h3>
                    <div class="row g-3">
                        <?php foreach ($palette as [$label, $color, $textClass]): ?>
                            <div class="col-sm-6">
                                <div class="rounded p-3 <?= esc($textClass ?: 'text-white', 'attr') ?>" style="background-color: <?= esc($color, 'attr') ?>">
                                    <strong class="d-block"><?= esc($label) ?></strong>
                                    <span><?= esc(strtoupper($color)) ?></span>
                                </div>
                            </div>
                        <?php endforeach ?>
                    </div>
                </div>
                <div class="col-lg-6">
                    <h3>Tipografi dan jarak</h3>
                    <div class="border rounded p-4">
                        <div class="h2 mb-2">Ringkasan layanan</div>
                        <div class="h3 mb-2">Perbandingan bulanan</div>
                        <p class="mb-2">Jumlah kunjungan berdasarkan jenis layanan.</p>
                        <small class="text-muted">Data simulasi Januari–Juni 2026</small>
                    </div>
                </div>
                <div class="col-12">
                    <h3>Tombol dan badge</h3>
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#testing-modal">Lihat detail</button>
                        <a class="btn btn-light-primary" href="#testing-filters">Ke filter</a>
                        <button type="button" class="btn btn-primary" disabled>Nonaktif</button>
                        <span class="badge" data-testing-tone="teal">Selesai</span>
                        <span class="badge" data-testing-tone="yellow">Menunggu</span>
                        <span class="badge" data-testing-tone="red">Gagal</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="card mb-5" aria-labelledby="testing-states">
        <div class="card-header"><h2 id="testing-states" class="card-title">Status dan modal</h2></div>
        <div class="card-body">
            <div class="row g-3">
                <?php foreach ([
                    ['teal', 'bi-info-circle', 'Informasi', 'Data di halaman ini adalah simulasi.'],
                    ['teal', 'bi-check-circle', 'Berhasil', 'Filter berhasil diterapkan.'],
                    ['yellow', 'bi-inbox', 'Data kosong', 'Tidak ada kunjungan pada periode yang dipilih.'],
                    ['red', 'bi-exclamation-circle', 'Gagal memuat', 'Coba muat ulang data.'],
                ] as [$tone, $icon, $label, $message]): ?>
                    <div class="col-md-6">
                        <div class="alert h-100 mb-0" data-testing-tone="<?= $tone ?>">
                            <strong class="d-block"><i class="bi <?= $icon ?> me-2 testing-status-icon" aria-hidden="true"></i><?= esc($label) ?></strong>
                            <?= esc($message) ?>
                        </div>
                    </div>
                <?php endforeach ?>
                <div class="col-md-6">
                    <div class="alert h-100 mb-0" data-testing-tone="yellow">
                        <strong class="d-block"><span class="spinner-border spinner-border-sm me-2 testing-status-icon" aria-hidden="true"></span>Memuat data</strong>
                        Menyiapkan ringkasan kunjungan.
                    </div>
                </div>
                <div class="col-md-6 d-flex flex-wrap align-items-center gap-2">
                    <button type="button" class="btn btn-light-primary" data-bs-toggle="modal" data-bs-target="#testing-modal">Buka modal contoh</button>
                    <button type="button" class="btn btn-primary" id="testing-report-progress">
                        <i class="bi bi-file-earmark-pdf me-1" aria-hidden="true"></i>Buat laporan contoh
                    </button>
                    <button type="button" class="btn btn-light-primary" id="testing-import-progress">
                        <i class="bi bi-upload me-1" aria-hidden="true"></i>Simulasi impor E-Klaim
                    </button>
                </div>
            </div>
        </div>
    </section>

    <section id="testing-question-filter" class="app-filter-card" aria-labelledby="testing-question-filter-title">
        <div class="app-filter-heading">
            <h2 id="testing-question-filter-title"><i class="bi bi-funnel me-2" aria-hidden="true"></i>Filter pertanyaan</h2>
            <p>Pilih status untuk melihat jumlah pertanyaan dummy.</p>
        </div>
        <div class="app-filter-fields">
            <div class="app-filter-options" role="group" aria-label="Filter status pertanyaan">
                <?php foreach ([['Semua', 3], ['Aktif', 2], ['Nonaktif', 1]] as $index => [$label, $count]): ?>
                    <button type="button" class="btn btn-sm btn-light-primary" data-question-count="<?= $count ?>"
                        aria-pressed="<?= $index === 0 ? 'true' : 'false' ?>" aria-controls="testing-question-filter-count">
                        <?= esc($label) ?>
                    </button>
                <?php endforeach ?>
            </div>
        </div>
        <div id="testing-question-filter-count" class="app-filter-scope" data-testing-tone="teal" role="status">3 pertanyaan dummy sesuai filter.</div>
    </section>

    <section class="card mb-5" aria-labelledby="testing-word-cloud-title">
        <div class="card-header">
            <div>
                <h2 id="testing-word-cloud-title" class="mb-1">Word cloud</h2>
                <p class="text-muted mb-0">Kata yang paling sering muncul pada masukan dummy.</p>
            </div>
        </div>
        <div class="card-body">
            <?php
            $words = [
                ['Ramah', 24, false], ['Cepat', 18, false], ['Informasi', 14, false],
                ['Antrean', 12, false], ['Lama', 9, true], ['Nyaman', 6, false],
            ];
            $maxWordCount = max(array_column($words, 1));
            ?>
            <ul class="app-word-cloud list-unstyled mb-0" aria-label="Kata yang paling sering muncul pada masukan">
                <?php foreach ($words as $index => [$word, $count, $negative]): ?>
                    <li class="app-cloud-word <?= $negative ? 'app-cloud-negative' : 'app-cloud-color-' . ($index % 4) ?>"
                        style="font-size: <?= 13 + round(25 * sqrt($count / $maxWordCount)) ?>px">
                        <?= esc($word) ?><span class="visually-hidden">: <?= $count ?> masukan</span>
                    </li>
                <?php endforeach ?>
            </ul>
            <div class="alert mb-0 mt-4" data-testing-tone="teal" role="note">
                <i class="bi bi-info-circle me-2 testing-status-icon" aria-hidden="true"></i>Ukuran kata menunjukkan jumlah masukan yang memuatnya.
            </div>
        </div>
    </section>

    <div class="modal fade" id="testing-import-modal" tabindex="-1" aria-labelledby="testing-import-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 id="testing-import-title" class="modal-title">Impor data</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Ini contoh tampilan tombol impor. Data Testing Page sudah disediakan untuk simulasi.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-primary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="testing-modal" tabindex="-1" aria-labelledby="testing-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 id="testing-modal-title" class="modal-title">Detail kunjungan</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <p id="testing-modal-summary" class="mb-0">Ringkasan ini menampilkan <strong id="testing-modal-total"></strong> kunjungan simulasi sesuai filter yang diterapkan.</p>
                    <p id="testing-modal-row" class="mb-0" hidden></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>
