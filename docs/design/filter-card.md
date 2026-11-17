# Card filter

Acuan sementara: filter modul UR, khususnya Data E-Klaim. [Indeks](README.md).

Sumber global: public/assets/css/filter-card.css, dimuat oleh app/Views/template/head.php.

## Struktur

~~~html
<section class="app-filter-card" aria-labelledby="page-filter-title">
  <div class="app-filter-heading">
    <h2 id="page-filter-title"><i class="bi bi-funnel me-2" aria-hidden="true"></i>Filter data</h2>
    <p>Pilih periode dan jenis layanan untuk menyesuaikan data.</p>
  </div>
  <div class="app-filter-fields">
    <div>
      <label for="page-service">Jenis layanan</label>
      <select id="page-service" class="form-select">
        <option value="">Semua layanan</option>
        <option value="1">Rawat inap</option>
        <option value="2">Rawat jalan</option>
      </select>
    </div>
    <button type="button" class="btn btn-light-primary">Muat ulang</button>
  </div>
  <div class="app-filter-scope">Data berdasarkan tanggal pulang.</div>
</section>
~~~

Field 42px, gap 16px, radius card 14px, aksen #087e82. Label berada di atas kontrol. Tombol sejajar bagian bawah field. Di ponsel field tersusun vertikal. Native select dan Select2 memakai tema teal dari select-theme.css.

Pertahankan method/action form, ID, name, nilai option, dan event handler. Untuk submit server, ganti pembungkus field menjadi form.app-filter-fields dengan action/method yang sesuai. Contoh di atas adalah struktur visual; event Muat ulang tetap perlu dihubungkan script modul.

Ringkasan dan tabel harus mengikuti cakupan filter yang sama. Pergantian filter pada tabel server-side mengembalikan halaman ke awal. Pisahkan form laporan/unduhan dari area field filter. Jangan menghapus filter bisnis yang diperlukan hanya untuk menyederhanakan layout.
