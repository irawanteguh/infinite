<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ModelBahv;

class Bahv extends BaseController{

    protected $md;

    public function __construct(){
        $this->md = new ModelBahv();
    }
    
    public function importtxtbahv(){
        $headersRaw = request()->getPost('headers');
        $rowsRaw    = request()->getPost('data');

        $headers = json_decode($headersRaw, true);
        $rows    = json_decode($rowsRaw, true);

        if (!is_array($headers) || empty($headers) || !is_array($rows)) {
            return $this->response->setJSON([
                "responCode"   => "01",
                "responMsg"    => "Data or header is empty.",
                "responResult" => [
                    "total"   => 0,
                    "success" => 0,
                    "failed"  => 0
                ]
            ]);
        }

        $success = 0;
        $failed  = 0;

        foreach ($rows as $row) {
            $nosep = "";

            try {

                if (!is_array($row)) {
                    $failed++;
                    continue;
                }

                if (count($row) < count($headers)) {
                    $row = array_pad($row, count($headers), "");
                } elseif (count($row) > count($headers)) {
                    $row = array_slice($row, 0, count($headers));
                }

                $row = array_combine($headers, $row);

                if ($row === false) {
                    $failed++;
                    continue;
                }

                $nosep = trim((string) ($row["NOSEP"] ?? ""));

                if ($nosep === "") {
                    $failed++;
                    continue;
                }

                $kodeINACBG = $this->cleanTxt($row["KDINACBG"] ?? null);

                $data = [];

                $data["BAHV_ID"]               = 'd80ebae3-c768-11f1-9151-e0be037258a4';
                $data["STATUS"]                = strtoupper($this->cleanTxt($row["STATUS"] ?? null));
                $data["KODEPPK"]               = $this->cleanTxt($row["KODEPPK"] ?? isset($_SESSION["kodeppk"]) ? $_SESSION["kodeppk"] : "");
                $data["NAMAPPK"]               = $this->cleanTxt($row["NAMAPPK"] ?? null);
                $data["NOSEP"]                 = $nosep;
                $data["TGLSEP"]                = $this->formatDateEklaim($row["TGLSEP"] ?? null);
                $data["TGLPULANG"]             = $this->formatDateEklaim($row["TGLPULANG"] ?? null);
                $data["TGLVERIF"]              = $this->formatDateEklaim($row["TGLVERIF"] ?? null);
                $data["JNSPEL"]                = strtoupper($this->cleanTxt($row["JNSPEL"] ?? null)) === 'RAWAT INAP' ? '1' : '2';
                $data["NOKARTU"]               = $this->cleanTxt($row["NOKARTU"] ?? null);
                $data["NIK"]                   = $this->cleanTxt($row["NIK"] ?? null);
                $data["NMPESERTA"]             = $this->cleanTxt($row["NMPESERTA"] ?? null);
                $data["POLI"]                  = ($poli = $this->cleanTxt($row["POLI"] ?? null))     === '' ? null : $poli;
                $data["KDINACBG"]              = $kodeINACBG;
                $data["CMG"]                   = !empty($kodeINACBG) ? substr($kodeINACBG, 0, 1) : null;
                $data["CG"]                    = !empty($kodeINACBG) ? substr($kodeINACBG, 2, 1) : null;
                $data["CT"]                    = !empty($kodeINACBG) ? substr($kodeINACBG, 4, 2) : null;
                $data["SL"]                    = !empty($kodeINACBG) ? substr($kodeINACBG, 7, 3) : null;
                $data["PENGAJUAN"]             = $this->cleanNumber($row["BYPENGAJUAN"] ?? null);
                $data["ALGORITMA_TYPE"]        = $data["ALGORITMA"] = trim(explode('->', $this->cleanTxt($row["ALGORITMA"] ?? null))[0]);
                $data["ALGORITMA"]             = $this->cleanTxt($row["ALGORITMA"] ?? null);
                $data["KETERANGAN_VERIFIKASI"] = $this->cleanTxt($row["KETERANGANVERIFIKASI"] ?? null);
                $data["CREATED_BY"]            = isset($_SESSION["userid"]) ? $_SESSION["userid"] : "SYSTEM";

                $result = $this->md->insertpendingbahv($data);

                if ($result === false) {
                    $failed++;
                    log_message('error','IMPORT BAHV GAGAL | NOSEP: ' .$nosep.' | DATA: ' . json_encode($data, JSON_UNESCAPED_UNICODE).' | DB ERROR: ' . json_encode($dbError, JSON_UNESCAPED_UNICODE));
                    continue;
                }

                $success++;
            } catch (\Throwable $e) {
                $failed++;
                log_message('error','IMPORT BAHV EXCEPTION | NOSEP: '.$nosep.' | PESAN: '.$e->getMessage().' | FILE: ' . $e->getFile().' | LINE: ' . $e->getLine());
            }
        }

        return $this->response->setJSON([
            "responCode"   => "00",
            "responMsg"    => "Import BAHV berhasil diproses.",
            "responResult" => [
                "total"   => count($rows),
                "success" => $success,
                "failed"  => $failed
            ]
        ]);
    }

    private function formatDateEklaim($date){
        if ($date === null) {
            return null;
        }

        $date = trim((string) $date);

        if ($date === '' || in_array(strtolower($date), ['-', '/', '.', 'none'], true)) {
            return null;
        }

        $formats = [
            '!n/j/y',
            '!m/d/y',
            '!n/j/Y',
            '!m/d/Y',
            '!d/m/Y',
            '!Y-m-d',
            '!d-m-Y'
        ];

        foreach ($formats as $format) {
            $dt = \DateTime::createFromFormat($format, $date);

            if ($dt !== false) {
                $checkFormat = ltrim($format, '!');

                if ($dt->format($checkFormat) === $date) {
                    return $dt->format('Y-m-d 00:00:00');
                }
            }
        }

        log_message('error', 'FORMAT TANGGAL BAHV TIDAK COCOK: [' . $date . ']');

        return null;
    }

    private function cleanTxt($value){
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        if ($value === '' || strtolower($value) === 'none' || $value === '-') {
            return null;
        }

        return trim($value, '"');
    }

    private function cleanNumber($value){
        $value = $this->cleanTxt($value);

        if ($value === null || !is_numeric($value)) {
            return null;
        }

        return $value;
    }

}