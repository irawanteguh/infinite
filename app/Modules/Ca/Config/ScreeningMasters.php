<?php

namespace Modules\Ca\Config;

class ScreeningMasters
{
    public const RISK_LABELS = [
        'Average Risk (Risiko ringan)',
        'Moderate Risk (Risiko Sedang)',
        'High Risk (Risiko Tinggi)',
    ];

    public const INSTRUMENTS = [
        'IASTO' => [
            'name' => 'IASTO–PDPI 2023 · Skrining kanker paru',
            'risks' => [[null, 11, self::RISK_LABELS[0]], [12, 16, self::RISK_LABELS[1]], [17, 29, self::RISK_LABELS[2]]],
            'note' => 'Rentang yang dapat dihasilkan oleh sembilan pertanyaan adalah 9–28. Skor 29 tercantum pada sumber kategori. Formulir belum menentukan skor diagnosis kanker tepat 5 tahun lalu, berhenti merokok ≥15 tahun, atau aturan jika TBC dan PPOK sama-sama ada.',
            'questions' => [
                ['Jenis kelamin Anda?', [['Laki-laki', 3], ['Perempuan', 1]]],
                ['Berapa usia Anda sekarang?', [['>65 tahun', 3], ['45–65 tahun', 2], ['<45 tahun', 1]]],
                ['Apakah pernah didiagnosis menderita kanker?', [['Ya, >5 tahun lalu', 3], ['Ya, <5 tahun lalu', 2], ['Tidak pernah', 1]]],
                ['Apakah ayah, ibu, atau saudara kandung menderita kanker?', [['Ya, kanker paru', 3], ['Ya, kanker jenis lain', 2], ['Tidak ada', 1]]],
                ['Bagaimana riwayat merokok atau paparan asap rokok (kretek, rokok putih, vape, shisha, cerutu, rokok linting, dan sejenisnya)?', [['Perokok aktif, masih merokok dalam 1 tahun ini', 4], ['Bekas perokok, berhenti <15 tahun', 3], ['Perokok pasif di rumah atau kantor', 2], ['Tidak merokok', 1]]],
                ['Apakah pernah bekerja di lingkungan yang mengandung zat karsinogen (pertambangan, pabrik, bengkel, garmen, laboratorium kimia, galangan kapal, dan sejenisnya)?', [['Ya', 3], ['Tidak yakin / ragu-ragu', 2], ['Tidak', 1]]],
                ['Apakah tinggal di lingkungan berpolusi tinggi (sekitar pabrik, pertambangan, tempat pembuangan sampah, atau tepi jalan besar)?', [['Ya', 3], ['Tidak yakin / ragu-ragu', 2], ['Tidak', 1]]],
                ['Apakah lingkungan dalam rumah tidak sehat (ventilasi buruk, atap asbes, lantai tanah, dapur kayu bakar atau briket, atau rutin memakai obat nyamuk bakar atau semprot)?', [['Ya', 3], ['Tidak yakin / ragu-ragu', 2], ['Tidak', 1]]],
                ['Apakah pernah didiagnosis atau diobati karena penyakit paru kronik?', [['Ya, pernah tuberkulosis (TBC)', 3], ['Ya, pernah PPOK', 2], ['Tidak', 1]]],
            ],
        ],
        'APCS' => [
            'name' => 'APCS · Skrining kanker kolorektal',
            'risks' => [[0, 1, self::RISK_LABELS[0]], [2, 3, self::RISK_LABELS[1]], [4, 7, self::RISK_LABELS[2]]],
            'note' => '',
            'questions' => [
                ['Berapa usia Anda sekarang?', [['<50 tahun', 0], ['50–69 tahun', 2], ['≥70 tahun', 3]]],
                ['Jenis kelamin Anda?', [['Perempuan', 0], ['Laki-laki', 1]]],
                ['Apakah ayah, ibu, saudara kandung, atau anak kandung menderita kanker kolorektal?', [['Tidak ada', 0], ['Ada', 2]]],
                ['Bagaimana riwayat merokok Anda?', [['Tidak pernah merokok', 0], ['Masih atau pernah merokok', 1]]],
            ],
        ],
    ];
}
