# Card ringkasan

Acuan: Tarif Positif/Negatif, Severity I/III, dan LOS. [Indeks](README.md).

Susunan tiap KPI: label singkat, ikon di kanan atas, angka utama, lalu keterangan satu atau dua baris. Gunakan angka dari data aktual dan jelaskan unit maupun cakupannya.

## Pola UR yang tersedia

- Container ur-kpis, item card ur-kpi.
- Elemen ur-kpi-label, ur-kpi-value, ur-kpi-icon dan small untuk keterangan.
- Desktop tiga kolom, tablet dua kolom, ponsel satu kolom pada halaman analisis tersebut.
- Gap 16px, padding sekitar 20px; pada ponsel 16px. Angka utama sekitar 28px dan menggunakan tabular numerals.

CSS KPI saat ini masih berada di public/assets/css/ur/tarifpositif.css, tarifnegatif.css, severity1.css, severity3.css, dan los.css. Class ur-kpis saja belum menjamin grid aktif pada setiap halaman; sertakan style halaman yang sesuai atau ekstrak komponen bersama sebelum dipakai modul baru. Dashboard memakai pola pe-kpis/pe-kpi yang berbeda.

## Aturan angka

Jumlah episode bukan pasien unik. Format jumlah memakai lokal id-ID. Nilai rupiah menampilkan Rp; angka ringkas boleh digunakan di KPI dan tooltip sebaiknya menampilkan angka penuh. Persentase menggunakan numerator/denominator yang jelas.

Tampilkan — ketika nilai belum tersedia atau denominator tidak valid. Angka nol hanya untuk hasil nol yang diketahui. Rasio periode dihitung dari total pembilang dibagi total penyebut, bukan rata-rata persentase bulanan. KPI tetap mengikuti periode/layanan, bukan halaman pagination tabel.
