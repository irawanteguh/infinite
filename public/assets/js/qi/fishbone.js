(function ($) {
    "use strict";
    const base = ($('#fishboneRoot').data('endpoint') || '') + '/';
    const indicatorRef = String($('#fishboneRoot').data('indicator-ref') || '');
    const indicatorDepartment = String($('#fishboneRoot').data('department') || '');
    const indicatorPeriod = String($('#fishboneRoot').data('period') || '');
    const mode = String($('#fishboneRoot').data('mode') || 'list');
    const initialAnalysisId = String($('#fishboneRoot').data('selected-id') || '');
    const categories = ['Man (SDM)', 'Method (Metode)', 'Machine (Mesin/Peralatan)', 'Material (Bahan)', 'Measurement (Pengukuran)', 'Environment (Lingkungan)'];
    const categoryLabels = {
        'Man (SDM)': 'Man', 'Method (Metode)': 'Method', 'Machine (Mesin/Peralatan)': 'Machine',
        'Material (Bahan)': 'Material', 'Measurement (Pengukuran)': 'Measurement', 'Environment (Lingkungan)': 'Environment'
    };
    const diagramColors = ['#009ef7', '#50cd89', '#7239ea', '#f1416c', '#ffc700', '#17c653'];
    const diagramTints = ['#f1faff', '#e8fff3', '#f8f5ff', '#fff5f8', '#fff8dd', '#effff3'];
    let selectedId = "";
    let analyses = [];
    let causes = [];
    const escapeHtml = value => $('<div>').text(value == null ? '' : String(value)).html();
    const modal = id => bootstrap.Modal.getOrCreateInstance(document.getElementById(id));
    function notify(icon, title, text) { Swal.fire({icon, title, text}); }
    function renderList() {
        const $rows = $('#fishboneRows').empty();
        $('#fishboneEmpty').toggle(analyses.length === 0);
        analyses.forEach(item => {
            const diagramUrl = `${base}diagram?fishbone_id=${encodeURIComponent(item.fishbone_id)}`;
            $rows.append(`<tr><td><span class="fw-bolder text-gray-800">${escapeHtml(item.problem)}</span></td><td>${escapeHtml(item.indikator || '-')}</td><td>${escapeHtml(item.department || '-')}</td><td>${escapeHtml(item.tahun || '-')}</td><td class="text-end text-nowrap"><a class="btn btn-sm btn-light-primary" href="${diagramUrl}">Pilih</a> <button type="button" class="btn btn-sm btn-icon btn-light-primary fishbone-edit" data-id="${escapeHtml(item.fishbone_id)}" title="Ubah"><i class="bi bi-pencil"></i></button> <button type="button" class="btn btn-sm btn-icon btn-light-danger fishbone-delete" data-id="${escapeHtml(item.fishbone_id)}" title="Hapus"><i class="bi bi-trash"></i></button></td></tr>`);
        });
    }
    function renderCauses() {
        const $list = $('#fishboneCauseList').empty();
        const color = ['primary','success','warning','info','danger','dark'];
        const priorityBadge = {urgent: 'danger', medium: 'warning', low: 'success'};
        const priorityLabel = {urgent: 'Urgent', medium: 'Sedang', low: 'Rendah'};
        categories.forEach((category, i) => {
            const items = causes.filter(c => c.category === category);
            const entries = items.length ? items.map(c => `<li class="d-flex align-items-start justify-content-between gap-2 mb-2"><span><span class="badge badge-light-${priorityBadge[c.priority] || 'warning'} me-2">${priorityLabel[c.priority] || 'Sedang'}</span>${escapeHtml(c.cause)}</span><span class="text-nowrap"><button class="btn btn-sm btn-icon btn-light fishbone-cause-edit" data-id="${escapeHtml(c.cause_id)}" title="Ubah"><i class="bi bi-pencil"></i></button> <button class="btn btn-sm btn-icon btn-light-danger fishbone-cause-delete" data-id="${escapeHtml(c.cause_id)}" title="Hapus"><i class="bi bi-x-lg"></i></button></span></li>`).join('') : '<li class="text-muted">Belum ada penyebab</li>';
            $list.append(`<div class="col-md-6"><div class="border border-${color[i]} border-dashed rounded p-4 h-100"><div class="fw-bolder text-${color[i]} mb-3">${escapeHtml(categoryLabels[category] || category)}</div><ul class="list-unstyled mb-0">${entries}</ul></div></div>`);
        });
        drawDiagram();
    }
    function drawDiagram() {
        const width = 1000, height = 470, spineY = 235, headX = 790;
        const headW = 118, headH = 88, headTop = spineY - headH / 2;
        let svg = `<svg viewBox="0 0 ${width} ${height}" role="img" aria-label="Diagram fishbone sebab akibat" class="w-100"><defs><marker id="fishboneArrow" markerWidth="10" markerHeight="10" refX="8" refY="4" orient="auto"><path d="M0,0 L0,8 L9,4 z" fill="#315b80"/></marker></defs><line x1="42" y1="${spineY}" x2="${headX - 1}" y2="${spineY}" stroke="#315b80" stroke-width="5" stroke-linecap="round" marker-end="url(#fishboneArrow)"/>`;
        const positions = [{x:120, top:true},{x:315, top:true},{x:510, top:true},{x:120, top:false},{x:315, top:false},{x:510, top:false}];
        categories.forEach((category, i) => {
            const p = positions[i], anchorY = p.top ? 88 : 382, branchX = p.x + 48;
            const color = diagramColors[i], tint = diagramTints[i];
            svg += `<line x1="${branchX}" y1="${anchorY}" x2="${p.x + 78}" y2="${spineY}" stroke="${color}" stroke-width="3.5" stroke-linecap="round"/><circle cx="${p.x + 78}" cy="${spineY}" r="6" fill="#fff" stroke="${color}" stroke-width="3"/><text x="${p.x + 46}" y="${p.top ? 58 : 425}" text-anchor="middle" fill="${color}" font-size="16" font-weight="700">${escapeHtml(categoryLabels[category] || category)}</text>`;
            const bucket = causes.filter(c => c.category === category).slice(0, 4);
            bucket.forEach((cause, j) => {
                const ty = p.top ? 106 + j * 22 : 282 + j * 22;
                const tx = p.x - 84;
                const label = cause.cause.length > 22 ? cause.cause.slice(0, 19) + '…' : cause.cause;
                svg += `<line x1="${tx + 110}" y1="${ty}" x2="${tx + 128}" y2="${ty}" stroke="${color}" stroke-width="2"/><foreignObject x="${tx - 20}" y="${ty - 11}" width="126" height="21"><div xmlns="http://www.w3.org/1999/xhtml" style="text-align:right;white-space:nowrap;overflow:hidden"><span title="${escapeHtml(cause.cause)}" style="display:inline-block;max-width:126px;overflow:hidden;text-overflow:ellipsis;font:600 10px Arial,sans-serif;color:${color};background:${tint};border:1px solid ${color};border-radius:12px;padding:3px 7px">${escapeHtml(label)}</span></div></foreignObject>`;
            });
        });
        const item = analyses.find(a => String(a.fishbone_id) === String(selectedId));
        const problem = item ? item.problem : 'Pilih analisis';
        svg += `<rect x="${headX}" y="${headTop}" width="${headW}" height="${headH}" rx="14" fill="#fff" stroke="#009ef7" stroke-width="3"/><foreignObject x="${headX + 6}" y="${headTop + 8}" width="${headW - 12}" height="${headH - 16}"><div xmlns="http://www.w3.org/1999/xhtml" style="font:700 13px Arial;color:#181c32;text-align:center;display:flex;align-items:center;justify-content:center;height:100%;overflow:hidden;line-height:1.25;padding:2px">${escapeHtml(problem)}</div></foreignObject></svg>`;
        $('#fishboneDiagram').html(svg);
    }
    function loadAnalysis(id) {
        $.post(base + 'fishbonedata', {fishbone_id:id}).done(res => {
            if (res.responseCode !== '00') return notify('error','Gagal memuat',res.responseDesc || 'Analisis tidak dapat dimuat.');
            selectedId = id; causes = res.responseCauses || [];
            if (mode === 'diagram') analyses = [res.responseResult];
            $('#fishboneDepartmentLabel').text(res.responseResult.department || ''); $('#fishboneAddCauseButton').prop('disabled',false);
            if (mode === 'list') renderList();
            renderCauses();
        }).fail(() => notify('error','Gagal memuat','Analisis Fishbone tidak dapat dimuat.'));
    }
    function loadList() {
        return $.post(base + 'analysislist', {indikator_ref_id: indicatorRef}).done(res => {
            if (res.responseCode !== '00') return notify('error','Gagal memuat',res.responseDesc || 'Daftar tidak dapat dimuat.');
            analyses = res.responseResult || []; selectedId = '';
            renderList();
            causes = [];
        }).fail(() => notify('error','Gagal memuat','Daftar Fishbone tidak dapat dimuat.'));
    }
    $(document).on('click','#fishboneAddButton',function(){ $('#fishboneAnalysisForm')[0].reset(); $('#fishboneId').val(''); $('#fishboneIndicatorRef').val(indicatorRef); if(indicatorRef){$('#fishboneDepartment').val(indicatorDepartment);$('#fishbonePeriod').val(indicatorPeriod);} $('#fishboneAnalysisTitle').text(indicatorRef ? 'Buat Fishbone dari Indikator' : 'Buat Analisis Fishbone'); modal('fishboneAnalysisModal').show(); });
    $(document).on('submit','#fishboneAnalysisForm',function(e){ e.preventDefault(); $.post(base+'saveanalysis',$(this).serialize()).done(res=>{ if(res.responseCode!=='00') return notify('error','Gagal menyimpan',res.responseDesc); modal('fishboneAnalysisModal').hide(); loadList(); }).fail(()=>notify('error','Request gagal','Analisis Fishbone tidak dapat disimpan.')); });
    $(document).on('click','.fishbone-edit',function(){const a=analyses.find(x=>String(x.fishbone_id)===String($(this).data('id'))); if(!a)return; $('#fishboneId').val(a.fishbone_id);$('#fishboneIndicatorRef').val(a.indikator_ref_id || '');$('#fishboneProblem').val(a.problem);$('#fishboneDepartment').val(a.department_id);$('#fishbonePeriod').val(a.periode_id);$('#fishboneAnalysisTitle').text('Ubah Analisis Fishbone');modal('fishboneAnalysisModal').show();});
    $(document).on('click','.fishbone-delete',function(){const id=$(this).data('id');Swal.fire({icon:'warning',title:'Hapus analisis?',text:'Seluruh penyebab pada analisis ini juga akan diarsipkan.',showCancelButton:true,confirmButtonText:'Hapus',cancelButtonText:'Batal'}).then(result=>{if(!result.isConfirmed)return;$.post(base+'deleteanalysis',{fishbone_id:id}).done(res=>{if(res.responseCode!=='00')return notify('error','Gagal menghapus',res.responseDesc);analyses=res.responseAnalyses||[];selectedId='';loadList();}).fail(()=>notify('error','Request gagal','Analisis tidak dapat dihapus.'));});});
    $(document).on('click','#fishboneAddCauseButton',function(){if(!selectedId)return;$('#fishboneCauseForm')[0].reset();$('#causeFishboneId').val(selectedId);$('#causeId').val('');$('#fishboneCauseTitle').text('Tambah Penyebab');modal('fishboneCauseModal').show();});
    $(document).on('click','.fishbone-cause-edit',function(){const c=causes.find(x=>String(x.cause_id)===String($(this).data('id')));if(!c)return;$('#causeFishboneId').val(selectedId);$('#causeId').val(c.cause_id);$('#causeCategory').val(c.category);$('#causeDescription').val(c.cause);$('#causePriority').val(c.priority || 'medium');$('#fishboneCauseTitle').text('Ubah Penyebab');modal('fishboneCauseModal').show();});
    $(document).on('submit','#fishboneCauseForm',function(e){e.preventDefault();$.post(base+'savecause',$(this).serialize()).done(res=>{if(res.responseCode!=='00')return notify('error','Gagal menyimpan',res.responseDesc);causes=res.responseCauses||[];renderCauses();modal('fishboneCauseModal').hide();}).fail(()=>notify('error','Request gagal','Penyebab tidak dapat disimpan.'));});
    $(document).on('click','.fishbone-cause-delete',function(){const causeId=$(this).data('id');Swal.fire({icon:'warning',title:'Hapus penyebab?',showCancelButton:true,confirmButtonText:'Hapus',cancelButtonText:'Batal'}).then(result=>{if(!result.isConfirmed)return;$.post(base+'deletecause',{fishbone_id:selectedId,cause_id:causeId}).done(res=>{if(res.responseCode!=='00')return notify('error','Gagal menghapus',res.responseDesc);causes=res.responseCauses||[];renderCauses();}).fail(()=>notify('error','Request gagal','Penyebab tidak dapat dihapus.'));});});
    $(function(){ if(mode === 'diagram' && initialAnalysisId){ loadAnalysis(initialAnalysisId); } else { loadList(); } });
})(jQuery);
