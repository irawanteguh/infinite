# Knowledge Base Infinite

Gunakan indeks ini untuk menemukan titik masuk kode yang sesuai dengan tugas. Mulai dari satu dokumen dan ikuti tautan ke file pemilik perilaku. Catatan ini merangkum hasil pemeriksaan repositori pada 27 November 2026. Konfigurasi deployment, isi katalog modul, kondisi skema aktif, dan hasil layanan eksternal memerlukan verifikasi tersendiri.

| Tugas | Mulai dari |
| --- | --- |
| Pengembangan backend | [Arsitektur](architecture.md), [Routing](routing.md) |
| Redesain frontend | [Panduan desain](../design/README.md), [Komponen bersama](shared-components.md) |
| Autentikasi dan izin | [Autentikasi](authentication.md), [HR](modules/hr.md) |
| Perubahan database | [Database](database.md), migrasi modul terkait |
| Integrasi API | [Integrasi](integrations.md), [REST API](modules/restapi.md) |
| Pengembangan dashboard | [Aturan bisnis](business-rules.md), [Panduan grafik](../design/charts.md) |
| Analisis data | [Database](database.md), [Aturan bisnis](business-rules.md) |
| Indikator mutu | [QI](modules/qi.md), [Organization](modules/organization.md) |
| Klaim BPJS | [Klaim](modules/klaim.md), [UR](modules/ur.md) |
| SATUSEHAT | [SATUSEHAT](modules/satusehat.md), [Integrasi](integrations.md) |
| Pengembangan modul | [Katalog modul](modules.md) |
| Debugging dan validasi | [Pengembangan](development.md), [Routing](routing.md) |

Setiap halaman modul mencantumkan controller, model atau client, aset, cakupan akses, dan batas verifikasi. Tautan migrasi menunjukkan definisi yang tersedia di repositori. Relasi dari SQL join menunjukkan cara kode membaca data. Verifikasi sumber yang dirujuk sebelum mengubah implementasi, lalu perbarui bagian dokumentasi yang terdampak dalam perubahan yang sama.
