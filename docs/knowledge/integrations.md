# Integrasi aplikasi

## SATUSEHAT FHIR

[BirthWorkspace::api()](../../app/Modules/Satusehat/Libraries/BirthWorkspace.php) membaca environment yang dipilih dari workspace REST API dan memvalidasi target FHIR serta hash tujuan bila dipasok. Mengirim Bearer token lewat cURL dan dapat meminta token OAuth client credentials ketika token kosong atau saat 401 pada operasi yang memenuhi syarat. Token yang diperbarui disimpan selama instance workspace berjalan. Timeout koneksi 15 detik, request FHIR 90 detik secara default, dan OAuth 20 detik. Hasil berupa status HTTP dan body terurai. Kegagalan transport dibedakan dari respons HTTP.

[EncounterRegistration::bridge()](../../app/Modules/Satusehat/Libraries/EncounterRegistration.php) memulai catatan di [BridgingLogModel](../../app/Modules/Satusehat/Models/BridgingLogModel.php) sebelum pertukaran. Penyelesaian mencatat status HTTP, durasi, body, serta hasil `success`, `http_error`, atau `network_error` dalam scope group/org. Log bridging dapat memuat data pasien. Untuk mapping Patient, Organization, Location, Encounter, dan workspace kelahiran, lihat [SATUSEHAT](modules/satusehat.md).

## Workspace REST API dan ClaimGuard

[Collection::send()](../../app/Modules/RestAPI/Controllers/Collection.php) memeriksa sesi, POST, dan CSRF sebelum mengeksekusi request HTTP dari Collections dengan cURL, timeout koneksi 15 detik, dan total 90 detik. Respons sukses berisi status upstream, header, dan body. Kegagalan pengiriman dipetakan ke HTTP 502. [WorkspaceStorage](../../app/Modules/RestAPI/Libraries/WorkspaceStorage.php) mengelola penyimpanan file menurut scope sesi. ClaimGuard menyediakan template, preset, dan evaluasi aturan dari payload JSON. Detail [REST API](modules/restapi.md).

## NTI dan klaim

[NtiClient](../../app/Modules/Nti/Services/NtiClient.php) mengakses perangkat menggunakan konfigurasi environment dan cookie login di sisi server. Timeout koneksi 3 detik dan total 6 detik. [NTI](modules/nti.md) mendokumentasikan pembacaan parsial dan UI.

Data BPJS masuk melalui file e-Klaim dan BAHV. [Controller impor UR](../../app/Controllers/Ur.php) dan [Bahv](../../app/Controllers/Bahv.php) menormalisasi kolom input sebelum diteruskan ke model. Hubungan analitiknya dijelaskan di [UR](modules/ur.md) dan [Klaim](modules/klaim.md). Alur ini menunjukkan pertukaran berbasis file.

Kredensial, isi workspace, payload pasien, respons layanan, dan konektivitas jaringan deployment tidak diperiksa.
