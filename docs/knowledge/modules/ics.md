# ICS — master MDC dan aturan coding

[Mastermdc](../../../app/Modules/Ics/Controllers/Mastermdc.php) menangani halaman `ics/mastermdc`, daftar MDC/section, tambah dan ubah MDC, serta `updaterule()`, `insertmasterrule()`, dan `savemasternotes()`. Halaman admin memerlukan login/grant. [Additional](additional.md) menyediakan pembaca MDC terpisah.

[MastermdcModel](../../../app/Modules/Ics/Models/MastermdcModel.php) memakai koneksi default dan mereferensikan `dt01_ics_mdc`, `dt01_ics_ch3_section`, `dt01_ics_coding_rule`, `dt01_ics_coding_rule_detail`, serta `dt01_ics_source_document`. Tampilan, dialog, dan interaksi ada di [Views](../../../app/Modules/Ics/Views/), [Modal](../../../app/Modules/Ics/Modal/), serta [mastermdc.js](../../../public/assets/js/ics/mastermdc.js).

Master dan aturan dalam modul ini merupakan data aplikasi. Versi sumber coding dan cakupan hubungan dengan aturan audit UR belum diverifikasi.
