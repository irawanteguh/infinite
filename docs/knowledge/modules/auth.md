# Auth

[Auth](../../../app/Modules/Auth/Controllers/Auth.php) menangani landing dan login, POST `auth/signin` dan `auth/changepassword`, serta `auth/logout`. [SignModel](../../../app/Modules/Auth/Models/SignModel.php) membaca `dt01_gen_user_data` dan organisasi terkait di `dt01_gen_organization_ms` melalui model CI4. Akun aktif, pemeriksaan kredensial, dan scope perubahan password dibahas di [autentikasi](../authentication.md).

[Views](../../../app/Modules/Auth/Views/) memuat landing dan form login. [Modal](../../../app/Modules/Auth/Modal/) dan [aset auth](../../../public/assets/js/auth/) mencakup UI perubahan password. [HR](hr.md) menjadi titik masuk pengelolaan akun dan grant. [Developer](developer.md) memiliki master organisasi dan model penyimpanan User Log.

Pengecualian filter global untuk route auth dan pemeriksaan internal controller berlaku per aksi. Login, data akun deployment, dan masa sesi di web server belum diverifikasi.
