# UR — analisis e-Klaim dan peninjauan episode

| Alur | Titik masuk |
| --- | --- |
| Data dan dashboard | [Dashboard](../../../app/Modules/Ur/Controllers/Dashboard.php) dan [Dataeklaim](../../../app/Modules/Ur/Controllers/Dataeklaim.php) menyediakan data mentah, ringkasan, review dan tren di `ur/dashboard` serta `ur/dataeklaim`. |
| Review episode | [InternalReviewModel](../../../app/Modules/Ur/Models/InternalReviewModel.php) menyediakan `queue`, `trend`, `pendingTopupQueue`, dan `history`. [EpisodeReview](../../../app/Modules/Ur/Services/EpisodeReview.php) menyusun temuan internal dari data episode dan aturan terkait. |
| Audit | [Auditkoding](../../../app/Modules/Ur/Controllers/Auditkoding.php) menyediakan ringkasan, kasus, dan rincian sequencing. [Auditmedik](../../../app/Modules/Ur/Controllers/Auditmedik.php) memiliki halaman dan feed data terpisah. Referensi aturan ada di [Config](../../../app/Modules/Ur/Config/). |
| Analisis khusus | [Controllers](../../../app/Modules/Ur/Controllers/) dan [Models](../../../app/Modules/Ur/Models/) memuat AnalysTSL, CMI, biaya, LOS, tarif positif/negatif, severity, diagnosis, top-up, dan cohort layanan/kondisi tertentu. |

[Ur::importtxteklaim()](../../../app/Controllers/Ur.php) adalah handler impor legacy, di luar namespace modul. Tabel utama adalah `dt01_bpjs_ur_dt`; review juga membaca `dt01_bpjs_pending_klaim`, yang dipakai [Klaim](klaim.md). Analisis case mix memakai master `dt01_inacbg_casegroup_ms`, `dt01_inacbg_cmg_ms`, dan cost weight ranap/rajal. Model memakai koneksi default. Analisis umum memakai kode rumah sakit dan periode sesuai model. Formula CMI dan batas interpretasi temuan ada di [aturan bisnis](../business-rules.md).

Route `ur/<controller>` mengikuti login/grant. Impor bersama mengikuti izin package UR. [Views](../../../app/Modules/Ur/Views/) dan [aset UR](../../../public/assets/js/ur/) memuat dashboard, tabel, detail, dan grafik. Cakupan episode yang cocok master, kualitas impor, dan kinerja query pada dataset deployment belum diverifikasi.
