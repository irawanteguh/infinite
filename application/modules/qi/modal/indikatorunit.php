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
            <form action="<?php echo base_url();?>index.php/qi/indikatorunit/addindikatorunit" id="formaddindikatorunit">
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

<div class="modal fade" id="modal_input_nilai_indikator" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header pb-0">
                <h1 class="mb-3">Submit Penilaian Indikator</h1>
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <span class="svg-icon svg-icon-1">
                        <i class="bi bi-x-lg"></i>
                    </span>
                </div>
            </div>
            <form action="<?php echo base_url();?>index.php/qi/indikatorunit/inputnilaiindikator" id="forminputnilaiindikator">
                <input type="hidden" id="modal_input_nilai_indikator_indikatorid" name="modal_input_nilai_indikator_indikatorid">
                <input type="hidden" id="modal_input_nilai_indikator_bulan" name="modal_input_nilai_indikator_bulan">
                <div class="modal-body">
                    <div class="text-start mb-5">
                        <div class="text-muted fw-bold fs-5">Please add unit indicator.</div>
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
                                <textarea name="modal_add_pengajuanindikatorunit_alasan" id="modal_add_pengajuanindikatorunit_alasan" class="form-control form-control-solid" rows="10" required></textarea>
                            </div>
                            <div class="col-md-6 mb-5">
                                <label class="d-flex align-items-center fs-5 fw-bold mb-2">
                                    <span class="required">Rencana Tindak Lanjut</span>
                                    <i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip" title="Periode"></i>
                                </label>
                                <textarea name="modal_add_pengajuanindikatorunit_rtl" id="modal_add_pengajuanindikatorunit_rtl" class="form-control form-control-solid" rows="10" required></textarea>
                            </div>
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