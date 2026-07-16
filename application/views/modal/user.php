<div class="modal fade" id="modal_add_user" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header pb-0">
                <h1 class="mb-3">Add User</h1>
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <span class="svg-icon svg-icon-1">
                        <i class="bi bi-x-lg"></i>
                    </span>
                </div>
            </div>
            <form action="<?php echo base_url();?>index.php/hr/users/adduser" id="formadduser">
                <div class="modal-body">
                    <div class="text-start mb-5">
                        <div class="text-muted fw-bold fs-5">Please add the employee data.</div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 row">
                            <div class="col-md-2">
                                <div class="col-md-12 mb-5">
                                    <label class="d-flex align-items-center fs-5 fw-bold mb-2">
                                        <span>Avatar</span>
                                    </label>
                                    <div class="image-input image-input-outline" data-kt-image-input="true" style="background-image: url(<?php echo base_url()?>assets/media/avatars/blank.png)">
                                        <div class="image-input-wrapper w-125px h-125px"
                                            style="background-image: url(<?php echo base_url()?>assets/media/avatars/blank.png)">
                                        </div>
                                        <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Change avatar">
                                            <i class="bi bi-pencil-fill fs-7"></i>
                                            <input type="file" name="modal_add_user_avatar" id="modal_add_user_avatar" accept=".jpeg .jpg" />
                                            <input type="hidden" name="avatar_remove" />
                                        </label>
                                        <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="Cancel avatar">
                                            <i class="bi bi-x fs-2"></i>
                                        </span>
                                        <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="remove" data-bs-toggle="tooltip" title="Remove avatar">
                                            <i class="bi bi-x fs-2"></i>
                                        </span>
                                    </div>
                                </div> 
                            </div>
                            <div class="col-md-10 row">
                                <div class="col-md-6 mb-5">
                                    <label class="d-flex align-items-center fs-5 fw-bold mb-2">
                                        <span class="required">Username</span>
                                    </label>
                                    <input type="text" class="form-control form-control-solid form-control-sm" id="modal_add_user_username" name="modal_add_user_username" required>
                                </div> 
                                <div class="col-md-6 mb-5">
                                    <label class="d-flex align-items-center fs-5 fw-bold mb-2">
                                        <span>ID Internal Rumah Sakit</span>
                                    </label>
                                    <input type="text" class="form-control form-control-solid form-control-sm" id="modal_add_user_nikrs" name="modal_add_user_nikrs">
                                </div>                                  
                                <div class="col-md-6 mb-5">
                                    <label class="d-flex align-items-center fs-5 fw-bold mb-2">
                                        <span class="required">Nama Karyawan</span>
                                    </label>
                                    <input type="text" class="form-control form-control-solid form-control-sm" id="modal_add_user_name" name="modal_add_user_name" required>
                                </div>                             
                                <div class="col-md-6 mb-5">
                                    <label class="d-flex align-items-center fs-5 fw-bold mb-2">
                                        <span class="required">Email Address</span>
                                        <i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip" title="Silakan alamat email anda yang aktif"></i>
                                    </label>
                                    <input type="text" class="form-control form-control-solid form-control-sm" placeholder="Silakan Masukan Alamat Email Anda" id="modal_add_user_email" name="modal_add_user_email" required>
                                </div>
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

<div class="modal fade" id="modal_edit_user" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header pb-0">
                <h1 class="mb-3">Edit User</h1>
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <span class="svg-icon svg-icon-1">
                        <i class="bi bi-x-lg"></i>
                    </span>
                </div>
            </div>
            <form action="<?php echo base_url();?>index.php/hr/users/edituser" id="formedituser">
                <input type="hidden" id="modal_edit_user_userid" name="modal_edit_user_userid">
                <div class="modal-body">
                    <div class="text-start mb-5">
                        <div class="text-muted fw-bold fs-5">Please edit the employee data.</div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 row">
                            <div class="col-md-2">
                                <div class="col-md-12 mb-5">
                                    <label class="d-flex align-items-center fs-5 fw-bold mb-2">
                                        <span>Avatar</span>
                                    </label>
                                    <div class="image-input image-input-outline" data-kt-image-input="true" style="background-image: url(<?php echo base_url()?>assets/media/avatars/blank.png)">
                                        <div id="avatar-preview-edit" class="image-input-wrapper w-125px h-125px" style="background-image: url(<?php echo base_url()?>assets/media/avatars/blank.png)"></div>
                                        <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Change avatar">
                                            <i class="bi bi-pencil-fill fs-7"></i>
                                            <input type="file" name="modal_edit_user_avatar" id="modal_edit_user_avatar" accept=".jpeg .jpg" />
                                            <input type="hidden" name="avatar_remove" />
                                        </label>
                                        <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="Cancel avatar">
                                            <i class="bi bi-x fs-2"></i>
                                        </span>
                                        <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="remove" data-bs-toggle="tooltip" title="Remove avatar">
                                            <i class="bi bi-x fs-2"></i>
                                        </span>
                                    </div>
                                </div> 
                            </div>
                            <div class="col-md-10 row">
                                <div class="col-md-6 mb-5">
                                    <label class="d-flex align-items-center fs-5 fw-bold mb-2">
                                        <span class="required">Username</span>
                                    </label>
                                    <input type="text" class="form-control form-control-solid form-control-sm" id="modal_edit_user_username" name="modal_edit_user_username" readonly>
                                </div> 
                                <div class="col-md-6 mb-5">
                                    <label class="d-flex align-items-center fs-5 fw-bold mb-2">
                                        <span>ID Internal Rumah Sakit</span>
                                    </label>
                                    <input type="text" class="form-control form-control-solid form-control-sm" id="modal_edit_user_nikrs" name="modal_edit_user_nikrs">
                                </div>                                  
                                <div class="col-md-6 mb-5">
                                    <label class="d-flex align-items-center fs-5 fw-bold mb-2">
                                        <span class="required">Nama Karyawan</span>
                                    </label>
                                    <input type="text" class="form-control form-control-solid form-control-sm" id="modal_edit_user_name" name="modal_edit_user_name" required>
                                </div>                             
                                <div class="col-md-6 mb-5">
                                    <label class="d-flex align-items-center fs-5 fw-bold mb-2">
                                        <span class="required">Email Address</span>
                                        <i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip" title="Silakan alamat email anda yang aktif"></i>
                                    </label>
                                    <input type="text" class="form-control form-control-solid form-control-sm" placeholder="Silakan Masukan Alamat Email Anda" id="modal_edit_user_email" name="modal_edit_user_email" required>
                                </div>
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