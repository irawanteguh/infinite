datamastermdc();

function datamastermdc(){
    $.ajax({
        url      : url + "additional/viewmdc/datamastermdc",
        type     : "POST",
        dataType : "JSON",
        beforeSend: function () {
            Swal.fire({
                title            : 'Processing',
                html             : 'Please wait while the system displays the requested data.',
                allowOutsideClick: false,
                allowEscapeKey   : false,
                showConfirmButton: false,
                didOpen          : () => Swal.showLoading()
            });

            $("#viewdetailmdc").empty();
        },
        success: function (response) {

            if (!response || response.responseCode !== "00") {
                Swal.fire({
                    icon             : 'warning',
                    title            : 'No Records Found',
                    text             : 'No records are available for the selected period.',
                    showConfirmButton: false,
                    timer            : 2000
                });
                return;
            }

            
const result = Array.isArray(response.responseResult)
    ? response.responseResult
    : [];

const escapeHtml = function (value) {
    return String(value == null ? "" : value).replace(
        /[&<>"']/g,
        function (char) {
            return {
                "&": "&amp;",
                "<": "&lt;",
                ">": "&gt;",
                '"': "&quot;",
                "'": "&#039;"
            }[char];
        }
    );
};

const renderExplanation = function (value) {
    const text = String(value || "").trim();

    if (!text) {
        return `
            <div class="text-muted py-4 text-center">
                <i class="fas fa-info-circle mr-2"></i>
                Belum tersedia catatan pedoman untuk MDC ini.
            </div>`;
    }

    // Setiap baris diproses sebagai teks biasa untuk
    // mencegah HTML dari database dieksekusi.
    return text
        .split(/\r?\n/)
        .map(function (line) {
            const safeLine = escapeHtml(line.trim());

            if (!safeLine) {
                return "";
            }

            return `
                <p class="mdc-guideline-paragraph">
                    ${safeLine}
                </p>`;
        })
        .join("");
};

let viewResult = `
    <div class="card card-custom border-0 shadow-sm mb-5 mdc-reference">
        <div class="card-body p-5">
            <div class="d-flex align-items-center flex-wrap mb-5">
                <div class="mdc-reference-icon mr-4 mb-2">
                    <i class="fas fa-book-medical"></i>
                </div>

                <div class="flex-grow-1 mb-2">
                    <div class="text-muted font-size-sm font-weight-bold
                                text-uppercase mb-1">
                        iDRG · Clinical Coding Reference
                    </div>
                    <h3 class="font-weight-bolder mb-1">
                        Referensi Master MDC
                    </h3>
                    <div class="text-muted font-size-sm">
                        Pedoman pengodean diagnosis dan prosedur
                    </div>
                </div>

                <div class="mdc-total-badge mb-2">
                    ${result.length} MDC
                </div>
            </div>

            <div class="mdc-search-wrapper mb-5">
                <div class="input-group input-group-solid">
                    <div class="input-group-prepend">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                    </div>
                    <input
                        type="text"
                        id="searchMasterMdc"
                        class="form-control"
                        placeholder="Cari kode, nama MDC, atau isi pedoman..."
                        autocomplete="off">
                </div>
            </div>

            <div id="mdcReferenceList">
`;

result.forEach(function (item, index) {
    const code = escapeHtml(item.mdc_code);
    const nameId = escapeHtml(item.name_id);
    const nameEn = escapeHtml(item.name_en);
    const chapter = escapeHtml(item.chapter_no);
    const explanation = renderExplanation(item.explanation);

    const searchText = escapeHtml([
        item.mdc_code,
        item.name_id,
        item.name_en,
        item.chapter_no,
        item.explanation
    ].join(" "));

    viewResult += `
        <article
            class="mdc-item-card mb-4"
            data-search="${searchText}">

            <div class="mdc-item-header">
                <div class="d-flex align-items-start">
                    <div class="mdc-number mr-3">
                        ${code}
                    </div>

                    <div class="flex-grow-1 min-width-0">
                        <div class="d-flex flex-wrap align-items-center mb-2">
                            <span class="mdc-section-label mr-2">
                                MASTER MDC
                            </span>

                            <span class="text-muted font-size-xs">
                                Bab ${chapter || "-"}
                            </span>
                        </div>

                        <h4 class="mdc-name-id mb-1">
                            ${nameId || "Nama MDC tidak tersedia"}
                        </h4>

                        <div class="mdc-name-en">
                            ${nameEn || "-"}
                        </div>
                    </div>
                </div>
            </div>

            <div class="mdc-item-body">
                <div class="d-flex align-items-center mb-4">
                    <div class="mdc-guideline-icon mr-2">
                        <i class="fas fa-clipboard-list"></i>
                    </div>

                    <h5 class="font-weight-bolder mb-0">
                        Pedoman Pengodean
                    </h5>
                </div>

                <div class="mdc-guideline-content">
                    ${explanation}
                </div>
            </div>

            <div class="mdc-item-footer">
                <span class="text-muted font-size-xs">
                    <i class="fas fa-bookmark mr-1"></i>
                    Referensi MDC ${code}
                </span>

                <button
                    type="button"
                    class="btn btn-sm btn-light-primary btn-toggle-mdc"
                    aria-expanded="true">
                    <i class="fas fa-chevron-up mr-1"></i>
                    Sembunyikan pedoman
                </button>
            </div>
        </article>
    `;
});

viewResult += `
            </div>

            <div id="mdcSearchEmpty"
                 class="text-center py-10 d-none">
                <i class="fas fa-search fa-2x text-muted mb-3"></i>
                <h5 class="font-weight-bold">Data tidak ditemukan</h5>
                <div class="text-muted font-size-sm">
                    Coba gunakan kata kunci atau kode MDC lain.
                </div>
            </div>
        </div>
    </div>
`;

$("#viewdetailmdc").html(viewResult);

// Pencarian berdasarkan kode, nama, dan isi pedoman.
$("#searchMasterMdc")
    .off("input.mdc")
    .on("input.mdc", function () {
        const keyword = String($(this).val() || "")
            .toLocaleLowerCase("id-ID")
            .trim();

        let visibleCount = 0;

        $("#mdcReferenceList .mdc-item-card").each(function () {
            const searchable = String($(this).attr("data-search") || "")
                .toLocaleLowerCase("id-ID");

            const matches = searchable.includes(keyword);

            $(this).toggle(matches);

            if (matches) {
                visibleCount++;
            }
        });

        $("#mdcSearchEmpty").toggleClass(
            "d-none",
            visibleCount > 0
        );
    });

// Expand / collapse masing-masing pedoman.
$("#viewdetailmdc")
    .off("click.mdc", ".btn-toggle-mdc")
    .on("click.mdc", ".btn-toggle-mdc", function () {
        const $button = $(this);
        const $card = $button.closest(".mdc-item-card");
        const $body = $card.find(".mdc-item-body");
        const isExpanded = $button.attr("aria-expanded") === "true";

        $body.toggle(!isExpanded);
        $button.attr("aria-expanded", String(!isExpanded));

        $button.html(
            !isExpanded
                ? '<i class="fas fa-chevron-up mr-1"></i> Sembunyikan pedoman'
                : '<i class="fas fa-chevron-down mr-1"></i> Tampilkan pedoman'
        );
    });


        },
        complete: function () {
            Swal.close();
        },
        error: function () {
            Swal.fire({
                icon             : "error",
                title            : "Request Failed",
                text             : "An error occurred while processing your request.",
                confirmButtonText: "OK"
            });
        }
    });
};