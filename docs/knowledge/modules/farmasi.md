# Farmasi — master obat

[Masterobat::index() dan masterobat()](../../../app/Modules/Farmasi/Controllers/Masterobat.php) menampilkan halaman `farmasi/masterobat` dan menyediakan data melalui `farmasi/masterobat/masterobat`. Login dan grant katalog berlaku. UI berada di [v_masterobat](../../../app/Modules/Farmasi/Views/v_masterobat.php) dan [masterobat.js](../../../public/assets/js/farmasi/masterobat.js).

[MasterobatModel::masterobat()](../../../app/Modules/Farmasi/Models/MasterobatModel.php) menggunakan koneksi default. Query mengambil obat aktif dari `dt01_frm_obat_ms` dan menggabungkan `dt01_frm_harga_modal_obat_hd`, `dt01_frm_harga_jual_obat_hd`, serta hasil agregasi `dt01_frm_stock_out_dt` memakai `obat_id`. Aktivitas stok difilter ke tahun berjalan menurut database. Nama pembuat dibaca dari `dt01_gen_user_data`.

Query yang diperiksa tidak memiliki kondisi group/org. Kardinalitas tabel harga, pemisahan data di deployment, dan transaksi stok di luar modul ini belum diverifikasi.
