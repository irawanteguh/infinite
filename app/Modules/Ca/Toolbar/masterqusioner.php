<div class="module-toolbar-actions">
    <a href="<?= esc(site_url('ca/caskrining') . '?' . http_build_query(['welcome' => '1', 'group_id' => session()->get('groupid'), 'org_id' => session()->get('orgid')]), 'attr') ?>" class="btn btn-light-primary" target="_blank" rel="noopener"><i class="bi bi-clipboard-heart" aria-hidden="true"></i> Pengisian kuesioner</a>
</div>
