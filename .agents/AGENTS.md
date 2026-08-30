# Agentic Rules & Operating Principles - ArtaLedger System

Dokumen ini mendefinisikan aturan dan alur kerja utama bagi AI Agen yang beroperasi dalam basis kode **ArtaLedger**.

---

## 🛡️ Prinsip Utama Operasional Agen

1. **Observe First (Amati Sebelum Tindakan)**
   - Selalu periksa skema database, rute Laravel, model Eloquent, controller, dan pengujian sebelum melakukan perubahan kode.
   - Jangan berasumsi mengenai struktur tabel, variabel, atau relasi tanpa pembuktian empiris.

2. **Atomic Mutations (Perubahan Atomik)**
   - Setiap modifikasi kode harus berfokus pada 1 masalah/fitur spesifik.
   - Hindari refactoring massal tanpa instruksi eksplisit.

3. **Alur Kerja Terstruktur (ECC Protocol)**
   - **Plan**: Buat rencana kerja dan verifikasi rancangan.
   - **Test**: Buat atau jalankan pengujian Pest PHP.
   - **Implement**: Tulis kode implementasi Laravel.
   - **Review**: Periksa standar kebersihan kode (Laravel Pint).
   - **Verify**: Masuk ke tahap verifikasi akhir sampai semua pengujian lulus.

4. **Kualitas Kode & Standar Sintaks**
   - Jalankan `vendor/bin/pint` untuk merapikan format kode PHP.
   - Gunakan `vendor/bin/pest` untuk memastikan tidak ada pengujian yang retak.

5. **AgentShield Security Guard**
   - Jangan pernah menyimpan rahasia, API Key, atau password dalam repositori. Gunakan `.env`.
   - Pastikan perlindungan CSRF, otorisasi Gate/Policy Laravel, dan validasi input selalu aktif pada setiap Form Request.

6. **Mandatory Project Skill Execution (Penggunaan Skill Proyek Wajib)**
   - Setiap kali memproses permintaan pengguna, agen WAJIB menginspeksi dan mematuhi pedoman skill terkait di folder `.agents/skills/` (seperti `ui-ux-pro-max`, `code-quality-and-testing`, `database-schema-management`, `ecc-agent-toolkit`, `seo-optimization`) sebelum merancang dan menulis kode.
