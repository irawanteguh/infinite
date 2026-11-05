<div class="modal fade" id="modal_add_periodepelaporan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header pb-0">
                <h1 class="mb-3">Add Periode Pelaporan</h1>
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <span class="svg-icon svg-icon-1">
                        <i class="bi bi-x-lg"></i>
                    </span>
                </div>
            </div>
            <form action="<?php echo base_url();?>index.php/qi/periodepelaporan/addperiode" id="formaddperiode">
                <div class="modal-body">
                    <div class="text-start mb-5">
                        <div class="text-muted fw-bold fs-5">Please add the period report.</div>
                    </div>
                    <div class="row">
                        <div class="col-md-2 mb-5">
                            <label class="d-flex align-items-center fs-5 fw-bold mb-2">
                                <span class="required">Periode</span>
                                <i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip" title="Periode"></i>
                            </label>
                            <input type="text" class="form-control form-control-solid" id="modal_add_periodepelaporan_periode" name="modal_add_periodepelaporan_periode" required>
                        </div>
                        <div class="col-md-10">
                            <label class="d-flex align-items-center fs-5 fw-bold mb-2">
                                <span class="required">Ketua / PJ Komite Mutu</span>
                                <i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip" title="Ketua / PJ Komite Mutu"></i>
                            </label>
                            <select data-control="select2" data-dropdown-parent="#modal_add_periodepelaporan" data-placeholder="Please Select User" class="form-select form-select-solid" name="modal_add_periodepelaporan_user" id="modal_add_periodepelaporan_user" required>
                                <?php echo $masterdatauser;?>
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

<div class="modal fade" id="modal_edit_periodepelaporan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header pb-0">
                <h1 class="mb-3">Edit Periode Pelaporan</h1>
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <span class="svg-icon svg-icon-1">
                        <i class="bi bi-x-lg"></i>
                    </span>
                </div>
            </div>
            <form action="<?php echo base_url();?>index.php/qi/periodepelaporan/editperiode" id="formeditperiode">
                <div class="modal-body">
                    <div class="text-start mb-5">
                        <div class="text-muted fw-bold fs-5">Please update the period report.</div>
                    </div>
                    <input type="hidden" id="modal_edit_periodepelaporan_id" name="modal_edit_periodepelaporan_id">
                    <div class="row">
                        <div class="col-md-2 mb-5">
                            <label class="d-flex align-items-center fs-5 fw-bold mb-2">
                                <span class="required">Periode</span>
                                <i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip" title="Periode"></i>
                            </label>
                            <input type="text" class="form-control form-control-solid" id="modal_edit_periodepelaporan_periode" name="modal_edit_periodepelaporan_periode" required>
                        </div>
                        <div class="col-md-10">
                            <label class="d-flex align-items-center fs-5 fw-bold mb-2">
                                <span class="required">Ketua / PJ Komite Mutu</span>
                                <i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip" title="Ketua / PJ Komite Mutu"></i>
                            </label>
                            <select data-control="select2" data-dropdown-parent="#modal_edit_periodepelaporan" data-placeholder="Please Select User" class="form-select form-select-solid" name="modal_edit_periodepelaporan_user" id="modal_edit_periodepelaporan_user" required>
                                <?php echo $masterdatauser; ?>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="d-flex align-items-center fs-5 fw-bold mb-2">
                                <span class="required">Status Periode Pelaporan</span>
                                <i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip" title="Status Periode Pelaporan"></i>
                            </label>
                            <select data-control="select2" data-dropdown-parent="#modal_edit_periodepelaporan" data-placeholder="Please Select Status" class="form-select form-select-solid" name="modal_edit_periodepelaporan_status" id="modal_edit_periodepelaporan_status" required>
                                <option value="1">Draft - Pengguna menyusun indikator</option>
                                <option value="3">Publikasi - Pengguna dapat melakukan pengisian nilai indikator</option>
                                <option value="2">Verifikasi - Komite melakukan verifikasi indikator</option>
                                <option value="4">Final - Periode laporan telah berakhir dan data dikunci</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer p-1">
                    <input class="btn btn-light-primary" id="btnprosesedit" type="submit" value="UPDATE" name="simpan">
                </div>
            </form>
        </div>
    </div>
</div>