<div class="modal fade" id="modal_edit_masterindikator" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header pb-0">
                <h1 class="mb-3">Edit Master Indikator Mutu</h1>
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <span class="svg-icon svg-icon-1">
                        <i class="bi bi-x-lg"></i>
                    </span>
                </div>
            </div>
            <form action="<?php echo base_url();?>index.php/qi/masterindikator/editmasterindikator" id="formeditmasterindikator">
                <input type="hidden" id="modal_edit_masterindikator_indikatorid" name="modal_edit_masterindikator_indikatorid">
                <div class="modal-body" style="max-height:80vh; overflow-y:auto;">

                    <div class="text-start mb-7">
                        <div class="text-muted fw-bold fs-6">
                            Lengkapi informasi master indikator mutu.
                        </div>
                    </div>

                    <div class="row">

                        <div class="col-md-12 mb-5">
                            <label class="required form-label fw-bold">Nama Indikator</label>
                            <input type="text" class="form-control form-control-solid" id="modal_edit_masterindikator_indikator" name="modal_edit_masterindikator_indikator" maxlength="1000" placeholder="Masukkan nama indikator" required>
                        </div>

                        <div class="col-md-12 mb-5">
                            <label class="form-label fw-bold">Dasar Pemikiran</label>
                            <textarea class="form-control form-control-solid" id="modal_edit_masterindikator_dasarpemikiran" name="modal_edit_masterindikator_dasarpemikiran" rows="3" placeholder="Masukkan dasar pemikiran indikator"></textarea>
                        </div>

                        <div class="col-md-6 mb-5">
                            <label class="form-label fw-bold">Tujuan</label>
                            <textarea class="form-control form-control-solid" id="modal_edit_masterindikator_tujuan" name="modal_edit_masterindikator_tujuan" rows="3" placeholder="Masukkan tujuan indikator"></textarea>
                        </div>

                        <div class="col-md-6 mb-5">
                            <label class="form-label fw-bold">Definisi Operasional</label>
                            <textarea class="form-control form-control-solid" id="modal_edit_masterindikator_definisi" name="modal_edit_masterindikator_definisi" rows="3" placeholder="Masukkan definisi operasional"></textarea>
                        </div>

                        <div class="col-md-6 mb-5">
                            <label class="form-label fw-bold">Numerator</label>
                            <input type="text" class="form-control form-control-solid" id="modal_edit_masterindikator_numerator" name="modal_edit_masterindikator_numerator" placeholder="Masukkan numerator">
                        </div>

                        <div class="col-md-6 mb-5">
                            <label class="form-label fw-bold">Denominator</label>
                            <input type="text" class="form-control form-control-solid" id="modal_edit_masterindikator_denominator" name="modal_edit_masterindikator_denominator" placeholder="Masukkan denominator">
                        </div>

                        <div class="col-md-12 mb-5">
                            <label class="form-label fw-bold">Formula</label>
                            <input type="text" class="form-control form-control-solid" id="modal_edit_masterindikator_formula" name="modal_edit_masterindikator_formula" placeholder="Masukkan formula">
                        </div>

                    </div>

                    <div class="separator separator-dashed my-8"></div>

                    <h5 class="fw-bold mb-5">Dimensi Mutu</h5>

                    <div class="row mb-5">

                        <div class="col-md-4">
                            <div class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" id="modal_edit_masterindikator_dimensikeselamatan" name="modal_edit_masterindikator_dimensikeselamatan" value="Y">
                                <label class="form-check-label" for="modal_edit_masterindikator_dimensikeselamatan">Keselamatan Pasien</label>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" id="modal_edit_masterindikator_dimensiwaktu" name="modal_edit_masterindikator_dimensiwaktu" value="Y">
                                <label class="form-check-label" for="modal_edit_masterindikator_dimensiwaktu">Tepat Waktu</label>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" id="modal_edit_masterindikator_dimensiefektif" name="modal_edit_masterindikator_dimensiefektif" value="Y">
                                <label class="form-check-label" for="modal_edit_masterindikator_dimensiefektif">Efektif</label>
                            </div>
                        </div>

                        <div class="col-md-4 mt-4">
                            <div class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" id="modal_edit_masterindikator_dimensiefisien" name="modal_edit_masterindikator_dimensiefisien" value="Y">
                                <label class="form-check-label" for="modal_edit_masterindikator_dimensiefisien">Efisien</label>
                            </div>
                        </div>

                        <div class="col-md-4 mt-4">
                            <div class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" id="modal_edit_masterindikator_dimensipasien" name="modal_edit_masterindikator_dimensipasien" value="Y">
                                <label class="form-check-label" for="modal_edit_masterindikator_dimensipasien">Berfokus pada Pasien</label>
                            </div>
                        </div>

                        <div class="col-md-4 mt-4">
                            <div class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" id="modal_edit_masterindikator_dimensiintegrasi" name="modal_edit_masterindikator_dimensiintegrasi" value="Y">
                                <label class="form-check-label" for="modal_edit_masterindikator_dimensiintegrasi">Terintegrasi</label>
                            </div>
                        </div>

                    </div>

                    <div class="separator separator-dashed my-8"></div>

                    <div class="row">

                        <div class="col-md-6 mb-5">
                            <label class="form-label fw-bold">Populasi</label>
                            <input type="text" class="form-control form-control-solid" id="modal_edit_masterindikator_populasi" name="modal_edit_masterindikator_populasi" placeholder="Masukkan populasi">
                        </div>

                        <div class="col-md-6 mb-5">
                            <label class="form-label fw-bold">Metode Pengumpulan</label>
                            <input type="text" class="form-control form-control-solid" id="modal_edit_masterindikator_metodepengumpulan" name="modal_edit_masterindikator_metodepengumpulan" placeholder="Masukkan metode pengumpulan">
                        </div>

                        <div class="col-md-6 mb-5">
                            <label class="form-label fw-bold">Kriteria Inklusi</label>
                            <textarea class="form-control form-control-solid" id="modal_edit_masterindikator_kriteriainklusi" name="modal_edit_masterindikator_kriteriainklusi" rows="3" placeholder="Masukkan kriteria inklusi"></textarea>
                        </div>

                        <div class="col-md-6 mb-5">
                            <label class="form-label fw-bold">Kriteria Eksklusi</label>
                            <textarea class="form-control form-control-solid" id="modal_edit_masterindikator_kriteriaeksklusi" name="modal_edit_masterindikator_kriteriaeksklusi" rows="3" placeholder="Masukkan kriteria eksklusi"></textarea>
                        </div>

                        <div class="col-md-12 mb-5">
                            <label class="form-label fw-bold">Instrumen</label>
                            <input type="text" class="form-control form-control-solid" id="modal_edit_masterindikator_instrumen" name="modal_edit_masterindikator_instrumen" placeholder="Masukkan instrumen">
                        </div>

                    </div>

                    <div class="separator separator-dashed my-8"></div>

                    <div class="row">

                        <div class="col-md-4 mb-5">
                            <label class="required form-label fw-bold">Satuan</label>
                            <select class="form-select form-select-solid" data-control="select2" data-dropdown-parent="#modal_edit_masterindikator" id="modal_edit_masterindikator_satuanid" name="modal_edit_masterindikator_satuanid" data-placeholder="Pilih satuan" required>
                                <?php echo $mastersatuan;?>
                            </select>
                        </div>

                        <div class="col-md-4 mb-5">
                            <label class="required form-label fw-bold">Frekuensi</label>
                            <select class="form-select form-select-solid" data-control="select2" data-dropdown-parent="#modal_edit_masterindikator" id="modal_edit_masterindikator_frekuensiid" name="modal_edit_masterindikator_frekuensiid" data-placeholder="Pilih frekuensi" required>
                                <?php echo $masterfrekuensi;?>
                            </select>
                        </div>

                        <div class="col-md-4 mb-5">
                            <label class="required form-label fw-bold">Sumber Data</label>
                            <select class="form-select form-select-solid" data-control="select2" data-dropdown-parent="#modal_edit_masterindikator" id="modal_edit_masterindikator_sumberid" name="modal_edit_masterindikator_sumberid" data-placeholder="Pilih sumber data" required>
                                <?php echo $mastersumber;?>
                            </select>
                        </div>

                        <div class="col-md-4 mb-5">
                            <label class="required form-label fw-bold">Donabedian</label>
                            <select class="form-select form-select-solid" data-control="select2" data-dropdown-parent="#modal_edit_masterindikator" id="modal_edit_masterindikator_donabedianid" name="modal_edit_masterindikator_donabedianid" data-placeholder="Pilih Donabedian" required>
                                <?php echo $masterdonabedian;?>
                            </select>
                        </div>

                        <div class="col-md-4 mb-5">
                            <label class="required form-label fw-bold">Target Capaian</label>
                            <select class="form-select form-select-solid" data-control="select2" data-dropdown-parent="#modal_edit_masterindikator" id="modal_edit_masterindikator_targetcapaian" name="modal_edit_masterindikator_targetcapaian" data-placeholder="Pilih target capaian" required>
                                <?php echo $mastertarget;?>
                            </select>
                        </div>

                        <div class="col-md-4 mb-5">
                            <label class="form-label fw-bold">Benchmark</label>
                            <select class="form-select form-select-solid" data-control="select2" data-dropdown-parent="#modal_edit_masterindikator" id="modal_edit_masterindikator_benchmarkid" name="modal_edit_masterindikator_benchmarkid" data-placeholder="Pilih benchmark">
                                <?php echo $masterbenchmark;?>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-bold">Status</label>
                            <select class="form-select form-select-solid" id="modal_edit_masterindikator_active" name="modal_edit_masterindikator_active">
                                <option value="">Pilih status</option>
                                <option value="1">Aktif</option>
                                <option value="0">Tidak Aktif</option>
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