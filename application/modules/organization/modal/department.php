<div class="modal fade" id="modal_add_department" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header pb-0">
                <h1 class="mb-3">Add Department</h1>
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <span class="svg-icon svg-icon-1">
                        <i class="bi bi-x-lg"></i>
                    </span>
                </div>
            </div>
            <form action="<?php echo base_url();?>index.php/organization/department/adddepartment" id="formadddepartment">
                <div class="modal-body">
                    <div class="text-start mb-5">
                        <div class="text-muted fw-bold fs-5">Please add the department data.</div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-5">
                            <label class="d-flex align-items-center fs-5 fw-bold mb-2">
                                <span class="required">Department</span>
                                <i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip" title="Department"></i>
                            </label>
                            <input type="text" class="form-control form-control-solid" id="modal_add_department_department" name="modal_add_department_department" required>
                        </div>
                        <div class="col-md-12">
                            <label class="d-flex align-items-center fs-5 fw-bold mb-2">
                                <span class="required">Head Department</span>
                                <i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip" title="Head Department"></i>
                            </label>
                            <select data-control="select2" data-dropdown-parent="#modal_add_department" data-placeholder="Please Select User" class="form-select form-select-solid" name="modal_add_department_headdepartment" id="modal_add_department_headdepartment" required>
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