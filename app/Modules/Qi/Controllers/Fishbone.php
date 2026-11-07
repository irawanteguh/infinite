<?php

namespace Modules\Qi\Controllers;

use App\Controllers\BaseController;
use Modules\Qi\Models\FishboneModel;

class Fishbone extends BaseController
{
    protected $md;
    private $categories = ['Man (SDM)', 'Method (Metode)', 'Machine (Mesin/Peralatan)', 'Material (Bahan)', 'Measurement (Pengukuran)', 'Environment (Lingkungan)'];
    private $priorities = ['urgent', 'medium', 'low'];

    public function __construct()
    {
        $this->md = new FishboneModel();
    }

    public function index()
    {
        $groupid = session()->get('groupid');
        $orgid = session()->get('orgid');
        $referenceId = trim((string) request()->getGet('indikator_ref_id'));
        $linkedIndicator = $referenceId !== '' ? $this->md->indicatorReference($referenceId, $groupid, $orgid) : null;
        $data = [
            'departments' => $this->md->departments($groupid, $orgid),
            'periods' => $this->md->periods($groupid, $orgid),
            'categories' => $this->categories,
            'linkedIndicator' => $linkedIndicator,
        ];
        return view('template/dashboard-light-aside', [
            'contents' => view('Modules\\Qi\\Views\\v_fishbone', $data),
            'fishboneDepartments' => $data['departments'],
            'fishbonePeriods' => $data['periods'],
            'fishboneCategories' => $data['categories'],
            'linkedIndicator' => $linkedIndicator,
        ]);
    }

    public function diagram()
    {
        $groupid = session()->get('groupid');
        $orgid = session()->get('orgid');
        $fishboneId = trim((string) request()->getGet('fishbone_id'));
        $analysis = $fishboneId !== '' ? $this->md->analysis($fishboneId, $groupid, $orgid) : null;
        if (!$analysis) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'departments' => $this->md->departments($groupid, $orgid),
            'periods' => $this->md->periods($groupid, $orgid),
            'categories' => $this->categories,
            'linkedIndicator' => null,
            'selectedAnalysis' => $analysis,
        ];
        return view('template/dashboard-light-aside', [
            'contents' => view('Modules\\Qi\\Views\\v_fishbonediagram', $data),
            'fishboneDepartments' => $data['departments'],
            'fishbonePeriods' => $data['periods'],
            'fishboneCategories' => $data['categories'],
            'linkedIndicator' => null,
        ]);
    }

    public function fishbonedata()
    {
        $groupid = session()->get('groupid');
        $orgid = session()->get('orgid');
        $id = trim((string) request()->getPost('fishbone_id'));
        $analysis = $this->md->analysis($id, $groupid, $orgid);
        if (!$analysis) {
            return $this->jsonError('Analisis Fishbone tidak ditemukan.');
        }
        return response()->setJSON(['responseCode' => '00', 'responseHead' => 'success', 'responseResult' => $analysis, 'responseCauses' => $this->md->causes($id)]);
    }

    public function analysislist()
    {
        $referenceId = trim((string) request()->getPost('indikator_ref_id'));
        if ($referenceId !== '' && !$this->md->indicatorReference($referenceId, session()->get('groupid'), session()->get('orgid'))) {
            return $this->jsonError('Indikator tidak ditemukan.');
        }
        return response()->setJSON([
            'responseCode' => '00', 'responseHead' => 'success',
            'responseResult' => $this->md->analyses(session()->get('groupid'), session()->get('orgid'), $referenceId),
        ]);
    }

    public function saveanalysis()
    {
        $id = trim((string) request()->getPost('fishbone_id'));
        $problem = trim((string) request()->getPost('problem'));
        $department = trim((string) request()->getPost('department_id'));
        $period = trim((string) request()->getPost('periode_id'));
        $referenceId = trim((string) request()->getPost('indikator_ref_id'));
        if ($problem === '') {
            return $this->jsonError('Masalah wajib diisi.');
        }
        $indicator = null;
        if ($referenceId !== '') {
            $indicator = $this->md->indicatorReference($referenceId, session()->get('groupid'), session()->get('orgid'));
            if (!$indicator) {
                return $this->jsonError('Indikator tidak tersedia.');
            }
            $department = $indicator->department_id;
        }
        if ($department === '' || !$this->md->departmentExists($department, session()->get('groupid'), session()->get('orgid'))
            || ($referenceId !== '' && $period === '')
            || ($period !== '' && !$this->md->periodExists($period, session()->get('groupid'), session()->get('orgid')))) {
            return $this->jsonError('Departemen atau periode tidak tersedia.');
        }
        if ($id !== '' && !$this->md->analysis($id, session()->get('groupid'), session()->get('orgid'))) {
            return $this->jsonError('Analisis Fishbone tidak ditemukan.');
        }
        if ($id !== '') {
            $existing = $this->md->analysis($id, session()->get('groupid'), session()->get('orgid'));
            if ((string) ($existing->indikator_ref_id ?? '') !== $referenceId) {
                return $this->jsonError('Relasi indikator analisis tidak dapat diubah.');
            }
        }
        $data = [
            'problem' => $problem, 'department_id' => $department, 'periode_id' => $period !== '' ? $period : null,
            'indikator_ref_id' => $referenceId !== '' ? $referenceId : null,
            'group_id' => session()->get('groupid'), 'org_id' => session()->get('orgid'),
            'last_update_by' => session()->get('userid'), 'last_update_date' => date('Y-m-d H:i:s'),
        ];
        if ($id === '') {
            $id = function_exists('generateuuid') ? generateuuid() : bin2hex(random_bytes(16));
            $data['fishbone_id'] = $id;
            $data['active'] = '1';
            $data['created_by'] = session()->get('userid');
            $data['created_date'] = date('Y-m-d H:i:s');
        }
        if (!$this->md->saveAnalysis(trim((string) request()->getPost('fishbone_id')), $data, session()->get('groupid'), session()->get('orgid'))) {
            return $this->jsonError('Analisis Fishbone gagal disimpan.');
        }
        return response()->setJSON(['responseCode' => '00', 'responseHead' => 'success', 'responseDesc' => 'Analisis Fishbone berhasil disimpan.', 'responseResult' => $id, 'responseAnalyses' => $this->md->analyses(session()->get('groupid'), session()->get('orgid'), $referenceId)]);
    }

    public function savecause()
    {
        $fishboneId = trim((string) request()->getPost('fishbone_id'));
        $causeId = trim((string) request()->getPost('cause_id'));
        $category = trim((string) request()->getPost('category'));
        $cause = trim((string) request()->getPost('cause'));
        $priority = trim((string) request()->getPost('priority'));
        if (!$this->md->analysis($fishboneId, session()->get('groupid'), session()->get('orgid'))) {
            return $this->jsonError('Analisis Fishbone tidak ditemukan.');
        }
        if (!in_array($category, $this->categories, true) || $cause === '' || !in_array($priority, $this->priorities, true)) {
            return $this->jsonError('Kategori, penyebab, dan prioritas wajib diisi dengan benar.');
        }
        $data = ['cause' => $cause, 'category' => $category, 'priority' => $priority, 'last_update_by' => session()->get('userid'), 'last_update_date' => date('Y-m-d H:i:s')];
        if ($causeId === '') {
            $causeId = function_exists('generateuuid') ? generateuuid() : bin2hex(random_bytes(16));
            $data += ['cause_id' => $causeId, 'fishbone_id' => $fishboneId, 'active' => '1', 'created_by' => session()->get('userid'), 'created_date' => date('Y-m-d H:i:s')];
            $saved = $this->md->saveCause('', $data, $fishboneId);
        } else {
            $saved = $this->md->saveCause($causeId, $data, $fishboneId);
        }
        if (!$saved) {
            return $this->jsonError('Penyebab gagal disimpan.');
        }
        return response()->setJSON(['responseCode' => '00', 'responseHead' => 'success', 'responseDesc' => 'Penyebab berhasil disimpan.', 'responseCauses' => $this->md->causes($fishboneId)]);
    }

    public function deletecause()
    {
        $fishboneId = trim((string) request()->getPost('fishbone_id'));
        $causeId = trim((string) request()->getPost('cause_id'));
        if (!$this->md->analysis($fishboneId, session()->get('groupid'), session()->get('orgid'))) {
            return $this->jsonError('Analisis Fishbone tidak ditemukan.');
        }
        $ok = $this->md->deleteCause($causeId, $fishboneId, ['active' => '0', 'last_update_by' => session()->get('userid'), 'last_update_date' => date('Y-m-d H:i:s')]);
        return response()->setJSON(['responseCode' => $ok ? '00' : '01', 'responseHead' => $ok ? 'success' : 'error', 'responseDesc' => $ok ? 'Penyebab dihapus.' : 'Penyebab gagal dihapus.', 'responseCauses' => $this->md->causes($fishboneId)]);
    }

    public function deleteanalysis()
    {
        $id = trim((string) request()->getPost('fishbone_id'));
        if (!$this->md->analysis($id, session()->get('groupid'), session()->get('orgid'))) {
            return $this->jsonError('Analisis Fishbone tidak ditemukan.');
        }
        db_connect()->transStart();
        db_connect()->table('dt01_qi_fishbone_dt')->where('fishbone_id', $id)->update(['active' => '0']);
        db_connect()->table('dt01_qi_fishbone_hd')->where('fishbone_id', $id)->where('group_id', session()->get('groupid'))->where('org_id', session()->get('orgid'))->update(['active' => '0', 'last_update_by' => session()->get('userid'), 'last_update_date' => date('Y-m-d H:i:s')]);
        db_connect()->transComplete();
        return response()->setJSON(['responseCode' => '00', 'responseHead' => 'success', 'responseDesc' => 'Analisis Fishbone dihapus.', 'responseAnalyses' => $this->md->analyses(session()->get('groupid'), session()->get('orgid'))]);
    }

    private function jsonError($message)
    {
        return response()->setJSON(['responseCode' => '01', 'responseHead' => 'error', 'responseDesc' => $message]);
    }
}
