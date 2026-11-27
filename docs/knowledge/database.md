# Database dan integritas data

## Koneksi

[Database.php](../../app/Config/Database.php) mendefinisikan `default` (MySQLi) dan `tests` (SQLite3 in-memory). Constructor memilih `tests` pada environment `testing`. Model dengan `DBGroup = null` memakai grup default yang berlaku. [SetupCaMasters](../../app/Commands/SetupCaMasters.php) memilih grup bernama `default` secara eksplisit. Beberapa model menerima koneksi lewat constructor untuk pengujian. Dialek SQL produksi yang terlihat mencakup `YEAR`, `DATE_FORMAT` dan `LEAST`, sehingga cakupan SQLite terbatas pada kode yang kompatibel atau punya cabang khusus.

## Definisi dari migrasi

| Sumber | Kunci dan relasi yang didefinisikan |
| --- | --- |
| [CA masters](../../app/Modules/Ca/Database/CreateScreeningMasters.php), [transaksi](../../app/Modules/Ca/Database/CreateScreeningTransactions.php) | PK `instrument_id`, `question_id`, `response_id`, dan pasangan detail `response_id/question_id`. FK pertanyaan dan header ke instrumen, detail ke header dan pertanyaan. Kode instrumen unik per group/org. |
| [PE masters](../../app/Modules/Pe/Database/2026-11-11-000001_CreateQuestionnaireMasters.php), [PREM](../../app/Modules/Pe/Database/2026-11-11-000003_CreatePremResponses.php), [PROM](../../app/Modules/Pe/Database/2026-11-11-000004_CreatePromResponses.php), [SKM](../../app/Modules/Pe/Database/2026-11-13-000012_SeparateSkmTransactions.php) | Master pertanyaan mengacu ke instrumen. Header PREM/PROM berkunci `response_id` dengan `submission_key` unik, detail berkunci `response_id/question_id` dan mengacu ke header. Migrasi SKM membuat tabel sendiri dan menambahkan FK pada jalur MySQL. [LinkPremToSkm](../../app/Modules/Pe/Database/2026-11-13-000013_LinkPremToSkm.php) menangani relasi PREM ke SKM. |
| [HR akses](../../app/Modules/Hr/Database/2026-11-13-000001_CreateUserModuleAccess.php), [User Activity](../../app/Modules/Developer/Database/2026-11-24-000001_CreateUserActivity.php) | Manager berkunci `group_id/org_id/user_id`, grant menambahkan `modules_id`, audit memakai `audit_id`. Aktivitas memakai `activity_id` dan indeks tenant/tanggal. |
| [SATUSEHAT migrations](../../app/Modules/Satusehat/Database/) | PK lokal per master dan transaksi. Organization/Location unik pada group/org/target/IHS. Encounter unik pada group/org/environment/nomor registrasi. Bridging log memiliki indeks group/org/Encounter. |
| [Skrip cost weight INA-CBG](../../app/Database/Scripts/casemix_cost_weights.sql) | Definisi master ranap dan rajal memakai PK `id`. Query CMI mencocokkan kode INA-CBG ke `fs_kd_inacbg` menurut jenis layanan. |

## Kontrak yang terlihat dari model

Nama tabel yang ditemukan umumnya memakai prefix `dt01_` dengan domain seperti `gen`, `qi`, `bpjs`, `pe`, atau `satusehat`. Suffix `_ms`, `_hd`, dan `_dt` lazim pada master, header, dan detail. [Indeks modul](modules.md) mengarah ke daftar tabel masing-masing. Contoh relasi yang terlihat dari query adalah `dt01_qi_indikator_it.referensi_id -> dt01_qi_indikator_hd.transaksi_id` di [SummaryModel](../../app/Modules/Qi/Models/SummaryModel.php) serta `obat_id` lintas master, harga, dan pemakaian pada [MasterobatModel](../../app/Modules/Farmasi/Models/MasterobatModel.php). Bukti join menetapkan kontrak query. Keberadaan constraint fisik memerlukan DDL atau pemeriksaan skema.

Scope tergantung domain. `group_id/org_id` digunakan antara lain pada HR, CA, PE, dan SATUSEHAT. QI juga memakai relasi department/PIC. UR memakai `kode_rs` pada agregasi e-Klaim. Cek query pemilik sebelum menyamakan tenant key atau menambah join.

Transaksi eksplisit mencakup grant beserta audit HR, header/detail CA dan PE, reorder katalog Developer, serta sinkronisasi master Organization/Location SATUSEHAT. Contoh import dan ekspor terdapat di [komponen bersama](shared-components.md). Agregasi perlu mengikuti grain, periode, dan perlakuan null tiap [aturan laporan](business-rules.md).

Penerapan dan urutan migrasi di setiap deployment, tipe kolom skema lama, FK yang hidup, serta indeks dan kardinalitas aktual belum diverifikasi.
