# 🏛️ ArtaLedger - Enterprise ERP Accounting System

**ArtaLedger** adalah sistem akuntansi ERP modern berbasis **Laravel 12**, **Livewire v3**, **Tailwind CSS**, dan **MySQL/PostgreSQL**. Aplikasi ini dirancang untuk menangani pencatatan jurnal transaksi, impor masif dari berkas Excel (*Jurnal Umum*), pengelolaan Saldo Awal, serta penyajian Laporan Keuangan enterprise secara otomatis, presisi, dan seimbang (*double-entry bookkeeping*).

---

## 🌟 Fitur Utama (Key Features)

### 1. 📥 Modul Impor Jurnal Excel Otomatis (`/accounting/import`)
- Membaca dan mengonversi sheet `Jurnal Umum` secara otomatis tanpa perlu manipulasi file manual.
- **VLOOKUP Evaluation Engine**: Membaca nilai terhitung VLOOKUP dari berkas Excel tanpa lag performance (`getOldCalculatedValue()`).
- **Pembersihan Otomatis**: Membuang baris kosong atau nominal Rp 0,00.
- **Deteksi Unit Otomatis**: Memetakan kata kunci keterangan (RS Tandun, Klinik Utama, Sri Rokan, dll) dengan *fallback* otomatis ke **Unit KP (Kantor Pusat)**.
- **Statistik & Keseimbangan**: 3 Kartu statistik real-time (Total Debit, Total Kredit, Selisih dengan toleransi pembulatan Rp 1,00).
- **Batch Deletion & Rollback**: Fitur hapus batch impor yang akan melakukan *cascade rollback* bersih pada data staging dan General Ledger.

### 2. 🧮 Posting & Laporan Saldo Awal (`/accounting/reports/opening-balance`)
- Berkas master: `database/data/saldo_awal.json` & `database/seeders/SaldoAwalSeeder.php`.
- Nominal terposting: **Rp 74.769.918.345,53** (Total Debit = Total Kredit = **100% BALANCE**, Selisih Rp 0,00).
- Menyimpan nilai desimal sen presisi dari catatan akuntansi asli (`66.654.717,91`).
- Terintegrasi penuh sebagai Jurnal Posting resmi `SA-2025-001` pada tanggal `01/01/2025`.
- Antarmuka **Read-Only** yang menyajikan rincian Kode Akun, Nama Akun, Normal Balance (**D/K**), Debit, dan Kredit.

### 3. 📊 Modul Laporan Keuangan Official
Terintegrasi dalam 6 menu laporan utama pada sidebar:
1. 📖 **Buku Besar** (`/accounting/reports/general-ledger`): Sub-navbar *Buku Besar (Header)* dan *Buku Besar Pembantu (Posting)*.
2. ⚖️ **Neraca** (`/accounting/reports/worksheet`): Sub-navbar *Neraca Lajur (Worksheet)*, *Neraca Saldo (Trial Balance)*, dan *Laporan Neraca (Balance Sheet)*.
3. 📈 **Laba Rugi** (`/accounting/reports/profit-loss`): Laporan kinerja operasional pendapatan & beban.
4. 💵 **Arus Kas** (`/accounting/reports/cash-flow`): Laporan penerimaan dan pengeluaran kas.
5. 🧮 **Saldo Awal** (`/accounting/reports/opening-balance`): Laporan posisi saldo awal resmi per periode bulan.
6. 📊 **Perubahan Ekuitas** (`/accounting/reports/changes-in-equity`): Laporan pergerakan modal pemilik.

### 4. ⚙️ Pengaturan & Master Data
- **Master Chart of Accounts (COA)** (`/accounting/accounts`): Manajemen akun bertingkat (*Header/Group* vs *Detail/Posting*) lengkap dengan *Normal Balance* (Debit/Kredit).
- **Jenis Jurnal** (`/accounting/journal-types`): Pengelolalan jenis jurnal transaksi.
- **Unit Perusahaan** (`/accounting/units`): Pengelolaan unit usaha (KP, RS Tandun, Klinik Utama, FKTP, dll).
- **Periode Akuntansi** (`/accounting/periods`): Pengelolaan status periode akuntansi (*Open/Closed*).

---

## 🚀 Panduan Instalasi (Installation Guide)

### Prasyarat System:
- PHP >= 8.2
- Composer >= 2.5
- Node.js >= 18.x & NPM
- MySQL >= 8.0 / PostgreSQL

### Langkah-Langkah Instalasi:

1. **Clone Repositori**:
   ```bash
   git clone https://github.com/wardana22/artaledger.git
   cd artaledger
   ```

2. **Install Dependensi Composer & NPM**:
   ```bash
   composer install
   npm install
   ```

3. **Konfigurasi Berkas Environment (`.env`)**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Atur kredensial database pada `.env`:*
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=artaledger
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Jalankan Migrasi Database & Seeder**:
   ```bash
   php artisan migrate:fresh --seed
   ```
   *Perintah ini otomatis memuat Master COA, Jenis Jurnal, Unit Perusahaan, Periode 2025-2026, dan Posting Jurnal Saldo Awal SA-2025-001.*

5. **Jalankan Server Lokal**:
   ```bash
   npm run dev
   # Pada terminal kedua:
   php artisan serve
   ```
   Akses aplikasi pada browser di `http://localhost:8000`.

---

## 🧪 Pengujian & Formatter Kode (Testing & Quality)

ArtaLedger menerapkan standar kualitas kode berbasis **Laravel Pint** dan pengujian otomatis **Pest PHP**.

### 1. Jalankan Pengujian Pest PHP:
```bash
vendor/bin/pest tests/Feature/Accounting/
```
*Hasil uji: 32 Tests Passed / 62 Assertions (100% Passed).*

### 2. Jalankan Code Formatter Pint:
```bash
vendor/bin/pint
```

---

## 📄 Lisensi Proyek

Hak Cipta © 2026 **ArtaLedger Team**. Seluruh hak cipta dilindungi undang-undang.
