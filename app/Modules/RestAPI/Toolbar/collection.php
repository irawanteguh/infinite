<div id="collection-environment-toolbar" class="d-flex flex-wrap align-items-center gap-3">
    <label for="request-environment" class="form-label mb-0">Environment</label>
    <select id="request-environment" class="form-select form-select-sm w-200px" aria-label="Environment"></select>
    <button id="view-request-environment" type="button" class="btn btn-sm btn-icon btn-light-primary" aria-label="Lihat environment" title="Lihat environment" data-bs-toggle="modal" data-bs-target="#collection-environment-modal">
        <i class="bi bi-eye fs-4" aria-hidden="true"></i>
    </button>
    <div class="btn-group ms-auto">
        <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">Signature</button>
        <div class="dropdown-menu dropdown-menu-end">
            <button id="request-signature-bpjs" type="button" class="dropdown-item">BPJS</button>
        </div>
    </div>
</div>
