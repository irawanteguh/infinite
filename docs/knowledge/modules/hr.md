# HR — pengguna dan hak akses

[Users](../../../app/Modules/Hr/Controllers/Users.php) menangani daftar, pembuatan, edit, aktivasi, dan penghapusan pengguna pada `hr/users`. [UsersModel](../../../app/Modules/Hr/Models/UsersModel.php) memakai koneksi default dan data pengguna, organisasi, serta department dari tabel `dt01_gen_*`. Periksa scope pada masing-masing aksi sebelum mengubah operasi akun.

[Access](../../../app/Modules/Hr/Controllers/Access.php) menangani daftar, detail, dan simpan grant di `hr/access`. Akses diberikan kepada pengelola dalam group/org. [AccessModel::saveAccess()](../../../app/Modules/Hr/Models/AccessModel.php) membandingkan revisi untuk mendeteksi perubahan bersamaan serta menyimpan grant dan jejak audit dalam transaksi. `state()` dipakai untuk route dan navigasi.

[Migrasi akses](../../../app/Modules/Hr/Database/2026-11-13-000001_CreateUserModuleAccess.php) mendefinisikan `dt01_gen_access_manager`, `dt01_gen_user_module_access`, dan `dt01_gen_user_module_access_log`. Katalog berasal dari `dt01_gen_modules_ms`. UI berada di [Views](../../../app/Modules/Hr/Views/) dan [aset HR](../../../public/assets/js/hr/). Rujuk [autentikasi](../authentication.md) untuk alur penegakan akses. Daftar pengelola dan grant yang terpasang belum diverifikasi.
