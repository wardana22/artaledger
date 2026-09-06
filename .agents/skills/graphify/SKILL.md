---
name: graphify
description: Alat analisis basis kode untuk mengubah struktur aplikasi menjadi Knowledge Graph terstruktur di docs/KNOWLEDGE_GRAPH.md. Wajib dijalankan setiap kali ada penambahan atau pembaruan modul, rute, model, service, atau tes.
repository: https://github.com/Graphify-Labs/graphify
---

# Graphify - ArtaLedger System

Skill ini menyajikan pemetaan relasi arsitektural antar komponen basis kode ArtaLedger ke dalam visualisasi diagram graf (*Directed Acyclic Graph / DAG*) dan dokumen keterhubungan modul pada `docs/KNOWLEDGE_GRAPH.md`.

---

## ⚡ Aturan Wajib Eksekusi (Mandatory Trigger Policy)

Agen **WAJIB** memperbarui `docs/KNOWLEDGE_GRAPH.md` sebelum melakukan komit lokal atau menyelesaikan sesi tugas apabila terjadi salah satu dari kondisi berikut:
1. **Rute Baru / Berubah**: Penambahan atau modifikasi endpoint HTTP pada `routes/web.php` atau `routes/api.php`.
2. **Komponen Livewire / Controller Baru**: Pembuatan komponen Livewire di `app/Livewire/` atau Controller di `app/Http/Controllers/`.
3. **Domain Service Baru**: Pembuatan atau modifikasi service layer di `app/Domain/` atau `app/Services/`.
4. **Model & Skema Database**: Pembuatan Model di `app/Models/`, migrasi di `database/migrations/`, atau seeder master di `database/seeders/`.
5. **Pengujian Baru**: Pembuatan suite pengujian Pest PHP di `tests/Feature/` atau `tests/Unit/`.

---

## 📋 Prosedur Pembaruan Knowledge Graph

Ketika melakukan perubahan pada sistem, ikuti langkah berikut:

### 1. Periksa Entitas Terdampak
Identifikasi seluruh file baru dan terhubung:
- Rute $\rightarrow$ Livewire/Controller $\rightarrow$ Domain Services $\rightarrow$ Models $\rightarrow$ Views $\rightarrow$ Tests.

### 2. Mutakhirkan Diagram Mermaid DAG
Pada berkas [docs/KNOWLEDGE_GRAPH.md](file:///d:/Belajar%20Laravel/artaledger/docs/KNOWLEDGE_GRAPH.md):
- Tambahkan atau perbarui blok `subgraph` terkait.
- Hubungkan simpul rute (`A_*`), controller/livewire (`B_*` atau `C_*`), service (`S_*`), model (`M_*`), dan seeder/view.

### 3. Mutakhirkan Daftar Rincian Modul
Pastikan bagian daftar tautan file Markdown menyertakan:
- Tautan rute dan URL endpoint.
- File Controller / Livewire terkait beserta path lengkapnya.
- File Domain Service dan penjelasannya.
- File Model Eloquent dan migrasi terkait.
- File pengujian Pest PHP yang memvalidasi fitur tersebut.

### 4. Validasi Akhir
Pastikan tidak ada tautan rusak atau komponen baru yang tertinggal sebelum melangkah ke tahap komit Git.
