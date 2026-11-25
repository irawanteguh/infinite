(function () {
    'use strict';
    const selector = '#dataraweklaim_table';
    if (!document.querySelector(selector)) {
        const form = document.querySelector('#review-filters');
        if (!form) return;

        $('#review-year').on('change', () => form.requestSubmit());
        $('#review-filters select').on('change', () => form.requestSubmit());
        const section = document.querySelector('#review-trends');
        if (section) setupReviewTrends(section);
        return;
    }
    function setupReviewTrends(section) {
        const status = section.querySelector('#review-trend-status');
        const chartGrid = section.querySelector('#review-trend-charts');
        const labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        const number = new Intl.NumberFormat('id-ID');
        const currentMonth = Number(section.dataset.currentMonth);

        const loadTrends = async () => {
            status.textContent = 'Menghitung tren dari data E-Klaim…';
            const counts = Array.from({length:12}, () => ({}));
            const endpoint = new URL(section.dataset.endpoint);
            endpoint.searchParams.set('year', section.dataset.year);
            endpoint.searchParams.set('service', section.dataset.service);
            try {
                const response = await fetch(endpoint, {
                    cache:'no-store', headers:{Accept:'application/json'}
                });
                if (!response.ok) throw new Error('Tren gagal dimuat.');
                const {months} = await response.json();
                if (!months || typeof months !== 'object') throw new Error('Hasil tren tidak valid.');
                for (let month = 1; month <= 12; month++) {
                    counts[month - 1] = months[month] || {};
                }

                const last = counts.reduce((latest, month, index) => month.episodes ? index : latest, -1);
                if (last < 0) {
                    status.textContent = 'Tidak ada data episode untuk tahun dan layanan ini.';
                    return;
                }

                chartGrid.hidden = false;
                const monthLabels = labels.map((label, index) => index + 1 === currentMonth ? label + '*' : label);
                for (const el of chartGrid.querySelectorAll('[data-trend-category]')) {
                    const key = el.dataset.trendCategory;
                    const values = counts.map(month => month.episodes ? (month[key] || 0) : null);
                    const change = chartGrid.querySelector('[data-trend-change="' + key + '"]');
                    if (last > 0 && values[last - 1] !== null) {
                        const delta = values[last] - values[last - 1];
                        const direction = delta < 0 ? 'Turun ' + number.format(-delta)
                            : delta > 0 ? 'Naik ' + number.format(delta) : 'Tetap';
                        change.textContent = labels[last - 1] + ' → ' + monthLabels[last] + ' · ' + direction
                            + ' (' + number.format(values[last - 1]) + ' → ' + number.format(values[last]) + ' SEP)';
                    } else {
                        change.textContent = 'Belum ada dua bulan berurutan untuk dibandingkan.';
                    }

                    const available = values.map((value, index) => value === null ? null
                        : monthLabels[index] + ' ' + number.format(value) + ' SEP').filter(Boolean);
                    el.replaceChildren();
                    el.setAttribute('aria-label', available.join(', '));
                    if (typeof ApexCharts === 'undefined') {
                        el.textContent = available.join(' · ');
                        continue;
                    }
                    const chart = new ApexCharts(el, {
                        chart:{type:'bar', height:200, toolbar:{show:false}, animations:{enabled:false}, fontFamily:'inherit'},
                        series:[{name:'SEP dengan temuan', data:values}],
                        xaxis:{categories:monthLabels}, yaxis:{min:0, decimalsInFloat:0, forceNiceScale:true},
                        colors:['#087e82'], plotOptions:{bar:{borderRadius:3}}, dataLabels:{enabled:false},
                        noData:{text:'Tidak ada data'}
                    });
                    await chart.render();
                }
                status.textContent = currentMonth ? '* Bulan berjalan. Data masih dapat bertambah.' : '';
            } catch (error) {
                chartGrid.hidden = true;
                status.textContent = 'Tren gagal dimuat. Muat ulang halaman untuk mencoba kembali.';
            }
        };
        loadTrends();
    }

    const nf = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 });
    const esc = value => String(value ?? '').replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[char]));
    const numeric = value => Number.isFinite(Number(value)) ? Number(value) : 0;
    const money = value => 'Rp ' + nf.format(numeric(value));
    const text = value => esc(value || '—');
    const codes = value => value ? esc(value).split(';').join(', ') : '—';
    let request = null, generation = 0, tableApi = null, refreshSummary = true;
    const status = (message, error = false) => $('#ek-status').text(message).toggleClass('is-error', error);

    function summary(value) {
        [['cbg', 'total_tarif'], ['idrg', 'idrg_total_tarif']].forEach(([id, key]) => {
            const difference = value ? numeric(value[key]) - numeric(value.tarif_rs) : 0;
            $('#ek-' + id + '-difference')
                .text(value ? 'Selisih terhadap tarif RS ' + (difference > 0 ? '+' : '') + money(difference) : '')
                .toggleClass('is-negative', !!value && difference < 0)
                .toggleClass('is-positive', !!value && difference > 0);
        });
        $('#ek-count').text(value ? nf.format(numeric(value.episodes)) : '—');
        [['hospital','tarif_rs'],['cbg','total_tarif'],['idrg','idrg_total_tarif']].forEach(([id, key]) => $('#ek-' + id).text(value ? money(value[key]) : '—'));
    }

    function service(row) {
        return String(row.ptd) === '1' ? '<span class="ek-service ek-service-inpatient">Rawat inap</span>' : String(row.ptd) === '2' ? '<span class="ek-service ek-service-outpatient">Rawat jalan</span>' : '—';
    }

    function details(row, index) {
        const field = (label, value) => '<div class="ek-detail-field"><dt>' + label + '</dt><dd>' + value + '</dd></div>';
        return '<div class="ek-detail" id="ek-detail-' + index + '"><div class="ek-detail-heading"><i class="bi bi-file-earmark-medical" aria-hidden="true"></i> Rincian episode · ' + text(row.nama_pasien) + '</div><dl class="ek-detail-grid">' +
            field('Nomor SEP', text(row.sep)) + field('Nomor kartu', text(row.nokartu)) + field('Nomor rekam medis', text(row.mrn)) + field('DPJP', text(row.dpjp)) +
            field('Tanggal masuk', text(row.admission_date)) + field('Tanggal pulang', text(row.discharge_date)) + field('Lama perawatan (LOS)', esc(row.los ?? '—') + ' hari') + field('Jenis layanan', service(row)) +
            field('Diagnosis', codes(row.diaglist)) + field('Prosedur', codes(row.proclist)) + '</dl></div>';
    }

    function loadPage(data, callback) {
        const current = ++generation;
        if (request) request.abort();
        const period = $('#selectperiode').val();
        const selectedService = $('#ek-service').val() || '';
        const serviceLabel = selectedService === '1' ? 'Rawat inap' : selectedService === '2' ? 'Rawat jalan' : 'Semua layanan';
        const fresh = refreshSummary;
        refreshSummary = false;
        $('#ek-refresh').prop('disabled', true);
        $(selector).attr('aria-busy', 'true');
        status('');
        request = $.ajax({
            beforeSend: function () {
                Swal.fire({
                    title: 'Menyiapkan Data E-Klaim',
                    html: '<div class="report-progress" role="status" aria-live="polite">' +
                        '<p class="ur-loading-description">Memuat rincian data E-Klaim.</p>' +
                        '<div class="progress report-progress-meter ur-loading-meter" aria-label="Data E-Klaim sedang dimuat">' +
                        '<div class="progress-bar"></div></div>' +
                        '<p class="ur-loading-caption">Sedang memproses data…</p></div>',
                    customClass: {popup: 'report-progress-popup ur-loading-popup'},
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false
                });
            },
            url:url + 'ur/dataeklaim/dataraweklaim', type:'POST', dataType:'json', timeout:45000,
            data:Object.assign({}, data, {selectperiode:period, service:selectedService, month:$('#ek-month').val() || '', refreshSummary:fresh ? '1' : '0'}),
            success:function(response) {
                if (current !== generation) return;
                if (!response || response.responseCode !== '00' || !Array.isArray(response.data)) {
                    fail('Respons data tidak dapat dibaca. Silakan muat ulang.'); return;
                }
                summary(response.summary);
                status('');
                callback(response);
            },
            error:function(xhr, state) {
                if (state === 'abort' || current !== generation) return;
                const message = xhr.responseJSON?.responseDesc || (state === 'timeout' ? 'Pemuatan data terlalu lama. Silakan coba muat ulang.' : xhr.status === 200 ? 'Sesi atau respons server tidak valid. Silakan masuk kembali.' : 'Gagal memuat data. Silakan coba muat ulang.');
                fail(message);
            },
            complete:function() {
                if (current !== generation) return;
                Swal.close();
                $(selector).attr('aria-busy', 'false');
                $('#ek-refresh').prop('disabled', false);
                request = null;
            }
        });
        function fail(message) {
            summary(null); refreshSummary = true;
            status(message, true);
            callback({draw:data.draw,recordsTotal:0,recordsFiltered:0,data:[]});
        }
    }

    function load() {
        refreshSummary = true;
        summary(null);
        if (tableApi) { tableApi.ajax.reload(null, true); return; }
        tableApi = initDataTable(selector, '#searchtable', 10, {
            serverSide:true, processing:false, deferRender:true, searchDelay:400,
            lengthMenu:[10,25,50,100], ordering:false,
            dom:"<'ek-datatable-scroll't><'app-table-footer'<'app-table-meta'li>p>",
            ajax:loadPage,
            initComplete:function() {
                $(this.api().table().container()).find('.ek-datatable-scroll').attr({tabindex:0,role:'region','aria-label':'Tabel e-klaim, geser untuk melihat seluruh kolom'});
            },
            columns:[
                {data:null,render:(value,type,row,meta)=>meta.settings._iDisplayStart + meta.row + 1},
                {data:'nama_pasien',render:value=>'<span class="ek-patient">' + text(value) + '</span>'},
                {data:'mrn',className:'ek-rm',render:value=>text(value)},
                {data:'sep',className:'ek-sep',render:value=>text(value)},
                {data:'admission_date',className:'ek-date',render:value=>text(value)},
                {data:'discharge_date',className:'ek-date',render:value=>text(value)},
                {data:'dpjp',className:'ek-dpjp',render:value=>text(value)},
                {data:null,render:(value,type,row)=>service(row)},
                {data:'tarif_rs',className:'text-end',render:value=>money(value)},
                {data:'total_tarif',className:'text-end',render:value=>money(value)},
                {data:'idrg_total_tarif',className:'text-end',render:value=>money(value)},
                {data:null,className:'text-end',searchable:false,orderable:false,render:(value,type,row,meta)=>'<button type="button" class="btn btn-sm btn-light-primary ek-toggle" aria-expanded="false" aria-controls="ek-detail-' + meta.row + '" aria-label="Lihat rincian ' + text(row.nama_pasien) + '"><i class="bi bi-chevron-down" aria-hidden="true"></i><span>Detail</span></button>'}
            ],
            language:{emptyTable:'Belum ada data pada periode ini.',zeroRecords:'Tidak ada data yang sesuai pencarian.',processing:'Memuat data…'}
        });
    }
    $(selector).on('click', '.ek-toggle', function () {
        if (!tableApi) return;
        const button = $(this), row = tableApi.row(button.closest('tr'));
        if (!row.data()) return;
        const opened = row.child.isShown();
        if (opened) row.child.hide();
        else row.child(details(row.data(), row.index()), 'ek-detail-row').show();
        button.attr('aria-controls', 'ek-detail-' + row.index()).attr('aria-expanded', String(!opened));
        button.find('span').text(opened ? 'Detail' : 'Tutup');
        button.find('i').toggleClass('bi-chevron-down', opened).toggleClass('bi-chevron-up', !opened);
        button.closest('tr').toggleClass('ek-row-open', !opened);
    });
    if ($.fn.select2) {
        $('#selectperiode,#ek-service,#ek-month').each(function () {
            const control = $(this);
            if (!control.data('select2')) {
                control.select2({width: '100%', minimumResultsForSearch: Infinity});
            }
        });
    }
    window.dataraweklaim = load;
    $('#selectperiode,#ek-service,#ek-month').on('change', load);
    $('#ek-refresh').on('click', load);
    const reviewLink = document.querySelector('#ek-review-link');
    if (reviewLink) {
        const reviewUrl = new URL(reviewLink.href);
        const updateReviewLink = () => {
            reviewUrl.search = new URLSearchParams({
                year: $('#selectperiode').val() || '',
                month: $('#ek-month').val() || '',
                service: $('#ek-service').val() || ''
            }).toString();
            reviewLink.href = reviewUrl.toString();
        };
        $('#selectperiode,#ek-service,#ek-month').on('change', updateReviewLink);
        updateReviewLink();
    }
    load();
})();
