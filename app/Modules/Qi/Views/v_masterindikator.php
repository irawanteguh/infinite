<div id="master-indikator-page" class="d-flex flex-column gap-6">
    <header class="app-geometric-banner app-module-banner rounded-3 p-6 p-md-8 d-flex flex-column">
        <div class="app-banner-heading">
            <h2 class="text-white fw-bolder fs-2 mb-2">Master Indikator Mutu</h2>
            <p class="text-white mb-0">Kelola definisi, numerator, denominator, dimensi mutu, dan status indikator organisasi.</p>
        </div>
    </header>
    <div id="master-indikator-feedback" class="alert alert-danger mb-0" role="alert" hidden></div>
    <section class="card app-table-card" aria-labelledby="master-indikator-title">
        <div class="card-header app-table-heading">
            <div>
                <h2 id="master-indikator-title" class="app-table-title">Daftar indikator</h2>
                <p class="app-table-description">Cari indikator berdasarkan kode, nama, jenis, atau status.</p>
            </div>
            <div class="app-table-search">
                <i class="bi bi-search" aria-hidden="true"></i>
                <label for="searchtable" class="visually-hidden">Cari indikator</label>
                <input type="search" class="form-control form-control-solid" placeholder="Cari indikator…" id="searchtable">
            </div>
        </div>
        <div class="card-body">
            <table class="table align-middle table-row-dashed gy-2 fs-8" id="datamasterindikator_table" data-datatable="manual">
                <thead>
                    <tr>
                        <th scope="col" class="text-end">#</th>
                        <th scope="col">Kode</th>
                        <th scope="col">Indikator</th>
                        <th scope="col">Numerator / Denominator</th>
                        <th scope="col">Satuan</th>
                        <th scope="col">Status</th>
                        <th scope="col">Diperbarui oleh</th>
                        <th scope="col" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700" id="resultdatamasterindikator"></tbody>
            </table>
        </div>
    </section>
</div>
