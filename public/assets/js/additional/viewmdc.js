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

            
            const result = Array.isArray(response.responseResult) ? response.responseResult : [];

            // const renderExplanation = function (value) {
            //     const text = String(value || "").trim();

            //     if (!text) {
            //         return `
            //             <div class="text-muted py-4 text-center">
            //                 <i class="fas fa-info-circle mr-2"></i>
            //                 Belum tersedia catatan pedoman untuk MDC ini.
            //             </div>`;
            //     }

            //     return text
            //         .split(/\r?\n/)
            //         .map(function (line) {
            //             const safeLine = escapeHtml(line.trim());

            //             if (!safeLine) {
            //                 return "";
            //             }

            //             return `
            //                 <p class="mdc-guideline-paragraph">
            //                     ${safeLine}
            //                 </p>`;
            //         })
            //         .join("");
            // };

            let viewResult = `
                                <div class="card card-custom gutter-b shadow-sm">
                                    <div class="card-header border-0 py-5">
                                        <div class="card-title">
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <span class="font-size-sm font-weight-bold text-uppercase d-block mb-1">Indonesian Coding Standard (ICS)</span>
                                                    <h6 class="card-label font-weight-bold mb-1 text-muted">ICS Versi I Tahun 2025</h6>
                                                    <span class="font-size-sm text-muted">Referensi Standar Pengodean Diagnosis dan Prosedur iDRG</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-toolbar">
                                            <span class="badge badge-light-info font-weight-bold">${result.length} Major Diagnostic Category</span>
                                        </div>
                                    </div>

                                    <div class="card-body pt-0">
                                        <div class="separator separator-solid mb-5"></div>
                                        <div class="form-group mb-5">
                                            <label class="font-weight-bold text-muted mb-1">Pencarian Major Diagnostic Category</label>
                                            <div class="input-group input-group-solid">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-search text-muted"></i></span>
                                                </div>
                                                <input type="text" id="searchMasterMdc" class="form-control" placeholder="Cari kode, nama MDC, atau isi pedoman ICS..." autocomplete="off">
                                            </div>
                                            <span class="form-text text-muted">Cari berdasarkan kode MDC, nama, chapter, atau penjelasan dalam standar ICS.</span>
                                        </div>
                                        <div id="mdcReferenceList">
                            `;


            for (var i in result) {
                var item        = result[i];
                var collapseId  = 'kt_mdc_detail_' + item.id;
                var isFirst     = (parseInt(i, 10) === 0);
                var code        = esc(item.mdc_code || '-');
                var chapter     = esc(item.chapter_no || '-');
                var nameId      = esc(item.name_id || item.name_en || 'Nama MDC tidak tersedia');
                var nameEn      = esc(item.name_en || '-');
                var explanation = item.explanation ? esc(item.explanation) : 'Belum ada pedoman pengodean untuk MDC ini.';
                var example     = item.example ? esc(item.example): 'Belum ada contoh kasus untuk MDC ini.';
                var page        = item.page_end && item.page_end !== item.page_start ? item.page_start + ' - ' + item.page_end : (item.page_start || '-');

                var searchText = [
                    item.mdc_code,
                    item.name_id,
                    item.name_en,
                    item.chapter_no,
                    item.page_start,
                    item.page_end,
                    item.explanation,
                    item.example
                ].join(' ');

                viewResult += `
                    <div class="py-0 mdc-item" data-kt-mdc-row="${esc(item.id)}" data-search="${esc(searchText)}">

                        <div class="separator separator-dashed my-2 mdc-separator"></div>

                        <!-- HEADER MDC -->
                        <div class="py-4 d-flex align-items-center justify-content-between flex-wrap">

                            <div class="d-flex align-items-center flex-grow-1 min-w-0" role="button" data-bs-toggle="collapse" data-bs-target="#${collapseId}" aria-expanded="${isFirst ? 'true' : 'false'}" aria-controls="${collapseId}">

                                <div class="me-3">
                                    <i class="bi bi-chevron-right text-muted fs-5"></i>
                                </div>

                                <div class="symbol symbol-40px me-3 flex-shrink-0">
                                    <span class="symbol-label bg-light-primary text-primary fw-bolder">
                                        ${code}
                                    </span>
                                </div>

                                <div class="me-3 min-w-0">
                                    <div class="d-flex align-items-center flex-wrap">
                                        <h6 class="text-gray-800 fw-bolder mb-1 me-3">
                                            ${nameId}
                                        </h6>

                                        <span class="badge badge-light-primary mb-1">
                                            Bab ${chapter}
                                        </span>
                                    </div>

                                    <div class="text-muted fs-7">
                                        ${nameEn}
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center ms-auto mt-2 mt-md-0">
                                <span class="text-muted fs-7 me-4">
                                    <i class="bi bi-file-earmark-text me-1"></i>
                                    Hal. ${esc(page)}
                                </span>
                            </div>
                        </div>

                        <!-- DETAIL MDC -->
                        <div
                            id="${collapseId}"
                            class="collapse ${isFirst ? 'show' : ''}"
                            data-bs-parent="#mdcReferenceList">

                            <div class="ps-4 ps-md-10 pb-5">

                                <!-- RINGKASAN -->
                                <div class="d-flex flex-wrap mb-4">

                                    <div class="border border-gray-300 border-dashed rounded py-3 px-4 me-3 mb-3">
                                        <div class="fs-7 text-muted fw-bold mb-1">
                                            Kode MDC
                                        </div>
                                        <div class="fs-4 fw-bolder text-gray-800">
                                            ${code}
                                        </div>
                                    </div>

                                    <div class="border border-gray-300 border-dashed rounded py-3 px-4 me-3 mb-3">
                                        <div class="fs-7 text-muted fw-bold mb-1">
                                            Bab ICS
                                        </div>
                                        <div class="fs-4 fw-bolder text-gray-800">
                                            ${chapter}
                                        </div>
                                    </div>

                                    <div class="border border-gray-300 border-dashed rounded py-3 px-4 me-3 mb-3">
                                        <div class="fs-7 text-muted fw-bold mb-1">
                                            Halaman
                                        </div>
                                        <div class="fs-4 fw-bolder text-gray-800">
                                            ${esc(page)}
                                        </div>
                                    </div>

                                </div>

                                <!-- NAMA MDC   -->
                                <div class="bg-light rounded p-4 mb-4">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="bi bi-diagram-3 text-primary fs-4 me-3"></i>
                                        <span class="fw-bolder text-gray-800">
                                            Major Diagnostic Category
                                        </span>
                                    </div>

                                    <div class="text-gray-800 fw-bold mb-1">
                                        ${nameId}
                                    </div>

                                    <div class="text-muted fst-italic fs-7">
                                        ${nameEn}
                                    </div>
                                </div>

                                <!-- PEDOMAN PENGODEAN -->
                                <div class="bg-light-warning border-start border-4 border-warning rounded p-5 mb-5">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class='bi bi-exclamation-triangle-fill text-warning fs-3 me-3'></i>
                                        <div>
                                            <div class="fw-bolder text-gray-800 fs-6">Perhatian Khusus Penginputan iDRG</div>
                                            <div class="text-muted fs-7">Indonesian Coding Standard (ICS) Versi I Tahun 2025</div>
                                        </div>
                                    </div>
                                    <div class="text-gray-700 lh-lg">${formatExplanation(explanation)}</div>
                                </div>

                                <!-- CONTOH KASUS -->
                                <div class="bg-light-info border-start border-4 border-info rounded p-5">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="bi bi-info-circle-fill text-info fs-3 me-3"></i>
                                        <div>
                                            <div class="fw-bolder text-gray-800 fs-6">
                                                Contoh Kasus
                                            </div>
                                            <div class="text-muted fs-7">
                                                Ilustrasi penerapan pengodean berdasarkan MDC
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-gray-700 lh-lg">${formatExplanation(example)}</div>
                                </div>

                            </div>
                        </div>
                    </div>
                `;
            }

            $("#viewdetailmdc").html(viewResult);

            
var mdcSearchTimer;

$("#searchMasterMdc")
    .off("input.searchMdc")
    .on("input.searchMdc", function () {
        var keyword = String($(this).val() || "").trim();

        clearTimeout(mdcSearchTimer);

        mdcSearchTimer = setTimeout(function () {
            var searchKeyword = keyword.toLowerCase();
            var visibleCount = 0;

            $("#mdcReferenceList [data-kt-mdc-row]").each(function () {
                var row = $(this);
                var searchData = String(
                    row.attr("data-search") || ""
                ).toLowerCase();

                var isMatch = searchData.includes(searchKeyword);

                row.toggle(isMatch);

                var collapse = row.find(".collapse");

                if (isMatch && searchKeyword !== "") {
                    collapse.addClass("show");
                } else if (!isMatch || searchKeyword === "") {
                    collapse.removeClass("show");
                }

                if (isMatch) {
                    visibleCount++;
                }
            });

            // Highlight setelah proses filter selesai
            highlightMdcText(keyword);

            $("#mdcSearchEmpty").remove();

            if (visibleCount === 0) {
                $("#mdcReferenceList").append(`
                    <div id="mdcSearchEmpty" class="text-center py-10">
                        <i class="fas fa-search text-muted fs-2x"></i>
                        <div class="fw-bold text-gray-700">
                            MDC tidak ditemukan
                        </div>
                    </div>
                `);
            }
        }, 250);
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

function formatExplanation(text) {
    if (!text) return '-';

    var normalized = String(text)
        .replace(/\r\n?/g, '\n')
        .replace(/\*\*●\*\*/g, '●')
        .replace(/●/g, '\n●')
        .replace(/○/g, '\n○');

    var lines = normalized
        .split('\n')
        .map(function(line) {
            return line.trim();
        })
        .filter(Boolean);

    var items = [];
    var current = null;
    var pendingType = null;

    lines.forEach(function(line) {
        if (line === '●' || line === '○') {
            pendingType = line === '●' ? 'main' : 'sub';
            return;
        }

        if (line.startsWith('●') || line.startsWith('○')) {
            current = {
                type: line.startsWith('●') ? 'main' : 'sub',
                text: line.substring(1).trim(),
                children: []
            };

            items.push(current);
            pendingType = null;
            return;
        }

        if (pendingType) {
            current = {
                type: pendingType,
                text: line,
                children: []
            };

            items.push(current);
            pendingType = null;
            return;
        }

        if (current) {
            current.text += ' ' + line;
        } else {
            items.push({
                type: 'text',
                text: line,
                children: []
            });
        }
    });

    var result = [];
    var lastMain = null;

    items.forEach(function(item) {
        if (item.type === 'sub' && lastMain) {
            lastMain.children.push(item);
        } else {
            result.push(item);

            if (item.type === 'main') {
                lastMain = item;
            } else if (item.type !== 'sub') {
                lastMain = null;
            }
        }
    });

    return `
        <ul class="ps-5 my-0">
            ${result.map(function(item) {
                var content = esc(item.text);

                if (item.type === 'text') {
                    return `<li class="list-unstyled">${content}</li>`;
                }

                var subItems = item.children.map(function(sub) {
                    return `<li>${esc(sub.text)}</li>`;
                }).join('');

                return `
                    <li>
                        ${content}
                        ${subItems ? `
                            <ul class="ps-5 my-0">
                                ${subItems}
                            </ul>
                        ` : ''}
                    </li>`;
            }).join('')}
        </ul>`;
}



function highlightMdcText(keyword) {
    var container = $("#mdcReferenceList");

    // Hapus semua highlight lama dan kembalikan teks aslinya
    container.find(".mdc-highlight").each(function () {
        $(this).replaceWith(document.createTextNode($(this).text()));
    });

    if (!keyword || !keyword.trim()) {
        return;
    }

    var escapedKeyword = keyword.trim().replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    var regex = new RegExp(escapedKeyword, "gi");

    function walkTextNodes(node) {
        $(node).contents().each(function () {
            if (this.nodeType === 3) {
                var text = this.nodeValue;

                regex.lastIndex = 0;

                if (regex.test(text)) {
                    regex.lastIndex = 0;

                    var fragment = document.createDocumentFragment();
                    var lastIndex = 0;
                    var match;

                    while ((match = regex.exec(text)) !== null) {
                        fragment.appendChild(
                            document.createTextNode(
                                text.substring(lastIndex, match.index)
                            )
                        );

                        var highlight = document.createElement("span");
                        highlight.className =
                            "mdc-highlight bg-danger text-white fw-bold rounded px-1";
                        highlight.textContent = match[0];

                        fragment.appendChild(highlight);

                        lastIndex = match.index + match[0].length;
                    }

                    fragment.appendChild(
                        document.createTextNode(text.substring(lastIndex))
                    );

                    $(this).replaceWith(fragment);
                }
            } else if (
                this.nodeType === 1 &&
                !/^(SCRIPT|STYLE|INPUT|TEXTAREA|SELECT)$/i.test(this.tagName) &&
                !$(this).hasClass("mdc-highlight")
            ) {
                walkTextNodes(this);
            }
        });
    }

    container.find("[data-kt-mdc-row]:visible").each(function () {
        walkTextNodes(this);
    });
}
