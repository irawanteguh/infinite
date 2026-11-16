(function () {
    'use strict';
    const root = document.getElementById('hr-access');
    if (!root) return;
    const byId = id => document.getElementById(id);
    const userSelect = byId('access-user');
    const escape = value => String(value ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const review = bootstrap.Modal.getOrCreateInstance(byId('access-review-modal'), {backdrop:'static'});
    let users = [], modules = [], detail = null, selected = new Set(), baseline = new Set();
    let loading = false, saving = false, loadNumber = 0, csrf = root.dataset.csrfHash;
    const changed = () => modules.filter(m => selected.has(m.modules_id) !== baseline.has(m.modules_id));
    const filtered = () => {
        const search = byId('access-search').value.trim().toLocaleLowerCase('id');
        const filter = byId('access-filter').value;
        return modules.filter(m => (!search || (m.modules_name+' '+m.category+' '+m.package).toLocaleLowerCase('id').includes(search))
            && (filter === 'all' || (filter === 'allowed') === selected.has(m.modules_id)));
    };
    function notify(message, error = false) {
        const box = byId('access-message');
        box.className = 'alert '+(error?'alert-danger':'alert-success'); box.textContent = message;
    }
    async function request(action, payload) {
        const options = {credentials:'same-origin',headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}};
        if (payload) { payload.set(root.dataset.csrfName,csrf); options.method='POST'; options.body=payload; }
        const response = await fetch(root.dataset.endpoint+'/'+action,options);
        if (response.redirected || !(response.headers.get('content-type')||'').includes('application/json')) throw new Error('Sesi berakhir atau respons tidak valid. Muat ulang halaman.');
        const result = await response.json();
        if (result.csrfHash) csrf=result.csrfHash;
        if (!response.ok || result.responseCode !== '00') throw new Error(result.responseDesc || 'Data akses belum dapat diproses.');
        return result;
    }
    function controls() {
        const busy=loading||saving, edits=changed().length;
        root.setAttribute('aria-busy',String(busy));
        userSelect.disabled=busy;
        byId('access-reload').disabled=busy;
        ['access-search','access-filter','access-check-visible','access-clear-visible'].forEach(id=>{byId(id).disabled=busy||!detail;});
        byId('access-save').disabled=busy||!detail||!edits;
        byId('access-reset').disabled=busy||!detail||!edits;
        byId('access-confirm').disabled=busy;
        byId('access-review-cancel').disabled=saving;
        byId('access-review-modal').querySelector('.btn-close').disabled=saving;
        byId('access-confirm').textContent=saving?'Menyimpan…':'Simpan perubahan';
        byId('access-save-label').textContent=edits?edits+' perubahan belum disimpan':'Belum ada perubahan';
        byId('access-change-count').textContent=detail?(edits?edits+' perubahan belum disimpan':'Sesuai akses tersimpan'):'Pilih pengguna untuk mulai mengatur akses';
        byId('access-selected-count').textContent=detail?modules.filter(m=>selected.has(m.modules_id)).length:'—';
        byId('access-editor-status').textContent=busy?'MEMUAT':detail?(edits?'BELUM DISIMPAN':'TERSIMPAN'):'BELUM ADA PENGGUNA';
        document.querySelectorAll('#access-modules input').forEach(input=>{input.disabled=busy;});
    }
    function render() {
        const list=filtered();
        byId('access-visible-count').textContent=detail?list.length+' dari '+modules.length+' modul ditampilkan':'—';
        if (detail) {
            const groups=new Map();
            list.forEach(m=>{if(!groups.has(m.category))groups.set(m.category,[]);groups.get(m.category).push(m);});
            byId('access-modules').innerHTML=list.length?Array.from(groups,([category,items])=>'<section class="access-group"><div class="access-group-head"><h3>'+escape(category)+'</h3><span>'+items.filter(m=>selected.has(m.modules_id)).length+' / '+items.length+' diizinkan</span></div>'+items.map(m=>{
                const allowed=selected.has(m.modules_id),dirty=allowed!==baseline.has(m.modules_id);
                return '<label class="access-module '+(allowed?'is-allowed ':'')+(dirty?'is-changed':'')+'"><input type="checkbox" class="form-check-input" data-module-id="'+escape(m.modules_id)+'" '+(allowed?'checked':'')+'><span class="access-module-copy"><strong>'+escape(m.modules_name)+'</strong><small>'+escape(m.package+'/'+m.def_controller)+'</small></span><span class="access-module-status">'+(allowed?'Diizinkan':'Belum diizinkan')+'</span></label>';
            }).join('')+'</section>').join(''):'<div class="access-empty"><i class="bi bi-search" aria-hidden="true"></i><h3>Tidak ada modul yang sesuai</h3><p>Ubah kata pencarian atau filter untuk melihat modul lainnya.</p></div>';
        } else {
            byId('access-modules').innerHTML='<div class="access-empty"><i class="bi bi-person-lock" aria-hidden="true"></i><h3>Pilih pengguna terlebih dahulu</h3><p>Daftar modul dan akses tersimpan akan ditampilkan di sini.</p></div>';
        }
        controls();
    }
    function applyDetail(value) {
        detail=value;selected=new Set(value.moduleIds);baseline=new Set(value.moduleIds);
        byId('access-user-info').classList.remove('d-none');
        byId('access-initial').textContent=(value.user.name||value.user.username).slice(0,1).toUpperCase();
        byId('access-person-name').textContent=value.user.name||value.user.username;
        byId('access-person-username').textContent='@'+value.user.username;
        byId('access-department').textContent=users.find(u=>u.user_id===value.user.user_id)?.department || 'Departemen belum diisi';
        const suspended=['Y','1'].includes(String(value.user.suspend).toUpperCase());
        byId('access-person-badges').innerHTML=(value.manager?'<span class="badge badge-light-success">Pengelola akses</span>':'')+(suspended?'<span class="badge badge-light-warning">Akun disuspend</span>':'<span class="badge badge-light-success">Akun aktif</span>');
        byId('access-updated').textContent=value.updated?value.updated.created_at+' · '+(value.updated.updated_by||'Pengelola akses'):'Belum pernah diatur';
        render();
    }
    async function loadUser(id) {
        const sequence=++loadNumber;loading=true;controls();
        try {
            const result=await request('detail?user_id='+encodeURIComponent(id));
            if(sequence!==loadNumber)return;
            applyDetail(result.detail);
        } catch(error) { detail=null;selected=new Set();baseline=new Set();byId('access-user-info').classList.add('d-none');notify(error.message,true);render(); }
        finally {if(sequence===loadNumber){loading=false;controls();}}
    }
    async function discardAllowed() {
        if(!changed().length)return true;
        const result=await Swal.fire({title:'Abaikan perubahan?',text:'Perubahan akses yang belum disimpan akan dibatalkan.',icon:'question',showCancelButton:true,confirmButtonText:'Abaikan perubahan',cancelButtonText:'Lanjut mengatur',confirmButtonColor:'#087e82'});
        return result.isConfirmed;
    }
    async function reload() {
        if(loading||saving||!await discardAllowed())return;
        const prior=detail?.user.user_id;loading=true;controls();
        try {
            const result=await request('data');users=result.users;modules=result.modules;
            userSelect.replaceChildren(new Option('Pilih pengguna',''));
            users.forEach(u=>userSelect.add(new Option((u.name||u.username)+' · @'+u.username,u.user_id)));
            byId('access-user-count').textContent=users.length;byId('access-module-count').textContent=modules.length;
            detail=null;selected=new Set();baseline=new Set();byId('access-user-info').classList.add('d-none');
            userSelect.value=users.some(u=>u.user_id===prior)?prior:'';
            jQuery(userSelect).trigger('change.select2');render();
        } catch(error) {notify(error.message,true);}
        finally {loading=false;controls();}
        if(userSelect.value)await loadUser(userSelect.value);
    }
    const userControl=jQuery(userSelect);
    if(!userControl.data('select2'))userControl.select2({width:'100%',placeholder:'Cari nama atau username',allowClear:true});
    userControl.on('change',async()=>{
        const id=userSelect.value;
        if(loading||saving)return;
        if(!await discardAllowed()){userSelect.value=detail?.user.user_id||'';userControl.trigger('change.select2');return;}
        byId('access-message').classList.add('d-none');
        byId('access-search').value='';byId('access-filter').value='all';
        if(id)await loadUser(id);else{detail=null;selected=new Set();baseline=new Set();byId('access-user-info').classList.add('d-none');render();}
    });
    byId('access-modules').addEventListener('change',event=>{
        const input=event.target.closest('input[data-module-id]');if(!input||loading||saving)return;
        if(input.checked)selected.add(input.dataset.moduleId);else selected.delete(input.dataset.moduleId);
        const id=input.dataset.moduleId;render();
        // Preserve keyboard focus after the list updates.
        Array.from(byId('access-modules').querySelectorAll('input')).find(i=>i.dataset.moduleId===id)?.focus();
    });
    byId('access-search').addEventListener('input',render);byId('access-filter').addEventListener('change',render);
    byId('access-check-visible').addEventListener('click',()=>{filtered().forEach(m=>selected.add(m.modules_id));render();});
    byId('access-clear-visible').addEventListener('click',()=>{filtered().forEach(m=>selected.delete(m.modules_id));render();});
    byId('access-reset').addEventListener('click',()=>{selected=new Set(baseline);render();});
    byId('access-reload').addEventListener('click',reload);
    byId('access-save').addEventListener('click',()=>{
        if(!detail||!changed().length||saving||loading)return;
        const added=changed().filter(m=>selected.has(m.modules_id)),removed=changed().filter(m=>!selected.has(m.modules_id));
        byId('access-review-user').textContent=(detail.user.name||detail.user.username)+' (@'+detail.user.username+')';
        const list=(title,items)=>items.length?'<h3 class="access-review-heading">'+title+' ('+items.length+')</h3><ul class="access-review-list">'+items.map(m=>'<li>'+escape(m.modules_name)+' <span class="text-muted">· '+escape(m.category)+'</span></li>').join('')+'</ul>':'';
        byId('access-review-changes').innerHTML=list('Akses ditambahkan',added)+list('Akses dilepas',removed);
        byId('access-review-error').classList.add('d-none');review.show();
    });
    byId('access-confirm').addEventListener('click',async()=>{
        if(saving||loading||!detail||!changed().length)return;
        const payload=new FormData();payload.set('user_id',detail.user.user_id);payload.set('revision',detail.revision);payload.set('modules_present','1');
        modules.filter(m=>selected.has(m.modules_id)).forEach(m=>payload.append('modules[]',m.modules_id));
        saving=true;controls();
        try {const result=await request('save',payload);applyDetail(result.detail);review.hide();notify(result.responseDesc);}
        catch(error){byId('access-review-error').textContent=error.message;byId('access-review-error').classList.remove('d-none');}
        finally {saving=false;controls();}
    });
    byId('access-review-modal').addEventListener('hide.bs.modal',event=>{if(saving&&changed().length)event.preventDefault();});
    window.addEventListener('beforeunload',event=>{if(changed().length){event.preventDefault();event.returnValue='';}});
    reload();
})();
