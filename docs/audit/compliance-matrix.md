# Matriks kepatuhan

Status kategori memakai **C** Compliant, **P** Partially Compliant, **N** Non-Compliant, **NA** Not Applicable, dan **NV** Not Verified. Status C terbatas pada kriteria sampel di tabel bukti. NV mencakup kategori dengan bukti yang belum cukup. Overall adalah P bila bukti menunjukkan campuran capaian dan gap atau jika masih ada NV. Semua kategori modul yang relevan harus diperiksa sebelum Overall dapat menjadi C.

Skor memakai jumlah (C × 1 + P × 0,5 + N × 0) dibagi kategori terverifikasi. Coverage adalah kategori terverifikasi dibagi kategori yang berlaku. Perbandingan memakai kriteria dan denominator yang sama. Metrik agregat menjumlahkan kategori lintas modul, dengan bobot sama per kategori.

## Baseline

| Module | Architecture | UI/UX | Tables | Charts | Forms | JavaScript | Backend | Database | Security | Business Logic | Overall Status | Coverage | Score |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| Additional | P | N | NA | NA | NA | P | P | C | NV | NV | P | 5/7 (71.4%) | 50.0% |
| Auth | C | NV | NA | NA | P | P | P | NV | P | NV | P | 5/8 (62.5%) | 60.0% |
| Ca | C | NV | NV | N | C | C | C | P | C | NV | P | 7/10 (70.0%) | 78.6% |
| Developer | C | N | C | NV | NV | P | C | NV | NV | NV | P | 5/10 (50.0%) | 70.0% |
| Farmasi | C | P | N | NA | NA | N | P | P | N | NV | P | 7/8 (87.5%) | 35.7% |
| Hr | C | NV | NV | NA | C | NV | P | P | N | NV | P | 5/9 (55.6%) | 60.0% |
| Ics | C | NV | NV | NA | NV | P | P | NV | P | NV | P | 4/9 (44.4%) | 62.5% |
| Klaim | C | NV | C | P | NV | C | N | P | N | P | P | 8/10 (80.0%) | 56.2% |
| Nti | C | NV | NA | NA | NV | C | C | NA | P | NV | P | 4/7 (57.1%) | 87.5% |
| Organization | C | NV | C | NA | NV | NV | P | N | N | NV | P | 5/9 (55.6%) | 50.0% |
| Pe | C | NV | C | P | C | C | C | P | C | NV | P | 8/10 (80.0%) | 87.5% |
| Qi | C | C | C | C | NV | C | P | NV | P | NV | P | 7/10 (70.0%) | 85.7% |
| RestAPI | N | NV | NV | NA | NV | NV | P | NA | N | NV | P | 3/8 (37.5%) | 16.7% |
| Risk | C | NV | C | NV | C | C | C | C | C | NV | P | 7/10 (70.0%) | 100.0% |
| Satusehat | C | C | C | NA | C | C | C | C | C | NV | P | 8/9 (88.9%) | 100.0% |
| Ur | C | NV | C | P | NV | C | P | P | N | NV | P | 7/10 (70.0%) | 64.3% |

Coverage **95/144 (66.0%)**. Skor **69.5%**.

## Sesudah remediasi

| Module | Architecture | UI/UX | Tables | Charts | Forms | JavaScript | Backend | Database | Security | Business Logic | Overall Status | Coverage | Score |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| Additional | P | C | NA | NA | NA | C | P | C | NV | NV | P | 5/7 (71.4%) | 80.0% |
| Auth | C | NV | NA | NA | P | P | P | NV | P | NV | P | 5/8 (62.5%) | 60.0% |
| Ca | C | NV | NV | N | C | C | C | P | C | NV | P | 7/10 (70.0%) | 78.6% |
| Developer | C | C | C | NV | NV | P | C | NV | NV | NV | P | 5/10 (50.0%) | 90.0% |
| Farmasi | C | P | C | NA | NA | C | P | P | C | NV | P | 7/8 (87.5%) | 78.6% |
| Hr | C | NV | NV | NA | C | NV | P | P | N | NV | P | 5/9 (55.6%) | 60.0% |
| Ics | C | NV | NV | NA | NV | P | P | NV | P | NV | P | 4/9 (44.4%) | 62.5% |
| Klaim | C | NV | C | P | NV | C | C | P | P | P | P | 8/10 (80.0%) | 75.0% |
| Nti | C | NV | NA | NA | NV | C | C | NA | P | NV | P | 4/7 (57.1%) | 87.5% |
| Organization | C | NV | C | NA | NV | NV | P | N | N | NV | P | 5/9 (55.6%) | 50.0% |
| Pe | C | NV | C | P | C | C | C | P | C | NV | P | 8/10 (80.0%) | 87.5% |
| Qi | C | C | C | C | NV | C | P | NV | P | NV | P | 7/10 (70.0%) | 85.7% |
| RestAPI | N | NV | NV | NA | NV | NV | P | NA | N | NV | P | 3/8 (37.5%) | 16.7% |
| Risk | C | NV | C | NV | C | C | C | C | C | NV | P | 7/10 (70.0%) | 100.0% |
| Satusehat | C | C | C | NA | C | C | C | C | C | NV | P | 8/9 (88.9%) | 100.0% |
| Ur | C | NV | C | P | NV | C | C | P | P | NV | P | 7/10 (70.0%) | 78.6% |

Coverage **95/144 (66.0%)**. Skor **77.9%**.

## Bukti dan batas status per modul

Kriteria lintas modul mencakup resolusi namespace dan route, pola template, pemakaian helper tabel, grafik bulanan, proteksi form, siklus JavaScript, kontrak controller, serta scope dan bentuk akses data. Bukti di bawah menunjuk alur sampel. Perilaku produksi dan alur lain tetap memerlukan verifikasi.

| Modul | Bukti yang ditinjau dan batasnya |
| --- | --- |
| Additional | [Welcome](../../app/Modules/Additional/Controllers/Welcomepage.php) memanggil UserActivityModel dengan scope group/org/user. [Welcome view](../../app/Modules/Additional/Views/v_welcomepage.php) memuat panel tanggal dan heading. [ViewMDC](../../app/Modules/Additional/Controllers/ViewMDC.php) dan JS memakai daftar referensi dengan pencarian lokal, sehingga DataTables dan grafik tidak berlaku pada sampel. Resolusi slug case-sensitive masih perlu uji. |
| Auth | [Auth](../../app/Modules/Auth/Controllers/Auth.php) dan [SignModel](../../app/Modules/Auth/Models/SignModel.php) menunjukkan route login, penulisan sesi, validasi CSRF perubahan password, dan predicate group/org/user. Pemeriksaan baru terbatas pada sebagian alur form dan sesi. Login serta perubahan ID sesi belum diuji. |
| CA | [Caskrining](../../app/Modules/Ca/Controllers/Caskrining.php) memvalidasi alur form publik, termasuk CSRF. [DashboardModel](../../app/Modules/Ca/Models/DashboardModel.php) memakai scope group/org dan agregasi bulanan. [Dashboard JS](../../public/assets/js/ca/dashboard.js) meneruskan seri ke ApexCharts. Bulan kosong dan definisi rentang risiko membutuhkan verifikasi semantik. |
| Developer | [Backupdb](../../app/Modules/Developer/Controllers/Backupdb.php) memakai login, verb dan CSRF pada mutasi. [Backup JS](../../public/assets/js/developer/backupdb.js) dan Testing Page memakai helper tabel. [Operasional Sistem](../../app/Modules/Developer/Controllers/Os.php) dan [tes sinkronisasi](../../tests/developer/module-sync.php) memberi bukti transaksi dan plan katalog. Cakupan izin backup di deployment belum diuji. |
| Farmasi | [Masterobat](../../app/Modules/Farmasi/Controllers/Masterobat.php), [model](../../app/Modules/Farmasi/Models/MasterobatModel.php), [view](../../app/Modules/Farmasi/Views/v_masterobat.php), dan [JS](../../public/assets/js/farmasi/masterobat.js) membentuk daftar baca berisi harga dan total pemakaian. Semua hasil diambil sekaligus. Kardinalitas harga dan scope database aktif belum terverifikasi. |
| HR | [Access](../../app/Modules/Hr/Controllers/Access.php) dan [AccessModel](../../app/Modules/Hr/Models/AccessModel.php) memeriksa pengelola, CSRF, scope dan revisi. [Tes akses](../../tests/hr/access.php) memakai SQLite in-memory. [UsersModel](../../app/Modules/Hr/Models/UsersModel.php) membatasi scope daftar, sedangkan scope mutasi tertunda. |
| ICS | [Mastermdc](../../app/Modules/Ics/Controllers/Mastermdc.php) dan [MastermdcModel](../../app/Modules/Ics/Models/MastermdcModel.php) memakai master MDC, section, aturan, dan catatan. Aset [mastermdc.js](../../public/assets/js/ics/mastermdc.js) mengikat modal melalui delegasi. CSRF dan konsistensi alur edit lama perlu diperiksa sebelum perubahan. |
| Klaim | [PendingModel](../../app/Modules/Klaim/Models/PendingModel.php) menerapkan filter provider, tanggal half-open, dan limit detail. [Pending JS](../../public/assets/js/klaim/pending.js) serta [claimcases.js](../../public/assets/js/klaim/claimcases.js) memasok grafik/tabel. [Tes Pending](../../tests/klaim/pending.php) memeriksa metrik snapshot. Alur impor BAHV dan biaya parsing breakdown tercatat sebagai temuan. |
| NTI | [Nti](../../app/Modules/Nti/Controllers/Nti.php) dan [NtiClient](../../app/Modules/Nti/Services/NtiClient.php) bertanggung jawab atas snapshot perangkat, dengan respons no-store. [monitoring.js](../../public/assets/js/nti/monitoring.js) mencegah request tumpang tindih dan memakai AbortController. Perangkat dan akses di perimeter deployment belum diuji. |
| Organization | [Department](../../app/Modules/Organization/Controllers/Department.php) dan [DepartmentModel](../../app/Modules/Organization/Models/DepartmentModel.php) menunjukkan scope baca, SQL yang menyisipkan nilai sesi, dan dua write tanpa transaksi. [Setting](../../app/Modules/Organization/Controllers/Setting.php) memiliki verifikasi token sendiri. [Department JS](../../public/assets/js/organization/department.js) memakai helper tabel. |
| PE | [Prem](../../app/Modules/Pe/Controllers/Prem.php) memvalidasi verb/token untuk kiriman. [PremModel](../../app/Modules/Pe/Models/PremModel.php) menyimpan header-detail dalam transaksi. [DashboardModel](../../app/Modules/Pe/Models/DashboardModel.php) menghitung seri bulanan dan [aset dashboard](../../public/assets/js/pe/dashboard.js) merender ApexCharts dan tabel bersama. Semantik bulan kosong hitungan belum disepakati. |
| QI | [Master Indikator](../../app/Modules/Qi/Views/v_masterindikator.php) memakai banner dan tabel bersama. [summary.js](../../public/assets/js/qi/summary.js) mengisi 12 bulan, memakai null tanpa referensi, dan menghancurkan instance sebelum render ulang. [Indikatorunit](../../app/Modules/Qi/Controllers/Indikatorunit.php) memuat workflow unit dan lampiran; matriks izin seluruh aksi belum diverifikasi. |
| REST API | [Routes](../../app/Config/Routes.php) mendeklarasikan ClaimGuard dan fallback. [WorkspaceStorage](../../app/Modules/RestAPI/Libraries/WorkspaceStorage.php) memakai file lock dan rename atomik, dengan scope group/org dan jalur kompatibilitas. [Collection](../../app/Modules/RestAPI/Controllers/Collection.php) membatasi metode dan memeriksa CSRF. Kebijakan outbound dan trafik deployment belum diverifikasi. |
| Risk | [Register](../../app/Modules/Risk/Controllers/Register.php) memeriksa mutation POST/token. [RegisterModel](../../app/Modules/Risk/Models/RegisterModel.php) membatasi record group/org dan department. [register.js](../../public/assets/js/risk/register.js) memakai helper tabel. [RiskMatrix](../../app/Modules/Risk/Libraries/RiskMatrix.php) memiliki definisi penilaian di source, dengan master dan interpretasi deployment belum diperiksa. |
| SATUSEHAT | [Patient](../../app/Modules/Satusehat/Controllers/Patient.php) dan [Encounter](../../app/Modules/Satusehat/Controllers/Encounter.php) memeriksa sesi dan CSRF untuk jalur write. [LocationModel](../../app/Modules/Satusehat/Models/LocationModel.php) serta [OrganizationModel](../../app/Modules/Satusehat/Models/OrganizationModel.php) memfilter group/org/target. [encounter.js](../../public/assets/js/satusehat/encounter.js) memakai helper tabel dan lifecycle tab. Bridging dan ketepatan klinis belum diuji. |
| UR | [InternalReviewModel](../../app/Modules/Ur/Models/InternalReviewModel.php) membatasi scan antrean dan memfilter provider pada history. [Dashboard JS](../../public/assets/js/ur/dashboard.js) mengelola instance grafik dan label bulan. [app/Controllers/Ur.php](../../app/Controllers/Ur.php) menjadi pemilik impor bersama. Kehilangan data pada cohort sempit, biaya tren, dan metrik finansial perlu bukti tambahan. |

## Pemeriksaan lintas modul

| Area | Bukti utama |
| --- | --- |
| Controller, routing, filter | [BaseController](../../app/Controllers/BaseController.php), [Routes](../../app/Config/Routes.php), [AuthFilter](../../app/Filters/AuthFilter.php) dan [ModuleAccess](../../app/Libraries/ModuleAccess.php) menjadi rantai pemeriksaan akses. Perubahan keamanan shared bergantung pada inventaris aksi dan caller yang difilter. |
| Query, command, skema | [Database](../knowledge/database.md) memetakan koneksi dan skema dalam source. Shared ModelBahv dan Modelur menjadi jalur impor. Inventaris Commands meliputi setup, sinkronisasi, dan retensi. Perintah mutasi dan skrip database hanya dipetakan dari source. |
| Template, JS, CSS | [Template head](../../app/Views/template/head.php) memuat gaya root dan [script.php](../../app/Views/template/script.php) memuat root sebelum aset halaman. [Helper tabel](../../public/assets/js/root/filterdatatables.js) menangani lifecycle manual/otomatis dan lokasi dropdown. [Helper grafik](../../public/assets/js/root/monthlycharts.js) menormalkan kategori bulan dan mempertahankan nol yang memang dikirim seri. [Modal JS](../../public/assets/js/root/modalscroll.js) menginisialisasi Select2 pada modal. |
