(function () {
    const root = document.getElementById('pdsaDiagramRoot');
    if (!root || root.dataset.canManage !== '1') return;

    const endpoint = root.dataset.endpoint;
    const status = document.getElementById('pdsaItemStatus');

    document.addEventListener('change', async (event) => {
        const checkbox = event.target.closest('.pdsa-item-completed');
        if (!checkbox || checkbox.disabled) return;

        const previous = !checkbox.checked;
        checkbox.disabled = true;
        try {
            const body = new URLSearchParams({
                pdsa_item_id: checkbox.dataset.itemId,
                completed: checkbox.checked ? '1' : '0',
            });
            const response = await fetch(`${endpoint}/toggleitem`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                body,
            });
            const result = await response.json();
            if (result.responseCode !== '00') throw new Error(result.responseDesc || 'Status item gagal disimpan.');

            const label = document.querySelector(`label[for="${CSS.escape(checkbox.id)}"]`);
            label?.classList.toggle('text-decoration-line-through', checkbox.checked);
            label?.classList.toggle('text-gray-500', checkbox.checked);
            label?.classList.toggle('text-gray-700', !checkbox.checked);

            const stage = checkbox.closest('.pdsa-stage');
            const stageItems = Array.from(stage.querySelectorAll('.pdsa-item-completed'));
            stage.querySelector('.pdsa-stage-count').textContent = `${stageItems.filter((item) => item.checked).length}/${stageItems.length}`;
            status.textContent = checkbox.checked ? 'Item ditandai selesai.' : 'Status selesai item dibatalkan.';
        } catch (error) {
            checkbox.checked = previous;
            window.alert(error.message || 'Status item gagal disimpan. Periksa koneksi lalu coba lagi.');
        } finally {
            checkbox.disabled = false;
        }
    });
})();
