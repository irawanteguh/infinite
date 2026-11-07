<?php

namespace Modules\Qi\Controllers;

use App\Controllers\BaseController;
use Modules\Qi\Models\SummaryModel;

class Summary extends BaseController
{
    protected $md;

    public function __construct()
    {
        $this->md = new SummaryModel();
    }

    public function index()
    {
        $groupid = session()->get('groupid');
        $orgid = session()->get('orgid');
        $periods = $this->md->periods($groupid, $orgid);
        $periodValues = array_map(static function ($period) {
            return (string) $period->tahun;
        }, $periods);

        $selectedPeriod = (string) request()->getGet('tahun');
        if (!in_array($selectedPeriod, $periodValues, true)) {
            $selectedPeriod = $periodValues[0] ?? '';
        }

        return view('template/dashboard-light-aside', [
            'contents' => view('Modules\Qi\Views\v_summary', ['selectedPeriod' => $selectedPeriod]),
            'summaryPeriods' => $periods,
            'selectedSummaryPeriod' => $selectedPeriod
        ]);
    }

    public function summarydata()
    {
        $groupid = session()->get('groupid');
        $orgid = session()->get('orgid');
        $year = (string) request()->getPost('tahun');
        $periods = $this->md->periods($groupid, $orgid);
        $periodValues = array_map(static function ($period) {
            return (string) $period->tahun;
        }, $periods);

        if (!in_array($year, $periodValues, true)) {
            return response()->setJSON([
                'responseCode' => '01',
                'responseHead' => 'info',
                'responseDesc' => 'Periode tidak tersedia.'
            ]);
        }

        return response()->setJSON([
            'responseCode' => '00',
            'responseHead' => 'success',
            'responseDesc' => 'Data ringkasan ditemukan.',
            'responseResult' => $this->md->summaryRows($groupid, $orgid, $year),
            'responseTrend' => $this->md->monthlyTrend($groupid, $orgid, $year)
        ]);
    }
}
