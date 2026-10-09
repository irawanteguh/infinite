<div class="modal fade" id="modal_add_master_mdc" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header pb-0">
                <h1 class="mb-3" id="title_modal_master_mdc">Add Master MDC</h1>
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <span class="svg-icon svg-icon-1">
                        <i class="bi bi-x-lg"></i>
                    </span>
                </div>
            </div>
            <form action="<?php echo base_url('Ics/mastermdc/addmastermdc'); ?>" method="post" id="formaddmastermdc" autocomplete="off">
                <?= csrf_field() ?>
                <div class="modal-body">                        
                    <div class="row g-4 mb-4">
                        <!-- Dokumen Sumber -->
                        <div class="col-md-8">
                            <label class="form-label fw-bold required">Dokumen Sumber</label>
                            <select class="form-select" id="document_id_mdc" name="DOCUMENT_ID">
                                <?php echo $masterdocument;?>
                            </select>
                            <div class="form-text">Contoh: Indonesian Coding Standard (ICS) iDRG - V1</div>
                        </div>
                        <!-- Kode MDC -->
                        <div class="col-md-4">
                            <label class="form-label fw-bold required">Kode MDC</label>
                            <input type="text" class="form-control" id="mdc_code" name="MDC_CODE" maxlength="2" placeholder="11">
                            <div class="form-text">2 digit (10 - 36, 90)</div>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <!-- Nama Indonesia -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold required">Nama (Indonesia)</label>
                            <input type="text" class="form-control" id="name_id_mdc" name="NAME_ID" maxlength="200" placeholder="Sistem Syaraf">
                        </div>
                        <!-- Nama Inggris -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nama (English)</label>
                            <input type="text" class="form-control" id="name_en_mdc" name="NAME_EN" maxlength="200" placeholder="Diseases and Disorders of the Nervous System">
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <!-- Bab -->
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Bab</label>
                            <input type="text" class="form-control" id="chapter_no_mdc" name="CHAPTER_NO" maxlength="10" placeholder="3.1">
                        </div>
                        <!-- Halaman Awal -->
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Halaman Awal</label>
                            <input type="number" class="form-control" id="page_start_mdc" name="PAGE_START" min="1" placeholder="65">
                        </div>
                        <!-- Halaman Akhir -->
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Halaman Akhir</label>
                            <input type="number" class="form-control" id="page_end_mdc" name="PAGE_END" min="1" placeholder="79">
                        </div>
                    </div>

                    <!-- Perhatian Khusus -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Perhatian Khusus Penginputan iDRG</label>
                        <textarea class="form-control" id="perhatian_khusus_input" name="PERHATIAN_KHUSUS_INPUT" rows="5" placeholder="Isi blok &quot;PERHATIAN KHUSUS DALAM PENGINPUTAN iDRG MDC xx&quot; (satu butir per baris)"></textarea>
                    </div>

                    <!-- Contoh Kasus -->
                    <div class="mb-2">
                        <label class="form-label fw-bold">Contoh Kasus</label>
                        <textarea class="form-control" id="contoh_kasus_icd" name="CONTOH_KASUS_ICD" rows="5" placeholder="Isi contoh kasus sesuai ketentuan coding ICD dalam kategori MDC xx"></textarea>
                    </div>
                    
                </div>
                <div class="modal-footer p-1">
                    <input class="btn btn-light-primary" id="modal_add_master_mdc_btn" type="submit" value="SUBMIT" name="simpan" >
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_edit_master_mdc" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header pb-0">
                <h1 class="mb-3" id="title_modal_edit_master_mdc">Edit Master MDC</h1>
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <span class="svg-icon svg-icon-1">
                        <i class="bi bi-x-lg"></i>
                    </span>
                </div>
            </div>
            <form action="<?php echo base_url('Ics/mastermdc/updatemastermdc'); ?>" method="post" id="formeditmastermdc" autocomplete="off">
                <?= csrf_field() ?>
                <input type="hidden" id="id_mdc" name="ID">
                <div class="modal-body">
                    <div class="row g-4 mb-4">
                        <div class="col-md-8">
                            <label class="form-label fw-bold required">Dokumen Sumber</label>
                            <select class="form-select" id="edit_document_id" name="DOCUMENT_ID" required>
                                <?php echo $masterdocument; ?>
                            </select>
                            <div class="form-text">Contoh: Indonesian Coding Standard (ICS) iDRG - V1</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold required">Kode MDC</label>
                            <input type="text" class="form-control" id="edit_mdc_code" name="MDC_CODE" maxlength="2" placeholder="11" readonly required>
                            <div class="form-text">2 digit (10 - 36, 90)</div>
                        </div>
                    </div>
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold required">Nama (Indonesia)</label>
                            <input type="text" class="form-control" id="edit_name_id" name="NAME_ID" maxlength="200" placeholder="Sistem Syaraf" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nama (English)</label>
                            <input type="text" class="form-control" id="edit_name_en" name="NAME_EN" maxlength="200" placeholder="Diseases and Disorders of the Nervous System">
                        </div>
                    </div>
                    <div class="row g-4 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Bab</label>
                            <input type="text" class="form-control" id="edit_chapter_no" name="CHAPTER_NO" maxlength="10" placeholder="3.1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Halaman Awal</label>
                            <input type="number" class="form-control" id="edit_page_start" name="PAGE_START" min="1" placeholder="65">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Halaman Akhir</label>
                            <input type="number" class="form-control" id="edit_page_end" name="PAGE_END" min="1" placeholder="79">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Perhatian Khusus Penginputan iDRG</label>
                        <textarea class="form-control" id="edit_perhatian" name="PERHATIAN_KHUSUS_INPUT" rows="5" placeholder="Isi blok &quot;PERHATIAN KHUSUS DALAM PENGINPUTAN iDRG MDC xx&quot; (satu butir per baris)"></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold">Contoh Kasus</label>
                        <textarea class="form-control" id="edit_contoh" name="CONTOH_KASUS_ICD" rows="5" placeholder="Isi contoh kasus sesuai ketentuan coding ICD dalam kategori MDC xx"></textarea>
                    </div>
                </div>
                <div class="modal-footer p-1">
                    <button class="btn btn-light-primary" id="modal_edit_master_mdc_btn" type="submit" name="update" value="1">UPDATE</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_edit_master_rules" tabindex="-1" aria-labelledby="title_modal_edit_master_rules" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
        <div class="modal-content">
            <div class="modal-header pb-0">
                <h1 class="mb-3" id="title_modal_edit_master_rules">Edit Coding Rules</h1>
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <span class="svg-icon svg-icon-1">
                        <i class="bi bi-x-lg"></i>
                    </span>
                </div>
            </div>
            <form action="<?php echo base_url('Ics/mastermdc/updaterule'); ?>" method="post" id="formeditreules" autocomplete="off">
                <div class="modal-body">
                    <input type="hidden" id="coding_rule_id" name="ID">
                    <input type="hidden" id="coding_rule_section_id" name="SECTION_ID">
                    <div class="row g-5">
                        <div class="col-md-3">
                            <label for="coding_rule_type" class="form-label required fw-bold">Rule Type</label>
                            <select class="form-select form-select-solid" id="coding_rule_type" name="RULE_TYPE" required>
                                <option value="">Select Rule Type</option>
                                <option value="PRIMARY_DX">Primary Diagnosis</option>
                                <option value="SECONDARY_DX">Secondary Diagnosis</option>
                                <option value="CODE_ALSO">Code Also</option>
                                <option value="EXTERNAL_CAUSE">External Cause</option>
                                <option value="SELECT_BY_CONDITION">Select by Condition</option>
                                <option value="UNSPECIFIED_FALLBACK">Unspecified Fallback</option>
                                <option value="OMIT_CODE">Omit Code</option>
                                <option value="DO_NOT_CODE_SEPARATELY">Do Not Code Separately</option>
                                <option value="INPUT_PROCEDURE">Input Procedure</option>
                                <option value="DEFINITION">Definition</option>
                                <option value="NOTE">Note</option>
                                <option value="EXAMPLE">Example</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="coding_rule_mandatory" class="form-label fw-bold">Mandatory</label>
                            <select class="form-select form-select-solid" id="coding_rule_mandatory" name="IS_MANDATORY">
                                <option value="">Not Specified</option>
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="coding_rule_sex" class="form-label fw-bold">Sex Condition</label>
                            <select class="form-select form-select-solid" id="coding_rule_sex" name="SEX_CONDITION">
                                <option value="">All</option>
                                <option value="L">Male</option>
                                <option value="P">Female</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="coding_rule_age" class="form-label fw-bold">Age Condition</label>
                            <input type="text" class="form-control form-control-solid" id="coding_rule_age" name="AGE_CONDITION" maxlength="60" placeholder="e.g. Age >= 18 years">
                        </div>
                        <div class="col-12">
                            <label for="coding_rule_condition" class="form-label fw-bold required">Condition Text</label>
                            <textarea class="form-control form-control-solid" id="coding_rule_condition" name="CONDITION_TEXT" rows="4" required placeholder="Enter the condition for applying this rule"></textarea>
                        </div>
                        <div class="col-12">
                            <label for="coding_rule_instruction" class="form-label fw-bold">Instruction Text</label>
                            <textarea class="form-control form-control-solid" id="coding_rule_instruction" name="INSTRUCTION_TEXT" rows="4" placeholder="Enter the coding rule instruction"></textarea>
                        </div>
                        <div class="col-12">
                            <label for="coding_rule_source" class="form-label fw-bold">Source Text</label>
                            <textarea class="form-control form-control-solid" id="coding_rule_source" name="SOURCE_TEXT" rows="3" placeholder="Original text from the reference document"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="coding_rule_page_printed" class="form-label fw-bold">Printed Page</label>
                            <input type="number" class="form-control form-control-solid" id="coding_rule_page_printed" name="PAGE_PRINTED">
                        </div>
                        <div class="col-md-6">
                            <label for="coding_rule_page_pdf" class="form-label fw-bold">PDF Page</label>
                            <input type="number" class="form-control form-control-solid" id="coding_rule_page_pdf" name="PAGE_PDF">
                        </div>
                    </div>
                </div>
                <div class="modal-footer p-1">
                    <button class="btn btn-light-primary" id="modal_edit_master_rules_btn" type="submit" name="update">UPDATE</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_add_master_rules" tabindex="-1" aria-labelledby="title_modal_add_master_rules" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
        <div class="modal-content">
            <div class="modal-header pb-0">
                <h1 class="mb-3" id="title_modal_add_master_rules">Add Coding Rules</h1>
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <span class="svg-icon svg-icon-1">
                        <i class="bi bi-x-lg"></i>
                    </span>
                </div>
            </div>
            <form action="<?php echo base_url('Ics/mastermdc/insertmasterrule'); ?>" method="post" id="formaddrules" autocomplete="off">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <input type="hidden" id="add_coding_rule_id" name="ID">
                    <input type="hidden" id="add_coding_rule_section_id" name="SECTION_ID">
                    <div class="row g-5">
                        <div class="col-md-3">
                            <label for="add_coding_rule_type" class="form-label required fw-bold">Rule Type</label>
                            <select class="form-select form-select-solid" id="add_coding_rule_type" name="RULE_TYPE" required>
                                <option value="">Select Rule Type</option>
                                <option value="PRIMARY_DX">Primary Diagnosis</option>
                                <option value="SECONDARY_DX">Secondary Diagnosis</option>
                                <option value="CODE_ALSO">Code Also</option>
                                <option value="EXTERNAL_CAUSE">External Cause</option>
                                <option value="SELECT_BY_CONDITION">Select by Condition</option>
                                <option value="UNSPECIFIED_FALLBACK">Unspecified Fallback</option>
                                <option value="OMIT_CODE">Omit Code</option>
                                <option value="DO_NOT_CODE_SEPARATELY">Do Not Code Separately</option>
                                <option value="INPUT_PROCEDURE">Input Procedure</option>
                                <option value="DEFINITION">Definition</option>
                                <option value="NOTE">Note</option>
                                <option value="EXAMPLE">Example</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="add_coding_rule_mandatory" class="form-label fw-bold">Mandatory</label>
                            <select class="form-select form-select-solid" id="add_coding_rule_mandatory" name="IS_MANDATORY">
                                <option value="">Not Specified</option>
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="add_coding_rule_sex" class="form-label fw-bold">Sex Condition</label>
                            <select class="form-select form-select-solid" id="add_coding_rule_sex" name="SEX_CONDITION">
                                <option value="">All</option>
                                <option value="L">Male</option>
                                <option value="P">Female</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="add_coding_rule_age" class="form-label fw-bold">Age Condition</label>
                            <input type="text" class="form-control form-control-solid" id="add_coding_rule_age" name="AGE_CONDITION" maxlength="60" placeholder="e.g. Age >= 18 years">
                        </div>
                        <div class="col-12">
                            <label for="add_coding_rule_condition" class="form-label fw-bold required">Condition Text</label>
                            <textarea class="form-control form-control-solid" id="add_coding_rule_condition" name="CONDITION_TEXT" rows="4" required placeholder="Enter the condition for applying this rule"></textarea>
                        </div>
                        <div class="col-12">
                            <label for="add_coding_rule_instruction" class="form-label fw-bold">Instruction Text</label>
                            <textarea class="form-control form-control-solid" id="add_coding_rule_instruction" name="INSTRUCTION_TEXT" rows="4" placeholder="Enter the coding rule instruction"></textarea>
                        </div>
                        <div class="col-12">
                            <label for="add_coding_rule_source" class="form-label fw-bold">Source Text</label>
                            <textarea class="form-control form-control-solid" id="add_coding_rule_source" name="SOURCE_TEXT" rows="3" placeholder="Original text from the reference document"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="add_coding_rule_page_printed" class="form-label fw-bold">Printed Page</label>
                            <input type="number" class="form-control form-control-solid" id="add_coding_rule_page_printed" name="PAGE_PRINTED" min="1">
                        </div>
                        <div class="col-md-6">
                            <label for="add_coding_rule_page_pdf" class="form-label fw-bold">PDF Page</label>
                            <input type="number" class="form-control form-control-solid" id="add_coding_rule_page_pdf" name="PAGE_PDF" min="1">
                        </div>
                    </div>
                </div>
                <div class="modal-footer p-1">
                    <input class="btn btn-light-primary" id="modal_add_master_rules_btn" type="submit" value="SUBMIT" name="simpan" >
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_add_rule_notes" tabindex="-1"
    aria-labelledby="title_modal_add_rule_notes" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">

            <!-- HEADER -->
            <div class="modal-header pb-0">
                <h1 class="mb-3 fs-3" id="title_modal_add_rule_notes">
                    Add Notes
                </h1>

                <button type="button"
                    class="btn btn-sm btn-icon btn-active-color-primary"
                    data-bs-dismiss="modal"
                    aria-label="Close">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <!-- FORM -->
            <form id="formaddrulenotes" autocomplete="off">
                <div class="modal-body">

                    <input type="hidden" id="notes_rule_id" name="RULE_ID">
                    <input type="hidden" id="notes_section_id" name="SECTION_ID">

                    <!-- RULE INFORMATION -->
                    <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed p-4 mb-5">
                        <i class="bi bi-info-circle fs-2x text-primary me-4"></i>

                        <div class="d-flex flex-column">
                            <h5 class="mb-1 text-gray-900">Coding Rule</h5>
                            <span class="text-gray-700 fs-7" id="notes_rule_condition">
                                Select a coding rule to add notes.
                            </span>
                        </div>
                    </div>

                    <!-- NOTES -->
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <label class="form-label fw-bold mb-0">
                            Notes Details
                        </label>

                        <button type="button"
                            class="btn btn-sm btn-light-primary"
                            id="btn_add_note_item">
                            <i class="bi bi-plus-lg me-1"></i>
                            Add Item
                        </button>
                    </div>

                    <div id="rule_notes_container">

                        <div class="rule-note-item mb-3">
                            <div class="d-flex align-items-start gap-3">

                                <span class="badge badge-light-primary mt-3">1</span>

                                <div class="flex-grow-1">
                                    <textarea
                                        class="form-control form-control-solid rule-note-text"
                                        name="NOTES[]"
                                        rows="2"
                                        placeholder="Enter note details..."
                                        required></textarea>
                                </div>

                                <button type="button"
                                    class="btn btn-sm btn-icon btn-light-danger btn-remove-note mt-1"
                                    title="Remove note">
                                    <i class="bi bi-trash"></i>
                                </button>

                            </div>
                        </div>

                    </div>

                    <div class="text-muted fs-7 mt-3">
                        Each item will be displayed as a separate list item (li).
                    </div>

                </div>

                <!-- FOOTER -->
                <div class="modal-footer p-3">
                    <button type="button"
                        class="btn btn-light"
                        data-bs-dismiss="modal">
                        CANCEL
                    </button>

                    <button type="submit"
                        class="btn btn-light-primary"
                        id="modal_add_rule_notes_btn">
                        <i class="bi bi-check-lg me-1"></i>
                        SAVE NOTES
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>
