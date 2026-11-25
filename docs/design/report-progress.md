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

## Pemuatan dashboard

Untuk modul berbasis jQuery AJAX, panggil `showDataLoading()` tepat sebelum memulai permintaan. Helper bersama menampilkan SweetAlert dan menutupnya setelah permintaan selesai. Hindari pesan pemuatan periode tambahan di dalam halaman.

Gunakan pola dashboard UR untuk permintaan yang menunggu satu respons server. Modal memakai sudut membulat, judul teal, deskripsi singkat, dan progress bar bergerak. Tampilkan keterangan status di bawah bar tanpa daftar tahap.

- Judul **Menyiapkan dashboard UR**.
- Deskripsi **Memuat ringkasan dan grafik E-Klaim.**
- Status **Sedang memproses data…**
- Gunakan kelas `report-progress-popup`, `report-progress`, dan `progress report-progress-meter` dari CSS bersama.
- Warna bar menggunakan `--app-status-teal-text` dan latarnya `--app-status-teal-bg`.
- Selama menunggu respons, bar bergerak tanpa persentase. Tutup modal ketika permintaan selesai atau gagal, lalu tampilkan hasil pada halaman.
- Nonaktifkan penutupan melalui klik luar dan Escape selama permintaan berlangsung. Hormati `prefers-reduced-motion` dengan menghentikan animasi bar.

[Implementasi dashboard UR](../../public/assets/js/ur/dashboard.js) · [Gaya modal dashboard UR](../../public/assets/css/ur/dashboard.css)

[CSS bersama](../../public/assets/css/root/report-progress.css) · [Demo Testing Page](../../public/assets/js/developer/testingpage.js) · [Impor UR](../../public/assets/js/root/ur.js) · [Laporan indikator mutu](../../public/assets/js/qi/summaryreport.js)
