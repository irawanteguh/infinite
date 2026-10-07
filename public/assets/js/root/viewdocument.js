$(document).on("click", ".btn-view-document", function () {

    const filename  = $(this).attr("data-filename");
    const page      = $(this).attr("data-page") || 1;
    const highlight = $(this).attr("data-highlight") || "";

    const url = "/infinite/assets/document/ics/" + filename + ".pdf#page=" + page + "&zoom=117";

    // console.log("File     :", filename);
    // console.log("Page     :", page);
    // console.log("Highlight:", highlight);

    $("#iframe_view_document")
        .hide()
        .attr("src", "")
        .attr("src", url)
        .off("load")
        .on("load", function () {
            $(this).show();
        });

});