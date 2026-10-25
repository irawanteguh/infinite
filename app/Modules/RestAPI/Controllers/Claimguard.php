<?php

namespace Modules\RestAPI\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class Claimguard extends ResourceController{
    protected $format = 'json';

    public function requestTemplate(): ResponseInterface
    {
        return $this->respond([
            'data' => [
                'request_name' => 'ClaimGuard - Template Evaluasi Semua Algoritma',
                'method' => 'POST',
                'url' => '/api/v1/claimguard/rulesclaimguard',
                'headers' => ['Content-Type' => 'application/json'],
                'body' => $this->buildRequestTemplate(),
            ],
            'meta' => [
                'success' => true,
                'message' => 'Template request berhasil dibuat.',
                'service' => 'Medical Diagnosis Recommendation API',
                'version' => '1.0.0',
                'timestamp' => date('c'),
            ],
        ]);
    }

    public function requestPresets(): ResponseInterface
    {
        return $this->respond([
            'data' => $this->getRequestPresets(),
            'meta' => [
                'success' => true,
                'message' => 'Daftar preset request berhasil dibuat.',
                'service' => 'Medical Diagnosis Recommendation API',
                'version' => '1.0.0',
                'timestamp' => date('c'),
            ],
        ]);
    }

    public function requestBody($algorithmNumber = null): ResponseInterface
    {
        $algorithmId = 'ALGORITHM_' . str_pad((string) $algorithmNumber, 4, '0', STR_PAD_LEFT);
        $presets = $this->getRequestPresets();

        if (!isset($presets[$algorithmId])) {
            return $this->respond([
                'data' => null,
                'meta' => [
                    'success' => false,
                    'message' => "Preset {$algorithmId} tidak ditemukan.",
                    'timestamp' => date('c'),
                ],
            ], 404);
        }

        return $this->respond([
            'data' => $presets[$algorithmId],
            'meta' => [
                'success' => true,
                'message' => 'Request body berhasil dibuat.',
                'timestamp' => date('c'),
            ],
        ]);
    }

    public function exportPostmanCollection(): ResponseInterface
    {
        $presets = $this->getRequestPresets();
        $items = [];

        foreach ($presets as $algorithmId => $preset) {
            $items[] = [
                'name' => $algorithmId . ' - ' . $preset['request_name'],
                'request' => [
                    'method' => 'POST',
                    'header' => [
                        [
                            'key' => 'Content-Type',
                            'value' => 'application/json',
                        ],
                    ],
                    'body' => [
                        'mode' => 'raw',
                        'raw' => json_encode(
                            $preset['body'],
                            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
                        ),
                        'options' => [
                            'raw' => [
                                'language' => 'json',
                            ],
                        ],
                    ],
                    'url' => [
                        'raw' => 'http://localhost/infinite/api/v1/claimguard/rulesclaimguard',
                        'protocol' => 'http',
                        'host' => ['localhost'],
                        'path' => [
                            'infinite',
                            'api',
                            'v1',
                            'claimguard',
                            'rulesclaimguard',
                        ],
                    ],
                ],
            ];
        }

        $collection = [
            'info' => [
                'name' => 'ClaimGuard - Diagnosis Recommendation API',
                'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
            ],
            'item' => $items,
        ];

        $json = json_encode(
            $collection,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );

        return $this->response
            ->setStatusCode(200)
            ->setHeader('Content-Type', 'application/json')
            ->setHeader('Content-Disposition', 'attachment; filename="ClaimGuard-Postman-Collection.json"')
            ->setBody($json);
    }

    public function evaluate(): ResponseInterface
    {
        $requestData = $this->request->getJSON(true);

        if (!is_array($requestData)) {
            return $this->respond([
                'meta' => [
                    'success' => false,
                    'message' => 'Request body harus berupa JSON object yang valid.',
                    'timestamp' => date('c'),
                ],
            ], 400);
        }

        $diaglist = trim((string) ($requestData['diaglist'] ?? ''));
        $proclist = trim((string) ($requestData['proclist'] ?? ''));

        if ($diaglist === '' && $proclist === '') {
            return $this->respond([
                'data' => [],
                'meta' => [
                    'success' => false,
                    'message' => 'Field diaglist atau proclist wajib diisi.',
                    'timestamp' => date('c'),
                ],
            ], 400);
        }

        $diagnosisCodes = array_values(array_filter(
            array_map(
                static fn ($code) => strtoupper(trim($code)),
                explode(';', $diaglist)
            ),
            static fn ($code) => $code !== ''
        ));

        $procedureCodes = array_values(array_filter(
            array_map(
                static fn ($code) => strtoupper(trim($code)),
                explode(';', $proclist)
            ),
            static fn ($code) => $code !== ''
        ));

        $context = [
            'diagnosis_codes' => $diagnosisCodes,
            'procedure_codes' => $procedureCodes,

            // Parameter umum
            'ptd' => (string) ($requestData['ptd'] ?? ''),
            'umur_tahun' => $this->toInt($requestData['umur_tahun'] ?? null),
            'los' => $this->toFloat($requestData['los'] ?? null),

            // ALGORITHM_0015: Gagal napas
            'gagal_nafas_jenis' => strtoupper($this->toText($requestData['gagal_nafas_jenis'] ?? '') ?? ''),
            'spo2' => $this->toFloat($requestData['spo2'] ?? null),
            'pao2' => $this->toFloat($requestData['pao2'] ?? null),
            'sao2' => $this->toFloat($requestData['sao2'] ?? null),
            'fio2' => $this->toFloat($requestData['fio2'] ?? null),
            'paco2' => $this->toFloat($requestData['paco2'] ?? null),
            'paco2_baseline' => $this->toFloat($requestData['paco2_baseline'] ?? null),
            'ph' => $this->toFloat($requestData['ph'] ?? null),
            'terapi_napas' => $this->toBool($requestData['terapi_nafas'] ?? null),
            'dokumentasi_gejala_nafas' => $this->toBool($requestData['dokumentasi_gejala_nafas'] ?? null),

            // ALGORITHM_0016: Cardiac arrest
            'dokumentasi_cpr' => $this->toBool($requestData['dokumentasi_cpr'] ?? null),
            'dokumentasi_resusitasi' => $this->toBool($requestData['dokumentasi_resusitasi'] ?? null),
            'status_pasien' => strtoupper($this->toText($requestData['status_pasien'] ?? '') ?? ''),
            'kondisi_obstetri' => $this->toBool($requestData['kondisi_obstetri'] ?? null),

            // ALGORITHM_0017: Pneumonia
            'foto_toraks' => $this->toBool($requestData['foto_toraks'] ?? null),
            'gejala_klinis' => $this->toBool($requestData['gejala_klinis'] ?? null),
            'derajat_pneumonia' => strtoupper($this->toText($requestData['derajat_pneumonia'] ?? '') ?? ''),
            'skor_pneumonia' => $this->toText($requestData['skor_pneumonia'] ?? null),
            'kultur_dilakukan' => $this->toBool($requestData['kultur_dilakukan'] ?? null),
            'antibiotik_diberikan' => $this->toBool($requestData['antibiotik_diberikan'] ?? null),
            'antibiotik_jam' => $this->toFloat($requestData['antibiotik_jam'] ?? null),
            'komorbid' => $this->toBool($requestData['komorbid'] ?? null),

            // ALGORITHM_0018: Obstetri
            'kasus_obstetri' => $this->toBool($requestData['kasus_obstetri'] ?? null),
            'kasus_obstetri_jenis' => $this->toText($requestData['kasus_obstetri_jenis'] ?? null),
            'indikasi_rawat_inap' => $this->toBool($requestData['indikasi_rawat_inap'] ?? null),
            'status_emergensi' => $this->toBool($requestData['status_emergensi'] ?? null),

            // ALGORITHM_0019: Hiponatremia / hipokalemia
            'natrium' => $this->toFloat($requestData['natrium'] ?? null),
            'kalium' => $this->toFloat($requestData['kalium'] ?? null),
            'rujukan_na_bawah' => $this->toFloat($requestData['rujukan_na_bawah'] ?? null) ?? 135.0,
            'rujukan_k_bawah' => $this->toFloat($requestData['rujukan_k_bawah'] ?? null) ?? 3.5,
            'gejala_hipokalemia' => $this->toBool($requestData['gejala_hipokalemia'] ?? null),
            'terapi_elektrolit' => $this->toBool($requestData['terapi_elektrolit'] ?? null),
            'pemeriksaan_serial' => $this->toBool($requestData['pemeriksaan_serial'] ?? null),

            // ALGORITHM_0020: AKI / CKD / insufisiensi renal
            'kreatinin_sekarang' => $this->toFloat($requestData['kreatinin_sekarang'] ?? null),
            'kreatinin_baseline' => $this->toFloat($requestData['kreatinin_baseline'] ?? null),
            'waktu_baseline_hari' => $this->toInt($requestData['waktu_baseline_hari'] ?? null),
            'urine_output' => $this->toFloat($requestData['urine_output'] ?? null),
            'egfr' => $this->toFloat($requestData['egfr'] ?? null),
            'jenis_kelamin' => strtoupper($this->toText($requestData['jenis_kelamin'] ?? '') ?? ''),
            'terapi_pengganti_ginjal' => $this->toBool($requestData['terapi_pengganti_ginjal'] ?? null),

            // ALGORITHM_0021: Syok hipovolemik
            'tanda_perfusi' => $this->toBool($requestData['tanda_perfusi'] ?? null),
            'penyebab_shock' => $this->toText($requestData['penyebab_shock'] ?? null),
            'tindakan_shock' => $this->toBool($requestData['tindakan_shock'] ?? null),

            // ALGORITHM_0022: Soft-tissue tumor
            'lokasi_tumor' => $this->toText($requestData['lokasi_tumor'] ?? null),
            'kedalaman_fascia' => $this->toBool($requestData['kedalaman_fascia'] ?? null),
            'ukuran_tumor_cm' => $this->toFloat($requestData['ukuran_tumor_cm'] ?? null),
            'struktur_vital' => $this->toBool($requestData['struktur_vital'] ?? null),
            'hasil_pa' => $this->toBool($requestData['hasil_pa'] ?? null),
            'tindakan_eksisi' => $this->toBool($requestData['tindakan_eksisi'] ?? null),
            'tindakan_biopsi' => $this->toBool($requestData['tindakan_biopsi'] ?? null),

            // ALGORITHM_0023: Anemia
            'hemoglobin' => $this->toFloat($requestData['hemoglobin'] ?? null),
            'hb_ambang' => $this->toFloat($requestData['hb_ambang'] ?? null),
            'hamil' => $this->toBool($requestData['hamil'] ?? null),
            'jenis_anemia' => $this->toText($requestData['jenis_anemia'] ?? null),
            'transfusi_diberikan' => $this->toBool($requestData['transfusi_diberikan'] ?? null),

            // ALGORITHM_0024: Sepsis
            'suhu' => $this->toFloat($requestData['suhu'] ?? null),
            'nadi' => $this->toFloat($requestData['nadi'] ?? null),
            'frekuensi_nafas' => $this->toFloat($requestData['frekuensi_nafas'] ?? null),
            'leukosit' => $this->toFloat($requestData['leukosit'] ?? null),
            'bukti_infeksi' => $this->toBool($requestData['bukti_infeksi'] ?? null),
            'kultur_hasil' => $this->toBool($requestData['kultur_hasil'] ?? null),

            // ALGORITHM_0025: CHF
            'echo_tanggal' => $this->toText($requestData['echo_tanggal'] ?? null),
            'ef' => $this->toFloat($requestData['ef'] ?? null),
            'gejala_chf' => $this->toBool($requestData['gejala_chf'] ?? null),
            'terapi_tambahan_chf' => $this->toBool($requestData['terapi_tambahan_chf'] ?? null),
            'chf_terkontrol' => $this->toBool($requestData['chf_terkontrol'] ?? null),
        ];

        $algorithms = [
            $this->algorithm_0001($diagnosisCodes, $procedureCodes, $context),
            $this->algorithm_0002($diagnosisCodes, $procedureCodes, $context),
            $this->algorithm_0003($diagnosisCodes, $procedureCodes, $context),
            $this->algorithm_0004($diagnosisCodes, $procedureCodes, $context),
            $this->algorithm_0005($diagnosisCodes, $procedureCodes, $context),
            $this->algorithm_0006($diagnosisCodes, $procedureCodes, $context),
            $this->algorithm_0007($diagnosisCodes, $procedureCodes, $context),
            $this->algorithm_0008($diagnosisCodes, $procedureCodes, $context),
            $this->algorithm_0009($diagnosisCodes, $procedureCodes, $context),
            $this->algorithm_0010($diagnosisCodes, $procedureCodes, $context),
            $this->algorithm_0011($diagnosisCodes, $procedureCodes, $context),
            $this->algorithm_0012($diagnosisCodes, $procedureCodes, $context),
            $this->algorithm_0013($diagnosisCodes, $procedureCodes, $context),
            $this->algorithm_0014($diagnosisCodes, $procedureCodes, $context),
            $this->algorithm_0015($diagnosisCodes, $procedureCodes, $context),
            $this->algorithm_0016($diagnosisCodes, $procedureCodes, $context),
            $this->algorithm_0017($diagnosisCodes, $procedureCodes, $context),
            $this->algorithm_0018($diagnosisCodes, $procedureCodes, $context),
            $this->algorithm_0019($diagnosisCodes, $procedureCodes, $context),
            $this->algorithm_0020($diagnosisCodes, $procedureCodes, $context),
            $this->algorithm_0021($diagnosisCodes, $procedureCodes, $context),
            $this->algorithm_0022($diagnosisCodes, $procedureCodes, $context),
            $this->algorithm_0023($diagnosisCodes, $procedureCodes, $context),
            $this->algorithm_0024($diagnosisCodes, $procedureCodes, $context),
            $this->algorithm_0025($diagnosisCodes, $procedureCodes, $context),
        ];

        $matchedAlgorithms = array_values(array_filter(
            $algorithms,
            static fn ($result) => $result !== null
        ));

        foreach ($matchedAlgorithms as &$algorithm) {
            foreach (['coder', 'physician'] as $role) {
                if (!isset($algorithm['recommendations'][$role])) {
                    continue;
                }

                if (($algorithm['recommendations'][$role]['status'] ?? true) === false) {
                    unset($algorithm['recommendations'][$role]['recommendation']);
                    unset($algorithm['recommendations'][$role]['potential_claim']);
                    unset($algorithm['recommendations'][$role]['topup']);
                }
            }
        }
        unset($algorithm);

        return $this->respond([
            'data' => $matchedAlgorithms,
            'meta' => [
                'success' => true,
                'message' => 'Evaluasi diagnosis berhasil diselesaikan.',
                'service' => 'Medical Diagnosis Recommendation API',
                'version' => '1.0.0',
                'timestamp' => date('c'),
                'input' => $this->buildInputMeta($context),
            ],
        ]);
    }

    private function algorithm_0001(array $codes, array $procedureCodes, array $context): ?array {
        $ptd = (string) ($context['ptd'] ?? '');

        $firstCode = $codes[0] ?? '';
        $hasI678 = in_array('I67.8', $codes, true);

        if ($firstCode !== 'I10' || !$hasI678) {
            return null;
        }

        $potential = $ptd === '1' ? 3666200 : null;
        $topupName = null;
        $topupValue = null;

        return [
            'algorithm_id' => 'ALGORITHM_0001',
            'algorithm_name' => 'I10 and I67.8 Diagnosis Review',
            'recommendations' => [
                'coder' => [
                    'status' => true,
                    'recommendation' => 'Tinjau urutan diagnosis. Jika didukung pedoman pengkodean yang berlaku dan dokumentasi medis, tetapkan I67.8 sebagai diagnosis utama serta kode yang sesuai di antara I10, I15.0, I15.1, I15.2, I15.8, atau I15.9 sebagai diagnosis sekunder.',
                    'potential_claim' => $potential,
                    'topup' => [
                        'topup_name' => $topupName,
                        'topup_value' => $topupValue,
                    ],
                ],
                'physician' => [
                    'status' => false,
                    'recommendation' => 'Mohon tinjau dokumentasi klinis untuk memastikan hubungan dan relevansi diagnosis yang tercatat. Penetapan diagnosis utama harus berdasarkan evaluasi klinis dan dokumentasi medis.',
                    'potential_claim' => $potential,
                    'topup' => [
                        'topup_name' => $topupName,
                        'topup_value' => $topupValue,
                    ],
                ],
            ],
        ];
    }

    private function algorithm_0002(array $codes, array $procedureCodes, array $context): ?array {
        $ptd = (string) ($context['ptd'] ?? '');

        $firstCode = $codes[0] ?? '';
        $hasI693 = in_array('I69.3', $codes, true);

        if ($firstCode !== 'I10' || !$hasI693) {
            return null;
        }

        $potential = $ptd === '1' ? 3666200 : null;
        $topupName = null;
        $topupValue = null;

        return [
            'algorithm_id' => 'ALGORITHM_0002',
            'algorithm_name' => 'I10 and I69.3 Diagnosis Review',
            'recommendations' => [
                'coder' => [
                    'status' => true,
                    'recommendation' => 'Tinjau urutan diagnosis. Jika didukung pedoman pengkodean yang berlaku dan dokumentasi medis, tetapkan I69.3 sebagai diagnosis utama serta kode yang sesuai di antara I10, I15.0, I15.1, I15.2, I15.8, atau I15.9 sebagai diagnosis sekunder.',
                    'potential_claim' => $potential,
                    'topup' => [
                        'topup_name' => $topupName,
                        'topup_value' => $topupValue,
                    ],
                ],
                'physician' => [
                    'status' => false,
                    'recommendation' => 'Mohon tinjau dokumentasi klinis untuk memastikan hubungan dan relevansi diagnosis yang tercatat. Penetapan diagnosis utama harus berdasarkan evaluasi klinis dan dokumentasi medis.',
                    'potential_claim' => $potential,
                    'topup' => [
                        'topup_name' => $topupName,
                        'topup_value' => $topupValue,
                    ],
                ],
            ],
        ];
    }

    private function algorithm_0003(array $codes, array $procedureCodes, array $context): ?array {
        $ageYears = $context['umur_tahun'] ?? null;
        $firstCode = $codes[0] ?? '';

        // Aturan berlaku jika J40 merupakan diagnosis pertama dan usia < 15 tahun.
        if ($firstCode !== 'J40' || $ageYears === null || $ageYears >= 15) {
            return null;
        }

        $recommendation = 'Tinjau penggunaan kode J40 karena pasien berusia di bawah 15 tahun. Kode dan diagnosis harus sesuai dengan evaluasi klinis serta dokumentasi medis.';

        return [
            'algorithm_id' => 'ALGORITHM_0003',
            'algorithm_name' => 'J40 Age Review',
            'recommendations' => [
                'coder' => [
                    'status' => true,
                    'recommendation' => $recommendation,
                    'potential_claim' => null,
                    'topup' => [
                        'topup_name' => null,
                        'topup_value' => null,
                    ],
                ],
                'physician' => [
                    'status' => true,
                    'recommendation' => 'Mohon tinjau diagnosis dan dokumentasi klinis pasien berusia di bawah 15 tahun untuk memastikan penggunaan kode yang paling sesuai.',
                    'potential_claim' => null,
                    'topup' => [
                        'topup_name' => null,
                        'topup_value' => null,
                    ],
                ],
            ],
        ];
    }

    private function algorithm_0004(array $codes, array $procedureCodes, array $context): ?array {
        $ageYears = $context['umur_tahun'] ?? null;
        $hasJ209 = in_array('J20.9', $codes, true);

        if (!$hasJ209 || $ageYears === null || $ageYears >= 15) {
            return null;
        }

        return [
            'algorithm_id' => 'ALGORITHM_0004',
            'algorithm_name' => 'J20.9 Age Review',
            'recommendations' => [
                'coder' => [
                    'status' => true,
                    'recommendation' => 'J20.9 digunakan untuk pasien berusia di bawah 15 tahun. Pastikan kode diagnosis spesifiknya sudah ditentukan dan sesuai dengan dokumentasi medis.',
                    'potential_claim' => null,
                    'topup' => [
                        'topup_name' => null,
                        'topup_value' => null,
                    ],
                ],
                'physician' => [
                    'status' => true,
                    'recommendation' => 'Mohon pastikan penggunaan kode J20.9 sesuai untuk pasien berusia di bawah 15 tahun dan tentukan diagnosis spesifiknya berdasarkan evaluasi klinis serta dokumentasi medis.',
                    'potential_claim' => null,
                    'topup' => [
                        'topup_name' => null,
                        'topup_value' => null,
                    ],
                ],
            ],
        ];
    }

    private function algorithm_0005(array $codes, array $procedureCodes, array $context): ?array {
        $firstCode = $codes[0] ?? '';

        $hasN20 = $firstCode === 'N20.0';

        $hasN30ToN39 = false;
        foreach ($codes as $code) {
            if (preg_match('/^N3[0-9](\.[0-9A-Z]+)?$/', $code)) {
                $hasN30ToN39 = true;
                break;
            }
        }

        if (!$hasN20 || !$hasN30ToN39) {
            return null;
        }

        return [
            'algorithm_id' => 'ALGORITHM_0005',
            'algorithm_name' => 'N20.0 and N30–N39 Omit-Code Review',
            'recommendations' => [
                'coder' => [
                    'status' => true,
                    'recommendation' => 'Terdapat N20.0 dan diagnosis N30*–N39*. Evaluasi aturan omit code: diagnosis N30*–N39* mungkin tidak perlu dikoding bersamaan dengan N20.0, sesuai pedoman koding yang berlaku.',
                    'potential_claim' => null,
                    'topup' => [
                        'topup_name' => null,
                        'topup_value' => null,
                    ],
                ],
                'physician' => [
                    'status' => false,
                    'recommendation' => 'Mohon tinjau dokumentasi klinis untuk memastikan diagnosis yang tercatat relevan dan didukung evaluasi klinis.',
                    'potential_claim' => null,
                    'topup' => [
                        'topup_name' => null,
                        'topup_value' => null,
                    ],
                ],
            ],
        ];
    }

    private function algorithm_0006(array $codes, array $procedureCodes, array $context): ?array {
        $los = $context['los'] ?? null;

        $hasJ22 = false;
        foreach ($codes as $code) {
            if (preg_match('/^J22([.]|$)/', $code)) {
                $hasJ22 = true;
                break;
            }
        }

        if ($los === null || $los <= 5 || !$hasJ22) {
            return null;
        }

        return [
            'algorithm_id' => 'ALGORITHM_0006',
            'algorithm_name' => 'J22 Diagnosis Review for Length of Stay Over 5 Days',
            'recommendations' => [
                'coder' => [
                    'status' => true,
                    'recommendation' => 'Evaluasi diagnosis J22: tinjau perkembangan klinis, hasil pemeriksaan penunjang, dan dokumentasi medis untuk memastikan spesifisitas diagnosis akhir.',
                    'potential_claim' => null,
                    'topup' => [
                        'topup_name' => null,
                        'topup_value' => null,
                    ],
                ],
                'physician' => [
                    'status' => false,
                    'recommendation' => 'Evaluasi diagnosis J22: tinjau perkembangan klinis, hasil pemeriksaan penunjang, dan dokumentasi medis untuk memastikan spesifisitas diagnosis akhir.',
                    'potential_claim' => null,
                    'topup' => [
                        'topup_name' => null,
                        'topup_value' => null,
                    ],
                ],
            ],
        ];
    }

    private function algorithm_0007(array $codes, array $procedureCodes, array $context): ?array {
        $los = $context['los'] ?? null;

        $hasJ22 = false;
        foreach ($codes as $code) {
            if (preg_match('/^J22([.]|$)/', $code)) {
                $hasJ22 = true;
                break;
            }
        }

        if ($los === null || $los > 5 || !$hasJ22) {
            return null;
        }

        return [
            'algorithm_id' => 'ALGORITHM_0007',
            'algorithm_name' => 'J22 Diagnosis Review for Length of Stay Up to 5 Days',
            'recommendations' => [
                'coder' => [
                    'status' => true,
                    'recommendation' => 'Evaluasi kesesuaian diagnosis J22 dengan kondisi klinis dan lama rawat kurang dari 5 hari. Tinjau dokumentasi medis, hasil pemeriksaan penunjang, serta kemungkinan diagnosis yang lebih spesifik sesuai temuan klinis.',
                    'potential_claim' => null,
                    'topup' => [
                        'topup_name' => null,
                        'topup_value' => null,
                    ],
                ],
                'physician' => [
                    'status' => false,
                    'recommendation' => 'Evaluasi kesesuaian diagnosis J22 dengan kondisi klinis dan lama rawat kurang dari 5 hari. Tinjau dokumentasi medis, hasil pemeriksaan penunjang, serta kemungkinan diagnosis yang lebih spesifik sesuai temuan klinis.',
                    'potential_claim' => null,
                    'topup' => [
                        'topup_name' => null,
                        'topup_value' => null,
                    ],
                ],
            ],
        ];
    }

    private function algorithm_0008(array $codes, array $procedureCodes, array $context): ?array {
        $ptd = (string) ($context['ptd'] ?? '');

        $hasI21 = false;
        foreach ($codes as $code) {
            if (preg_match('/^I21(\.[0-9]*)?$/', $code)) {
                $hasI21 = true;
                break;
            }
        }

        if (!$hasI21 || !in_array('89.52', $procedureCodes, true) || $ptd !== '1') {
            return null;
        }

        return [
            'algorithm_id' => 'ALGORITHM_0008',
            'algorithm_name' => 'Streptokinase Top Up Review',
            'recommendations' => [
                'coder' => [
                    'status' => true,
                    'recommendation' => 'Periksa top up untuk streptokinase.',
                    'potential_claim' => 4850700,
                    'topup' => ['topup_name' => 'Streptokinase', 'topup_value' => 4850700],
                ],
                'physician' => [
                    'status' => false,
                    'recommendation' => 'Periksa top up untuk streptokinase.',
                    'potential_claim' => 4850700,
                    'topup' => ['topup_name' => 'Streptokinase', 'topup_value' => 4850700],
                ],
            ],
        ];
    }

    private function algorithm_0009(array $codes, array $procedureCodes, array $context): ?array {
        if (
            !in_array('I63.9', $codes, true)
            || !in_array('87.03', $procedureCodes, true)
        ) {
            return null;
        }

        return [
            'algorithm_id' => 'ALGORITHM_0009',
            'algorithm_name' => 'Alteplase Non INA-CBG Tariff Review',
            'recommendations' => [
                'coder' => [
                    'status' => true,
                    'recommendation' => 'Periksa tarif non INA-CBG untuk alteplase, apabila memang ada tata laksana obat alteplase sesuai dengan PMK 3 Tahun 2023.',
                    'potential_claim' => 0,
                    'topup' => ['topup_name' => 'Alteplase', 'topup_value' => 0],
                ],
                'physician' => [
                    'status' => false,
                    'recommendation' => 'Periksa tarif non INA-CBG untuk alteplase, apabila memang ada tata laksana obat alteplase sesuai dengan PMK 3 Tahun 2023.',
                    'potential_claim' => 0,
                    'topup' => ['topup_name' => 'Alteplase', 'topup_value' => 0],
                ],
            ],
        ];
    }

    private function algorithm_0010(array $codes, array $procedureCodes, array $context): ?array {
        $ptd = (string) ($context['ptd'] ?? '');

        $diagnosisPattern = '/^(M84\.15|M80\.05|S72\.00|M84\.45|S72\.0|M17\.1)$/';
        $procedurePattern = '/^81\.5[124]$/';

        $hasDiagnosis = false;
        foreach ($codes as $code) {
            if (preg_match($diagnosisPattern, $code)) {
                $hasDiagnosis = true;
                break;
            }
        }

        $hasProcedure = false;
        foreach ($procedureCodes as $code) {
            if (preg_match($procedurePattern, $code)) {
                $hasProcedure = true;
                break;
            }
        }

        if (!$hasDiagnosis || !$hasProcedure || $ptd !== '1') {
            return null;
        }

        $recommendation = 'Periksa top up untuk hip implant/knee implant. Kriteria: penjaminan tidak dapat dilakukan di RS kelas D; layanan dilakukan oleh operator sesuai kewenangan klinis dan kompetensi; serta tersedia alat untuk pelaksanaan operasi hip/knee implant.';

        return [
            'algorithm_id' => 'ALGORITHM_0010',
            'algorithm_name' => 'Hip/Knee Implant Top Up Review',
            'recommendations' => [
                'coder' => [
                    'status' => true,
                    'recommendation' => $recommendation,
                    'potential_claim' => 0,
                    'topup' => ['topup_name' => 'Hip Replacement/Knee Replacement', 'topup_value' => 0],
                ],
                'physician' => [
                    'status' => false,
                    'recommendation' => $recommendation,
                    'potential_claim' => 0,
                    'topup' => ['topup_name' => 'Hip Replacement/Knee Replacement', 'topup_value' => 0],
                ],
            ],
        ];
    }

    private function algorithm_0011(array $codes, array $procedureCodes, array $context): ?array {
        $ptd = (string) ($context['ptd'] ?? '');

        $diagnosisPattern = '/^(I20\.9|I21\.[0149]|I25\.0)$/';
        $procedurePattern = '/^00\.4[012]$/';

        $hasDiagnosis = false;
        foreach ($codes as $code) {
            if (preg_match($diagnosisPattern, $code)) {
                $hasDiagnosis = true;
                break;
            }
        }

        $hasProcedure = false;
        foreach ($procedureCodes as $code) {
            if (preg_match($procedurePattern, $code)) {
                $hasProcedure = true;
                break;
            }
        }

        if (!$hasDiagnosis || !$hasProcedure || $ptd !== '1') {
            return null;
        }

        $recommendation = 'Periksa top up untuk Percutaneous Coronary Intervention (PCI).';

        return [
            'algorithm_id' => 'ALGORITHM_0011',
            'algorithm_name' => 'PCI Top Up Review',
            'recommendations' => [
                'coder' => [
                    'status' => true,
                    'recommendation' => $recommendation,
                    'potential_claim' => 0,
                    'topup' => ['topup_name' => 'Percutaneous Coronary Intervention (PCI)', 'topup_value' => 0],
                ],
                'physician' => [
                    'status' => false,
                    'recommendation' => $recommendation,
                    'potential_claim' => 0,
                    'topup' => ['topup_name' => 'Percutaneous Coronary Intervention (PCI)', 'topup_value' => 0],
                ],
            ],
        ];
    }

    private function algorithm_0012(array $codes, array $procedureCodes, array $context): ?array {
        if (!in_array('Z51.0', $codes, true)) {
            return null;
        }

        $recommendation = 'Periksa top up untuk stereotactic surgery dan radiotherapy.';

        return [
            'algorithm_id' => 'ALGORITHM_0012',
            'algorithm_name' => 'Stereotactic Surgery and Radiotherapy Top Up Review',
            'recommendations' => [
                'coder' => [
                    'status' => true,
                    'recommendation' => $recommendation,
                    'potential_claim' => 0,
                    'topup' => ['topup_name' => 'Stereotactic Surgery & Radiotherapy', 'topup_value' => 0],
                ],
                'physician' => [
                    'status' => false,
                    'recommendation' => $recommendation,
                    'potential_claim' => 0,
                    'topup' => ['topup_name' => 'Stereotactic Surgery & Radiotherapy', 'topup_value' => 0],
                ],
            ],
        ];
    }

    private function algorithm_0013(array $codes, array $procedureCodes, array $context): ?array {
        $ptd = (string) ($context['ptd'] ?? '');

        if (
            !in_array('Z09.8', $codes, true)
            || !in_array('31.42', $procedureCodes, true)
            || $ptd !== '2'
        ) {
            return null;
        }

        $recommendation = 'Periksa top up untuk microlaringoscopy.';

        return [
            'algorithm_id' => 'ALGORITHM_0013',
            'algorithm_name' => 'Microlaringoscopy Top Up Review',
            'recommendations' => [
                'coder' => [
                    'status' => true,
                    'recommendation' => $recommendation,
                    'potential_claim' => 0,
                    'topup' => ['topup_name' => 'Microlaringoscopy', 'topup_value' => 0],
                ],
                'physician' => [
                    'status' => false,
                    'recommendation' => $recommendation,
                    'potential_claim' => 0,
                    'topup' => ['topup_name' => 'Microlaringoscopy', 'topup_value' => 0],
                ],
            ],
        ];
    }

    private function algorithm_0014(array $codes, array $procedureCodes, array $context): ?array {
        $ptd = (string) ($context['ptd'] ?? '');

        $hasProcedure = false;
        foreach ($procedureCodes as $code) {
            if (preg_match('/^88\.9[37]$/', $code)) {
                $hasProcedure = true;
                break;
            }
        }

        if (!$hasProcedure || $ptd !== '1') {
            return null;
        }

        $recommendation = 'Periksa top up untuk Magnetic Resonance Imaging (MRI).';

        return [
            'algorithm_id' => 'ALGORITHM_0014',
            'algorithm_name' => 'MRI Top Up Review',
            'recommendations' => [
                'coder' => [
                    'status' => true,
                    'recommendation' => $recommendation,
                    'potential_claim' => 1679300,
                    'topup' => ['topup_name' => 'Magnetic Resonance Imaging (MRI)', 'topup_value' => 1679300],
                ],
                'physician' => [
                    'status' => false,
                    'recommendation' => $recommendation,
                    'potential_claim' => 1679300,
                    'topup' => ['topup_name' => 'Magnetic Resonance Imaging (MRI)', 'topup_value' => 1679300],
                ],
            ],
        ];
    }

    private function algorithm_0015(array $codes, array $procedureCodes, array $context): ?array {
        $hasJ96 = false;

        foreach ($codes as $code) {
            if (preg_match('/^J96(\.[0-9A-Z]+)?$/', $code)) {
                $hasJ96 = true;
                break;
            }
        }

        if (!$hasJ96) {
            return null;
        }

        $jenis = strtoupper((string) ($context['gagal_nafas_jenis'] ?? ''));
        $spo2 = $context['spo2'] ?? null;
        $pao2 = $context['pao2'] ?? null;
        $sao2 = $context['sao2'] ?? null;
        $fio2 = $context['fio2'] ?? null;
        $paco2 = $context['paco2'] ?? null;
        $ph = $context['ph'] ?? null;
        $paco2Baseline = $context['paco2_baseline'] ?? null;

        $akut = [];

        if ($pao2 !== null) {
            $akut['PaO2 < 60 mmHg'] = $pao2 < 60;
        }

        if ($sao2 !== null) {
            $akut['SaO2 < 91%'] = $sao2 < 91;
        }

        if ($spo2 !== null) {
            $akut['SpO2 < 91%'] = $spo2 < 91;
        }

        if ($pao2 !== null && $fio2 !== null && $fio2 > 0) {
            $akut['Rasio PaO2/FiO2 < 300'] = ($pao2 / $fio2) < 300;
        }

        if ($paco2 !== null && $ph !== null) {
            $akut['PaCO2 > 50 mmHg dengan pH < 7,35'] =
                $paco2 > 50 && $ph < 7.35;
        }

        if ($paco2 !== null && $paco2Baseline !== null) {
            $akut['Kenaikan PaCO2 >= 10 mmHg dari nilai dasar'] =
                ($paco2 - $paco2Baseline) >= 10;
        }

        $kronik = [];

        if ($paco2 !== null) {
            $kronik['PaCO2 > 50 mmHg'] = $paco2 > 50;
        }

        if ($ph !== null) {
            $kronik['pH normal (7,35–7,45)'] = $ph >= 7.35 && $ph <= 7.45;
        }

        if ($pao2 !== null) {
            $kronik['PaO2 < 60 mmHg'] = $pao2 < 60;
        } elseif ($sao2 !== null) {
            $kronik['SaO2 < 91%'] = $sao2 < 91;
        }

        if ($jenis === 'KRONIK') {
            $kriteriaDipakai = $kronik;
            $tipe = 'KRONIK';
        } else {
            $kriteriaDipakai = $akut;
            $tipe = $jenis === 'AKUT_ON_KRONIK' ? 'AKUT_ON_KRONIK' : 'AKUT';
        }

        $check = $this->buildCriteria($kriteriaDipakai);
        $jumlahTerpenuhi = count($check['met']);

        if ($tipe === 'KRONIK') {
            $kesimpulan = count($kriteriaDipakai) > 0
                && $jumlahTerpenuhi === count($kriteriaDipakai)
                    ? 'Seluruh kriteria gagal napas kronis yang dievaluasi terpenuhi.'
                    : 'Belum seluruh kriteria gagal napas kronis yang dievaluasi terpenuhi.';
        } else {
            $kesimpulan = $jumlahTerpenuhi >= 1
                ? 'Sedikitnya satu kriteria gagal napas akut yang dievaluasi terpenuhi.'
                : 'Belum ada kriteria gagal napas akut yang dapat dipastikan terpenuhi.';
        }

        return [
            'algorithm_id' => 'ALGORITHM_0015',
            'algorithm_name' => 'Gagal Napas (J96) Criteria Review',
            'criteria_check' => [
                'tipe_gagal_nafas' => $jenis !== '' ? $jenis : 'BELUM_DITENTUKAN',
                'kriteria' => $check,
                'kesimpulan' => $kesimpulan,
            ],
            'recommendations' => [
                'coder' => [
                    'status' => true,
                    'recommendation' => 'Tinjau dokumentasi kriteria gagal napas dan hasil pemeriksaan penunjang. Analisis gas darah bukan satu-satunya dasar penilaian; evaluasi kriteria yang tersedia bersama dokumentasi klinis. Penetapan diagnosis utama atau sekunder mengikuti pedoman pengkodean dan dokumentasi medis.',
                    'potential_claim' => null,
                    'topup' => ['topup_name' => null, 'topup_value' => null],
                ],
                'physician' => [
                    'status' => true,
                    'recommendation' => 'Mohon pastikan hasil pemeriksaan penunjang, termasuk SpO2, PaO2, SaO2, FiO2, PaCO2, dan pH bila tersedia, serta terapi napas dan evaluasi klinis terdokumentasi pada rekam medis.',
                    'potential_claim' => null,
                    'topup' => ['topup_name' => null, 'topup_value' => null],
                ],
            ],
        ];
    }

    private function algorithm_0016(array $codes, array $procedureCodes, array $context): ?array {
        $hasI46 = false;

        foreach ($codes as $code) {
            if (preg_match('/^I46(\.|$)/', $code)) {
                $hasI46 = true;
                break;
            }
        }

        if (!$hasI46) {
            return null;
        }

        $dokumentasiCpr = $context['dokumentasi_cpr'] ?? null;
        $dokumentasiResusitasi = $context['dokumentasi_resusitasi'] ?? null;
        $statusPasien = strtoupper((string) ($context['status_pasien'] ?? ''));

        $eksklusi = [];

        if ($this->hasCodeInRange($codes, 'O', 0, 7) || in_array('O08.8', $codes, true)) {
            $eksklusi[] = 'Abortus atau kehamilan mola (O00–O07, O08.8).';
        }

        if (in_array('O75.4', $codes, true)) {
            $eksklusi[] = 'Tindakan dan operasi kebidanan (O75.4).';
        }

        if (in_array('R57.0', $codes, true)) {
            $eksklusi[] = 'Syok kardiogenik (R57.0).';
        }

        $cek = [
            'Dokumentasi CPR tersedia' => $dokumentasiCpr,
            'Dokumentasi resusitasi tersedia' => $dokumentasiResusitasi,
            'Status pasien tercatat (HIDUP/MENINGGAL)' => $statusPasien !== '' ? true : null,
        ];

        return [
            'algorithm_id' => 'ALGORITHM_0016',
            'algorithm_name' => 'Cardiac Arrest (I46) Documentation Review',
            'criteria_check' => [
                'kode_terkait' => $eksklusi,
                'dokumentasi' => $this->buildCriteria($cek),
            ],
            'recommendations' => [
                'coder' => [
                    'status' => true,
                    'recommendation' => 'Tinjau kode I46 dan dokumentasi klinis terkait. Pengkodean INA-CBG menggunakan kaidah morbiditas, sehingga status pasien meninggal saja bukan alasan untuk menghapus kode. Periksa catatan eksklusi ICD-10 dan pastikan kode yang digunakan didukung dokumentasi medis.',
                    'potential_claim' => null,
                    'topup' => ['topup_name' => null, 'topup_value' => null],
                ],
                'physician' => [
                    'status' => true,
                    'recommendation' => 'Mohon pastikan kejadian cardiac arrest, tindakan CPR atau resusitasi, dan kronologi klinis terdokumentasi pada rekam medis. Penetapan diagnosis akhir berdasarkan evaluasi klinis dan dokumentasi medis.',
                    'potential_claim' => null,
                    'topup' => ['topup_name' => null, 'topup_value' => null],
                ],
            ],
        ];
    }

    private function algorithm_0017(array $codes, array $procedureCodes, array $context): ?array {
        $hasPneumonia = false;

        foreach ($codes as $code) {
            if (preg_match('/^J1[2-8](\.[0-9A-Z]+)?$/', $code)) {
                $hasPneumonia = true;
                break;
            }
        }

        if (!$hasPneumonia) {
            return null;
        }

        $fotoToraks = $context['foto_toraks'] ?? null;
        $gejalaKlinis = $context['gejala_klinis'] ?? null;
        $derajat = (string) ($context['derajat_pneumonia'] ?? '');
        $skor = (string) ($context['skor_pneumonia'] ?? '');
        $kultur = $context['kultur_dilakukan'] ?? null;
        $antibiotik = $context['antibiotik_diberikan'] ?? null;
        $antibiotikJam = $context['antibiotik_jam'] ?? null;
        $komorbid = $context['komorbid'] ?? null;
        $los = $context['los'] ?? null;

        $cek = [
            'Foto toraks terdokumentasi' => $fotoToraks,
            'Tanda dan gejala klinis terdokumentasi' => $gejalaKlinis,
            'Antibiotik diberikan' => $antibiotik,
            'Pemeriksaan kultur dilakukan atau keterbatasan pemeriksaan terdokumentasi' => $kultur,
        ];

        if ($antibiotikJam !== null) {
            $cek['Waktu pemberian antibiotik tercatat (jam)'] = true;
        }

        return [
            'algorithm_id' => 'ALGORITHM_0017',
            'algorithm_name' => 'Pneumonia Documentation and Specificity Review',
            'criteria_check' => [
                'derajat_pneumonia' => $derajat !== '' ? $derajat : 'BELUM_DITENTUKAN',
                'skor_pneumonia' => $skor !== '' ? $skor : 'BELUM_DITENTUKAN',
                'komorbid' => $komorbid,
                'antibiotik_jam' => $antibiotikJam,
                'lama_rawat_hari' => $los,
                'dokumentasi' => $this->buildCriteria($cek),
            ],
            'recommendations' => [
                'coder' => [
                    'status' => true,
                    'recommendation' => 'Tinjau kelengkapan dokumentasi pneumonia, termasuk foto toraks, tanda klinis, derajat atau skor pneumonia pada resume medis, serta pemeriksaan kultur atau alasan pemeriksaan tidak dilakukan. Jangan mengubah kode secara otomatis menjadi J22; gunakan diagnosis yang didokumentasikan dokter dan aturan pengkodean yang berlaku.',
                    'potential_claim' => null,
                    'topup' => ['topup_name' => null, 'topup_value' => null],
                ],
                'physician' => [
                    'status' => true,
                    'recommendation' => 'Mohon pastikan diagnosis pneumonia, temuan radiologi, tanda klinis, derajat atau skor pneumonia, komorbid, kultur bila dilakukan, serta antibiotik dan waktu pemberiannya terdokumentasi pada rekam medis.',
                    'potential_claim' => null,
                    'topup' => ['topup_name' => null, 'topup_value' => null],
                ],
            ],
        ];
    }

    private function algorithm_0018(array $codes, array $procedureCodes, array $context): ?array {
        $hasObstetri = false;
        foreach ($codes as $code) {
            if (preg_match('/^O\d/', $code)) {
                $hasObstetri = true;
                break;
            }
        }

        if (!$hasObstetri) {
            return null;
        }

        $kasusObstetri = $context['kasus_obstetri'] ?? null;
        $indikasiInap = $context['indikasi_rawat_inap'] ?? null;
        $emergensi = $context['status_emergensi'] ?? null;
        $jenisKasus = (string) ($context['kasus_obstetri_jenis'] ?? '');
        $los = $context['los'] ?? null;

        $cek = [
            'Indikasi rawat inap terdokumentasi' => $indikasiInap,
            'Status emergensi tercatat' => $emergensi,
        ];

        return [
            'algorithm_id' => 'ALGORITHM_0018',
            'algorithm_name' => 'Obstetri - Rawat Inap versus Rawat Jalan Review',
            'criteria_check' => [
                'jenis_kasus' => $jenisKasus !== '' ? $jenisKasus : 'BELUM_DITENTUKAN',
                'kasus_obstetri' => $kasusObstetri,
                'lama_rawat_hari' => $los,
                'dokumentasi' => $this->buildCriteria($cek),
            ],
            'recommendations' => [
                'coder' => [
                    'status' => true,
                    'recommendation' => 'Tandai kasus obstetri untuk telaah. Penentuan jenis klaim rawat inap atau rawat jalan tidak diatur secara tegas dalam pedoman pengkodean yang berlaku, sehingga perlu dikembalikan kepadaPrimarycareprovider untuk penetapan lebih lanjut. Pastikan resume medis memuat indikasi rawat inap dan justify tindakan yang dilakukan.',
                    'potential_claim' => null,
                    'topup' => ['topup_name' => null, 'topup_value' => null],
                ],
                'physician' => [
                    'status' => true,
                    'recommendation' => 'Mohon pastikan indikasi rawat inap, kondisi pasien saat masuk, dan justify tindakan obstetri terdokumentasi pada resume medis agar jenis klaim dapat ditentukan sesuai ketentuan yang berlaku.',
                    'potential_claim' => null,
                    'topup' => ['topup_name' => null, 'topup_value' => null],
                ],
            ],
        ];
    }

    private function algorithm_0019(array $codes, array $procedureCodes, array $context): ?array {
        $hasHypokalemia = in_array('E87.6', $codes, true);
        $hasHyponatremia = in_array('E87.1', $codes, true);

        if (!$hasHypokalemia && !$hasHyponatremia) {
            return null;
        }

        $natrium = $context['natrium'] ?? null;
        $kalium = $context['kalium'] ?? null;
        $gejala = $context['gejala_hipokalemia'] ?? null;
        $terapi = $context['terapi_elektrolit'] ?? null;
        $usia = $context['umur_tahun'] ?? null;
        $penyertaJantung = $context['komorbid'] ?? null;

        $ambangNa = $context['rujukan_na_bawah'] ?? 135;
        $ambangK = $context['rujukan_k_bawah'] ?? 3.5;

        $cek = [];

        if ($hasHyponatremia && $natrium !== null) {
            $cek['Natrium di bawah nilai rujukan'] = $natrium < $ambangNa;
        }

        if ($hasHypokalemia && $kalium !== null) {
            $cek['Kalium di bawah nilai rujukan'] = $kalium < $ambangK;
        }

        if ($hasHypokalemia && $gejala !== null) {
            $cek['Gejala klinis hipokalemia terdokumentasi'] = $gejala;
        }

        if ($terapi !== null) {
            $cek['Tata laksana elektrolit terdokumentasi'] = $terapi;
        }

        return [
            'algorithm_id' => 'ALGORITHM_0019',
            'algorithm_name' => 'Hiponatremia dan Hipokalemia (E87.1 / E87.6) Review',
            'criteria_check' => [
                'kode_hiponatremia' => $hasHyponatremia,
                'kode_hipokalemia' => $hasHypokalemia,
                'nilai_natrium' => $natrium,
                'nilai_kalium' => $kalium,
                'ambang_na_bawah' => $ambangNa,
                'ambang_k_bawah' => $ambangK,
                'catatan_usia' => $usia !== null
                    ? ($usia < 18 ? 'PADA_ANAK' : 'DEWASA')
                    : 'BELUM_DIKETAHUI',
                'penyerta_jantung' => $penyertaJantung,
                'dokumentasi' => $this->buildCriteria($cek),
            ],
            'recommendations' => [
                'coder' => [
                    'status' => true,
                    'recommendation' => 'Periksa dasar penetapan diagnosis hiponatremia atau hipokalemia berdasarkan hasil laboratorium, nilai rujukan, dokumentasi klinis, dan tata laksana yang diberikan. Pemeriksaan laboratorium serial tidak diwajibkan oleh aturan yang dirujuk dalam materi. Kode harus mengikuti diagnosis dokter yang terdokumentasi pada rekam medis.',
                    'potential_claim' => null,
                    'topup' => [
                        'topup_name' => null,
                        'topup_value' => null,
                    ],
                ],
                'physician' => [
                    'status' => true,
                    'recommendation' => 'Mohon pastikan diagnosis hiponatremia atau hipokalemia, hasil laboratorium, gejala yang relevan, serta tata laksana yang diberikan terdokumentasi pada resume medis. Penetapan diagnosis akhir berdasarkan evaluasi klinis.',
                    'potential_claim' => null,
                    'topup' => [
                        'topup_name' => null,
                        'topup_value' => null,
                    ],
                ],
            ],
        ];
    }

    private function algorithm_0020(array $codes, array $procedureCodes, array $context): ?array {
        $hasN17 = false;
        $hasN18 = false;
        $hasN19 = false;

        foreach ($codes as $code) {
            if (preg_match('/^N17(\.|$)/', $code)) {
                $hasN17 = true;
            }

            if (preg_match('/^N18(\.|$)/', $code)) {
                $hasN18 = true;
            }

            if (preg_match('/^N19(\.|$)/', $code)) {
                $hasN19 = true;
            }
        }

        if (!$hasN17 && !$hasN18 && !$hasN19) {
            return null;
        }

        $kreatininSekarang = $context['kreatinin_sekarang'] ?? null;
        $kreatininBaseline = $context['kreatinin_baseline'] ?? null;
        $waktuBaseline = $context['waktu_baseline_hari'] ?? null;
        $urineOutput = $context['urine_output'] ?? null;
        $egfr = $context['egfr'] ?? null;
        $terapi = $context['terapi_pengganti_ginjal'] ?? null;
        $jenisKelamin = strtoupper((string) ($context['jenis_kelamin'] ?? ''));

        $cek = [];

        if ($kreatininSekarang !== null && $kreatininBaseline !== null && $kreatininBaseline > 0) {
            $rasio = $kreatininSekarang / $kreatininBaseline;

            $cek['Kenaikan kreatinin >= 1,5 kali baseline'] = $rasio >= 1.5;
            $cek['Kenaikan kreatinin >= 2 kali baseline'] = $rasio >= 2.0;
            $cek['Kenaikan kreatinin >= 3 kali baseline'] = $rasio >= 3.0;
        }

        if ($kreatininSekarang !== null && $kreatininBaseline !== null && $waktuBaseline !== null) {
            $kenaikanKreatinin = $kreatininSekarang - $kreatininBaseline;

            $cek['Kenaikan kreatinin >= 0,3 mg/dL dalam 48 jam'] =
                $waktuBaseline <= 2 && $kenaikanKreatinin >= 0.3;
        }

        // Nilai ini hanya bisa dibandingkan dengan kriteria urine output
        // jika input memang dinyatakan dalam mL/kg/jam.
        if ($urineOutput !== null) {
            $cek['Output urine < 0,5 mL/kg/jam'] = $urineOutput < 0.5;
        }

        if ($egfr !== null) {
            $cek['eGFR < 60 mL/min/1,73 m2'] = $egfr < 60;
        }

        return [
            'algorithm_id' => 'ALGORITHM_0020',
            'algorithm_name' => 'AKI, CKD, dan Insufisiensi Renal Review',
            'criteria_check' => [
                'kode_gagal_ginjal_akut' => $hasN17,
                'kode_gagal_ginjal_kronis' => $hasN18,
                'kode_insufisiensi_renal' => $hasN19,
                'kreatinin_sekarang' => $kreatininSekarang,
                'kreatinin_baseline' => $kreatininBaseline,
                'waktu_baseline_hari' => $waktuBaseline,
                'urine_output' => $urineOutput,
                'egfr' => $egfr,
                'jenis_kelamin' => $jenisKelamin !== '' ? $jenisKelamin : 'BELUM_DITENTUKAN',
                'terapi_pengganti_ginjal' => $terapi,
                'dokumentasi' => $this->buildCriteria($cek),
            ],
            'recommendations' => [
                'coder' => [
                    'status' => true,
                    'recommendation' => 'Tinjau dasar penetapan diagnosis gagal ginjal akut, gagal ginjal kronis, atau insufisiensi renal berdasarkan dokumentasi medis dan pedoman pengkodean yang berlaku. Evaluasi nilai kreatinin, nilai baseline, waktu pemeriksaan, output urine, eGFR, dan terapi pengganti ginjal. Algoritma ini membantu telaah dan tidak menetapkan diagnosis secara otomatis.',
                    'potential_claim' => null,
                    'topup' => ['topup_name' => null, 'topup_value' => null],
                ],
                'physician' => [
                    'status' => true,
                    'recommendation' => 'Mohon pastikan hasil kreatinin, nilai baseline dan waktu pemeriksaannya, output urine beserta satuannya, eGFR, serta terapi pengganti ginjal terdokumentasi pada rekam medis. Penetapan diagnosis akhir tetap berdasarkan evaluasi klinis.',
                    'potential_claim' => null,
                    'topup' => ['topup_name' => null, 'topup_value' => null],
                ],
            ],
        ];
    }

    private function algorithm_0021(array $codes, array $procedureCodes, array $context): ?array {
        if (!in_array('R57.1', $codes, true)) {
            return null;
        }

        $tandaPerfusi = $context['tanda_perfusi'] ?? null;
        $penyebab = (string) ($context['penyebab_shock'] ?? '');
        $tindakan = $context['tindakan_shock'] ?? null;

        $cek = [
            'Tanda perfusi dan tanda vital terdokumentasi' => $tandaPerfusi,
            'Penyebab syok terdokumentasi' => $penyebab !== '' ? true : null,
            'Tindakan-ninehandling syok terdokumentasi' => $tindakan,
        ];

        return [
            'algorithm_id' => 'ALGORITHM_0021',
            'algorithm_name' => 'Syok Hipovolemik (R57.1) Documentation Review',
            'criteria_check' => [
                'penyebab_shock' => $penyebab !== '' ? $penyebab : 'BELUM_DITENTUKAN',
                'dokumentasi' => $this->buildCriteria($cek),
            ],
            'recommendations' => [
                'coder' => [
                    'status' => true,
                    'recommendation' => 'Tinjau dasar pengkodean syok hipovolemik. Tidak terdapat ketentuan pada pedoman pengkodean yang berlaku maupun pada keputusan kesepakatan yang menyatakan syok hipovolemik merupakan bagian atau perjalanan dari penyakit utama. Kaidah ICD-10 hanya menjelaskan kondisi yang dikecualikan pada sub kategori syok (R57), sehingga pastikan tidak ada kode syok yang terduplikasi dengan kode perdarahan, hipovolemia, atau diagnosis lain yang lebih spesifik.',
                    'potential_claim' => null,
                    'topup' => ['topup_name' => null, 'topup_value' => null],
                ],
                'physician' => [
                    'status' => true,
                    'recommendation' => 'Mohon pastikan penyebab syok, tanda tanda perfusi, dan tindakan yang dilakukan terdokumentasi pada resume medis.',
                    'potential_claim' => null,
                    'topup' => ['topup_name' => null, 'topup_value' => null],
                ],
            ],
        ];
    }

    private function algorithm_0022(array $codes, array $procedureCodes, array $context): ?array {
        $hasD21 = false;
        foreach ($codes as $code) {
            if (preg_match('/^D21(\.|$)/', $code)) {
                $hasD21 = true;
                break;
            }
        }

        if (!$hasD21) {
            return null;
        }

        $lokasi = (string) ($context['lokasi_tumor'] ?? '');
        $kedalaman = $context['kedalaman_fascia'] ?? null;
        $ukuran = $context['ukuran_tumor_cm'] ?? null;
        $strukturVital = $context['struktur_vital'] ?? null;
        $hasilPa = $context['hasil_pa'] ?? null;
        $eksisi = $context['tindakan_eksisi'] ?? null;
        $biopsi = $context['tindakan_biopsi'] ?? null;

        $cek = [
            'Tumor deep (di bawah fascia atau infiltratif)' => $kedalaman,
            'Ukuran tumor lebih dari 4 cm' => $ukuran !== null ? $ukuran > 4 : null,
            'Meluas ke struktur vital' => $strukturVital,
            'Hasil Patologi Anatomi tersedia' => $hasilPa,
        ];

        $luarKriteria = $kedalaman === false
            && ($ukuran === null || $ukuran <= 4)
            && $strukturVital === false;

        return [
            'algorithm_id' => 'ALGORITHM_0022',
            'algorithm_name' => 'Soft Tissue Tumor (D21) Criteria Review',
            'criteria_check' => [
                'lokasi_tumor' => $lokasi !== '' ? $lokasi : 'BELUM_DITENTUKAN',
                'ukuran_tumor_cm' => $ukuran,
                'tindakan_eksisi' => $eksisi,
                'tindakan_biopsi' => $biopsi,
                'dokumentasi' => $this->buildCriteria($cek),
                'kemungkinan_klaim_rawat_jalan' => $luarKriteria,
            ],
            'recommendations' => [
                'coder' => [
                    'status' => true,
                    'recommendation' => 'Periksa kelengkapan dokumen klaim tumor jaringan lunak: hasil Patologi Anatomi dan laporan operasi eksisi. Rawat inap hanya berlaku jika disertai paling sedikit satu syarat, yaitu tumor deep, ukuran lebih dari 4 cm yang meluas ke struktur vital, atau diagnose ganas serta ganas dengan keganasan. Di luar criteria tersebut, klaim diaspora sebagai rawat jalan. Tentukan hanya satu tindakan yang paling tepat, yaitu eksisi atau biopsi, dan bila eksisi dilanjutkan biopsi gunakan kode eksisi dan kode biopsi.',
                    'potential_claim' => null,
                    'topup' => ['topup_name' => null, 'topup_value' => null],
                ],
                'physician' => [
                    'status' => true,
                    'recommendation' => 'Mohon pastikan ukuran tumor, kedalaman terhadap fascia, keterlibatan struktur vital, serta hasil Patologi Anatomi dan laporan operasi terdokumentasi pada resume medis.',
                    'potential_claim' => null,
                    'topup' => ['topup_name' => null, 'topup_value' => null],
                ],
            ],
        ];
    }

    private function algorithm_0023(array $codes, array $procedureCodes, array $context): ?array {
        $anemiaPattern = '/^(D5[0-3]|D64)(\.[0-9A-Z]+)?$/';

        $hasAnemia = false;
        foreach ($codes as $code) {
            if (preg_match($anemiaPattern, $code)) {
                $hasAnemia = true;
                break;
            }
        }

        if (!$hasAnemia) {
            return null;
        }

        $hemoglobin = $context['hemoglobin'] ?? null;
        $hamil = $context['hamil'] ?? null;
        $jenisAnemia = (string) ($context['jenis_anemia'] ?? '');
        $transfusi = $context['transfusi_diberikan'] ?? null;

        $ambang = $context['hb_ambang'] ?? null;
        $sumberAmbang = 'BELUM_DITENTUKAN';

        if ($ambang === null && $hamil === true) {
            $ambang = 11;
            $sumberAmbang = 'WHO_IBU_HAMIL';
        } elseif ($ambang !== null) {
            $sumberAmbang = 'RUJUKAN_LABORATORIUM';
        }

        $cek = [];
        if ($hemoglobin !== null && $ambang !== null) {
            $cek['Hemoglobin di bawah standar rujukan'] = $hemoglobin < $ambang;
        }
        if ($transfusi !== null) {
            $cek['Transfusi diberikan'] = $transfusi;
        }

        return [
            'algorithm_id' => 'ALGORITHM_0023',
            'algorithm_name' => 'Anemia (D50-D53, D64) Coding Review',
            'criteria_check' => [
                'nilai_hemoglobin' => $hemoglobin,
                'ambang_hemoglobin' => $ambang,
                'sumber_ambang' => $sumberAmbang,
                'hamil' => $hamil,
                'jenis_anemia' => $jenisAnemia !== '' ? $jenisAnemia : 'BELUM_DITENTUKAN',
                'dokumentasi' => $this->buildCriteria($cek),
            ],
            'recommendations' => [
                'coder' => [
                    'status' => true,
                    'recommendation' => 'Periksa jenis anemia yang tercatat. Anemia defisiensi besi tanpa spesifikasi (D50.9) tidak otomatis diganti menjadi anemia nutritional (D53.9) karena tidak terdapat kode exclude yang mengarahkan hal tersebut pada sub kategori D50 sampai D53. Anemia pada ibu hamil tetap dikoding bila terdapat bukti klinis atau hasil laboratorium, tanpa syarat pemberian transfusi. Kode mengikuti diagnosis yang ditulis dokter pada rekam medis.',
                    'potential_claim' => null,
                    'topup' => ['topup_name' => null, 'topup_value' => null],
                ],
                'physician' => [
                    'status' => true,
                    'recommendation' => 'Mohon pastikan jenis anemia, hasil pemeriksaan laboratoriтуery, dan terapi yang diberikan termasuk transfusi terdokumentasi pada resume medis agar kode dapat ditetapkan sesuai diagnosis.',
                    'potential_claim' => null,
                    'topup' => ['topup_name' => null, 'topup_value' => null],
                ],
            ],
        ];
    }

    private function algorithm_0024(array $codes, array $procedureCodes, array $context): ?array {
        $hasSepsis = false;
        foreach ($codes as $code) {
            if (preg_match('/^(A40|A41|R65)(\.[0-9A-Z]+)?$/', $code)) {
                $hasSepsis = true;
                break;
            }
        }

        if (!$hasSepsis) {
            return null;
        }

        $suhu = $context['suhu'] ?? null;
        $nadi = $context['nadi'] ?? null;
        $frekuensiNafas = $context['frekuensi_nafas'] ?? null;
        $leukosit = $context['leukosit'] ?? null;
        $buktiInfeksi = $context['bukti_infeksi'] ?? null;
        $kultur = $context['kultur_hasil'] ?? null;

        $cek = [];

        if ($suhu !== null) {
            $cek['Suhu tubuh > 38,5 derajat Celsius atau < 36 derajat Celsius'] = ($suhu > 38.5 || $suhu < 36);
        }
        if ($nadi !== null) {
            $cek['Denyut jantung > 90 kali per menit'] = $nadi > 90;
        }
        if ($frekuensiNafas !== null) {
            $cek['Frekuensi pernapasan > 20 kali per menit'] = $frekuensiNafas > 20;
        }
        if ($leukosit !== null) {
            $cek['Leukosit > 10.000 sel/uL atau < 4.500 sel/uL'] = ($leukosit > 10000 || $leukosit < 4500);
        }
        if ($buktiInfeksi !== null) {
            $cek['Terdapat fokus infeksi atau peradangan'] = $buktiInfeksi;
        }
        if ($kultur !== null) {
            $cek['Bukti kultur darah dengan hasil bakterimia dilampirkan'] = $kultur;
        }

        $check = $this->buildCriteria($cek);
        $jumlahTerpenuhi = count($check['met']);

        return [
            'algorithm_id' => 'ALGORITHM_0024',
            'algorithm_name' => 'Sepsis Documentation Review',
            'criteria_check' => [
                'kriteria_sirs' => $check,
                'jumlah_kriteria_terpenuhi' => $jumlahTerpenuhi,
                'minimal_dua_kriteria' => $jumlahTerpenuhi >= 2,
            ],
            'recommendations' => [
                'coder' => [
                    'status' => true,
                    'recommendation' => 'Tinjau kelengkapan dokumentasi sepsis. Penegakan sepsis dapat mengikuti kriteria SIRS dengan minimal dua dari empat keadaan, dan Codingan_
                    tidak diperkenankan diturunkan menjadi kode sepsis tanpa dasar diagnosis dokter pada rekam medis. Pertahankan kode yang telah ditulis dokter dan lampirkan bukti kultur darah beserta hasilnya.',
                    'potential_claim' => null,
                    'topup' => ['topup_name' => null, 'topup_value' => null],
                ],
                'physician' => [
                    'status' => true,
                    'recommendation' => 'Mohon pastikan diagnosis sepsis, hasil kultur darah beserta bakterimia, pilihan antibiotik spektrum luas, dan waktu pemberiannya terdokumentasi pada resume medis sesuai Pedoman Nasional Pelayanan Kedokteran Tata Laksana Sepsis.',
                    'potential_claim' => null,
                    'topup' => ['topup_name' => null, 'topup_value' => null],
                ],
            ],
        ];
    }

    private function algorithm_0025(array $codes, array $procedureCodes, array $context): ?array {
        $hasI50 = false;
        $hasI110 = false;

        foreach ($codes as $code) {
            if (preg_match('/^I50(\.|$)/', $code)) {
                $hasI50 = true;
            }
            if (preg_match('/^I11\.0$/', $code)) {
                $hasI110 = true;
            }
        }

        if (!$hasI50 && !$hasI110) {
            return null;
        }

        $echoTanggal = (string) ($context['echo_tanggal'] ?? '');
        $ef = $context['ef'] ?? null;
        $gejala = $context['gejala_chf'] ?? null;
        $terapiTambahan = $context['terapi_tambahan_chf'] ?? null;
        $terkontrol = $context['chf_terkontrol'] ?? null;

        $eksklusi = [];
        if ($this->hasCodeInRange($codes, 'O', 10, 11) || $this->hasCodeInRange($codes, 'O', 13, 16)) {
            $eksklusi[] = 'Kehamilan dan persalinan sertaEIbu_O10-O11, O13-O16).';
        }
        if ($this->hasCodeInRange($codes, 'I', 20, 25)) {
            $eksklusi[] = 'Penyakit arteri koroner (I20-I25).';
        }
        if (in_array('I27.0', $codes, true)) {
            $eksklusi[] = 'Hipertensi pulmonal (I27.0).';
        }
        if (in_array('P29.2', $codes, true)) {
            $eksklusi[] = 'Hipertensi neonatal (P29.2).';
        }

        $cek = [
            'Echocardiografi terdokumentasi' => $echoTanggal !== '' ? true : null,
            'Gejala gagal jantung terdokumentasi' => $gejala,
            'Terapi tambahan yang diberikan' => $terapiTambahan,
            'Kondisi gagal jantung terkontrol' => $terkontrol,
        ];

        return [
            'algorithm_id' => 'ALGORITHM_0025',
            'algorithm_name' => 'Congestive Heart Failure (I50 / I11.0) Documentation Review',
            'criteria_check' => [
                'tanggal_echo' => $echoTanggal !== '' ? $echoTanggal : 'BELUM_DILAKUKAN',
                'fungsi_sistolik_ef' => $ef,
                'catatan_ekstraksi_fraction' => $ef !== null && $ef > 50
                    ? 'EF di atas 50 persen tidak cukup untuk memastikan atau menyingkirkan diagnosis gagal jantung.'
                    : null,
                'kode_eksklusi_terdeteksi' => $eksklusi,
                'dokumentasi' => $this->buildCriteria($cek),
            ],
            'recommendations' => [
                'coder' => [
                    'status' => true,
                    'recommendation' => 'Tinjau kelengkapan dokumen klaim gagal jantung. Penegakan diagnosis gagal jantung harus disertai pemeriksaan echocardiografi, yang dapat berasal dari episode paling lama satu tahun, atau pemeriksaan yang lebih baru dengan indikasi clinical. pada CHF dengan hipertensi (I11.0) perhatikan kriteria exclude kategori I50. Jangan menetapkan kode CHF sebagai diagnosis sekunder pada kasus operasi elektif dengan lama rawat pendek tanpa dasar dokumentasi.',
                    'potential_claim' => null,
                    'topup' => ['topup_name' => null, 'topup_value' => null],
                ],
                'physician' => [
                    'status' => true,
                    'recommendation' => 'Mohon pastikan hasil echocardiografi beserta tanggalnya, gejala, terapi tambahan yang diberikan, serta kondisi gagal jantung yang terkontrol atau tidak terdokumentasi pada resume medis, dan diagnosis yang paling sesuai ditetapkan.',
                    'potential_claim' => null,
                    'topup' => ['topup_name' => null, 'topup_value' => null],
                ],
            ],
        ];
    }

    //Helpers

    private function buildRequestBody(array $overrides = []): array
    {
        return array_merge($this->buildRequestTemplate(), $overrides);
    }

    private function getRequestPresets(): array
    {
        $presets = [
            'ALGORITHM_0001' => [
                'request_name' => 'Hipertensi + Penyakit Serebrovaskular (I10 + I67.8)',
                'overrides' => [
                    'diaglist' => 'I10; I67.8',
                    'ptd' => '1',
                    'umur_tahun' => 50,
                ],
            ],
            'ALGORITHM_0002' => [
                'request_name' => 'Hipertensi + Sekuela Infark Serebral (I10 + I69.3)',
                'overrides' => [
                    'diaglist' => 'I10; I69.3',
                    'ptd' => '1',
                    'umur_tahun' => 50,
                ],
            ],
            'ALGORITHM_0003' => [
                'request_name' => 'Bronkitis (J40) - Pemeriksaan Usia',
                'overrides' => [
                    'diaglist' => 'J40',
                    'ptd' => '0',
                    'umur_tahun' => 14,
                ],
            ],
            'ALGORITHM_0004' => [
                'request_name' => 'Bronkitis Akut (J20.9) - Pasien di Bawah 15 Tahun',
                'overrides' => [
                    'diaglist' => 'J20.9',
                    'ptd' => '0',
                    'umur_tahun' => 14,
                ],
            ],
            'ALGORITHM_0005' => [
                'request_name' => 'Batu Ginjal + Diagnosis Saluran Kemih - Tinjau Omit Code (N20.0 + N30-N39)',
                'overrides' => [
                    'diaglist' => 'N20.0; N30.0',
                    'ptd' => '0',
                    'umur_tahun' => 50,
                ],
            ],
            'ALGORITHM_0006' => [
                'request_name' => 'Pneumonia Tidak Spesifik (J22) - LOS Lebih dari 5 Hari',
                'overrides' => [
                    'diaglist' => 'J22',
                    'ptd' => '0',
                    'umur_tahun' => 50,
                    'los' => 6,
                ],
            ],
            'ALGORITHM_0007' => [
                'request_name' => 'Pneumonia Tidak Spesifik (J22) - LOS 5 Hari atau Kurang',
                'overrides' => [
                    'diaglist' => 'J22',
                    'ptd' => '0',
                    'umur_tahun' => 50,
                    'los' => 5,
                ],
            ],
            'ALGORITHM_0008' => [
                'request_name' => 'Infark Miokard + Prosedur Streptokinase (I21 + 89.52)',
                'overrides' => [
                    'diaglist' => 'I21.0',
                    'proclist' => '89.52',
                    'ptd' => '1',
                    'umur_tahun' => 60,
                    'los' => 3,
                ],
            ],
            'ALGORITHM_0009' => [
                'request_name' => 'Infark Serebral + Prosedur Alteplase (I63.9 + 87.03)',
                'overrides' => [
                    'diaglist' => 'I63.9',
                    'proclist' => '87.03',
                    'ptd' => '1',
                    'umur_tahun' => 60,
                    'los' => 3,
                ],
            ],
            'ALGORITHM_0010' => [
                'request_name' => 'Hip/Knee Replacement - Pemeriksaan Top Up',
                'overrides' => [
                    'diaglist' => 'M17.1',
                    'proclist' => '81.51',
                    'ptd' => '1',
                    'umur_tahun' => 60,
                    'los' => 5,
                ],
            ],
            'ALGORITHM_0011' => [
                'request_name' => 'Penyakit Jantung Koroner + PCI (I20.9 + 00.40)',
                'overrides' => [
                    'diaglist' => 'I20.9',
                    'proclist' => '00.40',
                    'ptd' => '1',
                    'umur_tahun' => 60,
                    'los' => 3,
                ],
            ],
            'ALGORITHM_0012' => [
                'request_name' => 'Stereotactic Surgery & Radiotherapy (Z51.0)',
                'overrides' => [
                    'diaglist' => 'Z51.0',
                    'ptd' => '0',
                    'umur_tahun' => 60,
                    'los' => 1,
                ],
            ],
            'ALGORITHM_0013' => [
                'request_name' => 'Microlaringoscopy (Z09.8 + 31.42)',
                'overrides' => [
                    'diaglist' => 'Z09.8',
                    'proclist' => '31.42',
                    'ptd' => '2',
                    'umur_tahun' => 50,
                    'los' => 1,
                ],
            ],
            'ALGORITHM_0014' => [
                'request_name' => 'Magnetic Resonance Imaging - MRI (88.93)',
                'overrides' => [
                    'proclist' => '88.93',
                    'ptd' => '1',
                    'umur_tahun' => 50,
                    'los' => 1,
                ],
            ],
            'ALGORITHM_0015' => [
                'request_name' => 'Gagal Napas (J96) - Pemeriksaan Kriteria',
                'overrides' => [
                    'diaglist' => 'J96.0',
                    'gagal_nafas_jenis' => 'AKUT',
                    'spo2' => 90,
                    'pao2' => 58,
                    'sao2' => null,
                    'fio2' => null,
                    'paco2' => null,
                    'ph' => null,
                    'umur_tahun' => 50,
                ],
            ],
            'ALGORITHM_0016' => [
                'request_name' => 'Cardiac Arrest (I46) - Tinjau Dokumentasi CPR',
                'overrides' => [
                    'diaglist' => 'I46.9',
                    'dokumentasi_cpr' => true,
                    'dokumentasi_resusitasi' => true,
                    'status_pasien' => 'HIDUP',
                ],
            ],
            'ALGORITHM_0017' => [
                'request_name' => 'Pneumonia - Tinjau Dokumentasi Klinis',
                'overrides' => [
                    'diaglist' => 'J18.9',
                    'foto_toraks' => true,
                    'gejala_klinis' => true,
                    'skor_pneumonia' => 'CURB-65',
                    'derajat_pneumonia' => 'SEDANG',
                    'kultur_dilakukan' => true,
                    'antibiotik_diberikan' => true,
                    'antibiotik_jam' => 24,
                    'umur_tahun' => 50,
                    'los' => 4,
                ],
            ],
            'ALGORITHM_0018' => [
                'request_name' => 'Obstetri - Tinjau Rawat Inap atau Rawat Jalan',
                'overrides' => [
                    'diaglist' => 'O03.4',
                    'kasus_obstetri' => true,
                    'kasus_obstetri_jenis' => 'ABORTUS',
                    'indikasi_rawat_inap' => true,
                    'status_emergensi' => false,
                    'los' => 1,
                ],
            ],
            'ALGORITHM_0019' => [
                'request_name' => 'Hiponatremia + Hipokalemia - Tinjau Elektrolit',
                'overrides' => [
                    'diaglist' => 'E87.1; E87.6',
                    'natrium' => 130,
                    'kalium' => 3.2,
                    'gejala_hipokalemia' => true,
                    'terapi_elektrolit' => true,
                    'umur_tahun' => 50,
                ],
            ],
            'ALGORITHM_0020' => [
                'request_name' => 'AKI / CKD / Insufisiensi Renal - Tinjau Fungsi Ginjal',
                'overrides' => [
                    'diaglist' => 'N17.9; N18.9',
                    'kreatinin_sekarang' => 2.0,
                    'kreatinin_baseline' => 1.0,
                    'waktu_baseline_hari' => 2,
                    'urine_output' => 0.4,
                    'egfr' => 45,
                    'jenis_kelamin' => 'P',
                ],
            ],
            'ALGORITHM_0021' => [
                'request_name' => 'Syok Hipovolemik (R57.1) - Tinjau Dokumentasi',
                'overrides' => [
                    'diaglist' => 'R57.1',
                    'tanda_perfusi' => true,
                    'penyebab_shock' => 'KEHILANGAN CAIRAN',
                    'tindakan_shock' => true,
                ],
            ],
            'ALGORITHM_0022' => [
                'request_name' => 'Soft Tissue Tumor - Tinjau Kriteria dan PA',
                'overrides' => [
                    'diaglist' => 'D21.9',
                    'lokasi_tumor' => 'JARINGAN LUNAK',
                    'kedalaman_fascia' => true,
                    'ukuran_tumor_cm' => 5,
                    'struktur_vital' => true,
                    'hasil_pa' => true,
                    'tindakan_eksisi' => true,
                    'tindakan_biopsi' => true,
                ],
            ],
            'ALGORITHM_0023' => [
                'request_name' => 'Anemia - Tinjau Hemoglobin dan Terapi',
                'overrides' => [
                    'diaglist' => 'D50.9',
                    'hemoglobin' => 9,
                    'hb_ambang' => 11,
                    'hamil' => false,
                    'jenis_anemia' => 'DEFISIENSI BESI',
                    'transfusi_diberikan' => false,
                ],
            ],
            'ALGORITHM_0024' => [
                'request_name' => 'Sepsis - Tinjau Dokumentasi Klinis',
                'overrides' => [
                    'diaglist' => 'A41.9',
                    'suhu' => 39,
                    'nadi' => 100,
                    'frekuensi_nafas' => 22,
                    'leukosit' => 12000,
                    'bukti_infeksi' => true,
                    'kultur_hasil' => true,
                ],
            ],
            'ALGORITHM_0025' => [
                'request_name' => 'CHF - Tinjau Dokumentasi Echocardiography',
                'overrides' => [
                    'diaglist' => 'I50.9',
                    'echo_tanggal' => '2026-01-15',
                    'ef' => 45,
                    'gejala_chf' => true,
                    'terapi_tambahan_chf' => true,
                    'chf_terkontrol' => false,
                ],
            ],
        ];

        $result = [];

        foreach ($presets as $algorithmId => $preset) {
            $result[$algorithmId] = [
                'algorithm_id' => $algorithmId,
                'request_name' => $preset['request_name'],
                'method' => 'POST',
                'url' => '/api/v1/claimguard/rulesclaimguard',
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => $this->buildRequestBody($preset['overrides']),
            ];
        }

        return $result;
    }

    private function buildRequestTemplate(): array
    {
        return [
            'diaglist' => '',
            'proclist' => '',
            'ptd' => '',
            'umur_tahun' => null,
            'los' => null,

            // ALGORITHM_0015: Gagal napas (J96)
            'gagal_nafas_jenis' => '',
            'spo2' => null,
            'pao2' => null,
            'sao2' => null,
            'fio2' => null,
            'paco2' => null,
            'paco2_baseline' => null,
            'ph' => null,
            'terapi_nafas' => null,
            'dokumentasi_gejala_nafas' => null,

            // ALGORITHM_0016: Cardiac arrest (I46)
            'dokumentasi_cpr' => null,
            'dokumentasi_resusitasi' => null,
            'status_pasien' => '',
            'kondisi_obstetri' => null,

            // ALGORITHM_0017: Pneumonia
            'foto_toraks' => null,
            'gejala_klinis' => null,
            'derajat_pneumonia' => '',
            'skor_pneumonia' => '',
            'kultur_dilakukan' => null,
            'antibiotik_diberikan' => null,
            'antibiotik_jam' => null,
            'komorbid' => null,

            // ALGORITHM_0018: Obstetri rawat inap vs rawat jalan
            'kasus_obstetri' => null,
            'kasus_obstetri_jenis' => '',
            'indikasi_rawat_inap' => null,
            'status_emergensi' => null,

            // ALGORITHM_0019: Hiponatremia / hipokalemia
            'natrium' => null,
            'kalium' => null,
            'rujukan_na_bawah' => 135,
            'rujukan_k_bawah' => 3.5,
            'gejala_hipokalemia' => null,
            'terapi_elektrolit' => null,
            'pemeriksaan_serial' => null,

            // ALGORITHM_0020: AKI / CKD / insufisiensi renal
            'kreatinin_sekarang' => null,
            'kreatinin_baseline' => null,
            'waktu_baseline_hari' => null,
            'urine_output' => null,
            'egfr' => null,
            'jenis_kelamin' => '',
            'terapi_pengganti_ginjal' => null,

            // ALGORITHM_0021: Syok hipovolemik (R57.1)
            'tanda_perfusi' => null,
            'penyebab_shock' => '',
            'tindakan_shock' => null,

            // ALGORITHM_0022: Soft-tissue tumor
            'lokasi_tumor' => '',
            'kedalaman_fascia' => null,
            'ukuran_tumor_cm' => null,
            'struktur_vital' => null,
            'hasil_pa' => null,
            'tindakan_eksisi' => null,
            'tindakan_biopsi' => null,

            // ALGORITHM_0023: Anemia
            'hemoglobin' => null,
            'hb_ambang' => null,
            'hamil' => null,
            'jenis_anemia' => '',
            'transfusi_diberikan' => null,

            // ALGORITHM_0024: Sepsis
            'suhu' => null,
            'nadi' => null,
            'frekuensi_nafas' => null,
            'leukosit' => null,
            'bukti_infeksi' => null,
            'kultur_hasil' => null,

            // ALGORITHM_0025: CHF
            'echo_tanggal' => '',
            'ef' => null,
            'gejala_chf' => null,
            'terapi_tambahan_chf' => null,
            'chf_terkontrol' => null,
        ];
    }

    private function buildInputMeta(array $context): array
    {
        $input = [
            'diagnosis_codes' => $context['diagnosis_codes'],
            'procedure_codes' => $context['procedure_codes'],
            'ptd' => $context['ptd'],
            'age_years' => $context['umur_tahun'],
            'los' => $context['los'],
        ];

        foreach ($context as $key => $value) {
            if (in_array($key, ['diagnosis_codes', 'procedure_codes', 'ptd', 'umur_tahun', 'los'], true)) {
                continue;
            }

            if ($value !== null && $value !== '') {
                $input[$key] = $value;
            }
        }

        return $input;
    }

    private function buildCriteria(array $items): array
    {
        $met = [];
        $notMet = [];
        $unknown = [];

        foreach ($items as $label => $state) {
            if ($state === true) {
                $met[] = $label;
            } elseif ($state === false) {
                $notMet[] = $label;
            } else {
                $unknown[] = $label;
            }
        }

        return [
            'met' => $met,
            'not_met' => $notMet,
            'unknown' => $unknown,
            'total_evaluated' => count($met) + count($notMet),
        ];
    }

    private function hasCodeInRange(array $codes, string $prefix, int $from, int $to): bool
    {
        foreach ($codes as $code) {
            if (preg_match('/^' . preg_quote($prefix, '/') . '(\d{2})/', $code, $match)) {
                $number = (int) $match[1];

                if ($number >= $from && $number <= $to) {
                    return true;
                }
            }
        }

        return false;
    }

    private function toBool($value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        $normalized = strtolower(trim((string) $value));

        if (in_array($normalized, ['1', 'true', 'ya', 'yes'], true)) {
            return true;
        }

        if (in_array($normalized, ['0', 'false', 'tidak', 'no'], true)) {
            return false;
        }

        return null;
    }

    private function toFloat($value): ?float
    {
        return ($value === null || $value === '') ? null : (float) $value;
    }

    private function toInt($value): ?int
    {
        return ($value === null || $value === '') ? null : (int) $value;
    }

    private function toText($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return trim((string) $value);
    }
}