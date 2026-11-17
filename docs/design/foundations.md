# Dasar desain sementara

Acuan: modul UR. Kembali ke [indeks](README.md).

## Warna

| Kegunaan | Warna acuan |
| --- | --- |
| Aksen utama / tombol / halaman aktif | #087e82 |
| Hover utama | #066b6e |
| Latar info / filter | #f6faf9 |
| Latar header tabel | #eff7f5 |
| Pilihan / tombol lembut | #e5f2ef |
| Judul | #234a54 |
| Teks isi | #42636d |
| Deskripsi | #81969b |
| Border card | #e1ebed |

Gunakan warna semantik untuk nilai negatif dan error. Jangan mengubah nilai negatif menjadi positif hanya untuk menyamakan warna. Header halaman memakai latar transparan; hindari gradasi dekoratif seperti versi UR lama.

## Tipografi dan jarak

Sumber global: public/assets/css/typography.css dan public/assets/css/compact-layout.css. Ukuran default desktop: isi 14px, caption 12px, tabel 13px, judul halaman 28px, judul card 20px. Header tabel memakai bobot 700 dari table-header.css.

Card tabel mengikuti padding 24px, turun menjadi 16px di ponsel; radius 16px. Filter memakai radius 14px dan tinggi kontrol 42px. Grid KPI memakai jarak 16px. Periksa CSS global yang menggunakan !important sebelum menambah override.

## Responsif

Gunakan min-width:0 pada item grid/flex. Card tidak boleh membuat halaman melebar. Hanya tabel yang boleh digulir horizontal. Search berpindah ke baris berikutnya saat ruang tidak cukup. Gunakan label terlihat, fokus keyboard yang jelas, dan teks selain warna untuk menjelaskan status.
