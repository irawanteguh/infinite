# Klaim — analisis snapshot dan episode

| Halaman | Sumber |
| --- | --- |
| `klaim/pending` | [Pending](../../../app/Modules/Klaim/Controllers/Pending.php) menyajikan ringkasan, data mentah/detail, dan breakdown algoritma. [PendingModel](../../../app/Modules/Klaim/Models/PendingModel.php) memasok data dan [PendingAnalytics](../../../app/Modules/Klaim/Services/PendingAnalytics.php) menyusun metrik. |
| `klaim/berulang` | [Berulang](../../../app/Modules/Klaim/Controllers/Berulang.php) dan [BerulangModel](../../../app/Modules/Klaim/Models/BerulangModel.php) menangani rincian episode berulang. |
| `klaim/slitlamp` | [Slitlamp](../../../app/Modules/Klaim/Controllers/Slitlamp.php) dan [SlitlampModel](../../../app/Modules/Klaim/Models/SlitlampModel.php) menangani daftar/detail untuk kriteria halaman tersebut. |

Model memakai koneksi default. `PendingModel::scoped()` memfilter `dt01_bpjs_pending_klaim` menurut `KODEPPK` dan `TGLPULANG`, dengan filter tambahan untuk bulan, layanan, dan status. Detail Berulang dan Slitlamp memfilter teks algoritma sumber dan menggabungkan tarif dari `dt01_bpjs_ur_dt` menurut SEP dalam konteks provider/periode. Tarif join diisi bila hanya ada satu nilai berbeda per SEP. [BAHV](../../../app/Controllers/Bahv.php) menyediakan impor `bahv/importtxtbahv` lewat grant package Klaim. Lihat [UR](ur.md) untuk sumber raw e-Klaim dan [aturan snapshot](../business-rules.md) untuk penafsiran agregat.

UI berada di [Views](../../../app/Modules/Klaim/Views/), [Toolbar](../../../app/Modules/Klaim/Toolbar/), serta [aset Klaim](../../../public/assets/js/klaim/). Dashboard dan detail mengikuti login/grant, dengan scope data sesuai model. Belum ada verifikasi hasil pada data rumah sakit atau status pembayaran BPJS.

Impor BAHV mengembalikan jumlah total, berhasil, dan gagal. Log kegagalan mencatat jenis kegagalan tanpa identifier klaim, nilai tanggal masukan, atau isi baris.
