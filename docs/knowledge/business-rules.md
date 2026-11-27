# Aturan bisnis yang diterapkan

Rujukan di bawah menjelaskan perilaku implementasi. Definisi resmi, ketentuan regulator, serta kesesuaian klinis dan operasional membutuhkan konfirmasi pemilik domain.

| Area | Perilaku sumber |
| --- | --- |
| QI, agregasi | [SummaryModel::summaryRows()](../../app/Modules/Qi/Models/SummaryModel.php) menghitung rata-rata capaian per record bulan yang dinilai. Bulan tanpa record menghasilkan null untuk rata-rata. Kedua nilai yang dianggap nol memberi 100, penyebut nol di luar kondisi itu memberi 0, dan rasio lain dibatasi maksimum 100. Perhitungan pemenuhan target memiliki cabang target nol dan arah `L`. [SummaryreportModel](../../app/Modules/Qi/Models/SummaryreportModel.php) mengagregasi periode yang dipilih. |
| QI, input dan persetujuan | [IndicatorDeadline::allows()](../../app/Modules/Organization/Services/IndicatorDeadline.php) membuka input sejak awal bulan periode hingga akhir hari batas pada bulan berikutnya, dengan opsi perpanjangan. [Appindikator::updateapproval()](../../app/Modules/Organization/Controllers/Appindikator.php) mencatat status, aktor, dan waktu. |
| BPJS, pending | [PendingAnalytics::build()](../../app/Modules/Klaim/Services/PendingAnalytics.php) mempertahankan status sumber, membedakan nominal kosong dari nol, dan membandingkan bulan terbaru dengan bulan kalender sebelumnya jika ada datanya. Analytics menggambarkan snapshot pengajuan. |
| UR, CMI | [CasemixindexModel::monthlyCmi()](../../app/Modules/Ur/Models/CasemixindexModel.php) memakai rata-rata tertimbang cost weight INA-CBG berdasarkan jumlah episode yang cocok ke master layanan. Bulan berasal dari discharge date. `aggregate()` juga menghitung jumlah dan cakupan bobot iDRG positif secara terpisah. [EpisodeReview](../../app/Modules/Ur/Services/EpisodeReview.php) menghasilkan temuan peninjauan internal. |
| Coding | [Mastermdc](../../app/Modules/Ics/Controllers/Mastermdc.php) menangani master dan aturan coding. [Auditkoding](../../app/Modules/Ur/Controllers/Auditkoding.php) menyajikan temuan dari data episode. Buka sumber aturan sesuai fitur sebelum mengubah klasifikasi. |
| Risk | [RiskMatrix::assess()](../../app/Modules/Risk/Libraries/RiskMatrix.php) memetakan kemungkinan dan dampak 1–5 melalui tabel skor. Ambang penanganan pada metode ini adalah skor ≥12. `trend()` membandingkan skor monitoring dengan baseline. |
| Farmasi | [MasterobatModel::masterobat()](../../app/Modules/Farmasi/Models/MasterobatModel.php) menjumlahkan keluar stok aktif per obat dan per bulan untuk tahun saat ini menurut database. Ketiadaan agregat pemakaian tampil sebagai nol pada query ini. |
| PE dan CA | [PE](modules/pe.md) merujuk alur survei aktif dan laporan. [CA](modules/ca.md) merujuk jenis jawaban, skor, dan klasifikasi risiko instrumen. |
| SATUSEHAT | [SATUSEHAT](modules/satusehat.md) merujuk mapping dan alur submit, logging, dan rekonsiliasi. |

Target indikator, instrumen aktif, mapping kode yang terpasang, dan hasil hitung pada dataset deployment belum diperiksa.
