-- Rekomendasi indeks dashboard UR. Belum dijalankan otomatis.
-- Periksa indeks yang sudah ada dan jadwalkan DDL sesuai lingkungan database.
-- Filter query: KODE_RS = ? AND DISCHARGE_DATE >= ? AND DISCHARGE_DATE < ?
CREATE INDEX idx_ur_rs_discharge ON dt01_bpjs_ur_dt (KODE_RS, DISCHARGE_DATE);
