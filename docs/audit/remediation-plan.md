# Rencana perbaikan

## Perubahan yang dapat diterapkan langsung

1. **Klaim dan UR** — Hilangkan BOM yang memblokir load controller BAHV. Redaksi log kedua importir dengan mempertahankan kontrak respons dan penghitung baris. Uji lint, resolusi controller, serta smoke test kegagalan sintetis sebelum menyimpulkan alur impor sehat.
2. **Farmasi** — Gunakan markup kartu tabel bersama dan tandai inisialisasi manual. Pertahankan selector pencarian, kolom, dan ukuran halaman. Escape nilai teks, encode segmen URL, dan beri kesempatan pesan gagal atau kosong untuk terlihat. Uji respons sintetis pada UI.
3. **Additional dan Developer** — Standarkan heading Welcome dan Backup Database melalui kelas banner bersama. Periksa desktop dan ponsel dengan markup dan stylesheet aktual, serta sinkronkan FAQ dan Knowledge Base.

## Perubahan yang memerlukan persetujuan atau bukti tambahan

| Prioritas | Tindakan |
| --- | --- |
| P0 | Tetapkan scope write Department dan Users beserta kebijakan pemilihan kepala unit. Terapkan predicate scope dan transaksi setelah kebijakan disetujui. Uji regresi dengan dua organisasi sintetis. |
| P0 | Tetapkan kebijakan outbound REST API, termasuk integrasi internal yang sah. Pertimbangkan allowlist, pembatasan egress, dan batas ukuran respons sesuai kebutuhan integrasi. |
| P0 | Inventaris aksi mutasi lama dari caller aktif. Terapkan CSRF dan pembatasan HTTP method secara bertahap dengan pengujian caller web. Tentukan kebijakan akses ClaimGuard dan telemetri NTI berdasarkan deployment. |
| P1 | Verifikasi kontrak fallback REST API dan route kanonik ViewMDC pada filesystem case-sensitive sebelum menyesuaikan route atau nama class. |
| P1 | Minta keputusan tentang ketepatan semantik kalender CA/PE, agregasi risiko CA, dan grain obat/harga. Validasi dengan fixture sintetis yang mendokumentasikan definisi operator. |
| P1 | Ukur query Pending dan tren UR memakai distribusi data nonproduksi serta EXPLAIN. Periksa I/O dan memori sebelum memilih strategi agregasi atau cache. |
| P2 | Konfirmasi cakupan ekspor Master Obat. Cari rancangan atau kontrak yang disetujui sebelum mengaktifkan aksi unduh. Rapikan cabang duplikat pemilihan aset sebagai perubahan terpisah setelah verifikasi alias. |

Setiap batch yang mengubah alur pengguna harus memperbarui FAQ dan Knowledge Base yang terkait. Hasil statis, pengujian sintetis, browser, dan database aktif dicatat terpisah.

## Urutan yang telah dijalankan

1. Impor BAHV/e-Klaim. Lint dan kasus sintetis sebelum perbaikan UI.
2. Kartu tabel Master Obat, banner Welcome/Backup, dan pemilihan aset detail UR.
3. Binding query baca Department dan perpindahan Master Obat serta Referensi MDC ke helper pemuatan.
4. Uji ulang aset bersama dan sinkronkan panduan modul serta FAQ.

Dua request awal ICS memakai modal global yang sama. Perbaikannya perlu fixture yang meliputi dua urutan respons dan status sukses parsial. Lihat [INF-027](findings.md).
