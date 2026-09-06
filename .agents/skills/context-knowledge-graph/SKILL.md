---
name: context-knowledge-graph
description: Pemetaan arsitektur keterhubungan modul (Routes, Controllers, Services, Models, Tables, Views, Tests) ke dalam diagram DAG di docs/KNOWLEDGE_GRAPH.md. Wajib disinkronkan setiap pembaruan arsitektural.
repository: https://github.com/Graphify-Labs/graphify
---

# Context Knowledge Graph - ArtaLedger

Skill ini digunakan untuk mengelola dan menyinkronkan peta graf arsitektur sistem ArtaLedger pada berkas `docs/KNOWLEDGE_GRAPH.md`.

---

## 📌 Ketentuan Sinkronisasi Otomatis

Setiap kali agen mengimplementasikan fitur atau perbaikan yang menyentuh lapisan berikut:
- **Routes**: `routes/web.php`
- **Controllers & Livewire**: `app/Http/Controllers/`, `app/Livewire/`
- **Models & Migration**: `app/Models/`, `database/migrations/`, `database/seeders/`
- **Domain Services**: `app/Domain/`, `app/Services/`
- **Views**: `resources/views/`
- **Tests**: `tests/Feature/`, `tests/Unit/`

Agen **WAJIB** memperbarui diagram graf Mermaid dan dokumentasi keterhubungan modul pada `docs/KNOWLEDGE_GRAPH.md` sebelum melakukan komit lokal atau menandai tugas selesai.
