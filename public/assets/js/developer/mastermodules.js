let moduleOrderSaving = false;

window.addEventListener('pageshow', event => {
    if (event.persisted && moduleOrderSaving) window.location.reload();
});

document.querySelectorAll('[data-order-form]').forEach(list => {
    const form = document.getElementById(list.dataset.orderForm);
    const status = list.closest('.mastermodules-page').querySelector('[data-order-status]');
    const rowOf = node => node.firstElementChild;
    const currentOrder = () => Array.from(list.children, node => node.dataset.orderId);
    const nodeFor = element => {
        const node = element.closest('.module-tree-row')?.parentElement;
        return node?.parentElement === list ? node : null;
    };
    const savedOrder = currentOrder();
    let dragging = null;
    let dropTarget = null;

    function saveOrder() {
        const ids = currentOrder();
        if (ids.every((id, index) => id === savedOrder[index])) return;

        moduleOrderSaving = true;
        ids.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'module_ids[]';
            input.value = id;
            form.append(input);
        });
        status.textContent = 'Menyimpan urutan…';
        form.submit();
    }

    function clearDropTarget() {
        if (!dropTarget) return;
        dropTarget.node.classList.remove('is-drop-before', 'is-drop-after');
        dropTarget = null;
    }

    function destination(event) {
        const node = nodeFor(event.target);
        if (!node || node === dragging) return null;
        const bounds = rowOf(node).getBoundingClientRect();
        return { node, after: event.clientY > bounds.top + bounds.height / 2 };
    }

    list.addEventListener('click', event => {
        const button = event.target.closest('[data-move]');
        if (!button || moduleOrderSaving) return;
        const node = nodeFor(button);
        if (!node) return;
        const up = button.dataset.move === '-1';
        const neighbor = up ? node.previousElementSibling : node.nextElementSibling;
        if (!neighbor) return;

        list.insertBefore(node, up ? neighbor : neighbor.nextElementSibling);
        saveOrder();
    });

    list.addEventListener('dragstart', event => {
        const handle = event.target.closest('.module-drag-handle');
        const node = handle && nodeFor(handle);
        if (!node) return;
        if (moduleOrderSaving) {
            event.preventDefault();
            return;
        }
        dragging = node;
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', node.dataset.orderId);
        event.dataTransfer.setDragImage(rowOf(node), 16, 16);
        dragging.classList.add('is-dragging');
    });

    list.addEventListener('dragover', event => {
        if (!dragging) return;
        const target = destination(event);
        if (!target) {
            clearDropTarget();
            return;
        }
        event.preventDefault();
        event.dataTransfer.dropEffect = 'move';
        if (dropTarget?.node === target.node && dropTarget.after === target.after) return;

        clearDropTarget();
        target.node.classList.add(target.after ? 'is-drop-after' : 'is-drop-before');
        dropTarget = target;
    });

    list.addEventListener('dragleave', event => {
        if (!list.contains(event.relatedTarget)) clearDropTarget();
    });

    list.addEventListener('drop', event => {
        if (!dragging) return;
        const target = destination(event);
        clearDropTarget();
        if (!target) return;
        event.preventDefault();
        const moved = dragging;
        list.insertBefore(moved, target.after ? target.node.nextElementSibling : target.node);
        moved.classList.remove('is-dragging');
        dragging = null;
        saveOrder();
    });

    list.addEventListener('dragend', () => {
        if (dragging) dragging.classList.remove('is-dragging');
        dragging = null;
        clearDropTarget();
    });
});

const mastermodulesEditModal = document.querySelector('#mastermodules-edit');
if (mastermodulesEditModal) {
    const form = mastermodulesEditModal.querySelector('form');
    const editUrl = form.action;
    const title = mastermodulesEditModal.querySelector('.modal-title');
    const parent = mastermodulesEditModal.querySelector('[data-module-parent]');
    const submit = form.querySelector('[type="submit"]');
    const editFields = form.querySelector('[data-module-edit-fields]');
    mastermodulesEditModal.addEventListener('show.bs.modal', event => {
        const trigger = event.relatedTarget;
        if (!trigger?.dataset.moduleId) {
            event.preventDefault();
            return;
        }
        const addingChild = trigger.hasAttribute('data-add-child');
        form.action = addingChild ? mastermodulesEditModal.dataset.addUrl : editUrl;
        title.textContent = addingChild ? 'Tambah Submodul' : 'Edit modul';
        submit.textContent = addingChild ? 'Tambah submodul' : 'Simpan perubahan';
        parent.hidden = !addingChild;
        parent.textContent = addingChild ? 'Induk: ' + trigger.dataset.moduleName : '';
        form.elements.module_id.value = trigger.dataset.moduleId;
        form.elements.module_name.value = addingChild ? '' : trigger.dataset.moduleName;
        form.elements.icon.value = addingChild ? '' : trigger.dataset.moduleIcon;
        form.elements.package.value = trigger.dataset.modulePackage;
        form.elements.def_controller.value = addingChild ? '' : trigger.dataset.moduleController;
        editFields.hidden = addingChild;
        editFields.disabled = addingChild;
        form.elements.status.value = addingChild ? '' : trigger.dataset.moduleStatus;
        form.elements.version.value = addingChild ? '' : trigger.dataset.moduleVersion;
        form.elements.revision.value = addingChild ? '' : trigger.dataset.moduleRevision;
    });
    mastermodulesEditModal.addEventListener('shown.bs.modal', () => form.elements.module_name.focus());
}
