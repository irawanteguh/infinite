<?php

namespace Modules\Pe\Controllers;

// Preserve existing public links while generated links use /pe/skm.
class Premgeneral extends Skm
{
    public function index()
    {
        $query = request()->getGet();
        return redirect()->to(site_url('pe/skm') . ($query ? '?' . http_build_query($query) : ''));
    }
}