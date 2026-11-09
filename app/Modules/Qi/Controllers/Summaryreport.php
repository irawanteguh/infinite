<?php

namespace Modules\Qi\Controllers;

use App\Controllers\BaseController;
use Mpdf\Mpdf;
use Modules\Qi\Models\SummaryreportModel;

class Summaryreport extends BaseController
{
    protected $md;

    public function __construct()
    {
        $this->md = new SummaryreportModel();
    }

    public function index()
    {
        return view('template/dashboard-light-aside', [
            'contents' => view('Modules\\Qi\\Views\\v_summaryreport'),
        ]);
    }

    public function periods()
    {
        $rows = $this->md->listPeriodePelaporan(session()->get('groupid'), session()->get('orgid'));
        return response()->setJSON([
            'responseCode' => '00',
            'responseResult' => $rows,
        ]);
    }

    public function generate()
    {
        $startedAt = microtime(true);
        $periodeId = trim((string) request()->getGet('periode_id'));
        $groupId = session()->get('groupid');
        $orgId = session()->get('orgid');
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $periodeId)) {
            return response()->setStatusCode(404)->setBody('Periode pelaporan tidak ditemukan.');
        }

        if (!class_exists(Mpdf::class)) {
            log_message('error', 'Gagal membuat PDF summary report: dependensi mpdf/mpdf belum terpasang.');
            return response()->setStatusCode(500)->setBody('Dependensi PDF belum terpasang. Jalankan composer install pada folder aplikasi.');
        }

        $organization = $this->md->periodeHeader($groupId, $orgId, $periodeId);
        if (!$organization) {
            return response()->setStatusCode(404)->setBody('Periode pelaporan tidak ditemukan.');
        }
        $periode = $organization;
        $indicators = $this->md->summaryRowsByPeriode($groupId, $orgId, $periodeId);
        $trend = $this->md->monthlyTrendByPeriode($groupId, $orgId, $periodeId);
        $indicatorTrend = [];
        foreach ($this->md->monthlyTrendByIndicator($groupId, $orgId, $periodeId) as $trendRow) {
            $indicatorTrend[$trendRow->transaksi_id][(int) $trendRow->bulan] = (float) $trendRow->rata_pencapaian;
        }
        $fishbones = $this->md->fishboneAnalysesForPeriode($groupId, $orgId, $periodeId);
        $causesByAnalysis = $this->md->fishboneCausesForAnalyses(array_column($fishbones, 'fishbone_id'));
        foreach ($fishbones as $fishbone) {
            $fishbone->causes = $causesByAnalysis[$fishbone->fishbone_id] ?? [];
        }
        $cycles = $this->md->pdsaCyclesForPeriode($groupId, $orgId, $periodeId);
        $itemsByCycle = $this->md->pdsaItemsForCycles(array_column($cycles, 'pdsa_id'));
        foreach ($cycles as $cycle) {
            $cycle->items = $itemsByCycle[$cycle->pdsa_id] ?? [];
        }

        $measurements = $this->md->measurementDetails($groupId, $orgId, $periodeId);
        $dataLoadedAt = microtime(true);

        $viewData = [
            'periode' => $periode,
            'indicators' => $indicators,
            'trend' => $trend,
            'indicatorTrend' => $indicatorTrend,
            'measurements' => $measurements,
            'fishbones' => $fishbones,
            'cycles' => $cycles,
            'groupName' => (string) (session()->get('orgname') ?: 'Rumah Sakit'),
        ];
        $year = $periode->tahun ?? 'Tahunan';
        $filename = 'Laporan_Mutu_' . preg_replace('/[^A-Za-z0-9_-]/', '_', (string) $year) . '.pdf';
        // Periksa data terbaru sebelum memakai PDF tersimpan agar perubahan langsung terlihat.
        $cacheKey = 'qi_summary_pdf_' . hash('sha256', serialize([$groupId, $orgId, $periodeId]));
        $fingerprint = hash('sha256', serialize($viewData)
            . hash_file('sha256', APPPATH . 'Modules/Qi/Views/v_summaryreport_pdf.php')
            . hash_file('sha256', __FILE__));
        $pdfCache = null;
        try {
            $pdfCache = \Config\Services::cache();
            $cached = $pdfCache->get($cacheKey);
            if (is_array($cached) && ($cached['fingerprint'] ?? '') === $fingerprint
                && is_string($cached['pdf'] ?? null)) {
                log_message('info', sprintf('Summary report PDF: cache hit, total=%.3fs', microtime(true) - $startedAt));
                return response()->setHeader('Content-Type', 'application/pdf')
                    ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
                    ->setHeader('Content-Length', (string) strlen($cached['pdf']))
                    ->setBody($cached['pdf']);
            }
        } catch (\Throwable $e) {
            log_message('warning', 'Cache PDF summary report tidak tersedia: ' . $e->getMessage());
        }
        $viewData['generatedAt'] = date('d-m-Y H:i');
        $html = view('Modules\\Qi\\Views\\v_summaryreport_pdf', $viewData);

        // try {
        //     $mpdf = new Mpdf([
        //         'mode' => 'utf-8',
        //         'format' => 'A4',
        //         'orientation' => 'P',
        //         'margin_left' => 14,
        //         'margin_right' => 14,
        //         'margin_top' => 16,
        //         'margin_bottom' => 16,
        //         'tempDir' => WRITEPATH . 'cache',
        //     ]);
        //     $year = $periode->tahun ?? 'Tahunan';
        //     $mpdf->SetTitle('Laporan Mutu - ' . $year);
        //     $mpdf->SetAuthor('INFINITE');
        //     $mpdf->WriteHTML($html);
        //     $filename = 'Laporan_Mutu_' . preg_replace('/[^A-Za-z0-9_-]/', '_', (string) $year) . '.pdf';
        //     return response()
        //         ->setHeader('Content-Type', 'application/pdf')
        //         ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
        //         ->setBody($mpdf->Output($filename, 'S'));
        // } catch (\Throwable $e) {
        //     log_message('error', 'Gagal membuat PDF summary report: ' . $e->getMessage());
        //     return response()->setStatusCode(500)->setBody('PDF gagal dibuat. Periksa log aplikasi dan pastikan folder writable/cache dapat ditulis.');
        // }

        
        try {
            // Pastikan folder temporary mPDF tersedia
            $tempDir = WRITEPATH . 'cache';

            if (!is_dir($tempDir)) {
                if (!mkdir($tempDir, 0775, true) && !is_dir($tempDir)) {
                    throw new \RuntimeException('Gagal membuat folder: ' . $tempDir);
                }
            }

            if (!is_writable($tempDir)) {
                throw new \RuntimeException('Folder tidak memiliki izin tulis: ' . $tempDir);
            }

            // Laporan berbahasa Indonesia tidak memerlukan OpenType shaping.
            // Hindari data OTL per karakter pada tabel dan grafik yang panjang.
            $fontData = (new \Mpdf\Config\FontVariables())->getDefaults()['fontdata'];
            foreach (['dejavusans', 'dejavusanscondensed'] as $font) {
                $fontData[$font]['useOTL'] = 0;
            }

            $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'orientation' => 'P',
                'margin_left' => 14,
                'margin_right' => 14,
                'margin_top' => 16,
                'margin_bottom' => 16,
                'tempDir' => $tempDir,
                'default_font' => 'dejavusans',
                'fontdata' => $fontData,
                // Pemadatan tabel mPDF bermasalah pada sel colspan (fishbone).
                'packTableData' => false,
                // Tutup batas tabel saat isinya berlanjut ke halaman berikutnya.
                'splitTableBorderWidth' => 0.2,
            ]);

            $year = $periode->tahun ?? 'Tahunan';

            $mpdf->SetTitle('Laporan Mutu - ' . $year);
            $mpdf->SetAuthor('INFINITE');

            ini_set('pcre.backtrack_limit', '10000000');
            ini_set('pcre.recursion_limit', '1000000');

            $renderStartedAt = microtime(true);
            $mpdf->WriteHTML($html);

            $filename = 'Laporan_Mutu_' .
                preg_replace('/[^A-Za-z0-9_-]/', '_', (string) $year) .
                '.pdf';

            $pdfContent = $mpdf->Output('', 'S');
            if ($pdfCache !== null) {
                try {
                    $pdfCache->save($cacheKey, ['fingerprint' => $fingerprint, 'pdf' => $pdfContent], 300);
                } catch (\Throwable $e) {
                    log_message('warning', 'Cache PDF summary report gagal disimpan: ' . $e->getMessage());
                }
            }
            log_message('info', sprintf(
                'Summary report PDF: data=%.3fs, preparation=%.3fs, render=%.3fs, total=%.3fs, indicators=%d, measurements=%d, pages=%d',
                $dataLoadedAt - $startedAt,
                $renderStartedAt - $dataLoadedAt,
                microtime(true) - $renderStartedAt,
                microtime(true) - $startedAt,
                count($indicators), count($measurements), $mpdf->page
            ));

            return response()
                ->setHeader('Content-Type', 'application/pdf')
                ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
                ->setHeader('Content-Length', (string) strlen($pdfContent))
                ->setBody($pdfContent);

        } catch (\Throwable $e) {

            log_message('error', 'GAGAL GENERATE PDF SUMMARY REPORT');
            log_message('error', 'Error: ' . $e->getMessage());
            log_message('error', 'File: ' . $e->getFile());
            log_message('error', 'Line: ' . $e->getLine());
            log_message('error', 'Trace: ' . $e->getTraceAsString());

            return response()
                ->setStatusCode(500)
                ->setBody('PDF gagal dibuat. Periksa writable/logs untuk detail error.');
        }

    }
}
