# Grafik dan word cloud

[Indeks](README.md)

| Komponen | Contoh |
| --- | --- |
| Batang bertumpuk | Nilai per bulan dengan satu seri untuk setiap kategori. |
| Garis | Nilai bulanan dengan penanda titik. |
| Garis dengan acuan | Grafik garis dengan garis horizontal putus-putus. Nilai 30 adalah acuan dummy pada demo. |
| Donut | Cincin 77%, pemisah putih, total tebal di tengah, dan legenda eksternal berisi jumlah dan persentase. Angka total memakai 36 px di desktop dan 30 px pada layar kecil. |
| Peringkat | Batang horizontal hingga enam entri, diurutkan dari nilai terbesar. Lebar batang menunjukkan bagian dari total data yang lolos filter. |
| Word cloud | Ukuran kata mengikuti frekuensi, dengan variasi warna seperti dashboard PE. Kata negatif pada contoh berwarna merah muda #D76B82. |

Grafik bulanan memakai label singkat **Jan, Feb, Mar, Apr, Mei, Jun, Jul, Agu, Sep, Okt, Nov, Des** tanpa tahun. Selalu tampilkan 12 bulan, termasuk saat filter hanya menghasilkan sebagian bulan. Pertahankan posisi setiap nilai pada bulan asalnya dan gunakan null untuk bulan tanpa data agar tampil sebagai jeda. Helper bersama `public/assets/js/root/monthlycharts.js` menerapkan aturan ini pada grafik ApexCharts dengan kategori nama bulan dan seri angka. Grafik dengan format titik objek atau pembaruan kategori melalui updateOptions perlu menerapkan aturan yang sama pada konfigurasi modul.

Batang dan garis contoh memakai angka dummy langsung. Donut dan peringkat menyertakan persentase untuk menunjukkan proporsi dari total aktif. Sediakan pesan saat filter tidak menghasilkan data.

Word cloud Testing Page memakai kata dan frekuensi dummy yang tetap. Grafik lainnya mengikuti filter halaman.

Garis grid horizontal ApexCharts memakai ketebalan 1.5px dan warna #C5D8D9 melalui CSS tema bersama agar terlihat jelas.

[Konfigurasi grafik contoh](../../public/assets/js/developer/testingpage.js) · [Tampilan peringkat](../../public/assets/css/developer/testingpage.css) · [CSS word cloud bersama](../../public/assets/css/root/word-cloud.css)
