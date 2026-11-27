# Developer

Banner FAQ mengikuti [standar banner Betawi bersama](../../design/foundations.md#banner-betawi) dengan ornamen garis kecil yang tersebar acak. Pola dan overlay dikelola di CSS root.

Konten halaman FAQ untuk 16 modul dikelola di [HelpGuides](../../../app/Modules/Developer/Config/HelpGuides.php). [faq.js](../../../public/assets/js/developer/faq.js) menyediakan pencarian seluruh isi, navigasi modul, dan reset. [faq.css](../../../public/assets/css/developer/faq.css) mengatur layout responsif. Controller memeriksa grant dengan ModuleAccess sebelum menampilkan tombol pembuka fitur.

- [Mastermodules](../../../app/Modules/Developer/Controllers/Mastermodules.php) menangani hierarki, perubahan urutan, dan detail katalog `dt01_gen_modules_ms`. [MastermodulesModel](../../../app/Modules/Developer/Models/MastermodulesModel.php) menulis perubahan urutan dalam transaksi. Katalog dipakai oleh [routing dan auth](../authentication.md).
- [Os](../../../app/Modules/Developer/Controllers/Os.php) menangani setup aktivitas, preview/prune, dan ekspor/preview/apply sinkronisasi katalog. [ModuleCatalogSync](../../../app/Modules/Developer/Libraries/ModuleCatalogSync.php) memvalidasi dan menyusun rencana. Aksi tertentu melakukan pemeriksaan tambahan di controller.
- [Userlog](../../../app/Modules/Developer/Controllers/Userlog.php) merender timeline dan aktivitas terakhir per user per hari. [UserActivityModel](../../../app/Modules/Developer/Models/UserActivityModel.php) membaca `dt01_gen_user_activity` dalam scope tenant dengan retensi 30 hari. Welcome Additional memakai model ini untuk tampilan pribadi.
- [Backupdb](../../../app/Modules/Developer/Controllers/Backupdb.php) membuat, menampilkan, mengunduh, dan menghapus backup. [DatabaseDump](../../../app/Modules/Developer/Libraries/DatabaseDump.php) memakai koneksi CI4, dengan jalur utilitas dump untuk MySQLi. Periksa guard mutasi di controller.
- [Organization](../../../app/Modules/Developer/Controllers/Organization.php) dan [OrganizationModel](../../../app/Modules/Developer/Models/OrganizationModel.php) menangani `dt01_gen_organization_ms` dan akun administrator terkait di `dt01_gen_user_data`. [Masterjenisindikator](../../../app/Modules/Developer/Controllers/Masterjenisindikator.php) menangani `dt01_qi_jenis_indikator_ms`. Model memakai koneksi CI4.

Halaman di [Views](../../../app/Modules/Developer/Views/) dan [aset Developer](../../../public/assets/js/developer/) mengikuti route `developer/<controller>` dan grant katalog. Testing Page menjadi acuan [desain](../../design/README.md). [Database](../../../app/Modules/Developer/Database/) memuat definisi User Activity. Lokasi binary dump dan jadwal prune deployment belum diverifikasi.

[Faq::index()](../../../app/Modules/Developer/Controllers/Faq.php) merender [panduan penggunaan dan FAQ](../../../app/Modules/Developer/Views/v_faq.php) dari tombol sidebar bersama. GET `developer/faq` tersedia untuk pengguna dengan sesi login melalui [AuthFilter](../../../app/Filters/AuthFilter.php).

Agent memperbarui konten halaman bantuan dalam pekerjaan yang sama saat menambah atau mengubah fitur modul, mengikuti [aturan pemeliharaan FAQ](../../../AGENTS.md#pemeliharaan-faq--documentation). Panduan mencakup perilaku pengguna yang terverifikasi dan disesuaikan ketika fitur berubah atau dihapus.
