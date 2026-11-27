# Organization — unit dan kebijakan input indikator

| Route | Sumber |
| --- | --- |
| `organization/department` | [Department](../../../app/Modules/Organization/Controllers/Department.php) dan [DepartmentModel](../../../app/Modules/Organization/Models/DepartmentModel.php) mengelola master `dt01_gen_department_ms`, digunakan oleh HR, QI, Risk, dan laporan terkait. |
| `organization/appindikator` | [Appindikator](../../../app/Modules/Organization/Controllers/Appindikator.php) dan [AppindikatorModel](../../../app/Modules/Organization/Models/AppindikatorModel.php) menangani daftar/persetujuan indikator melalui `dt01_qi_indikator_hd`, master indikator, periode, dan tabel referensi. `updateapproval()` menyimpan status, pengguna, dan waktu persetujuan. |
| `organization/setting` | [Setting](../../../app/Modules/Organization/Controllers/Setting.php) dan [SettingModel](../../../app/Modules/Organization/Models/SettingModel.php) menangani pengaturan batas dan perpanjangan input; referensi `dt01_qi_input_setting` dan `dt01_qi_input_grant`. |

Model memakai koneksi CI4. Route mengikuti login/grant. [IndicatorDeadline](../../../app/Modules/Organization/Services/IndicatorDeadline.php) digunakan oleh workflow indikator [QI](qi.md). Master organisasi global berada di [Developer](developer.md). UI ada di [Views](../../../app/Modules/Organization/Views/) dan [aset Organization](../../../public/assets/js/organization/).

Status dan batas efektif di deployment serta cakupan per aksi persetujuan perlu dicek pada data dan controller terkait.

Query daftar Department dan pilihan kepala unit membind group dan organisasi dari sesi. Cakupan baca dirinci pada DepartmentModel.
