<?php

namespace Modules\Ur\Services;

class EpisodeReview
{
    private $ppk = [];
    private $tumor = [];

    public function __construct()
    {
        foreach ($this->catalog('ppk1') as $rule) {
            foreach (explode(';', $rule['diagnoses']) as $code) {
                $code = strtoupper(trim($code));
                if ($code !== '') $this->ppk[$code][] = $rule;
            }
        }
        foreach ($this->catalog('tumor') as $rule) {
            $diagnoses = array_filter(array_map('trim', explode(';', $rule['diagnoses'])));
            $procedures = array_filter(array_map('trim', explode(';', $rule['procedures'])));
            if (!$diagnoses || !$procedures) continue;
            $prefixes = array_map(static fn($code) => preg_quote($code, '/'), $diagnoses);
            $this->tumor[] = ['pattern' => '/^(' . implode('|', $prefixes) . ')/i', 'procedures' => $procedures, 'note' => $rule['note']];
        }
    }

    private function catalog(string $name): array
    {
        return json_decode(file_get_contents(APPPATH . 'Modules/Ur/Config/' . $name . '.json'), true, 512, JSON_THROW_ON_ERROR);
    }

    public function findings(array $row): array
    {
        $findings = [];
        $add = static function ($category, $title, $note) use (&$findings): void {
            $findings[] = compact('category', 'title', 'note');
        };
        $diagnoses = strtoupper(trim((string) $row['diaglist']));
        $procedures = strtoupper(trim((string) $row['proclist']));
        $primary = trim(explode(';', $diagnoses, 2)[0]);
        $codes = array_map('trim', explode(';', $procedures));
        $inpatient = (string) $row['ptd'] === '1';
        $los = $row['los'];
        $severity = $row['sl'];
        $hours = $row['vent_hour'];
        $short = in_array('96.71', $codes, true);
        $long = in_array('96.72', $codes, true);

        if ($diagnoses === '') $add('data', 'Diagnosis kosong', 'Periksa kelengkapan diagnosis episode.');
        if ($procedures === '') $add('data', 'Prosedur kosong', 'Konfirmasi apakah ada tindakan yang perlu dicatat.');
        if (preg_match('/^R[0-9]{2}(?:[.][0-9A-Z]+)?$/D', $primary)) {
            $add('koding', 'Kode gejala sebagai diagnosis utama', 'Tinjau diagnosis akhir dan bukti pendukungnya.');
        }
        if ($primary === 'I10' && preg_match('/;\s*I(?:67[.]8|69[.]3)\s*(?:;|$)/', $diagnoses)) {
            $add('koding', 'Urutan diagnosis I10 perlu diperiksa', 'Tinjau urutan I10 terhadap I67.8 atau I69.3 sesuai dokumentasi episode.');
        }
        if (array_intersect(['96.70', '96.71', '96.72'], $codes)
            && !preg_match('/(?:^|;)\s*(?:J96(?:[.][0-9A-Z]+)?|Z99[.]1)\s*(?:;|$)/', $diagnoses)) {
            $add('koding', 'Diagnosis terkait ventilasi perlu ditinjau', 'Periksa dokumentasi episode untuk tindakan ventilasi yang tercatat.');
        }
        if ($row['icu_indikator'] !== null && (string) $row['icu_indikator'] !== '0') {
            $add('klinis', 'Episode dengan indikator ICU', 'Tinjau catatan ICU dan kesesuaiannya dengan data episode.');
        }
        foreach ($this->ppk[$primary] ?? [] as $rule) {
            $add('klinis', 'PPK 1 · ' . trim($rule['name']), $rule['note']);
        }
        foreach ($this->tumor as $rule) {
            if (!preg_match($rule['pattern'], $primary) || !array_intersect($codes, $rule['procedures'])) continue;
            $add('klinis', 'Pola diagnosis dan tindakan tumor', str_replace(['\\\\n', '\\n'], "\n", $rule['note']));
        }
        if (!$inpatient) return $findings;

        if (($short || $long) && ($hours === null || (float) $hours === 0.0 || ($short && $long)
            || ($short && $hours >= 96) || ($long && $hours < 96))) {
            $add('koding', 'Kode dan jam ventilator perlu ditinjau', 'Cocokkan kode tindakan dan jam ventilator dengan dokumentasi episode.');
        }
        if ($los !== null && $los < 2) {
            $add('klinis', 'LOS rawat inap kurang dari 2 hari', 'Periksa indikasi rawat inap dan dokumentasi kepulangan.');
        }
        if ($los !== null && (($severity === 'I' && $los > 7) || ($severity === 'III' && $los < 4))) {
            $add('klinis', 'Lama rawat dan severity perlu ditinjau', 'Tinjau lama rawat dan severity bersama dokumentasi episode.');
        }
        if (strpos($diagnoses, 'E11.9') !== false && (strpos($procedures, '96.71') !== false || strpos($procedures, '96.72') !== false)) {
            $add('klinis', 'Pola DM dan tindakan ventilasi', 'Tinjau dokumentasi diagnosis dan tindakan sebagaimana pada daftar kasus DM.');
        }
        if (preg_match('/(?:^|;)\s*(?:J1[2-8]|J22)(?:[.]|;|$)/', $diagnoses)) {
            $add('klinis', 'Pola pneumonia atau infeksi saluran napas', 'Periksa dokumentasi dan pemeriksaan penunjang yang mendasari diagnosis.');
        }
        if ($los !== null && $severity === 'I' && preg_match('/(?:^|;)\s*J22(?:[.]|;|$)/', $diagnoses)) {
            $note = $los > 5
                ? 'Tinjau perkembangan klinis, pemeriksaan penunjang, dan dokumentasi diagnosis akhir.'
                : 'Tinjau kesesuaian diagnosis dengan kondisi klinis, lama rawat, dan pemeriksaan penunjang.';
            $add('klinis', 'Audit Medik · J22 dengan severity I', $note);
        }
        if ($los !== null && $los < 7 && preg_match('/E(40|41|42|43|44|45)/', $diagnoses)) {
            $add('klinis', 'Pola malnutrisi dengan LOS kurang dari 7 hari', 'Tinjau bukti penilaian gizi dan dokumentasi diagnosis.');
        }

        return $findings;
    }

    public function claimFindings(array $history): array
    {
        $findings = [];
        foreach ($history as $entry) {
            $status = strtoupper(trim((string) $entry['status']));
            $reasons = array_filter(array_map('trim', explode('|', (string) $entry['reason'])));
            if (!$reasons) $reasons = [''];
            foreach ($reasons as $reason) {
                $label = trim(preg_replace('/^(?:#\s*ALGORITMA|ALGORITMA|OTOVER)\s*->\s*/i', '', $reason));
                $namedPattern = in_array(strtoupper(rtrim($label, '.')), ['PROSEDUR BERULANG', 'PROSEDUR SLITLAMP'], true);
                if (!$namedPattern && !in_array($status, ['PENDING', 'TIDAK LAYAK'], true)) continue;
                $title = $label === '' ? 'Verifikasi BAHV · ' . $status : 'Pola BAHV · ' . $label;
                $note = 'Tinjau alasan verifikasi historis pada SEP ini.';
                $findings[$title] = ['category' => 'klaim', 'title' => $title, 'note' => $note];
            }
        }
        return array_values($findings);
    }
}
