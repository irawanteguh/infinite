<div class="modal fade" id="modal_add_organization" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header pb-0">
                <h1 class="mb-3">Add Organization</h1>
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <span class="svg-icon svg-icon-1">
                        <i class="bi bi-x-lg"></i>
                    </span>
                </div>
            </div>
            <form action="<?php echo base_url();?>index.php/developer/organization/addorganization" id="formaddorganization">
                <div class="modal-body">
                    <div class="text-start mb-5">
                        <div class="text-muted fw-bold fs-5">Please add the organization data.</div>
                    </div>
                    <div class="row">
                        <label class="d-flex align-items-center fs-5 fw-bold mb-2"><span class="required">Organization</span><i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip" title="Organization"></i></label>
                        <input type="text" class="form-control form-control-solid mb-5" id="modal_add_organization_name" name="modal_add_organization_name" placeholder="Enter Organization" required>

                        <label class="d-flex align-items-center fs-5 fw-bold mb-2"><span class="required">Type</span><i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip" title="Organization Type"></i></label>
                        <select class="form-select form-select-solid mb-5" data-control="select2" data-dropdown-parent="#modal_add_organization" id="modal_add_organization_type" name="modal_add_organization_type" required><option value="">Select Type</option><option value="Y">Headquarters</option><option value="B">Branch</option></select>

                        <div id="parentOrganizationContainer">
                            <label class="d-flex align-items-center fs-5 fw-bold mb-2">
                                <span class="required">Parent Organization</span>
                                <i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip" title="Select Headquarters Organization"></i>
                            </label>
                            <select class="form-select form-select-solid mb-5"
                                    data-control="select2"
                                    data-dropdown-parent="#modal_add_organization"
                                    id="modal_add_organization_header"
                                    name="modal_add_organization_header">
                                <option value="">Select Parent Organization</option>
                                <?php echo $masterorganization; ?>
                            </select>
                        </div>

                        <label class="d-flex align-items-center fs-5 fw-bold mb-2"><span>Website</span></label>
                        <input type="text" class="form-control form-control-solid mb-5" id="modal_add_organization_website" name="modal_add_organization_website" placeholder="https://example.com">

                        <label class="d-flex align-items-center fs-5 fw-bold mb-2"><span>Email</span></label>
                        <input type="text" class="form-control form-control-solid mb-5" id="modal_add_organization_email" name="modal_add_organization_email" placeholder="organization@example.com">

                        <label class="d-flex align-items-center fs-5 fw-bold mb-2"><span>Address</span></label>
                        <textarea class="form-control form-control-solid" id="modal_add_organization_address" name="modal_add_organization_address" rows="3" placeholder="Enter Address"></textarea>
                    </div>
                </div> 
                <div class="modal-footer p-1">	
                    <input class="btn btn-light-primary" id="btnproses" type="submit" value="SUBMIT" name="simpan" >			
                </div>  
            </form>  
        </div>
    </div>
</div>