/**
 * Select2 & native <select> inside Bootstrap scrollable modals:
 * - dropdownParent from data-dropdown-parent (KTApp.initSelect2 ignores this)
 * - focus trap no longer steals focus from Select2
 * - mousedown on Select2 does not bubble to the modal backdrop
 */
(function () {
    if (typeof jQuery === "undefined" || !jQuery.fn.select2) {
        return;
    }

    const $ = jQuery;

    function select2Options($el) {
        const options = {
            dir: document.body.getAttribute("direction") || "ltr"
        };
        const parentSelector = $el.attr("data-dropdown-parent");
        if (parentSelector) {
            const $parent = $(parentSelector);
            if ($parent.length) {
                options.dropdownParent = $parent;
            }
        }
        if ($el.attr("data-hide-search") === "true") {
            options.minimumResultsForSearch = Infinity;
        }
        const placeholder = $el.attr("data-placeholder");
        if (placeholder) {
            options.placeholder = placeholder;
            options.allowClear = $el.prop("multiple") || $el.find("option[value='']").length > 0;
        }
        return options;
    }

    function initSelect2InModal($modal) {
        $modal.find('[data-control="select2"], [data-kt-select2="true"]').each(function () {
            const $el = $(this);
            if ($el.hasClass("select2-hidden-accessible")) {
                $el.select2("destroy");
            }
            $el.select2(select2Options($el));
        });
    }

    document.addEventListener(
        "focusin",
        function (e) {
            if (e.target.closest(".select2-container") || e.target.closest(".select2-dropdown")) {
                e.stopImmediatePropagation();
            }
        },
        true
    );

    $(document).on("mousedown", ".select2-container, .select2-dropdown", function (e) {
        e.stopPropagation();
    });

    function patchSelect2AfterKtApp() {
        $(document)
            .find('[data-control="select2"][data-dropdown-parent], [data-kt-select2="true"][data-dropdown-parent]')
            .each(function () {
                const $el = $(this);
                if (!$el.hasClass("select2-hidden-accessible")) {
                    return;
                }
                $el.select2("destroy");
                $el.select2(select2Options($el));
            });
    }

    $(document).on("shown.bs.modal", ".modal", function () {
        initSelect2InModal($(this));
    });

    $(function () {
        patchSelect2AfterKtApp();
    });
})();
