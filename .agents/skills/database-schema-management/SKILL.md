---
name: database-schema-management
description: Inspeksi skema database MySQL/PostgreSQL, manajemen migrasi, relasi Eloquent, dan seeder data sampel.
repository: https://github.com/laravel/framework
---

# Database Schema Management - ArtaLedger

Skill ini mengatur siklus hidup skema basis data ArtaLedger.

## 🗄️ Komponen & Perintah
- **Migrations**: Modifikasi tabel pembukuan, transaksi, akun (Chart of Accounts), dan laporan keuangan.
- **Eloquent Relations**: Pembentukan relasi `belongsTo`, `hasMany`, `morphTo`.
- **Seeder**: Pengisian sampel data akun dasar (`DatabaseSeeder`).
```bash
php artisan migrate:status
php artisan db:seed
```
