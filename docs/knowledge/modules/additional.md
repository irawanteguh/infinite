# Additional

[Welcomepage::index() dan activityTimeline()](../../../app/Modules/Additional/Controllers/Welcomepage.php) merender `additional/welcomepage`. Ringkasan aktivitas menggunakan [UserActivityModel](../../../app/Modules/Developer/Models/UserActivityModel.php) dari Developer untuk membaca `dt01_gen_user_activity` dalam scope group, organisasi, dan pengguna sesi. Welcome dapat dibuka setelah login dengan pengecualian grant katalog. Lihat [autentikasi](../authentication.md).

[ViewMDC::index() dan datamastermdc()](../../../app/Modules/Additional/Controllers/ViewMDC.php) memakai template blank dan [ViewMDCModel](../../../app/Modules/Additional/Models/ViewMDCModel.php) untuk membaca `dt01_ics_mdc` melalui koneksi default. Master yang bisa diedit berada di [ICS](ics.md). Akses pembaca mengikuti grant route modul. Cocokkan kapitalisasi class dan aset pada filesystem target.

Tampilan ada di [Views](../../../app/Modules/Additional/Views/), dengan [aset ViewMDC](../../../public/assets/js/additional/). Aktivitas Welcome dan akses ViewMDC belum diuji melalui HTTP.
