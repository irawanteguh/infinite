<?php

namespace Modules\Pe\Config;

class GeneralQuestionnaires
{
    public const SERVICES = [
        'IGD' => ['code' => 'PREM_GENERAL_IGD', 'name' => 'SKM IGD', 'icon' => 'bi-heart-pulse', 'description' => 'Pengalaman saat menerima pelayanan kegawatdaruratan.'],
        'RJ' => ['code' => 'PREM_GENERAL_RJ', 'name' => 'SKM Rawat Jalan', 'icon' => 'bi-hospital', 'description' => 'Pengalaman selama kunjungan dan pemeriksaan rawat jalan.'],
        'RI' => ['code' => 'PREM_GENERAL_RI', 'name' => 'SKM Rawat Inap', 'icon' => 'bi-building', 'description' => 'Pengalaman selama menjalani perawatan di ruang rawat inap.'],
    ];

    public static function service(string $code): ?string
    {
        foreach (self::SERVICES as $service => $definition) {
            if ($definition['code'] === $code) return $service;
        }
        return null;
    }

    public static function questions(string $service): array
    {
        if (!isset(self::SERVICES[$service])) throw new \InvalidArgumentException('Jenis pelayanan SKM tidak valid.');
        return ['Survei Kepuasan Masyarakat' => array_column(SkmQuestionnaire::QUESTIONS, 0)];
    }
}