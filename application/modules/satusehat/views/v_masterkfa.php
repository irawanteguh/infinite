<div class="card bgi-no-repeat bgi-position-x-end bgi-size-cover" style="background-color:#334155;background-size:auto 100%;background-image:url(<?= base_url('assets/media/misc/taieri.svg') ?>);">
    <div class="card-body container-xxl pt-10 pb-8">
        <h1 class="fw-bold text-white mb-5">Search Kamus Farmasi dan Alkes</h1>
        <div class="d-lg-flex align-items-lg-center">
            <div class="rounded bg-white d-flex flex-column flex-lg-row align-items-lg-center p-5 w-xxl-850px h-lg-60px me-lg-10">
                <div class="row flex-grow-1">
                    <div class="col-lg-8 d-flex align-items-center mb-3 mb-lg-0">
                        <span class="svg-icon svg-icon-1 svg-icon-gray-400 me-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <rect opacity="0.5" x="17.0365" y="15.1223" width="8.15546" height="2" rx="1" transform="rotate(45 17.0365 15.1223)" fill="black"></rect>
                                <path d="M11 19C6.55556 19 3 15.4444 3 11C3 6.55556 6.55556 3 11 3C15.4444 3 19 6.55556 19 11C19 15.4444 15.4444 19 11 19ZM11 5C7.53333 5 5 7.53333 5 11C5 14.4667 7.53333 17 11 17C14.4667 17 17 14.4667 17 11C17 7.53333 14.4667 5 11 5Z" fill="black"></path>
                            </svg>
                        </span>
                        <input type="text" class="form-control form-control-flush flex-grow-1" name="search" id="search" placeholder="Cari nama obat atau alkes">
                    </div>
                    <div class="col-lg-4 d-flex align-items-center">
                        <div class="bullet bg-secondary d-none d-lg-block h-30px w-2px me-5"></div>
                        <span class="svg-icon svg-icon-1 svg-icon-gray-400 me-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                <g fill="none" fill-rule="evenodd">
                                    <rect x="5" y="5" width="5" height="5" rx="1" fill="#000"></rect>
                                    <rect x="14" y="5" width="5" height="5" rx="1" fill="#000" opacity=".3"></rect>
                                    <rect x="5" y="14" width="5" height="5" rx="1" fill="#000" opacity=".3"></rect>
                                    <rect x="14" y="14" width="5" height="5" rx="1" fill="#000" opacity=".3"></rect>
                                </g>
                            </svg>
                        </span>
                        <select class="form-select border-0 flex-grow-1" data-control="select2" data-placeholder="Kategori" data-hide-search="true" id="type">
                            <option></option>
                            <option value="farmasi" selected>Farmasi</option>
                            <option value="alkes">Alkes</option>
                        </select>
                    </div>
                </div>
                <div class="min-w-150px text-end ms-lg-5">
                    <button type="submit" class="btn btn-dark" id="btnSearch">Search</button>
                </div>
            </div>
        </div>
    </div>
</div>

<br>

<div class="list-group" id="listkfa"></div>