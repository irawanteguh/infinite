<?php

define('FCPATH', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
chdir(FCPATH);
require FCPATH . '../app/Config/Paths.php';
$paths = new Config\Paths();
require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';
require_once SYSTEMPATH . 'Config/DotEnv.php';
(new CodeIgniter\Config\DotEnv(ROOTPATH))->load();
if (!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', env('CI_ENVIRONMENT', 'production'));
}
$app = Config\Services::codeigniter();
$app->initialize();
$db = Config\Database::connect();

$referenceId = 'cf29d92a-d934-11f1-91d6-202b2018cf5a';
$indicator = $db->table('dt01_qi_indikator_hd h')
    ->select('h.transaksi_id, h.group_id, h.org_id, h.department_id, h.periode_id, h.created_by, i.indikator')
    ->join('dt01_qi_indikator_ms i', 'i.indikator_id = h.indikator_id', 'left')
    ->where('h.transaksi_id', $referenceId)
    ->where('h.active', '1')
    ->get()->getRowArray();

if (!$indicator) {
    fwrite(STDERR, "Indikator sumber tidak ditemukan atau tidak aktif. Tidak ada data yang ditambahkan.\n");
    exit(1);
}
if (!$db->tableExists('dt01_qi_fishbone_hd') || !$db->tableExists('dt01_qi_fishbone_dt')
    || !$db->fieldExists('indikator_ref_id', 'dt01_qi_fishbone_hd')
    || !$db->fieldExists('priority', 'dt01_qi_fishbone_dt')) {
    fwrite(STDERR, "Tabel atau kolom Fishbone belum lengkap. Tidak ada data yang ditambahkan.\n");
    exit(1);
}

$prefix = 'Contoh Dummy Fishbone ';
$existingRows = $db->table('dt01_qi_fishbone_hd')
    ->select('problem')
    ->where('indikator_ref_id', $referenceId)
    ->where('group_id', $indicator['group_id'])
    ->where('org_id', $indicator['org_id'])
    ->like('problem', $prefix, 'after')
    ->where('active', '1')
    ->orderBy('problem')
    ->get()->getResultArray();
$existingProblems = array_column($existingRows, 'problem');

$examples = [
    [
        'problem' => 'Kepatuhan pencatatan indikator belum mencapai target',
        'causes' => ['Petugas belum memahami definisi operasional', 'Alur pencatatan belum konsisten', 'Perangkat pencatatan terbatas saat jam sibuk', 'Formulir sumber data belum seragam', 'Perhitungan numerator belum dipahami merata', 'Beban layanan mengurangi waktu pencatatan'],
    ],
    [
        'problem' => 'Kelengkapan data indikator belum konsisten',
        'causes' => ['PIC pengganti belum memahami proses pencatatan', 'Checklist kelengkapan belum diterapkan', 'Akses aplikasi pencatatan belum merata', 'Dokumen bukti belum terkumpul di satu tempat', 'Sampel data belum diperiksa secara berkala', 'Koordinasi pengumpulan data antar shift terbatas'],
    ],
    [
        'problem' => 'Validasi data indikator sering terlambat',
        'causes' => ['Tanggung jawab validasi belum dibagi jelas', 'Jadwal validasi belum ditetapkan', 'Komputer untuk validasi digunakan bergantian', 'Bukti pendukung diterima mendekati tenggat', 'Data numerator dan denominator perlu dihitung ulang', 'Waktu validasi berbenturan dengan pelayanan'],
    ],
    [
        'problem' => 'Pelaporan hasil indikator melewati jadwal',
        'causes' => ['Petugas pelaporan merangkap tugas lain', 'Pengingat tenggat belum berjalan rutin', 'Template laporan belum tersedia di semua unit', 'Rekap bukti dari unit belum lengkap', 'Pemeriksaan tren belum dilakukan bertahap', 'Rapat koordinasi hasil belum terjadwal tetap'],
    ],
    [
        'problem' => 'Tindak lanjut capaian indikator belum efektif',
        'causes' => ['Pemilik tindak lanjut belum ditetapkan spesifik', 'Rencana aksi belum memakai tenggat yang jelas', 'Pemantauan progres belum dilakukan rutin', 'Dokumen rencana perbaikan tersebar', 'Ukuran keberhasilan tindak lanjut belum ditentukan', 'Dukungan koordinasi lintas unit masih terbatas'],
    ],
];

$categories = ['Man (SDM)', 'Method (Metode)', 'Machine (Mesin/Peralatan)', 'Material (Bahan)', 'Measurement (Pengukuran)', 'Environment (Lingkungan)'];
$priorities = ['urgent', 'medium', 'low', 'medium', 'low', 'medium'];
$creatorId = $indicator['created_by'] ?: null;
$now = date('Y-m-d H:i:s');
$createdHeaders = 0;
$createdCauses = 0;

$db->transBegin();
try {
    foreach ($examples as $index => $example) {
        $number = $index + 1;
        $problem = $prefix . $number . ': ' . $example['problem'];
        if (in_array($problem, $existingProblems, true)) {
            continue;
        }
        $fishboneId = sprintf('%s-%s-%s-%s-%s', bin2hex(random_bytes(4)), bin2hex(random_bytes(2)), '4' . substr(bin2hex(random_bytes(2)), 1), dechex((hexdec(bin2hex(random_bytes(2))) & 0x3fff) | 0x8000), bin2hex(random_bytes(6)));
        $saved = $db->table('dt01_qi_fishbone_hd')->insert([
            'fishbone_id' => $fishboneId,
            'group_id' => $indicator['group_id'],
            'org_id' => $indicator['org_id'],
            'department_id' => $indicator['department_id'],
            'periode_id' => $indicator['periode_id'],
            'indikator_ref_id' => $referenceId,
            'problem' => $problem,
            'active' => '1',
            'created_by' => $creatorId,
            'created_date' => $now,
            'last_update_by' => $creatorId,
            'last_update_date' => $now,
        ]);
        if (!$saved) {
            throw new RuntimeException('Gagal menambahkan header Fishbone.');
        }
        $createdHeaders++;

        foreach ($categories as $causeIndex => $category) {
            $causeId = sprintf('%s-%s-%s-%s-%s', bin2hex(random_bytes(4)), bin2hex(random_bytes(2)), '4' . substr(bin2hex(random_bytes(2)), 1), dechex((hexdec(bin2hex(random_bytes(2))) & 0x3fff) | 0x8000), bin2hex(random_bytes(6)));
            $saved = $db->table('dt01_qi_fishbone_dt')->insert([
                'cause_id' => $causeId,
                'fishbone_id' => $fishboneId,
                'category' => $category,
                'cause' => $example['causes'][$causeIndex],
                'priority' => $priorities[$causeIndex],
                'active' => '1',
                'created_by' => $creatorId,
                'created_date' => $now,
                'last_update_by' => $creatorId,
                'last_update_date' => $now,
            ]);
            if (!$saved) {
                throw new RuntimeException('Gagal menambahkan penyebab Fishbone.');
            }
            $createdCauses++;
        }
    }
    if (!$db->transStatus()) {
        throw new RuntimeException('Transaksi database gagal.');
    }
    $db->transCommit();
    echo json_encode([
        'status' => 'success',
        'indicator_reference' => $referenceId,
        'indicator_name' => $indicator['indikator'],
        'existing_examples' => count($existingProblems),
        'created_analyses' => $createdHeaders,
        'created_causes' => $createdCauses,
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
} catch (Throwable $exception) {
    $db->transRollback();
    fwrite(STDERR, $exception->getMessage() . " Tidak ada perubahan yang dipertahankan.\n");
    exit(1);
}
