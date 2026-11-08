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
        $periodeId = trim((string) request()->getGet('periode_id'));
        $groupId = session()->get('groupid');
        $orgId = session()->get('orgid');
        if ($periodeId === '' || !$this->md->periodeExists($periodeId, $groupId, $orgId)) {
            return response()->setStatusCode(404)->setBody('Periode pelaporan tidak ditemukan.');
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
        foreach ($fishbones as $fishbone) {
            $fishbone->causes = $this->md->fishboneCausesForAnalysis($fishbone->fishbone_id);
        }
        $cycles = $this->md->pdsaCyclesForPeriode($groupId, $orgId, $periodeId);
        foreach ($cycles as $cycle) {
            $cycle->items = $this->md->pdsaItemsForCycle($cycle->pdsa_id);
        }

        $measurements = $this->md->measurementDetails($groupId, $orgId, $periodeId);

        $html = view('Modules\\Qi\\Views\\v_summaryreport_pdf', [
            'periode' => $periode,
            'indicators' => $indicators,
            'trend' => $trend,
            'indicatorTrend' => $indicatorTrend,
            'measurements' => $measurements,
            'fishbones' => $fishbones,
            'cycles' => $cycles,
            'groupName' => (string) (session()->get('orgname') ?: 'Rumah Sakit'),
            'generatedAt' => date('d-m-Y H:i'),
        ]);

        try {
            $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'orientation' => 'P',
                'margin_left' => 14,
                'margin_right' => 14,
                'margin_top' => 16,
                'margin_bottom' => 16,
                'tempDir' => WRITEPATH . 'cache',
            ]);
            $year = $periode->tahun ?? 'Tahunan';
            $mpdf->SetTitle('Laporan Mutu - ' . $year);
            $mpdf->SetAuthor('INFINITE');
            $mpdf->WriteHTML($html);
            $filename = 'Laporan_Mutu_' . preg_replace('/[^A-Za-z0-9_-]/', '_', (string) $year) . '.pdf';
            return response()
                ->setHeader('Content-Type', 'application/pdf')
                ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
                ->setBody($mpdf->Output($filename, 'S'));
        } catch (\Throwable $e) {
            log_message('error', 'Gagal membuat PDF summary report: ' . $e->getMessage());
            return response()->setStatusCode(500)->setBody('PDF gagal dibuat. Periksa konfigurasi PDF server.');
        }
    }
}
