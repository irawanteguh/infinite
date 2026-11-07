(function ($) {
    "use strict";
    const base = ($('#fishboneRoot').data('endpoint') || '') + '/';
    const indicatorRef = String($('#fishboneRoot').data('indicator-ref') || '');
    const indicatorDepartment = String($('#fishboneRoot').data('department') || '');
    const indicatorPeriod = String($('#fishboneRoot').data('period') || '');
    const categories = ['Man (SDM)', 'Method (Metode)', 'Machine (Mesin/Peralatan)', 'Material (Bahan)', 'Measurement (Pengukuran)', 'Environment (Lingkungan)'];
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
            const active = String(item.fishbone_id) === String(selectedId);
            $rows.append(`<tr class="${active ? 'bg-light-primary' : ''}"><td><span class="fw-bolder text-gray-800">${escapeHtml(item.problem)}</span>${item.indikator ? `<div class="text-muted fs-8">${escapeHtml(item.indikator)}</div>` : ''}</td><td>${escapeHtml(item.department || '-')}</td><td>${escapeHtml(item.tahun || '-')}</td><td class="text-end text-nowrap"><button type="button" class="btn btn-sm ${active ? 'btn-primary' : 'btn-light-primary'} fishbone-select" data-id="${escapeHtml(item.fishbone_id)}">${active ? 'Dipilih' : 'Pilih'}</button> <button type="button" class="btn btn-sm btn-icon btn-light-primary fishbone-edit" data-id="${escapeHtml(item.fishbone_id)}" title="Ubah"><i class="bi bi-pencil"></i></button> <button type="button" class="btn btn-sm btn-icon btn-light-danger fishbone-delete" data-id="${escapeHtml(item.fishbone_id)}" title="Hapus"><i class="bi bi-trash"></i></button></td></tr>`);
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
            $list.append(`<div class="col-md-6"><div class="border border-${color[i]} border-dashed rounded p-4 h-100"><div class="fw-bolder text-${color[i]} mb-3">${escapeHtml(category)}</div><ul class="list-unstyled mb-0">${entries}</ul></div></div>`);
        });
        drawDiagram();
    }
    function drawDiagram() {
        const width = 1000, height = 440, spineY = 220, headX = 880;
        let svg = `<svg viewBox="0 0 ${width} ${height}" role="img" aria-label="Diagram fishbone sebab akibat" class="w-100"><defs><marker id="fishboneArrow" markerWidth="10" markerHeight="10" refX="8" refY="3" orient="auto"><path d="M0,0 L0,6 L9,3 z" fill="#7e8299"/></marker></defs><line x1="42" y1="${spineY}" x2="${headX}" y2="${spineY}" stroke="#7e8299" stroke-width="4" marker-end="url(#fishboneArrow)"/>`;
        const positions = [{x:170, top:true},{x:405, top:true},{x:640, top:true},{x:170, top:false},{x:405, top:false},{x:640, top:false}];
        categories.forEach((category, i) => {
            const p = positions[i], anchorY = p.top ? 85 : 355, branchX = p.x + 56;
            svg += `<line x1="${branchX}" y1="${anchorY}" x2="${p.x + 116}" y2="${spineY}" stroke="#a1a5b7" stroke-width="3"/><text x="${p.x}" y="${p.top ? 28 : 414}" fill="#181c32" font-size="16" font-weight="700">${escapeHtml(category)}</text>`;
            const bucket = causes.filter(c => c.category === category).slice(0, 4);
            bucket.forEach((cause, j) => {
                const ty = p.top ? 105 + j * 18 : 275 + j * 18;
                const branchAtY = p.top
                    ? branchX + ((ty - anchorY) / (spineY - anchorY)) * 60
                    : p.x + 116 - ((ty - spineY) / (anchorY - spineY)) * 60;
                const tx = branchAtY - 12;
                const label = cause.cause.length > 22 ? cause.cause.slice(0, 19) + '…' : cause.cause;
                const badgeColor = {urgent: 'danger', medium: 'warning', low: 'success'}[cause.priority] || 'warning';
                svg += `<foreignObject x="${tx - 135}" y="${ty - 15}" width="130" height="20"><div xmlns="http://www.w3.org/1999/xhtml" style="text-align:right;white-space:nowrap;overflow:hidden"><span class="badge badge-light-${badgeColor}" title="${escapeHtml(cause.cause)}" style="display:inline-block;max-width:130px;overflow:hidden;text-overflow:ellipsis;font-size:10px">${escapeHtml(label)}</span></div></foreignObject>`;
            });
        });
        const item = analyses.find(a => String(a.fishbone_id) === String(selectedId));
        const problem = item ? item.problem : 'Pilih analisis';
        svg += `<path d="M880 180 L962 180 L990 220 L962 260 L880 260 Z" fill="#f1faff" stroke="#009ef7" stroke-width="3"/><foreignObject x="886" y="188" width="76" height="64"><div xmlns="http://www.w3.org/1999/xhtml" style="font:700 12px Arial;color:#181c32;text-align:center;display:flex;align-items:center;height:100%;overflow:hidden">${escapeHtml(problem)}</div></foreignObject></svg>`;
        $('#fishboneDiagram').html(svg);
    }
    function loadAnalysis(id) {
        $.post(base + 'fishbonedata', {fishbone_id:id}).done(res => {
            if (res.responseCode !== '00') return notify('error','Gagal memuat',res.responseDesc || 'Analisis tidak dapat dimuat.');
            selectedId = id; causes = res.responseCauses || [];
            $('#fishboneDepartmentLabel').text(res.responseResult.department || ''); $('#fishboneAddCauseButton').prop('disabled',false);
            renderList(); renderCauses();
        }).fail(() => notify('error','Gagal memuat','Analisis Fishbone tidak dapat dimuat.'));
    }
    function loadList(selectId) {
        return $.post(base + 'analysislist', {indikator_ref_id: indicatorRef}).done(res => {
            if (res.responseCode !== '00') return notify('error','Gagal memuat',res.responseDesc || 'Daftar tidak dapat dimuat.');
            analyses = res.responseResult || []; selectedId = selectId || (analyses[0] && analyses[0].fishbone_id) || '';
            renderList();
            if (selectedId) loadAnalysis(selectedId); else { causes = []; $('#fishboneDiagram,#fishboneCauseList').empty(); $('#fishboneAddCauseButton').prop('disabled',true); $('#fishboneDepartmentLabel').text('Pilih analisis dari daftar'); }
        }).fail(() => notify('error','Gagal memuat','Daftar Fishbone tidak dapat dimuat.'));
    }
    $(document).on('click','#fishboneAddButton',function(){ $('#fishboneAnalysisForm')[0].reset(); $('#fishboneId').val(''); $('#fishboneIndicatorRef').val(indicatorRef); if(indicatorRef){$('#fishboneDepartment').val(indicatorDepartment);$('#fishbonePeriod').val(indicatorPeriod);} $('#fishboneAnalysisTitle').text(indicatorRef ? 'Buat Fishbone dari Indikator' : 'Buat Analisis Fishbone'); modal('fishboneAnalysisModal').show(); });
    $(document).on('submit','#fishboneAnalysisForm',function(e){ e.preventDefault(); $.post(base+'saveanalysis',$(this).serialize()).done(res=>{ if(res.responseCode!=='00') return notify('error','Gagal menyimpan',res.responseDesc); analyses=res.responseAnalyses||[]; selectedId=res.responseResult; renderList(); modal('fishboneAnalysisModal').hide(); loadAnalysis(selectedId); }).fail(()=>notify('error','Request gagal','Analisis Fishbone tidak dapat disimpan.')); });
    $(document).on('click','.fishbone-select',function(){loadAnalysis($(this).data('id'));});
    $(document).on('click','.fishbone-edit',function(){const a=analyses.find(x=>String(x.fishbone_id)===String($(this).data('id'))); if(!a)return; $('#fishboneId').val(a.fishbone_id);$('#fishboneIndicatorRef').val(a.indikator_ref_id || '');$('#fishboneProblem').val(a.problem);$('#fishboneDepartment').val(a.department_id);$('#fishbonePeriod').val(a.periode_id);$('#fishboneAnalysisTitle').text('Ubah Analisis Fishbone');modal('fishboneAnalysisModal').show();});
    $(document).on('click','.fishbone-delete',function(){const id=$(this).data('id');Swal.fire({icon:'warning',title:'Hapus analisis?',text:'Seluruh penyebab pada analisis ini juga akan diarsipkan.',showCancelButton:true,confirmButtonText:'Hapus',cancelButtonText:'Batal'}).then(result=>{if(!result.isConfirmed)return;$.post(base+'deleteanalysis',{fishbone_id:id}).done(res=>{if(res.responseCode!=='00')return notify('error','Gagal menghapus',res.responseDesc);analyses=res.responseAnalyses||[];selectedId='';loadList();}).fail(()=>notify('error','Request gagal','Analisis tidak dapat dihapus.'));});});
    $(document).on('click','#fishboneAddCauseButton',function(){if(!selectedId)return;$('#fishboneCauseForm')[0].reset();$('#causeFishboneId').val(selectedId);$('#causeId').val('');$('#fishboneCauseTitle').text('Tambah Penyebab');modal('fishboneCauseModal').show();});
    $(document).on('click','.fishbone-cause-edit',function(){const c=causes.find(x=>String(x.cause_id)===String($(this).data('id')));if(!c)return;$('#causeFishboneId').val(selectedId);$('#causeId').val(c.cause_id);$('#causeCategory').val(c.category);$('#causeDescription').val(c.cause);$('#causePriority').val(c.priority || 'medium');$('#fishboneCauseTitle').text('Ubah Penyebab');modal('fishboneCauseModal').show();});
    $(document).on('submit','#fishboneCauseForm',function(e){e.preventDefault();$.post(base+'savecause',$(this).serialize()).done(res=>{if(res.responseCode!=='00')return notify('error','Gagal menyimpan',res.responseDesc);causes=res.responseCauses||[];renderCauses();modal('fishboneCauseModal').hide();}).fail(()=>notify('error','Request gagal','Penyebab tidak dapat disimpan.'));});
    $(document).on('click','.fishbone-cause-delete',function(){const causeId=$(this).data('id');Swal.fire({icon:'warning',title:'Hapus penyebab?',showCancelButton:true,confirmButtonText:'Hapus',cancelButtonText:'Batal'}).then(result=>{if(!result.isConfirmed)return;$.post(base+'deletecause',{fishbone_id:selectedId,cause_id:causeId}).done(res=>{if(res.responseCode!=='00')return notify('error','Gagal menghapus',res.responseDesc);causes=res.responseCauses||[];renderCauses();}).fail(()=>notify('error','Request gagal','Penyebab tidak dapat dihapus.'));});});
    $(function(){loadList();});
})(jQuery);
