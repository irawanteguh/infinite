# PE — survei SKM, PREM, dan PROM

- [Skm](../../../app/Modules/Pe/Controllers/Skm.php), [Prem](../../../app/Modules/Pe/Controllers/Prem.php), [Premgeneral](../../../app/Modules/Pe/Controllers/Premgeneral.php), dan [Prom](../../../app/Modules/Pe/Controllers/Prom.php) menyediakan form dan submit publik sesuai [Filters](../../../app/Config/Filters.php). `Skm::submitprem()` mendukung kelanjutan PREM dari SKM.
- [Masterqusioner](../../../app/Modules/Pe/Controllers/Masterqusioner.php) mengelola instrumen, domain, dan pertanyaan. Sumber konfigurasi ada di [Config](../../../app/Modules/Pe/Config/).
- [PremModel::storeResponse()](../../../app/Modules/Pe/Models/PremModel.php) memvalidasi pilihan dan menyimpan header/detail dalam transaksi. Model memakai `submission_key` untuk pengenalan kiriman dan memeriksa record aktif. PREM yang terhubung SKM memeriksa referensi SKM dalam scope instrumen.
- [Dashboard](../../../app/Modules/Pe/Controllers/Dashboard.php) menyediakan `pe/dashboard`, `annualreport`, dan `skmreport`. [DashboardModel](../../../app/Modules/Pe/Models/DashboardModel.php) menangani analytics, [SkmReportModel](../../../app/Modules/Pe/Models/SkmReportModel.php) menghitung laporan SKM. Dashboard dan master mengikuti login/grant.

Model memakai koneksi CI4. Tabel `dt01_pe_instrument_ms`, `dt01_pe_question_ms`, dan `dt01_pe_domain_ms` menjadi master. SKM/PREM/PROM masing-masing memakai pasangan `dt01_pe_*_response_hd` dan `dt01_pe_*_response_dt`. [Migrasi PE](../../../app/Modules/Pe/Database/) mendokumentasikan normalisasi dan status aktif. Query laporan memakai scope group/org dan respons aktif sesuai modelnya.

`SkmReportModel::report()` membentuk cohort dari tanggal submit SKM. PREM terkait mengikuti tanggal dan jenis layanan SKM. Tingkat kelengkapan adalah jumlah SKM unik yang memiliki PREM terkait dibagi jumlah SKM dalam cohort, dengan hasil null bila cohort kosong. Ringkasan skor memproses SKM skala 4 dan PREM skala 5 melalui `summarize()`.

UI dan PDF ada di [Views](../../../app/Modules/Pe/Views/), dengan [aset PE](../../../public/assets/js/pe/). Penerapan migrasi dan instrumen aktif tiap tenant serta submit publik aktual belum diverifikasi.
