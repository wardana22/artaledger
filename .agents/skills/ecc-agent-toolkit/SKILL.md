---
name: ecc-agent-toolkit
description: Sistem optimalisasi alur kerja AI Agen (Engineering Control Center), AgentShield security, dan standarisasi siklus Plan -> Test -> Implement -> Review -> Verify.
repository: https://github.com/affaan-m/ecc
---

# ECC Agent Toolkit - ArtaLedger

Skill ini menyediakan kerangka kerja kontrol rekayasa sistem (*Engineering Control Center*) untuk AI Agen pada ArtaLedger.

## 🔄 Siklus Eksekusi ECC
1. **Branch & Plan**: Periksa konteks tugas. Jika berada di luar konteks branch saat ini, buat branch baru (`feature/<nama-fitur>`). Analisis kebutuhan dan buat draft rancangan perubahan.
2. **Test**: Siapkan test suite atau verifikasi kasus uji awal.
3. **Implement**: Eksekusi penulisan kode atau konfigurasi secara presisi.
4. **Review**: Lakukan linting (`pint`) dan analisis kode.
5. **Graphify Sync, Verify & Local Commit Only**:
   - **Sinkronisasi Knowledge Graph**: Pastikan berkas [docs/KNOWLEDGE_GRAPH.md](file:///d:/Belajar%20Laravel/artaledger/docs/KNOWLEDGE_GRAPH.md) telah diperbarui dengan rute, controller, service, model, atau test baru.
   - **Verifikasi**: Pastikan seluruh test Pest PHP passed.
   - **Komit Lokal**: Lakukan komit lokal pada branch fitur/topik. **SEKALI-KALI DILARANG MERGE ke `main` atau melakukan `git push` ke GitHub sebelum mendapatkan PERINTAH / INSTRUKSI EKSPLISIT DARI PENGGUNA**.
