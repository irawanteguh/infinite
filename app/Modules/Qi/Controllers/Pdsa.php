<?php

namespace Modules\Qi\Controllers;

use App\Controllers\BaseController;
use Modules\Qi\Models\PdsaModel;

class Pdsa extends BaseController
{
    protected $md;

    public function __construct()
    {
        $this->md = new PdsaModel();
    }

    public function index()
    {
        $groupId = session()->get('groupid');
        $orgId = session()->get('orgid');
        $userId = session()->get('userid');
        $indicatorRefId = trim((string) request()->getGet('indikator_ref_id'));
        $linkedIndicator = $indicatorRefId !== '' ? $this->md->indicatorReference($indicatorRefId, $groupId, $orgId, $userId) : null;
        $departments = $this->md->departments($groupId, $orgId, $userId);
        $periods = $this->md->periods($groupId, $orgId);
        $indicators = $this->md->indicators($groupId, $orgId, $userId);

        return view('template/dashboard-light-aside', [
            'contents' => view('Modules\\Qi\\Views\\v_pdsa', [
                'endpoint' => site_url('qi/pdsa'),
                'departments' => $departments,
                'periods' => $periods,
                'indicators' => $indicators,
                'linkedIndicator' => $linkedIndicator,
            ]),
            'pdsaDepartments' => $departments,
            'pdsaPeriods' => $periods,
            'pdsaIndicators' => $indicators,
        ]);
    }

    public function diagram()
    {
        $groupId = session()->get('groupid');
        $orgId = session()->get('orgid');
        $pdsaId = trim((string) request()->getGet('pdsa_id'));
        $cycle = $pdsaId !== '' ? $this->md->getCycleForOrganization($pdsaId, $groupId, $orgId) : null;
        if (!$cycle) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        $canManage = $this->md->getCycle($pdsaId, $groupId, $orgId, session()->get('userid')) !== null;

        return view('template/dashboard-light-aside', [
            'contents' => view('Modules\\Qi\\Views\\v_pdsadiagram', [
                'cycle' => $cycle,
                'items' => $this->md->items($pdsaId),
                'canManage' => $canManage,
                'endpoint' => site_url('qi/pdsa'),
            ]),
        ]);
    }

    public function data()
    {
        return response()->setJSON([
            'responseCode' => '00', 'responseHead' => 'success',
            'responseResult' => $this->md->listCycles(session()->get('groupid'), session()->get('orgid'), session()->get('userid'), trim((string) request()->getGet('indikator_ref_id'))),
        ]);
    }

    public function save()
    {
        $id = trim((string) request()->getPost('pdsa_id'));
        $groupId = session()->get('groupid');
        $orgId = session()->get('orgid');
        $userId = session()->get('userid');
        $departmentId = trim((string) request()->getPost('department_id'));
        $title = trim((string) request()->getPost('title'));
        $problem = trim((string) request()->getPost('problem'));
        $departmentIds = array_map(static function ($row) { return (string) $row->department_id; }, $this->md->departments($groupId, $orgId, $userId));

        if ($title === '' || $problem === '' || !in_array($departmentId, $departmentIds, true)) {
            return $this->jsonError('Judul, masalah, dan departemen yang menjadi tanggung jawab Anda wajib diisi.');
        }
        $indicatorRefId = trim((string) request()->getPost('indikator_ref_id'));
        $existing = $id !== '' ? $this->md->getCycle($id, $groupId, $orgId, $userId) : null;
        if ($id !== '' && !$existing) {
            return $this->jsonError('Header PDSA tidak ditemukan atau di luar departemen Anda.');
        }
        if ($existing && (string) ($existing->indikator_ref_id ?? '') !== $indicatorRefId) {
            return $this->jsonError('Relasi indikator pada header PDSA tidak dapat diubah.');
        }
        $periodId = trim((string) request()->getPost('periode_id'));
        if ($indicatorRefId !== '') {
            $indicator = $this->md->indicatorReference($indicatorRefId, $groupId, $orgId, $userId);
            if (!$indicator) return $this->jsonError('Indikator tidak tersedia untuk departemen Anda.');
            $departmentId = (string) $indicator->department_id;
            $periodId = (string) ($indicator->periode_id ?? '');
        }
        if ($periodId !== '' && !$this->md->periodExists($periodId, $groupId, $orgId)) {
            return $this->jsonError('Periode tidak tersedia di organisasi Anda.');
        }

        $now = date('Y-m-d H:i:s');
        $data = [
            'department_id' => $departmentId,
            'periode_id' => $periodId ?: null,
            'indikator_ref_id' => $indicatorRefId ?: null,
            'title' => $title, 'problem' => $problem,
            'aim' => trim((string) request()->getPost('aim')),
            'last_update_by' => $userId, 'last_update_date' => $now,
        ];
        if ($id === '') {
            $id = generateuuid();
            $data += [
                'pdsa_id' => $id, 'group_id' => $groupId, 'org_id' => $orgId,
                'active' => '1', 'created_by' => $userId, 'created_date' => $now,
            ];
        }

        if (!$this->md->saveCycle($id === (string) request()->getPost('pdsa_id') ? $id : '', $data, $groupId, $orgId)) {
            return $this->jsonError('Header PDSA gagal disimpan.');
        }
        return response()->setJSON(['responseCode' => '00', 'responseHead' => 'success', 'responseDesc' => 'Header PDSA berhasil disimpan.', 'responseResult' => $id]);
    }

    public function itemsdata()
    {
        $id = trim((string) request()->getGet('pdsa_id'));
        $groupId = session()->get('groupid');
        $orgId = session()->get('orgid');
        $cycle = $this->md->getCycleForOrganization($id, $groupId, $orgId);
        if (!$cycle) return $this->jsonError('Header PDSA tidak ditemukan.');

        return response()->setJSON([
            'responseCode' => '00', 'responseHead' => 'success', 'responseCycle' => $cycle,
            'responseItems' => $this->md->items($id),
        ]);
    }

    public function saveitems()
    {
        $id = trim((string) request()->getPost('pdsa_id'));
        $groupId = session()->get('groupid');
        $orgId = session()->get('orgid');
        $userId = session()->get('userid');
        $cycle = $this->md->getCycle($id, $groupId, $orgId, $userId);
        if (!$cycle) return $this->jsonError('Header PDSA tidak ditemukan atau di luar departemen Anda.');

        $items = json_decode((string) request()->getPost('items'), true);
        if (!is_array($items)) return $this->jsonError('Daftar item PDSA tidak valid.');
        $validPhases = ['Plan', 'Do', 'Study', 'Act'];
        $cleanItems = [];
        foreach ($items as $item) {
            if (!is_array($item) || !in_array($item['phase'] ?? '', $validPhases, true)) {
                return $this->jsonError('Tahap item PDSA tidak valid.');
            }
            $text = trim((string) ($item['item_text'] ?? ''));
            if ($text === '') continue;
            if (mb_strlen($text) > 5000) return $this->jsonError('Isi setiap item maksimal 5.000 karakter.');
            $cleanItems[] = [
                'phase' => $item['phase'],
                'item_text' => $text,
                'pdsa_item_id' => trim((string) ($item['pdsa_item_id'] ?? '')),
            ];
        }

        $now = date('Y-m-d H:i:s');
        $this->md->saveCycle($id, ['last_update_by' => $userId, 'last_update_date' => $now], $groupId, $orgId);
        $saved = $this->md->replaceItems($id, $cleanItems, $userId);
        if (!$saved) return $this->jsonError('Item PDSA gagal disimpan.');
        return response()->setJSON(['responseCode' => '00', 'responseHead' => 'success', 'responseDesc' => 'Item PDSA berhasil disimpan.', 'responseResult' => $id]);
    }

    public function toggleitem()
    {
        $groupId = session()->get('groupid');
        $orgId = session()->get('orgid');
        $userId = session()->get('userid');
        $itemId = trim((string) request()->getPost('pdsa_item_id'));
        $completedValue = (string) request()->getPost('completed');
        if (!in_array($completedValue, ['0', '1'], true)) {
            return $this->jsonError('Status item tidak valid.');
        }
        $item = $this->md->itemCycle($itemId, $groupId, $orgId);
        if (!$item || !$this->md->getCycle($item->pdsa_id, $groupId, $orgId, $userId)) {
            return $this->jsonError('Item tidak ditemukan atau di luar departemen Anda.');
        }
        $saved = $this->md->setItemCompleted($itemId, $item->pdsa_id, $completedValue === '1', [
            'last_update_by' => $userId,
            'last_update_date' => date('Y-m-d H:i:s'),
        ]);
        if (!$saved) return $this->jsonError('Status selesai item gagal disimpan.');
        return response()->setJSON(['responseCode' => '00', 'responseHead' => 'success', 'responseDesc' => 'Status item berhasil diperbarui.']);
    }

    public function archive()
    {
        $groupId = session()->get('groupid');
        $orgId = session()->get('orgid');
        $userId = session()->get('userid');
        $departmentIds = array_map(static function ($row) { return (string) $row->department_id; }, $this->md->departments($groupId, $orgId, $userId));
        $ok = $this->md->archiveCycle(trim((string) request()->getPost('pdsa_id')), $groupId, $orgId, $departmentIds, [
            'active' => '0', 'last_update_by' => $userId, 'last_update_date' => date('Y-m-d H:i:s'),
        ]);
        return response()->setJSON(['responseCode' => $ok ? '00' : '01', 'responseHead' => $ok ? 'success' : 'error', 'responseDesc' => $ok ? 'Header PDSA diarsipkan.' : 'Header PDSA tidak ditemukan.']);
    }

    private function jsonError($message)
    {
        return response()->setJSON(['responseCode' => '01', 'responseHead' => 'error', 'responseDesc' => $message]);
    }
}
