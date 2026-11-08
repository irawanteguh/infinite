<div class="modal fade" id="modal_add_pengajuanindikatorunit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header pb-0">
                <h1 class="mb-3">Add Pengajuan Indikator Unit</h1>
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <span class="svg-icon svg-icon-1">
                        <i class="bi bi-x-lg"></i>
                    </span>
                </div>
            </div>
            <form action="<?= site_url('qi/indikatorunit/addindikatorunit') ?>" id="formaddindikatorunit">
                <div class="modal-body">
                    <div class="text-start mb-5">
                        <div class="text-muted fw-bold fs-5">Please add unit indicator.</div>
                    </div>
                    <div class="row">
                        <div class="col-md-2 mb-5">
                            <label class="d-flex align-items-center fs-5 fw-bold mb-2">
                                <span class="required">Periode</span>
                                <i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip" title="Ketua / PJ Komite Mutu"></i>
                            </label>
                            <select data-control="select2" data-dropdown-parent="#modal_add_pengajuanindikatorunit" data-placeholder="Please Select Indicator" class="form-select form-select-solid" name="modal_add_pengajuanindikatorunit_periodeid" id="modal_add_pengajuanindikatorunit_periodeid" required>
                                <?php echo $masterperiodepelaporan;?>
                            </select>
                        </div>
                        <div class="col-md-5 mb-5">
                            <label class="d-flex align-items-center fs-5 fw-bold mb-2">
                                <span class="required">Department</span>
                                <i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip" title="Head Department"></i>
                            </label>
                            <select data-control="select2" data-dropdown-parent="#modal_add_pengajuanindikatorunit" data-placeholder="Please Select Department" class="form-select form-select-solid" name="modal_add_pengajuanindikatorunit_departmentid" id="modal_add_pengajuanindikatorunit_departmentid" required>
                                <?php echo $masterdatadepartment;?>
                            </select>
                        </div>
                        <div class="col-md-5 mb-5">
                            <label class="d-flex align-items-center fs-5 fw-bold mb-2">
                                <span class="required">PIC Indikator</span>
                                <i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip" title="Head Department"></i>
                            </label>
                            <select data-control="select2" data-dropdown-parent="#modal_add_pengajuanindikatorunit" data-placeholder="Please Select User" class="form-select form-select-solid" name="modal_add_pengajuanindikatorunit_userid" id="modal_add_pengajuanindikatorunit_userid" required>
                                <?php echo $masterdatauser;?>
                            </select>
                        </div>
                        <div class="col-md-2 mb-5">
                            <label class="d-flex align-items-center fs-5 fw-bold mb-2">
                                <span class="required">Target</span>
                                <i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip" title="Periode"></i>
                            </label>
                            <input type="text" class="form-control form-control-solid" id="modal_add_pengajuanindikatorunit_target" name="modal_add_pengajuanindikatorunit_target" required>
                        </div>
                        <div class="col-md-10 mb-5">
                            <label class="d-flex align-items-center fs-5 fw-bold mb-2">
                                <span class="required">Indikator</span>
                                <i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip" title="Ketua / PJ Komite Mutu"></i>
                            </label>
                            <select data-control="select2" data-dropdown-parent="#modal_add_pengajuanindikatorunit" data-placeholder="Please Select Indicator" class="form-select form-select-solid" name="modal_add_pengajuanindikatorunit_indikatorid" id="modal_add_pengajuanindikatorunit_indikatorid" required>
                                <?php echo $masterindikator;?>
                            </select>
                        </div>
                    </div>
                </div> 
                <div class="modal-footer p-1">	
                    <input class="btn btn-light-primary" id="btnproses" type="submit" value="SUBMIT" name="simpan" >			
                </div>  
            </form>  
        </div>
    </div>
</div>

<div class="modal fade" id="modal_create_fishbone_from_indicator" tabindex="-1" aria-hidden="true" data-bs-focus="false">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form id="form_create_fishbone_from_indicator">
                <div class="modal-header">
                    <h2 class="mb-0">Buat Analisis Fishbone</h2>
                    <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Tutup"><i class="bi bi-x-lg"></i></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="fishbone_indicator_ref_id" name="indikator_ref_id">
                    <div class="mb-5">
                        <label class="form-label required" for="fishbone_indicator_period">Periode</label>
                        <select class="form-select form-select-solid" id="fishbone_indicator_period" name="periode_id" required>
                            <?php echo $masterperiodepelaporan; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label required" for="fishbone_indicator_problem">Masalah</label>
                        <textarea class="form-control form-control-solid" id="fishbone_indicator_problem" name="problem" rows="5" maxlength="2000" placeholder="Masukkan masalah mutu yang akan dianalisis" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btn_save_fishbone_from_indicator">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_edit_pengajuanindikatorunit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header pb-0">
                <h1 class="mb-3">Edit Pengajuan Indikator Unit</h1>
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <span class="svg-icon svg-icon-1"><i class="bi bi-x-lg"></i></span>
                </div>
            </div>
            <form action="<?= site_url('qi/indikatorunit/updateindikatorunit') ?>" id="formeditindikatorunit" method="post">
                <div class="modal-body">
                    <div class="text-start mb-5">
                        <div class="text-muted fw-bold fs-5">Please update unit indicator.</div>
                    </div>
                    <input type="hidden" name="modal_edit_pengajuanindikatorunit_uuid" id="modal_edit_pengajuanindikatorunit_uuid">
                    <div class="row">
                        <div class="col-md-2 mb-5">
                            <label class="d-flex align-items-center fs-5 fw-bold mb-2">
                                <span class="required">Periode</span>
                                <i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip" title="Periode Pelaporan"></i>
                            </label>
                            <select data-control="select2" data-dropdown-parent="#modal_edit_pengajuanindikatorunit" data-placeholder="Please Select Period" class="form-select form-select-solid" name="modal_edit_pengajuanindikatorunit_periodeid" id="modal_edit_pengajuanindikatorunit_periodeid" required>
                                <?php echo $masterperiodepelaporan; ?>
                            </select>
                        </div>
                        <div class="col-md-5 mb-5">
                            <label class="d-flex align-items-center fs-5 fw-bold mb-2">
                                <span class="required">Department</span>
                                <i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip" title="Head Department"></i>
                            </label>
                            <select data-control="select2" data-dropdown-parent="#modal_edit_pengajuanindikatorunit" data-placeholder="Please Select Department" class="form-select form-select-solid" name="modal_edit_pengajuanindikatorunit_departmentid" id="modal_edit_pengajuanindikatorunit_departmentid" required>
                                <?php echo $masterdatadepartment; ?>
                            </select>
                        </div>
                        <div class="col-md-5 mb-5">
                            <label class="d-flex align-items-center fs-5 fw-bold mb-2">
                                <span class="required">PIC Indikator</span>
                                <i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip" title="Person In Charge"></i>
                            </label>
                            <select data-control="select2" data-dropdown-parent="#modal_edit_pengajuanindikatorunit" data-placeholder="Please Select User" class="form-select form-select-solid" name="modal_edit_pengajuanindikatorunit_userid" id="modal_edit_pengajuanindikatorunit_userid" required>
                                <?php echo $masterdatauser; ?>
                            </select>
                        </div>
                        <div class="col-md-2 mb-5">
                            <label class="d-flex align-items-center fs-5 fw-bold mb-2">
                                <span class="required">Target</span>
                                <i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip" title="Target Indikator"></i>
                            </label>
                            <input type="text" class="form-control form-control-solid" id="modal_edit_pengajuanindikatorunit_target" name="modal_edit_pengajuanindikatorunit_target" required>
                        </div>
                        <div class="col-md-10 mb-5">
                            <label class="d-flex align-items-center fs-5 fw-bold mb-2">
                                <span class="required">Indikator</span>
                                <i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip" title="Indikator Mutu"></i>
                            </label>
                            <select data-control="select2" data-dropdown-parent="#modal_edit_pengajuanindikatorunit" data-placeholder="Please Select Indicator" class="form-select form-select-solid" name="modal_edit_pengajuanindikatorunit_indikatorid" id="modal_edit_pengajuanindikatorunit_indikatorid" required>
                                <?php echo $masterindikator; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer p-1">
                    <input class="btn btn-light-primary" id="btnprosesedit" type="submit" value="UPDATE" name="update">
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_input_nilai_indikator" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header pb-0">
                <h1 class="mb-3" id="modal_input_nilai_indikator_title">Submit Penilaian Indikator</h1>
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <span class="svg-icon svg-icon-1">
                        <i class="bi bi-x-lg"></i>
                    </span>
                </div>
            </div>
            <form action="<?= site_url('qi/indikatorunit/inputnilaiindikator') ?>" id="forminputnilaiindikator" enctype="multipart/form-data">
                <input type="hidden" id="modal_input_nilai_indikator_referensiid" name="modal_input_nilai_indikator_referensiid">
                <input type="hidden" id="modal_input_nilai_indikator_indikatorid" name="modal_input_nilai_indikator_indikatorid">
                <input type="hidden" id="modal_input_nilai_indikator_bulan" name="modal_input_nilai_indikator_bulan">
                <div class="modal-body">
                    <div class="text-start mb-5">
                        <div class="text-muted fw-bold fs-5">Please submit indicator.</div>
                    </div>
                    <div class="row">
                        <div class="col-md-2 mb-5">
                            <label class="d-flex align-items-center fs-5 fw-bold mb-2">
                                <span class="required">Numerator</span>
                                <i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip" title="Periode"></i>
                            </label>
                            <input type="text" class="form-control form-control-solid" id="modal_add_pengajuanindikatorunit_numerator" name="modal_add_pengajuanindikatorunit_numerator" required>
                        </div>
                        <div class="col-md-2 mb-5">
                            <label class="d-flex align-items-center fs-5 fw-bold mb-2">
                                <span class="required">Denumerator</span>
                                <i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip" title="Periode"></i>
                            </label>
                            <input type="text" class="form-control form-control-solid" id="modal_add_pengajuanindikatorunit_denumerator" name="modal_add_pengajuanindikatorunit_denumerator" required>
                        </div>
                        <div class="row d-flex">
                            <div class="col-md-6 mb-5">
                                <label class="d-flex align-items-center fs-5 fw-bold mb-2">
                                    <span class="required">Alasan</span>
                                    <i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip" title="Periode"></i>
                                </label>
                                <textarea name="modal_add_pengajuanindikatorunit_alasan" id="modal_add_pengajuanindikatorunit_alasan" class="form-control form-control-solid" rows="5" required></textarea>
                            </div>
                            <div class="col-md-6 mb-5">
                                <label class="d-flex align-items-center fs-5 fw-bold mb-2">
                                    <span class="required">Rencana Tindak Lanjut</span>
                                    <i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip" title="Periode"></i>
                                </label>
                                <textarea name="modal_add_pengajuanindikatorunit_rtl" id="modal_add_pengajuanindikatorunit_rtl" class="form-control form-control-solid" rows="5" required></textarea>
                            </div>
                        </div>
                        <div class="col-12 mb-5">
                            <label class="d-flex align-items-center fs-5 fw-bold mb-2" for="modal_input_nilai_indikator_dokumen">
                                <span>Dokumen Pendukung</span>
                            </label>
                            <input type="file" class="form-control form-control-solid" id="modal_input_nilai_indikator_dokumen" name="supporting_document" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                            <div class="text-muted fs-7 mt-2">Format PDF, Word, Excel, JPG, atau PNG. Maksimal 10 MB.</div>
                        </div>
                    </div>
                </div> 
                <div class="modal-footer p-1">	
                    <input class="btn btn-light-primary" id="btn_submit_nilai_indikator" type="submit" value="SUBMIT" name="simpan" >
                </div>  
            </form>  
        </div>
    </div>
</div>
