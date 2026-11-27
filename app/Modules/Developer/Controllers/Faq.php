<?php

namespace Modules\Developer\Controllers;

use App\Controllers\BaseController;

class Faq extends BaseController
{
    public function index(): string
    {
        $guides = \Modules\Developer\Config\HelpGuides::modules();
        $catalog = (new \App\Models\Modelrouting())->allMenus();
        $state = (new \Modules\Hr\Models\AccessModel())->state(
            (string) $this->session->get('groupid'),
            (string) $this->session->get('orgid'),
            (string) $this->session->get('userid')
        );
        foreach ($guides as &$guide) {
            [$package, $controller] = explode('/', $guide['route']);
            $guide['canOpen'] = $guide['id'] !== 'auth'
                && \App\Libraries\ModuleAccess::routeAllowed($package, $controller, $catalog, $state);
        }
        unset($guide);

        return view('template/dashboard-light-aside', [
            'pageTitle' => 'FAQ & Documentation',
            'documentTitle' => 'FAQ & Documentation | Infinite',
            'contents' => view('Modules\Developer\Views\v_faq', ['guides' => $guides]),
        ]);
    }
}
