# CA — skrining kanker

| Fitur | Titik masuk |
| --- | --- |
| Skrining publik | [Caskrining::index(), start(), submit()](../../../app/Modules/Ca/Controllers/Caskrining.php) pada `ca/caskrining` dan aksi terkait, sesuai pengecualian filter. |
| Master kuesioner | [Masterqusioner](../../../app/Modules/Ca/Controllers/Masterqusioner.php) dan [MasterqusionerModel](../../../app/Modules/Ca/Models/MasterqusionerModel.php) mengelola instrumen, pertanyaan, opsi, dan rentang risiko. |
| Dashboard | [Dashboard](../../../app/Modules/Ca/Controllers/Dashboard.php) dan [DashboardModel::analytics()](../../../app/Modules/Ca/Models/DashboardModel.php) membaca respons aktif per group/org, rentang tanggal, dan filter instrumen/domisili. |

Definisi bawaan IASTO/APCS ada di [ScreeningMasters](../../../app/Modules/Ca/Config/ScreeningMasters.php). [CaskriningModel::store()](../../../app/Modules/Ca/Models/CaskriningModel.php) membedakan opsi `score`, `unscored`, dan `warning`. Total menjumlahkan opsi berskor, dengan nilai null bila tidak ada opsi berskor. Kategori mengambil rentang yang cocok. Dashboard memakai rentang master saat ini jika tersedia dan kategori tersimpan untuk instrumen tanpa rentang. Bandingkan jalur simpan dan dashboard sebelum analisis selisih.

Tabel `dt01_ca_instrument_ms`, `dt01_ca_question_ms`, `dt01_ca_response_hd`, dan `dt01_ca_response_dt` didefinisikan di [Database](../../../app/Modules/Ca/Database/). Model memakai koneksi CI4. Instrumen memberi scope pada respons publik. Master dan dashboard mengikuti login/grant. [Views](../../../app/Modules/Ca/Views/) berpasangan dengan [aset CA](../../../public/assets/js/ca/).

Master aktif, penerapan migrasi tipe jawaban, dan kecocokan instrumen dengan pedoman klinis belum diverifikasi.
