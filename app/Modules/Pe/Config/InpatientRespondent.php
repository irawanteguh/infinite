<?php

namespace Modules\Pe\Config;

class InpatientRespondent
{
    public const CLINICS = [
        'Poli Spesialis Anak',
        'Poli Sub Spesialis - Hematologi Onkologi Anak',
        'Poli Stunting',
        'Poli Spesialis Anastesi',
        'Poli Spesialis Bedah Umum',
        'Poli Spesialis Bedah Onkologi',
        'Poli Spesialis Bedah Saraf',
        'Poli Spesialis Bedah Toraks dan Kardiovaskuler',
        'Poli Spesialis Bedah Orthopedi',
        'Poli Spesialis Bedah Urologi',
        'Poli Spesialis Jantung dan Pembuluh Darah',
        'Layanan Cath - Lab',
        'Layanan Diagnostik Center',
        'Poli Spesialis Kebidanan dan Kandungan',
        'Poli Sub Spesialis - Ginekologi Onkologi',
        'Poli Spesialis Penyakit Dalam',
        'Poli Sub Spesialis - Penyakit Dalam Hematologi Onkologi Medik',
        'Poli Asoka',
        'Hemodialisa & CAPD',
        'Poli Spesialis Saraf',
        'Poli Spesialis Paru',
        'Poli TB Paru',
        'Poli Spesialis Mata',
        'Poli Spesialis Kulit dan Kelamin',
        'Poli Spesialis THT - KL',
        'Poli Gigi Umum',
        'Poli Gigi Spesialis Bedah Mulut',
        'Poli Gigi Spesialis Endodonsi',
        'Poli Gigi Spesialis Orthodonti',
        'Poli Gigi Spesialis Pedodontis',
        'Poli Gigi Spesialis Prostodonsia',
        'Poli Spesialis Gizi Klinik',
        'Poli Spesialis Psikiatri',
        'Poli Psikologi',
        'Poli Spesialis Rehabilitasi Medik',
        'Layanan Kemoterapi',
        'Poli Onkologi Radiasi (Radioterapi)',
        'Medical Check Up (MCU)',
        'Poli Umum',
    ];

    public const OPTIONS = [
        'respondent_gender' => ['label' => 'Jenis Kelamin', 'choices' => ['L' => 'Laki-laki', 'P' => 'Perempuan']],
        'ward_class' => ['label' => 'Pada Unit Layanan', 'choices' => ['KELAS_3' => 'Kelas 3', 'KELAS_2' => 'Kelas 2', 'KELAS_1' => 'Kelas 1', 'VIP' => 'VIP']],
        'occupation' => ['label' => 'Pekerjaan', 'choices' => ['PNS' => 'PNS', 'TNI' => 'TNI', 'POLRI' => 'POLRI', 'BUMN' => 'BUMN', 'SWASTA' => 'KARYAWAN SWASTA', 'WIRAUSAHA' => 'WIRAUSAHA', 'TIDAK_BEKERJA' => 'TIDAK BEKERJA', 'LAINNYA' => 'LAINNYA']],
        'education' => ['label' => 'Pendidikan', 'choices' => ['TIDAK_SEKOLAH' => 'Tidak Sekolah/Belum Sekolah', 'SD' => 'SD', 'SMP' => 'SMP', 'SMA' => 'SMA', 'D1_D3' => 'D1/D3', 'S1' => 'S1', 'S2' => 'S2', 'S3' => 'S3']],
        'insurance' => ['label' => 'Jaminan', 'choices' => ['BPJS_KESEHATAN' => 'BPJS Kesehatan', 'BPJS_KETENAGAKERJAAN' => 'BPJS Ketenagakerjaan', 'UMUM' => 'Pasien Umum (Berbayar)', 'ASURANSI' => 'Asuransi', 'JAMKESJAK' => 'Jamkesjak', 'TASPEN' => 'Taspen', 'JASARAHARJA' => 'Jasaraharja']],
    ];

    public const FIELDS = ['respondent_name', 'respondent_gender', 'respondent_age', 'ward_class', 'occupation', 'occupation_other', 'education', 'insurance', 'clinic'];

    public static function validate(array $input, bool $requireClinic = false): array
    {
        $data = [];
        foreach (self::FIELDS as $field) {
            $value = $input[$field] ?? '';
            if (!is_string($value) && !is_int($value)) throw new \InvalidArgumentException('Format data responden tidak valid.');
            $data[$field] = trim((string) $value);
        }
        if ($data['respondent_name'] === '' || mb_strlen($data['respondent_name']) > 200 || preg_match('/[\x00-\x1F\x7F]/u', $data['respondent_name'])) {
            throw new \InvalidArgumentException('Nama responden wajib diisi, maksimal 200 karakter.');
        }
        if (!preg_match('/\A[0-9]{1,3}\z/', $data['respondent_age']) || (int) $data['respondent_age'] > 120) {
            throw new \InvalidArgumentException('Usia wajib diisi dengan angka 0 sampai 120 tahun.');
        }
        $data['respondent_age'] = (int) $data['respondent_age'];
        foreach (self::OPTIONS as $field => $definition) {
            if (!isset($definition['choices'][$data[$field]])) throw new \InvalidArgumentException('Pilih ' . strtolower($definition['label']) . ' yang sesuai.');
        }
        if ($data['occupation'] === 'LAINNYA') {
            if ($data['occupation_other'] === '' || mb_strlen($data['occupation_other']) > 150 || preg_match('/[\x00-\x1F\x7F]/u', $data['occupation_other'])) {
                throw new \InvalidArgumentException('Isi keterangan pekerjaan lainnya, maksimal 150 karakter.');
            }
        } else {
            $data['occupation_other'] = null;
        }
        if ($requireClinic && !in_array($data['clinic'], self::CLINICS, true)) {
            throw new \InvalidArgumentException('Pilih poli yang sesuai.');
        }
        if (!$requireClinic) $data['clinic'] = null;
        return $data;
    }

    public static function summary(array $input): array
    {
        $result = ['Nama responden' => $input['respondent_name'] ?? '', 'Usia' => isset($input['respondent_age']) ? $input['respondent_age'] . ' tahun' : ''];
        foreach (self::OPTIONS as $field => $definition) {
            if ($field === 'ward_class' && ($input['service_type'] ?? $input['fixed_service_type'] ?? '') === 'IGD') continue;
            if ($field === 'ward_class' && ($input['service_type'] ?? $input['fixed_service_type'] ?? '') === 'RJ') {
                $result['Poli'] = $input['clinic'] ?? '';
                continue;
            }
            $result[$definition['label']] = $definition['choices'][$input[$field] ?? ''] ?? '';
        }
        if (($input['occupation'] ?? '') === 'LAINNYA') $result['Pekerjaan'] .= ': ' . ($input['occupation_other'] ?? '');
        return $result;
    }
}