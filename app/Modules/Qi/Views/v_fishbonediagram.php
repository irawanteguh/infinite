<div id="fishboneRoot" data-mode="diagram" data-selected-id="<?= esc($selectedAnalysis->fishbone_id) ?>" data-endpoint="<?= esc(site_url('qi/fishbone')) ?>"></div>

<div class="card">
    <div class="card-header border-0 pt-5">
        <div><h3 class="card-title fw-bolder">Diagram sebab-akibat</h3><div class="text-muted fs-7" id="fishboneDepartmentLabel">Memuat analisis…</div></div>
        <div class="card-toolbar"><button class="btn btn-sm btn-light-primary" id="fishboneAddCauseButton" disabled><i class="bi bi-plus-lg me-1"></i>Tambah penyebab</button></div>
    </div>
    <div class="card-body pt-0">
        <div class="table-responsive"><div id="fishboneDiagram" class="min-w-700px"></div></div>
        <div id="fishboneCauseList" class="row g-4 mt-2"></div>
    </div>
</div>
