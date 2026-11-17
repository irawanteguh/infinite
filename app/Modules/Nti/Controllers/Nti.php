<?php

namespace Modules\Nti\Controllers;

use App\Controllers\BaseController;
use Modules\Nti\Services\NtiClient;
use RuntimeException;

class Nti extends BaseController
{
    public function index()
    {
        helper('url');
        return view('template/dashboard-light-blank', ['contents' => view('Modules\Nti\Views\monitoring', [
            'endpoint' => site_url('nti/nti/snapshot'),
            'scriptUrl' => base_url('assets/js/nti/monitoring.js?v=' . filemtime(FCPATH . 'assets/js/nti/monitoring.js')),
            'homeUrl' => site_url('nti/nti'),
        ])]);
    }

    public function snapshot()
    {
        return $this->sensors(['internal', 'external', 'digital']);
    }

    public function sensorinternal() { return $this->sensors(['internal']); }
    public function sensorexternal() { return $this->sensors(['external']); }
    public function sensordigital() { return $this->sensors(['digital']); }

    private function sensors(array $groups)
    {
        try {
            $data = (new NtiClient())->read($groups);
            $ok = count(array_filter($data, static function ($item) { return $item['ok']; }));
            return service('response')->setHeader('Cache-Control', 'no-store')->setJSON([
                'ok' => $ok === count($groups), 'groups' => $data, 'checkedAt' => date(DATE_ATOM),
            ]);
        } catch (RuntimeException $e) {
            return service('response')->setHeader('Cache-Control', 'no-store')->setStatusCode(502)->setJSON(['ok' => false, 'error' => $e->getMessage()]);
        }
    }
}
