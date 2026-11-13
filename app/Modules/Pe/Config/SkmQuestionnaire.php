<?php

namespace Modules\Pe\Config;

class SkmQuestionnaire
{
    public const QUESTIONS = [
        ['Kesesuaian persyaratan pelayanan dengan jenis pelayanan', ['Sangat Tidak Sesuai', 'Tidak Sesuai', 'Sesuai', 'Sangat Sesuai']],
        ['Kemudahan prosedur pelayanan', ['Tidak Sesuai', 'Kurang Mudah', 'Mudah', 'Sangat Mudah']],
        ['Kecepatan Waktu Pelayanan', ['Tidak Cepat', 'Kurang Cepat', 'Cepat', 'Sangat Cepat']],
        ['Kewajaran Biaya/Tarif dalam Pelayanan', ['Sangat Mahal', 'Cukup Mahal', 'Murah/Terjangkau', 'Tanpa Biaya (Gratis)']],
        ['Kesesuaian Produk Layanan', ['Sangat Tidak Sesuai', 'Tidak Sesuai', 'Sesuai', 'Sangat Sesuai']],
        ['Kompetensi Petugas Pelayanan', ['Tidak Kompeten', 'Kurang Kompeten', 'Kompeten', 'Sangat Kompeten']],
        ['Perilaku Petugas (Kesopanan & Keramahan)', ['Tidak Sopan/Ramah', 'Kurang Sopan/Ramah', 'Sopan dan Ramah', 'Sangat Sopan dan Ramah']],
        ['Kualitas Sarana dan Prasarana', ['Buruk', 'Cukup', 'Baik', 'Sangat Baik']],
        ['Penanganan Pengaduan, Saran dan Masukan', ['Buruk', 'Kurang Baik', 'Baik', 'Sangat Baik']],
    ];

    public static function defaultOptions(): array
    {
        return [1 => 'Sangat tidak setuju', 2 => 'Tidak setuju', 3 => 'Ragu-ragu', 4 => 'Setuju', 5 => 'Sangat setuju'];
    }

    public static function options(array $question): array
    {
        $raw = $question['answer_options'] ?? null;
        if ($raw === null || $raw === '') return self::defaultOptions();
        $options = is_array($raw) ? $raw : json_decode($raw, true);
        if (!is_array($options) || !in_array(count($options), [4, 5], true)) throw new \RuntimeException('Pilihan jawaban tidak valid.');
        $result = [];
        foreach (array_values($options) as $index => $label) {
            if (!is_string($label) || trim($label) === '') throw new \RuntimeException('Label jawaban tidak valid.');
            $result[$index + 1] = $label;
        }
        return $result;
    }
}