(function () {
    'use strict';
    const root = document.getElementById('help-center');
    if (!root) return;
    const search = root.querySelector('#help-search');
    const articles = Array.from(root.querySelectorAll('[data-help-article]'));
    const links = Array.from(root.querySelectorAll('[data-help-nav]'));
    const normalize = value => value.normalize('NFKD').toLocaleLowerCase('id').trim();
    const content = articles.map(article => normalize(article.textContent));
    let moduleId = '';

    function update() {
        const terms = normalize(search.value).split(/\s+/).filter(Boolean);
        let count = 0;
        articles.forEach((article, index) => {
            const visible = (!moduleId || article.dataset.helpArticle === moduleId)
                && terms.every(term => content[index].includes(term));
            article.hidden = !visible;
            if (visible) count++;
            article.querySelectorAll('details').forEach(detail => { detail.open = visible && terms.length > 0; });
        });
        links.forEach(link => {
            if (link.dataset.helpNav === moduleId) link.setAttribute('aria-current', 'true');
            else link.removeAttribute('aria-current');
        });
        root.querySelector('#help-count').textContent = count + ' modul ditampilkan';
        root.querySelector('#help-empty').hidden = count !== 0;
    }

    function reset() {
        moduleId = '';
        search.value = '';
        update();
    }
    search.addEventListener('input', () => { moduleId = ''; update(); });
    links.forEach(link => link.addEventListener('click', () => {
        moduleId = link.dataset.helpNav;
        search.value = '';
        update();
    }));
    root.querySelector('[data-help-all]').addEventListener('click', reset);
    root.querySelector('#help-reset').addEventListener('click', () => { reset(); search.focus(); });
    const initialId = location.hash.replace('#guide-', '');
    if (articles.some(article => article.dataset.helpArticle === initialId)) moduleId = initialId;
    update();
})();
