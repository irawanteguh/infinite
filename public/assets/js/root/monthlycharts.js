(function () {
    if (typeof window.ApexCharts !== 'function') return;
    const labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    const names = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    const monthIndex = value => {
        const text = String(value).replace(/\*$/, '').trim().toLowerCase();
        return labels.findIndex((label, index) => label.toLowerCase() === text || names[index].toLowerCase() === text);
    };
    window.ApexCharts = new Proxy(window.ApexCharts, {
        construct(Chart, args) {
            const options = args[1];
            const categories = options?.xaxis?.categories;
            const series = options?.series;
            if (Array.isArray(categories) && categories.length && Array.isArray(series)
                && !options.plotOptions?.bar?.horizontal
                && categories.every(category => monthIndex(category) >= 0)
                && new Set(categories.map(monthIndex)).size === categories.length
                && series.every(item => Array.isArray(item.data) && item.data.length === categories.length
                    && item.data.every(value => value === null || typeof value === 'number'))) {
                const positions = categories.map(monthIndex);
                args[1] = {
                    ...options,
                    xaxis: {...options.xaxis, categories: labels.slice()},
                    series: series.map(item => {
                        const data = Array(12).fill(null);
                        item.data.forEach((value, index) => { data[positions[index]] = value; });
                        return {...item, data};
                    }),
                };
            }
            return Reflect.construct(Chart, args);
        },
    });
})();
