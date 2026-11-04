
<div class="modal fade" id="modal_add_department" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header pb-0">
                <h1 class="mb-3">Add Master Department</h1>
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <span class="svg-icon svg-icon-1">
                        <i class="bi bi-x-lg"></i>
                    </span>
                </div>
            </div>
            <form action="<?php echo base_url();?>index.php/qi/masterdepartment/addmasterdepartment" id="formaddmasterdepartment">
                <div class="modal-body" style="max-height:80vh; overflow-y:auto;">

                    <div class="text-start mb-7">
                        <div class="text-muted fw-bold fs-6">
                            Lengkapi informasi master department.
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-5">
                            <label class="required form-label fw-bold">Department</label>
                            <input type="text" class="form-control form-control-solid" id="modal_add_department_department" name="modal_add_department_department" maxlength="1000" placeholder="Masukkan nama department" required>
                        </div>
                        <div class="col-md-6 mb-5">
                            <label class="required form-label fw-bold">Head Department</label>
                            <select class="form-select form-select-solid" data-control="select2" data-dropdown-parent="#modal_add_department" id="modal_add_department_headdepartment" name="modal_add_department_headdepartment" data-placeholder="Pilih head department" required>
                                <?php echo $datamasteruser;?>
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

<div class="modal fade" id="modal_edit_masterdepartment" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header pb-0">
                <h1 class="mb-3">Edit Master Department</h1>
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <span class="svg-icon svg-icon-1">
                        <i class="bi bi-x-lg"></i>
                    </span>
                </div>
            </div>
            <form action="<?php echo base_url();?>index.php/qi/masterdepartment/editmasterdepartment" id="formeditmasterdepartment">
                <input type="hidden" id="modal_edit_masterdepartment_departmentid" name="modal_edit_masterdepartment_departmentid">
                <div class="modal-body" style="max-height:80vh; overflow-y:auto;">

                    <div class="text-start mb-7">
                        <div class="text-muted fw-bold fs-6">
                            Lengkapi informasi master department.
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-5">
                            <label class="required form-label fw-bold">Department</label>
                            <input type="text" class="form-control form-control-solid" id="modal_edit_masterdepartment_department" name="modal_edit_masterdepartment_department" maxlength="1000" placeholder="Masukkan nama department" required>
                        </div>
                        <div class="col-md-6 mb-5">
                            <label class="required form-label fw-bold">Head Department</label>
                            <select class="form-select form-select-solid" data-control="select2" data-dropdown-parent="#modal_edit_masterdepartment" id="modal_edit_masterdepartment_headdepartment" name="modal_edit_masterdepartment_headdepartment" data-placeholder="Pilih head department" required>
                                <?php echo $datamasteruser;?>
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