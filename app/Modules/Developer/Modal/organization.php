<div class="modal fade" id="modal_add_organization" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header pb-0">
                <h1 class="mb-3">Tambah Organisasi Rumah Sakit</h1>
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <span class="svg-icon svg-icon-1">
                        <i class="bi bi-x-lg"></i>
                    </span>
                </div>
            </div>
            <form action="<?php echo base_url();?>index.php/developer/organization/addorganization" id="formaddorganization">
                <div class="modal-body">
                    <div class="text-start mb-5">
                        <div class="text-muted fw-bold fs-6">Pilih jenis organisasi. Susunan induk dan unit akan mengikuti hirarki rumah sakit.</div>
                    </div>
                    <div class="row g-4">
                        <div class="col-12">
                            <label class="form-label required" for="modal_add_organization_type">Jenis Organisasi</label>
                            <select class="form-select form-select-solid" data-control="select2" data-dropdown-parent="#modal_add_organization" id="modal_add_organization_type" name="modal_add_organization_type" required>
                                <option value="">Pilih Jenis Organisasi</option>
                                <optgroup label="Swasta">
                                    <option value="private_company">PT / Badan Penyelenggara</option>
                                    <option value="private_hospital">Rumah Sakit Swasta</option>
                                </optgroup>
                                <optgroup label="Pemerintah Daerah">
                                    <option value="health_department">Dinas Kesehatan</option>
                                    <option value="subdistrict_health_department">Suku Dinas Kesehatan</option>
                                    <option value="government_hospital">Rumah Sakit Pemerintah Daerah</option>
                                </optgroup>
                            </select>
                            <div id="organizationHierarchyHelp" class="form-text mt-2">Pilih jenis organisasi untuk melihat jalur hirarkinya.</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label required" for="modal_add_organization_name">Nama Organisasi</label>
                            <input type="text" class="form-control form-control-solid" id="modal_add_organization_name" name="modal_add_organization_name" placeholder="Masukkan nama organisasi" required>
                        </div>

                        <div class="col-12" id="parentOrganizationContainer" style="display:none">
                            <label class="form-label required" id="parentOrganizationLabel" for="modal_add_organization_header">Organisasi Induk</label>
                            <select class="form-select form-select-solid" data-control="select2" data-dropdown-parent="#modal_add_organization" id="modal_add_organization_header" name="modal_add_organization_header">
                                <option value="">Pilih Organisasi Induk</option>
                            </select>
                        </div>

                        <div class="col-md-6" id="organizationHospitalCodeContainer" style="display:none">
                            <label class="form-label" id="organizationHospitalCodeLabel" for="modal_add_organization_koders">Kode RS</label>
                            <input type="text" class="form-control form-control-solid" id="modal_add_organization_koders" name="modal_add_organization_koders" placeholder="Contoh: 3171795">
                            <div class="form-text">Kode RS digunakan untuk membuat akun administrator rumah sakit.</div>
                        </div>

                        <div class="col-md-6" id="organizationPpkContainer" style="display:none">
                            <label class="form-label" for="modal_add_organization_kodeppk">Kode PPK</label>
                            <input type="text" class="form-control form-control-solid" id="modal_add_organization_kodeppk" name="modal_add_organization_kodeppk" placeholder="Kode PPK (opsional)">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="modal_add_organization_website">Website</label>
                            <input type="text" class="form-control form-control-solid" id="modal_add_organization_website" name="modal_add_organization_website" placeholder="https://example.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="modal_add_organization_email">Email</label>
                            <input type="email" class="form-control form-control-solid" id="modal_add_organization_email" name="modal_add_organization_email" placeholder="organisasi@example.com">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="modal_add_organization_address">Alamat</label>
                            <textarea class="form-control form-control-solid" id="modal_add_organization_address" name="modal_add_organization_address" rows="3" placeholder="Masukkan alamat"></textarea>
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
