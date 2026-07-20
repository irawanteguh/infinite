function renderteam(picindikator, maxShow = 5) {
    const avatarDefault = `${url}assets/media/avatars/blank.png`;

    if (!picindikator) return "-";

    const arr = picindikator.split(";").filter(x => x.trim() !== "");

    let html = "<div class='symbol-group symbol-hover'>";

    arr.slice(0, maxShow).forEach(function(item){

        const data   = item.split(":");
        const userid = data[0] || "";
        const nama   = data[1] || "-";
        const avatar = `${url}assets/media/avatars/${userid}.jpg`;

        html += `
            <div class="symbol symbol-35px symbol-circle"
                 data-bs-toggle="tooltip"
                 title="${nama}">
                <img src="${avatar}"
                     alt="${nama}"
                     onerror="this.onerror=null;this.src='${avatarDefault}';">
            </div>
        `;
    });

    if (arr.length > maxShow) {
        html += `
            <div class="symbol symbol-35px symbol-circle">
                <span class="symbol-label bg-light-primary text-primary fw-bold">
                    +${arr.length - maxShow}
                </span>
            </div>
        `;
    }

    html += "</div>";

    return html;
}