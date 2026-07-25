<div class="modal fade" id="modal_import_kfa" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header pb-0">
                <h1 class="mb-3">Import Kamus Farmasi Alkes</h1>
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <span class="svg-icon svg-icon-1">
                        <i class="bi bi-x-lg"></i>
                    </span>
                </div>
            </div>
            <form action="<?php echo base_url();?>index.php/satusehat/masterkfa/updatekfa" id="formupdatekfa">
                <input type="hidden" id="modal_import_kfa_kfaid" name="modal_import_kfa_kfaid">
                <div class="modal-body">
                    <div class="text-start mb-5">
                        <div class="text-muted fw-bold fs-5">Silakan lengkapi informasi harga distributor sebelum mengimpor data obat.</div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-5">
                        <label class="d-flex align-items-center fs-5 fw-bold mb-2"><span>Nama Obat</span></label>
                        <input type="text" class="form-control form-control-solid" id="modal_import_kfa_nama_obat" name="modal_import_kfa_nama_obat" readonly>
                        </div>

                        <div class="col-md-12 mb-5">
                        <label class="d-flex align-items-center fs-5 fw-bold mb-2"><span>Produsen</span></label>
                        <input type="text" class="form-control form-control-solid" id="modal_import_kfa_produsen" name="modal_import_kfa_produsen" readonly>
                        </div>

                        <div class="col-md-12 mb-5">
                        <label class="d-flex align-items-center fs-5 fw-bold mb-2"><span>HET</span></label>
                        <input type="text" class="form-control form-control-solid" id="modal_import_kfa_het" name="modal_import_kfa_het" readonly>
                        </div>


                        <div class="col-md-6 mb-5">
                        <label class="d-flex align-items-center fs-5 fw-bold mb-2"><span>Harga Distributor</span></label>
                        <input type="text" class="form-control" id="modal_import_kfa_harga_distributor" name="modal_import_kfa_harga_distributor" value="0">
                        </div>

                        <div class="col-md-6 mb-5">
                        <label class="d-flex align-items-center fs-5 fw-bold mb-2"><span>Diskon (%)</span></label>
                        <input type="text" class="form-control" id="modal_import_kfa_disc" name="modal_import_kfa_disc" value="0">
                        </div>

                        <div class="col-md-6 mb-5">
                        <label class="d-flex align-items-center fs-5 fw-bold mb-2"><span>PPN (%)</span></label>
                        <input type="text" class="form-control" id="modal_import_kfa_ppn" name="modal_import_kfa_ppn" value="11">
                        </div>

                        <div class="col-md-6 mb-5">
                        <label class="d-flex align-items-center fs-5 fw-bold mb-2"><span>Total</span></label>
                        <input type="text" class="form-control form-control-solid fw-bold text-primary" id="modal_import_kfa_total" name="modal_import_kfa_total" readonly>
                        </div>
                    </div>
                </div> 
                <div class="modal-footer p-1">	
                    <input class="btn btn-light-primary" id="btnproses" type="submit" value="IMPORT" name="simpan" >			
                </div>  
            </form>  
        </div>
    </div>
</div>
