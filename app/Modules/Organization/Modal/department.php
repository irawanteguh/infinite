
<div class="modal fade od-user-modal" id="modal_add_department" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header pb-0">
                <h1 class="mb-3">Tambah departemen</h1>
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <span class="svg-icon svg-icon-1">
                        <i class="bi bi-x-lg"></i>
                    </span>
                </div>
            </div>
            <form action="<?php echo base_url();?>index.php/qi/masterdepartment/addmasterdepartment" id="formaddmasterdepartment">
                <div class="modal-body">

                    <div class="text-start mb-7">
                        <div class="text-muted fw-bold fs-6">
                            Lengkapi nama departemen dan pilih kepala departemen.
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-5">
                            <label class="required form-label fw-bold">Departemen</label>
                            <input type="text" class="form-control form-control-solid" id="modal_add_department_department" name="modal_add_department_department" maxlength="1000" placeholder="Masukkan nama departemen" required>
                        </div>
                        <div class="col-md-6 mb-5">
                            <label class="required form-label fw-bold">Kepala departemen</label>
                            <select class="form-select form-select-solid" data-control="select2" data-dropdown-parent="#modal_add_department" id="modal_add_department_headdepartment" name="modal_add_department_headdepartment" data-placeholder="Pilih kepala departemen" required>
                                <?php echo $datamasteruser;?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer p-1">  
                    <input class="btn btn-light-primary" id="btnproses" type="submit" value="Simpan departemen" name="simpan" >            
                </div>  
            </form>  
        </div>
    </div>
</div>

<div class="modal fade od-user-modal" id="modal_edit_masterdepartment" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header pb-0">
                <h1 class="mb-3">Ubah departemen</h1>
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <span class="svg-icon svg-icon-1">
                        <i class="bi bi-x-lg"></i>
                    </span>
                </div>
            </div>
            <form action="<?php echo base_url();?>index.php/qi/masterdepartment/editmasterdepartment" id="formeditmasterdepartment">
                <input type="hidden" id="modal_edit_masterdepartment_departmentid" name="modal_edit_masterdepartment_departmentid">
                <div class="modal-body">

                    <div class="text-start mb-7">
                        <div class="text-muted fw-bold fs-6">
                            Lengkapi nama departemen dan pilih kepala departemen.
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-5">
                            <label class="required form-label fw-bold">Departemen</label>
                            <input type="text" class="form-control form-control-solid" id="modal_edit_masterdepartment_department" name="modal_edit_masterdepartment_department" maxlength="1000" placeholder="Masukkan nama departemen" required>
                        </div>
                        <div class="col-md-6 mb-5">
                            <label class="required form-label fw-bold">Kepala departemen</label>
                            <select class="form-select form-select-solid" data-control="select2" data-dropdown-parent="#modal_edit_masterdepartment" id="modal_edit_masterdepartment_headdepartment" name="modal_edit_masterdepartment_headdepartment" data-placeholder="Pilih kepala departemen" required>
                                <?php echo $datamasteruser;?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer p-1">  
                    <input class="btn btn-light-primary" id="btnproses" type="submit" value="Simpan departemen" name="simpan" >            
                </div>  
            </form>  
        </div>
    </div>
</div>