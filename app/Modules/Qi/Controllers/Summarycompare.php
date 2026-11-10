<?php

namespace Modules\Qi\Controllers;

use App\Controllers\BaseController;
use Modules\Qi\Models\SummaryModel;

class Summarycompare extends BaseController
{
    public function index()
    {
        $indicatorId = trim((string) request()->getGet('indikator_id'));
        $departmentId = trim((string) request()->getGet('department_id'));

        $periodRows = $indicatorId !== ''
            ? (new SummaryModel())->compareIndicatorPeriods(
                session()->get('groupid'),
                session()->get('orgid'),
                $indicatorId,
                $departmentId
            )
            : [];

        return view('template/dashboard-light-aside', [
            'contents' => view('Modules\\Qi\\Views\\v_summarycompre', [
                'periodRows' => $periodRows,
            ]),
        ]);
    }
}
