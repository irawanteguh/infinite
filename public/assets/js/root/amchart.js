
function renderWordCloud(elementId, data, options = {}) {
    const element = document.getElementById(elementId);

    if (!element) {
        console.error("WordCloud: elemen #" + elementId + " tidak ditemukan.");
        return;
    }

    const settings = Object.assign({
        maxCount: 10,
        minFontSize: 12,
        maxFontSize: 35,
        minWordLength: 2,
        fontFamily: "Poppins, sans-serif",
        colors: [
            0x1E3A5F,
            0x800000,
            0x4150A0,
            0xF0825A,
            0x64748B
        ]
    }, options);

    // Normalisasi dan batasi data
    const chartData = (Array.isArray(data) ? data : [])
        .map(function (item) {
            return {
                word: String(item.word || "").trim(),
                weight: Number(item.weight) || 0
            };
        })
        .filter(function (item) {
            return item.word.length >= settings.minWordLength &&
                   item.weight > 0;
        })
        .sort(function (a, b) {
            return b.weight - a.weight;
        })
        .slice(0, settings.maxCount);

    // Simpan instance grafik pada elemen
    let root = element._am5Root;
    let series = element._am5Series;

    // Bersihkan grafik jika data kosong
    if (chartData.length === 0) {
        if (root) {
            root.dispose();
            element._am5Root = null;
            element._am5Series = null;
        }

        element.innerHTML =
            '<div class="text-muted text-center py-10">' +
            'Tidak ada data untuk ditampilkan' +
            '</div>';

        return;
    }

    // Buat grafik hanya jika belum tersedia
    if (!root || !series) {
        if (root) {
            root.dispose();
        }

        element.innerHTML = "";

        root = am5.Root.new(elementId);
        element._am5Root = root;

        root.setThemes([
            am5themes_Animated.new(root)
        ]);

        series = root.container.children.push(
            am5wc.WordCloud.new(root, {
                categoryField: "word",
                valueField   : "weight",
                maxCount     : settings.maxCount,
                minFontSize  : settings.minFontSize,
                maxFontSize  : settings.maxFontSize,
                minWordLength: settings.minWordLength
            })
        );

        // series.set("colors", am5.ColorSet.new(root, {
        //     colors: settings.colors.map(function (color) {
        //         return am5.color(color);
        //     })
        // }));

        series.labels.template.setAll({
            fontFamily   : settings.fontFamily,
            fontWeight   : "500",
            paddingTop   : 2,
            paddingBottom: 2,
            paddingLeft  : 2,
            paddingRight : 2,
            tooltipText  : "{category}: {value}"
        });

        element._am5Series = series;
    }

    // Perbarui data tanpa membuat root baru
    series.data.setAll(chartData);
}
