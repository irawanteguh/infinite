<?php

namespace Modules\Ur\Controllers;

use App\Controllers\BaseController;
use Modules\Ur\Models\TarifpositifModel;

class Tarifpositif extends BaseController
{
    protected $md;

    public function __construct()
    {
        parent::__construct();
        $this->md = new TarifpositifModel();
    }

    public function index()
    {
        $periode = '';
        foreach ($this->md->periode() as $row) {
            $year = (int) $row->periode;
            $periode .= '<option value="' . $year . '">' . $year . '</option>';
        }
        return view('template/dashboard-light-aside', [
            'contents' => view('Modules\Ur\Views\v_tarifpositif', ['periode' => $periode])
        ]);
    }

    private function dataResponse($method)
    {
        $post = request()->getPost();
        $year = $post['selectperiode'] ?? '';
        if (!is_scalar($year) || !preg_match('/^\d{4}$/', (string) $year)
            || (int) $year < 2014 || (int) $year > (int) date('Y')) {
            return $this->errorResponse(422, 'Periode tahun tidak valid.');
        }
        $koders = $this->session->get('koders');
        if (!$koders) {
            return $this->errorResponse(403, 'Rumah sakit pada sesi belum dipilih.');
        }
        try {
            if ($method === 'cases') {
                $month = $post['month'] ?? '';
                $service = $post['service'] ?? '';
                $search = $post['search'] ?? '';
                $sort = $post['sort'] ?? 'gap';
                $offset = $post['offset'] ?? '0';
                $limit = $post['limit'] ?? '25';
                if (!is_scalar($month) || !preg_match('/^(?:|[1-9]|1[0-2])$/', (string) $month)
                    || !is_string($service) || !in_array($service, ['', '1', '2'], true)
                    || !is_string($search) || strlen($search) > 200
                    || !is_string($sort) || !in_array($sort, ['gap', 'date', 'tariff'], true)
                    || !is_scalar($offset) || !ctype_digit((string) $offset) || (float) $offset > 10000000
                    || !is_scalar($limit) || !in_array((string) $limit, ['25', '50', '100'], true)) {
                    return $this->errorResponse(422, 'Filter atau halaman tidak valid.');
                }
                $result = $this->md->cases($koders, (int) $year, (int) $month, $service, trim($search), (int) $offset, (int) $limit, $sort);
            } else {
                $result = $this->md->$method($koders, (int) $year);
            }
            return response()->setJSON(['responseCode' => '00', 'responseHead' => 'success', 'responseDesc' => 'Data berhasil dimuat.', 'responseResult' => $result]);
        } catch (\Throwable $e) {
            log_message('error', 'UR positive tariff query failed.');
            return $this->errorResponse(500, 'Data gagal dimuat. Silakan coba kembali.');
        }
    }

    private function errorResponse($status, $message)
    {
        return response()->setStatusCode($status)->setJSON(['responseCode' => '01', 'responseDesc' => $message, 'responseResult' => []]);
    }

    public function summary() { return $this->dataResponse('summary'); }
    public function cases() { return $this->dataResponse('cases'); }
    public function rawdata() { return $this->dataResponse('rawdata'); }
}
