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
            <form action="<?php echo base_url();?>index.php/qi/indikatorunit/inputnilaiindikator" id="forminputnilaiindikator">
                <div class="modal-body" style="max-height:80vh; overflow-y:auto;">

                    <div class="text-start mb-7">
                        <div class="text-muted fw-bold fs-6">
                            Lengkapi informasi master indikator mutu.
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-5">
                            <label class="required form-label fw-bold">Nama Indikator</label>
                            <input type="text" class="form-control form-control-solid" id="modal_indikator" name="modal_indikator" maxlength="1000" placeholder="Masukkan nama indikator mutu" required>
                        </div>

                        <div class="col-md-12 mb-5">
                            <label class="form-label fw-bold">Dasar Pemikiran</label>
                            <textarea class="form-control form-control-solid" id="modal_dasar_pemikiran" name="modal_dasar_pemikiran" rows="3" placeholder="Jelaskan latar belakang, regulasi, atau alasan pemilihan indikator."></textarea>
                        </div>

                        <div class="col-md-6 mb-5">
                            <label class="form-label fw-bold">Tujuan</label>
                            <textarea class="form-control form-control-solid" id="modal_tujuan" name="modal_tujuan" rows="3" placeholder="Jelaskan tujuan pengukuran indikator mutu."></textarea>
                        </div>

                        <div class="col-md-6 mb-5">
                            <label class="form-label fw-bold">Definisi Operasional</label>
                            <textarea class="form-control form-control-solid" id="modal_definisi" name="modal_definisi" rows="3" placeholder="Jelaskan definisi operasional indikator."></textarea>
                        </div>

                        <div class="col-md-6 mb-5">
                            <label class="form-label fw-bold">Numerator</label>
                            <input type="text" class="form-control form-control-solid" id="modal_numerator" name="modal_numerator" placeholder="Contoh: Jumlah pasien yang memenuhi indikator">
                        </div>

                        <div class="col-md-6 mb-5">
                            <label class="form-label fw-bold">Denominator</label>
                            <input type="text" class="form-control form-control-solid" id="modal_denumerator" name="modal_denumerator" placeholder="Contoh: Total pasien yang menjadi sasaran">
                        </div>

                        <div class="col-md-12 mb-5">
                            <label class="form-label fw-bold">Formula</label>
                            <input type="text" class="form-control form-control-solid" id="modal_formula" name="modal_formula" placeholder="Contoh: (Numerator / Denominator) × 100%">
                        </div>

                    </div>

                    <div class="separator separator-dashed my-8"></div>

                    <h5 class="fw-bold mb-5">Dimensi Mutu</h5>

                    <div class="row mb-5">

                        <div class="col-md-4">
                            <div class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" name="dimensi_keselamatan" value="Y">
                                <label class="form-check-label">Keselamatan Pasien</label>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" name="dimensi_waktu" value="Y">
                                <label class="form-check-label">Tepat Waktu</label>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" name="dimensi_efektif" value="Y">
                                <label class="form-check-label">Efektif</label>
                            </div>
                        </div>

                        <div class="col-md-4 mt-4">
                            <div class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" name="dimensi_efesien" value="Y">
                                <label class="form-check-label">Efisien</label>
                            </div>
                        </div>

                        <div class="col-md-4 mt-4">
                            <div class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" name="dimensi_pasien" value="Y">
                                <label class="form-check-label">Berfokus pada Pasien</label>
                            </div>
                        </div>

                        <div class="col-md-4 mt-4">
                            <div class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" name="dimensi_integrasi" value="Y">
                                <label class="form-check-label">Terintegrasi</label>
                            </div>
                        </div>

                    </div>

                    <div class="separator separator-dashed my-8"></div>

                    <div class="row">

                        <div class="col-md-6 mb-5">
                            <label class="form-label fw-bold">Populasi</label>
                            <input type="text" class="form-control form-control-solid" id="modal_populasi" name="modal_populasi" placeholder="Contoh: Seluruh pasien rawat inap">
                        </div>

                        <div class="col-md-6 mb-5">
                            <label class="form-label fw-bold">Metode Pengumpulan</label>
                            <input type="text" class="form-control form-control-solid" id="modal_metode" name="modal_metode" placeholder="Contoh: Observasi, Audit Rekam Medis, SIMRS">
                        </div>

                        <div class="col-md-6 mb-5">
                            <label class="form-label fw-bold">Kriteria Inklusi</label>
                            <textarea class="form-control form-control-solid" id="modal_inklusi" name="modal_inklusi" rows="3" placeholder="Masukkan kriteria inklusi."></textarea>
                        </div>

                        <div class="col-md-6 mb-5">
                            <label class="form-label fw-bold">Kriteria Eksklusi</label>
                            <textarea class="form-control form-control-solid" id="modal_eksklusi" name="modal_eksklusi" rows="3" placeholder="Masukkan kriteria eksklusi."></textarea>
                        </div>

                        <div class="col-md-12 mb-5">
                            <label class="form-label fw-bold">Instrumen</label>
                            <input type="text" class="form-control form-control-solid" id="modal_instrument" name="modal_instrument" placeholder="Contoh: Checklist Audit, Form Observasi, SIMRS">
                        </div>

                    </div>

                    <div class="separator separator-dashed my-8"></div>

                    <div class="row">

                        <div class="col-md-4 mb-5">
                            <label class="required form-label fw-bold">Satuan</label>
                            <select data-control="select2" data-dropdown-parent="#modal_edit_masterindikator" class="form-select form-select-solid" id="modal_satuan_id" name="modal_satuan_id" data-placeholder="Please Select Unit">
                                <?php echo $mastersatuan;?>
                            </select>
                        </div>

                        <div class="col-md-4 mb-5">
                            <label class="required form-label fw-bold">Frekuensi</label>
                            <select class="form-select form-select-solid" id="modal_frekuensi_id" name="modal_frekuensi_id">
                                <option value="">Pilih frekuensi pengukuran...</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-5">
                            <label class="required form-label fw-bold">Sumber Data</label>
                            <select class="form-select form-select-solid" id="modal_sumber_id" name="modal_sumber_id">
                                <option value="">Pilih sumber data...</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-5">
                            <label class="required form-label fw-bold">Donabedian</label>
                            <select class="form-select form-select-solid" id="modal_donabedian_id" name="modal_donabedian_id">
                                <option value="">Pilih kategori Donabedian...</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-5">
                            <label class="form-label fw-bold">Target Capaian</label>
                            <select class="form-select form-select-solid" id="modal_target_capaian" name="modal_target_capaian">
                                <option value="">Pilih target capaian...</option>
                                <option value="H">Higher is Better</option>
                                <option value="L">Lower is Better</option>
                                <option value="R">Range</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-5">
                            <label class="form-label fw-bold">Benchmark</label>
                            <select class="form-select form-select-solid" id="modal_benchmark_id" name="modal_benchmark_id">
                                <option value="">Pilih benchmark...</option>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-bold">Status</label>
                            <select class="form-select form-select-solid" id="modal_active" name="modal_active">
                                <option value="">Pilih status...</option>
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