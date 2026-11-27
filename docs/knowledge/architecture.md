# Arsitektur aplikasi

## Runtime dan pemuatan kode

[composer.json](../../composer.json) mensyaratkan PHP `^7.4 || ^8.0`, CI4 `4.4.8`, dan mPDF `^8.2`. [composer.lock](../../composer.lock) mengunci CI4 ke `v4.4.8`. Dependensi pengembangan mencakup Faker, vfsStream, dan PHPUnit 9. CLI yang diperiksa memakai PHP 7.4.33. Versi PHP web server bergantung deployment.

Titik masuk HTTP adalah [public/index.php](../../public/index.php), sedangkan CLI memakai [spark](../../spark). Document root mengarah ke `public/`. [Paths.php](../../app/Config/Paths.php) menunjuk lokasi aplikasi, framework, dan direktori kerja. Composer memetakan `App\\`, `Config\\`, dan `Modules\\`; [Autoload.php](../../app/Config/Autoload.php) memuat helper `url`, `form`, dan `system`. [env](../../env) merupakan template konfigurasi lokal.

## Alur tanggung jawab

1. [Routes.php](../../app/Config/Routes.php) mendefinisikan route eksplisit dan dispatcher HMVC. [Filters.php](../../app/Config/Filters.php) menentukan auth sebelum request serta User Activity dan Debug Toolbar sesudahnya. Lihat [routing](routing.md) untuk kontrak dispatcher.
2. [BaseController](../../app/Controllers/BaseController.php) membuka session dan membuat `Routingsystem` di constructor. Controller modul biasanya merender view melalui template bersama atau mengembalikan JSON lewat `response()`, lalu mendelegasikan query ke model. ClaimGuard memakai `ResourceController`. Import e-Klaim dan BAHV memiliki controller tingkat aplikasi.
3. Model CI4 memakai Query Builder atau SQL berparameter. Layanan yang punya domain khusus ditempatkan di modul, misalnya [EpisodeReview](../../app/Modules/Ur/Services/EpisodeReview.php) dan [StoredSummaryReport](../../app/Modules/Qi/Services/StoredSummaryReport.php). Direktori opsional mencakup `Config`, `Database`, `Modal`, `Toolbar`, `Pageheader`, `Services`, dan `Libraries`, dengan variasi kapitalisasi.
4. `Routingsystem::system()` menyusun data navigasi dari [Modelrouting](../../app/Models/Modelrouting.php). Session dan katalog modul mengendalikan akses halaman. Filter query menentukan data yang bisa dibaca atau diubah. Rujuk [autentikasi](authentication.md) dan [database](database.md).
5. View PHP memakai layout Metronic/Bootstrap, jQuery, dan AJAX. Penamaan aset lokal biasanya mengikuti modul/controller. Kontrak respons bervariasi. Contohnya `responseCode/responseDesc` pada beberapa master, `data/error/csrfHash` pada workspace SATUSEHAT, dan HTTP status dengan JSON pada ClaimGuard. Periksa konsumen JavaScript sebelum menyesuaikan respons. Template error berada di [app/Views/errors](../../app/Views/errors/).

## Proses operasional

[app/Commands](../../app/Commands/) berisi setup skema fitur, impor master wilayah, seed data dummy, sinkronisasi kuesioner SKM, dan pemangkasan User Log. [PruneUserActivity](../../app/Commands/PruneUserActivity.php) menyediakan perintah pemeliharaan. Laporan QI punya penyimpanan hasil dan status progres di alur aplikasinya. Jadwal cron, worker, dan konfigurasi layanan di luar repositori belum diverifikasi.
