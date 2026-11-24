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

Grafik bulanan memakai nama bulan tanpa tahun. Batang dan garis memakai angka dummy langsung. Donut dan peringkat menyertakan persentase untuk menunjukkan proporsi dari total aktif. Sediakan pesan saat filter tidak menghasilkan data.

Word cloud Testing Page memakai kata dan frekuensi dummy yang tetap. Grafik lainnya mengikuti filter halaman.

[Konfigurasi grafik contoh](../../public/assets/js/developer/testingpage.js) · [Tampilan peringkat](../../public/assets/css/developer/testingpage.css) · [CSS word cloud bersama](../../public/assets/css/root/word-cloud.css)
