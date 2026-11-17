# Grafik

Acuan: public/assets/js/ur/dashboard.js, severity1.js, severity3.js, los.js, dan tarifpositif.js. [Indeks](README.md).

Gunakan ApexCharts yang sudah tersedia. Pertahankan tipe grafik sesuai makna: garis untuk tren/rasio, batang untuk jumlah atau kontribusi. Header berisi judul dan deskripsi singkat tanpa label kapital tambahan yang mengulang judul.

## Tampilan

Tinggi grafik UR sekitar 285–300px. Teal #087e82 menjadi seri utama; #93b6c3 atau #93aab3 menjadi pembanding. Warna layanan harus tetap konsisten saat filter menghilangkan seri lain. Legenda di atas, rata kiri. Gunakan grid tipis, label yang terbaca, dan matikan toolbar chart jika tidak diperlukan.

Nama bulan ditulis lengkap. Tooltip menyebut unit: episode, hari, Rp atau %. Jangan menggunakan formatter rupiah untuk grafik jumlah. Bila layar sempit, label boleh dimiringkan tanpa menyingkat nama bulan.

## CRR

CRR layanan = total INA-CBG layanan ÷ total tarif RS layanan × 100%. Rawat jalan dan rawat inap menjadi dua seri dengan perhitungan masing-masing. Tampilkan garis acuan 100%. Bulan tanpa data atau dengan tarif RS nol memakai null/jeda, bukan nol persen. CRR bukan laba/rugi aktual.

Saat filter berubah, hancurkan instance grafik lama sebelum menggambar ulang. Jika library tidak tersedia, tampilkan pesan yang mengarahkan ke tabel; tabel tetap bisa dibaca. Berikan aria-label untuk grafik dan sediakan tabel atau ringkasan sebagai alternatif membaca data.
