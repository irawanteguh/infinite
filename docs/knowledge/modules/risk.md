# Risk — register dan pemantauan risiko

[Masterrisk](../../../app/Modules/Risk/Controllers/Masterrisk.php) mengelola kategori dan kode melalui [MasterriskModel](../../../app/Modules/Risk/Models/MasterriskModel.php), serta menampilkan master matriks. [Register](../../../app/Modules/Risk/Controllers/Register.php) menangani `risk/register`, `peta`, `save`, `monitor`, dan ekspor CSV. [RegisterModel](../../../app/Modules/Risk/Models/RegisterModel.php) menelusuri periode, unit, register, dan monitoring.

Tabel sumber meliputi `dt01_risk_category_ms`, `dt01_risk_code_ms`, `dt01_risk_matrix_ms`, `dt01_risk_register`, `dt01_risk_monitor`, serta master department dan periode mutu. Model memakai koneksi CI4. Login/grant berlaku, dan validasi unit/tenant serta aksi mutasi berada di controller/model. [RiskMatrix::assess() dan trend()](../../../app/Modules/Risk/Libraries/RiskMatrix.php) dipakai untuk penilaian dan perbandingan skor. Lihat [aturan bisnis](../business-rules.md) untuk ambang yang terlihat dari kode.

UI ada di [Views](../../../app/Modules/Risk/Views/) dan [aset Risk](../../../public/assets/js/risk/), dengan direktori pendukung `Modal`, `Toolbar`, dan `Pageheader`. Isi master aktif, DDL Risk, dan kesesuaian daftar kategori dengan kebijakan rumah sakit belum diverifikasi.
