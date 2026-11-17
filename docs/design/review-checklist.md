# Pemeriksaan perubahan desain

Gunakan seperlunya sesuai perubahan. [Indeks](README.md).

## Layout

- Header halaman, toolbar, filter, KPI dan tabel mengikuti pola UR yang sesuai.
- Judul/deskripsi card tidak berulang; search di kanan dan turun dengan rapi di ponsel.
- Teks thead tebal; angka rata kanan; nama bulan lengkap.
- Tidak ada scroll horizontal pada seluruh halaman. Scroll hanya pada tabel yang lebar.
- Info memakai ikon teal; pilihan select, fokus dan pagination aktif senada.

## Data dan interaksi

- ID, endpoint, nilai option dan kriteria bisnis tetap sesuai modul.
- Filter memengaruhi tabel, KPI dan grafik pada cakupan yang dijelaskan.
- Tabel besar dimuat per halaman; rekap bulanan menampilkan seluruh bulan.
- Pencarian DPJP independen bila rawat jalan/inap dipisahkan ke dua card.
- Total dan persentase benar sesudah filter/pencarian; denominator jelas.
- Nilai nol, denominator nol, null, data kosong dan error dibedakan.
- Detail bisa dibuka/tutup; pagination dan refresh tidak menggandakan baris atau handler.

## Validasi

Jalankan pemeriksaan sintaks PHP/JavaScript untuk file yang diubah. Gunakan data uji yang relevan bila perhitungan, pagination, atau filter berubah. Periksa tampilan desktop dan ponsel melalui browser bila tersedia. Jika browser tidak dapat dipakai, sebutkan bahwa validasi visual belum dilakukan; pemeriksaan sintaks bukan bukti layout sudah benar.

Dokumentasi sebaiknya diperbarui saat pola desain yang disepakati berubah. Jangan membuat aset screenshot, script verifikasi sementara, atau profil browser uji sebagai dependensi aplikasi.
