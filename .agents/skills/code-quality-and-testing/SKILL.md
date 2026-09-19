---
name: code-quality-and-testing
description: Standarisasi kualitas kode PHP menggunakan Laravel Pint dan pengujian otomatis berbasis Pest PHP.
repository: https://github.com/pestphp/pest
---

# Code Quality & Testing - ArtaLedger

Skill ini memadukan pembakuan format kode PHP dan uji otomatis pada sistem ArtaLedger.

## 🧪 Perintah Verifikasi
```bash
# Formatting kode PHP
vendor/bin/pint

# Eksekusi pengujian otomatis
vendor/bin/pest
```

---

## 🧐 Protokol "Reality Checker" (Mental Model Skeptis)
Diadaptasi dari peran **Reality Checker Specialist (The Agency)**, agen dilarang berasumsi atau memberikan status kelulusan (*PASSED*) hanya berdasarkan klaim teoritis.

### Aturan Verifikasi Empiris:
1. **Default to Skepticism (Selalu Curiga)**:
   - Jangan percaya kode berjalan hanya karena tidak ada error sintaks.
   - Wajib mencari skenario kegagalan: format tanggal ganjil (`Y-m-d` vs `d/m/Y`), pemisah ribuan/desimal (titik vs koma), dan baris kosong di tengah file upload/import.
2. **Evidence-Based Approval**:
   - Status selesai hanya sah jika perintah `vendor/bin/pest` dijalankan langsung di terminal dengan hasil seluruh tes lulus (`PASS`).
   - Apabila terdapat *warning* atau *deprecation notice*, selesaikan terlebih dahulu sebelum menyatakan tugas tuntas.
3. **Boundary & Edge-Case Testing**:
   - Uji batas minimal dan maksimal (misal: input bernilai `0`, angka negatif, atau string sangat panjang).

