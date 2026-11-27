# QI — indikator dan laporan mutu

| Alur | Titik masuk |
| --- | --- |
| Definisi dan periode | [Masterindikator](../../../app/Modules/Qi/Controllers/Masterindikator.php) dan [Periodepelaporan](../../../app/Modules/Qi/Controllers/Periodepelaporan.php) mengelola definisi, periode, dan tim periode. |
| Indikator unit | [Indikatorunit](../../../app/Modules/Qi/Controllers/Indikatorunit.php) menangani pengajuan, PIC, input bulanan, lampiran, dan data tim. [IndikatorunitModel](../../../app/Modules/Qi/Models/IndikatorunitModel.php) memeriksa hubungan pengguna dengan indikator/unit. Pengaturan tenggat dan approval ada di [Organization](organization.md). |
| Pemantauan | [Summary](../../../app/Modules/Qi/Controllers/Summary.php) dan [Summarycompare](../../../app/Modules/Qi/Controllers/Summarycompare.php) menggunakan [SummaryModel](../../../app/Modules/Qi/Models/SummaryModel.php) untuk ringkasan, tren, dan pembandingan periode. |
| Tindak lanjut | [Fishbone](../../../app/Modules/Qi/Controllers/Fishbone.php) dan [Pdsa](../../../app/Modules/Qi/Controllers/Pdsa.php) menangani analisis penyebab dan siklus perbaikan. |
| Laporan | [Summaryreport](../../../app/Modules/Qi/Controllers/Summaryreport.php) menyediakan `periods`, `generate`, `progress`, dan `viewreport`. [SummaryreportModel](../../../app/Modules/Qi/Models/SummaryreportModel.php) memasok data, [StoredSummaryReport](../../../app/Modules/Qi/Services/StoredSummaryReport.php) menyimpan hasil menurut group/org/periode, dan mPDF merender PDF. |

Model memakai grup default. Tabel utama `dt01_qi_indikator_ms`, `dt01_qi_indikator_hd`, `dt01_qi_indikator_it`, `dt01_qi_periode_mutu`, `dt01_qi_komite_mutu_hd`, tabel Fishbone/PDSA, dan master department. Route `qi/<controller>` mengikuti login/grant. Cakupan group/org dan unit/PIC bergantung aksi. Rumus ringkasan ada di [aturan bisnis](../business-rules.md).

[Views](../../../app/Modules/Qi/Views/), [aset QI](../../../public/assets/js/qi/), dan direktori pendukung `Modal`, `Toolbar`, `Team`, `Progress` memuat form serta tampilan laporan. Target aktif, isi periode dan kecocokan hasil PDF dengan data deployment belum diverifikasi.
