# Routing dan kontrak endpoint

[Routes.php](../../app/Config/Routes.php) adalah titik masuk. [Routing.php](../../app/Config/Routing.php) menetapkan `autoRoute = false`. Dispatcher aplikasi mengonstruksi namespace `Modules\\{Module}\\Controllers\\{Controller}` dengan `ucfirst` dan perlakuan khusus untuk `RestAPI`. Nama class dan file harus dapat di-resolve lewat autoload di filesystem target.

| Pola | Resolusi |
| --- | --- |
| GET `/{module}/{controller}` | `index()` |
| GET atau DELETE `/{module}/{controller}/{method}/{parameter}` | Method dengan satu argumen dari `(:any)` |
| GET, POST, atau DELETE `/{module}/{controller}/{method}` | Method tanpa argumen route |
| `/auth/*` yang eksplisit | Landing, login, signin, perubahan password, logout |
| POST `/ur/importtxteklaim`, `/bahv/importtxtbahv` | [Ur](../../app/Controllers/Ur.php) dan [Bahv](../../app/Controllers/Bahv.php) tingkat aplikasi |
| POST `/developer/backupdb/delete/{nama}` | `Backupdb::delete()` |
| GET `/developer/faq` | [Faq::index()](../../app/Modules/Developer/Controllers/Faq.php), halaman bantuan untuk pengguna dengan sesi login |
| `/api/v1/claimguard/*` | Route eksplisit ke [Claimguard](../../app/Modules/RestAPI/Controllers/Claimguard.php) |
| GET `/ur/analystslsimulation` | Redirect ke `ur/analystsl/simulation` |

Dispatcher closure menginstansiasi class langsung dan mengembalikan 404 jika class atau method tidak ditemukan. Method yang membutuhkan verb tertentu perlu mempertahankan validasi pada route atau controller yang menanganinya. Group API diletakkan sebelum pola HMVC. Ada fallback `ApiRouter::dispatch` di group API, tetapi class tersebut tidak ditemukan di direktori controller REST API yang diperiksa.

Untuk menelusuri endpoint, cocokkan verb pemanggil di `public/assets/js`, route, nama class, lalu [AuthFilter dan katalog akses](authentication.md). Untuk perubahan yang mencakup alias publik, telusuri juga pengecualian di [Filters.php](../../app/Config/Filters.php) dan validasi di controller. Aktivasi setiap entri katalog dan respons HTTP deployment belum diverifikasi.
