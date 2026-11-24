# Modal progres

[Indeks](README.md)

Gunakan SweetAlert dengan kelas **report-progress-popup**. Susun tahap di dalam **report-progress** dengan **report-progress-step** dan **report-progress-icon** pada tiap baris.

| Tahap | Tampilan |
| --- | --- |
| Menunggu | Titik dan garis penghubung berwarna lembut |
| Berjalan | Spinner teal, kelas **is-current**, dan atribut **aria-current="step"** |
| Selesai | Centang putih dengan latar teal dan kelas **is-complete** |

Jika progres dapat diukur, tambahkan **progress report-progress-meter** dengan **progress-bar** di dalamnya. Isi **aria-label** dan perbarui nilai **aria-valuenow** serta lebar batang bersamaan. Di UR, batang bergerak setelah server merespons setiap batch dan ringkasan menampilkan jumlah baris berhasil serta gagal. Pembuatan laporan indikator mutu mengikuti tahap dari server dan memakai daftar tahap.

Di Testing Page, buka **Status dan modal**, lalu klik **Buat laporan contoh** atau **Simulasi impor E-Klaim**. Kedua demo memakai timer lokal. Demo impor menampilkan batang 0–100% tanpa berkas atau penyimpanan data.

[CSS bersama](../../public/assets/css/root/report-progress.css) · [Demo Testing Page](../../public/assets/js/developer/testingpage.js) · [Impor UR](../../public/assets/js/root/ur.js) · [Laporan indikator mutu](../../public/assets/js/qi/summaryreport.js)
