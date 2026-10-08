<div class="modal fade" id="modal_add_master_mdc" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header pb-0">
                <h1 class="mb-3" id="title_modal_master_mdc">Add Master MDC</h1>
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <span class="svg-icon svg-icon-1">
                        <i class="bi bi-x-lg"></i>
                    </span>
                </div>
            </div>
            <form action="<?php echo base_url('Ics/mastermdc/addmastermdc'); ?>" method="post" id="formaddmastermdc" autocomplete="off">
                <?= csrf_field() ?>
                <div class="modal-body">                        
                    <div class="row g-4 mb-4">
                        <!-- Dokumen Sumber -->
                        <div class="col-md-8">
                            <label class="form-label fw-bold required">Dokumen Sumber</label>
                            <select class="form-select" id="document_id_mdc" name="DOCUMENT_ID">
                                <?php echo $masterdocument;?>
                            </select>
                            <div class="form-text">Contoh: Indonesian Coding Standard (ICS) iDRG - V1</div>
                        </div>
                        <!-- Kode MDC -->
                        <div class="col-md-4">
                            <label class="form-label fw-bold required">Kode MDC</label>
                            <input type="text" class="form-control" id="mdc_code" name="MDC_CODE" maxlength="2" placeholder="11">
                            <div class="form-text">2 digit (10 - 36, 90)</div>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <!-- Nama Indonesia -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold required">Nama (Indonesia)</label>
                            <input type="text" class="form-control" id="name_id_mdc" name="NAME_ID" maxlength="200" placeholder="Sistem Syaraf">
                        </div>
                        <!-- Nama Inggris -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nama (English)</label>
                            <input type="text" class="form-control" id="name_en_mdc" name="NAME_EN" maxlength="200" placeholder="Diseases and Disorders of the Nervous System">
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <!-- Bab -->
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Bab</label>
                            <input type="text" class="form-control" id="chapter_no_mdc" name="CHAPTER_NO" maxlength="10" placeholder="3.1">
                        </div>
                        <!-- Halaman Awal -->
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Halaman Awal</label>
                            <input type="number" class="form-control" id="page_start_mdc" name="PAGE_START" min="1" placeholder="65">
                        </div>
                        <!-- Halaman Akhir -->
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Halaman Akhir</label>
                            <input type="number" class="form-control" id="page_end_mdc" name="PAGE_END" min="1" placeholder="79">
                        </div>
                    </div>

                    <!-- Perhatian Khusus -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Perhatian Khusus Penginputan iDRG</label>
                        <textarea class="form-control" id="perhatian_khusus_input" name="PERHATIAN_KHUSUS_INPUT" rows="5" placeholder="Isi blok &quot;PERHATIAN KHUSUS DALAM PENGINPUTAN iDRG MDC xx&quot; (satu butir per baris)"></textarea>
                    </div>

                    <!-- Contoh Kasus -->
                    <div class="mb-2">
                        <label class="form-label fw-bold">Contoh Kasus</label>
                        <textarea class="form-control" id="contoh_kasus_icd" name="CONTOH_KASUS_ICD" rows="5" placeholder="Isi contoh kasus sesuai ketentuan coding ICD dalam kategori MDC xx"></textarea>
                    </div>
                    
                </div>
                <div class="modal-footer p-1">
                    <input class="btn btn-light-primary" id="modal_add_master_mdc_btn" type="submit" value="SUBMIT" name="simpan" >
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_edit_master_mdc" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header pb-0">
                <h1 class="mb-3" id="title_modal_edit_master_mdc">Edit Master MDC</h1>
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <span class="svg-icon svg-icon-1">
                        <i class="bi bi-x-lg"></i>
                    </span>
                </div>
            </div>
            <form action="<?php echo base_url('Ics/mastermdc/updatemastermdc'); ?>" method="post" id="formeditmastermdc" autocomplete="off">
                <?= csrf_field() ?>
                <input type="hidden" id="id_mdc" name="ID">
                <div class="modal-body">
                    <div class="row g-4 mb-4">
                        <div class="col-md-8">
                            <label class="form-label fw-bold required">Dokumen Sumber</label>
                            <select class="form-select" id="edit_document_id" name="DOCUMENT_ID" required>
                                <?php echo $masterdocument; ?>
                            </select>
                            <div class="form-text">Contoh: Indonesian Coding Standard (ICS) iDRG - V1</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold required">Kode MDC</label>
                            <input type="text" class="form-control" id="edit_mdc_code" name="MDC_CODE" maxlength="2" placeholder="11" readonly required>
                            <div class="form-text">2 digit (10 - 36, 90)</div>
                        </div>
                    </div>
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold required">Nama (Indonesia)</label>
                            <input type="text" class="form-control" id="edit_name_id" name="NAME_ID" maxlength="200" placeholder="Sistem Syaraf" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nama (English)</label>
                            <input type="text" class="form-control" id="edit_name_en" name="NAME_EN" maxlength="200" placeholder="Diseases and Disorders of the Nervous System">
                        </div>
                    </div>
                    <div class="row g-4 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Bab</label>
                            <input type="text" class="form-control" id="edit_chapter_no" name="CHAPTER_NO" maxlength="10" placeholder="3.1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Halaman Awal</label>
                            <input type="number" class="form-control" id="edit_page_start" name="PAGE_START" min="1" placeholder="65">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Halaman Akhir</label>
                            <input type="number" class="form-control" id="edit_page_end" name="PAGE_END" min="1" placeholder="79">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Perhatian Khusus Penginputan iDRG</label>
                        <textarea class="form-control" id="edit_perhatian" name="PERHATIAN_KHUSUS_INPUT" rows="5" placeholder="Isi blok &quot;PERHATIAN KHUSUS DALAM PENGINPUTAN iDRG MDC xx&quot; (satu butir per baris)"></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold">Contoh Kasus</label>
                        <textarea class="form-control" id="edit_contoh" name="CONTOH_KASUS_ICD" rows="5" placeholder="Isi contoh kasus sesuai ketentuan coding ICD dalam kategori MDC xx"></textarea>
                    </div>
                </div>
                <div class="modal-footer p-1">
                    <button class="btn btn-light-primary" id="modal_edit_master_mdc_btn" type="submit" name="update" value="1">UPDATE</button>
                </div>
            </form>
        </div>
    </div>
</div>