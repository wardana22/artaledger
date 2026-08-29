# 📜 Mapping Hak Akses (Permissions) per Menu, Tab Menu & Tombol Aksi

Dokumen ini membedah secara presisi 1-to-1 hubungan antara **Menu Navigasi**, **Tab Menu**, **Sub-Tab**, **Tombol Aksi Operasional**, dan **Kode Permission (*Permission Key*)** pada sistem **ArtaLedger**.

---

## 📂 KELOMPOK 1: MANAJEMEN AKUNTANSI

### 1.1 Menu Navigasi: Jurnal Transaksi (`/accounting/journals`)
- 📌 **Permission Utama Akses Menu**: `journals.view`

| Tingkat UI | Nama Menu / Tab / Aksi | Rute / Parameter | Kode Permission | Deskripsi & Kontrol Hak Akses |
|:---|:---|:---|:---|:---|
| **Tab 1** | **Daftar Jurnal (Posted & Reversed)** | `/accounting/journals?status=all` | `journals.view` | Melihat tabel transaksi jurnal yang sudah diposting / dibalikkan. |
| | 🔘 Tombol Lihat Detail & Audit Trail | Modal Pop-up | `journals.view` | Membuka rincian debit/kredit, poster, & timestamp transaksi. |
| | 🔘 Tombol Pembaikan Jurnal (*Reversal*) | Function Reversal | `journals.create` & `journals.post` | Membalikkan jurnal terposting dan membuat jurnal reversal otomatis. |
| **Tab 2** | **Approval Draft Jurnal** | `/accounting/journals?status=draft` | `journals.view` / `journals.edit` | Melihat daftar jurnal draft yang menunggu persetujuan. |
| | 🔘 Tombol Setujui & Posting Jurnal | Function Posting | `journals.post` | Menyetujui draft jurnal dan mempostingnya ke Buku Besar. |
| | 🔘 Tombol Edit Draft Jurnal | `/accounting/journals/{id}/edit` | `journals.edit` | Membuka form pengeditan racikan debit/kredit draft jurnal. |
| | 🔘 Tombol Hapus Draft Jurnal | Function Delete | `journals.delete` | Menghapus draft jurnal yang belum diposting. |
| **Sub-Form** | **Buat Jurnal Umum Baru** | `/accounting/journals/create` | `journals.create` | Membuka formulir pencatatan jurnal transaksi baru. |
| | 🔘 Tombol Simpan sebagai Draft | Action Form | `journals.create` | Menyimpan racikan jurnal dalam status draft. |
| | 🔘 Tombol Posting Langsung | Action Form | `journals.post` | Memvalidasi seimbang (*balanced*) & memposting langsung ke Buku Besar. |
| **Sub-Menu** | **Template Jurnal Berulang** | `/accounting/journals/templates` | `settings.templates` | Kelola racikan template akun debit & kredit berulang. |
| | 🔘 Tambah / Edit / Hapus Template | Modal Form | `settings.templates` | Membuat, mengedit, atau menghapus template jurnal. |
| **Sub-Form** | **Jurnal Penyesuaian (Adjustment)** | `/accounting/adjustments/create` | `journals.create` & `journals.post` | Membuka formulir khusus pencatatan jurnal penyesuaian. |

---

### 1.2 Menu Navigasi: Import Jurnal Excel (`/accounting/import`)
- 📌 **Permission Utama Akses Menu**: `journals.import` (atau `journals.create`)

| Tingkat UI | Nama Menu / Tab / Aksi | Rute / Parameter | Kode Permission | Deskripsi & Kontrol Hak Akses |
|:---|:---|:---|:---|:---|
| **Step 1** | **Upload File Excel** | Upload Dropzone | `journals.import` | Mengunggah file `.xlsx` / `.xls` berisi baris transaksi jurnal. |
| **Step 2** | **Validasi Baris Staging** | Staging Table | `journals.import` | Memeriksa keabsahan kode akun, tanggal, & keseimbangan debit/kredit. |
| **Step 3** | **Commit Posting ke Buku Besar** | Commit Action | `journals.import` & `journals.post` | Memindahkan transaksi staging bersih menjadi jurnal terposting. |
| **Aksi** | 🔘 Hapus Batch Staging | Action Delete Batch | `journals.import` | Menghapus staging import yang salah / gagal. |

---

### 1.3 Menu Navigasi: Periode Akuntansi (`/accounting/periods`)
- 📌 **Permission Utama Akses Menu**: `periods.view`

| Tingkat UI | Nama Menu / Tab / Aksi | Rute / Parameter | Kode Permission | Deskripsi & Kontrol Hak Akses |
|:---|:---|:---|:---|:---|
| **Fitur** | **Daftar Periode Akuntansi** | `/accounting/periods` | `periods.view` | Melihat tabel periode akuntansi & status (*open/closed/locked*). |
| **Aksi** | 🔘 Tambah Periode Baru | Modal Form | `periods.manage` | Membuka periode akuntansi baru. |
| **Aksi** | 🔘 Tutup Periode (*Close Period*) | Action Close | `periods.manage` | Mengunci pencatatan jurnal pada periode yang telah lewat. |
| **Aksi** | 🔘 Kunci Periode & Lihat Lock Key | Action Lock | `periods.manage_keys` | Mengunci & melihat Kunci Keamanan Rahasia 6-karakter (SuperAdmin). |
| **Aksi** | 🔘 Buka Kembali Periode (*Reopen*) | Modal Reopen | `periods.manage` | Membuka periode tertutup dengan memasukkan Lock Key + Alasan Audit. |

---

## 📂 KELOMPOK 2: LAPORAN KEUANGAN

### 2.1 Menu Navigasi: Buku Besar (General Ledger) (`/accounting/reports/general-ledger`)
- 📌 **Permission Utama Akses Menu**: `reports.view`

| Tingkat UI | Nama Menu / Tab / Aksi | Rute / Parameter | Kode Permission | Deskripsi & Kontrol Hak Akses |
|:---|:---|:---|:---|:---|
| **Tab 1** | **Buku Besar Akun Header (Group Ledger)** | `/accounting/reports/general-ledger` | `reports.view` | Memilih akun Header (Group) untuk melihat mutasi gabungan sub-akun. |
| **Tab 2** | **Buku Besar Pembantu (Subsidiary Ledger)** | `/accounting/reports/subsidiary-ledger` | `reports.view` | Memilih akun Posting (Detail) untuk audit rincian mutasi transaksi. |
| **Aksi** | 🔘 Filter Tanggal & Unit Perusahaan | Filter Bar | `reports.view` | Menyaring data laporan berdasarkan rentang tanggal & unit pengguna. |
| **Aksi** | 🔘 Ekspor / Cetak Laporan (Excel/PDF) | Action Export | `reports.export` | Mengunduh berkas laporan keuangan ke perangkat lokal. |

---

### 2.2 Menu Navigasi: Neraca (`/accounting/reports/worksheet`)
- 📌 **Permission Utama Akses Menu**: `reports.view`

| Tingkat UI | Nama Menu / Tab / Aksi | Rute / Parameter | Kode Permission | Deskripsi & Kontrol Hak Akses |
|:---|:---|:---|:---|:---|
| **Tab 1** | **Neraca Lajur 10-Kolom (Worksheet)** | `/accounting/reports/worksheet` | `reports.view` | Tampilan 10 kolom (Saldo Awal, Penyesuaian, Disesuaikan, Laba Rugi, Neraca). |
| **Tab 2** | **Neraca Saldo (Trial Balance)** | `/accounting/reports/trial-balance` | `reports.view` | Ringkasan saldo debit/kredit seluruh akun sebelum penyesuaian. |
| **Tab 3** | **Laporan Neraca Klasifikasi (Balance Sheet)**| `/accounting/reports/balance-sheet` | `reports.view` | Menyajikan Aset = Kewajiban + Ekuitas (termasuk Laba Bersih Berjalan). |
| **Aksi** | 🔘 Ekspor / Cetak Laporan (Excel/PDF) | Action Export | `reports.export` | Mengunduh laporan Neraca ke format Excel/PDF. |

---

### 2.3 Menu Navigasi: Laba Rugi (Profit & Loss) (`/accounting/reports/profit-loss`)
- 📌 **Permission Utama Akses Menu**: `reports.view`

| Tingkat UI | Nama Menu / Tab / Aksi | Rute / Parameter | Kode Permission | Deskripsi & Kontrol Hak Akses |
|:---|:---|:---|:---|:---|
| **Tampilan** | **Laba Rugi Bertingkat (4-Kolom)** | `/accounting/reports/profit-loss` | `reports.view` | Menyajikan Pendapatan, HPP, Laba Kotor, Beban Operasional, & Laba Bersih. |
| **Aksi** | 🔘 Ekspor / Cetak Laporan (Excel/PDF) | Action Export | `reports.export` | Mengunduh laporan Laba Rugi ke format Excel/PDF. |

---

### 2.4 Menu Navigasi: Arus Kas (Cash Flow) (`/accounting/reports/cash-flow`)
- 📌 **Permission Utama Akses Menu**: `reports.view`

| Tingkat UI | Nama Menu / Tab / Aksi | Rute / Parameter | Kode Permission | Deskripsi & Kontrol Hak Akses |
|:---|:---|:---|:---|:---|
| **Tampilan** | **Laporan Arus Kas (Direct Method)** | `/accounting/reports/cash-flow` | `reports.view` | Menyajikan Saldo Kas Awal + Penerimaan Kas - Pengeluaran Kas = Kas Akhir. |
| **Aksi** | 🔘 Ekspor / Cetak Laporan (Excel/PDF) | Action Export | `reports.export` | Mengunduh laporan Arus Kas ke format Excel/PDF. |

---

### 2.5 Menu Navigasi: Saldo Awal (`/accounting/opening-balance`)
- 📌 **Permission Utama Akses Menu**: `accounts.view` / `reports.view`

| Tingkat UI | Nama Menu / Tab / Aksi | Rute / Parameter | Kode Permission | Deskripsi & Kontrol Hak Akses |
|:---|:---|:---|:---|:---|
| **Tampilan** | **Daftar & Form Saldo Awal Akun** | `/accounting/opening-balance` | `accounts.view` | Melihat & menginput batch saldo awal akun per periode & unit. |
| **Aksi** | 🔘 Simpan Batch Saldo Awal | Action Save | `accounts.edit` | Menyimpan perubahan angka saldo awal akun. |

---

### 2.6 Menu Navigasi: Perubahan Ekuitas (`/accounting/reports/changes-in-equity`)
- 📌 **Permission Utama Akses Menu**: `reports.view`

| Tingkat UI | Nama Menu / Tab / Aksi | Rute / Parameter | Kode Permission | Deskripsi & Kontrol Hak Akses |
|:---|:---|:---|:---|:---|
| **Tampilan** | **Laporan Perubahan Ekuitas / Modal** | `/accounting/reports/changes-in-equity` | `reports.view` | Menyajikan Modal Awal + Laba Bersih Berjalan = Modal Akhir. |
| **Aksi** | 🔘 Ekspor / Cetak Laporan (Excel/PDF) | Action Export | `reports.export` | Mengunduh laporan Perubahan Ekuitas ke format Excel/PDF. |

---

## 📂 KELOMPOK 3: PENGATURAN & MASTER

### 3.1 Menu Navigasi: Master Akuntansi (`/accounting/accounts`)
- 📌 **Permission Utama Akses Menu**: `accounts.view`

| Tingkat UI | Nama Menu / Tab / Aksi | Rute / Parameter | Kode Permission | Deskripsi & Kontrol Hak Akses |
|:---|:---|:---|:---|:---|
| **Tab 1** | **Chart of Accounts (COA)** | `/accounting/accounts` | `accounts.view` | Tampilan hirarki pohon akun (Header & Posting Accounts). |
| | 🔘 Tombol Tambah Akun COA Baru | Modal Create Account | `accounts.create` | Membuat akun baru dalam struktur hirarki COA. |
| | 🔘 Tombol Edit Kode / Nama Akun | Modal Edit Account | `accounts.edit` | Mengubah informasi akun COA. |
| | 🔘 Tombol Hapus Akun COA | Action Delete | `accounts.delete` | Menghapus akun COA yang belum ber-transaksi. |
| **Tab 2** | **Master Jenis Jurnal** | `/accounting/journal-types` | `settings.journal_types` | Mengelola kode jenis jurnal (JK, BM, SA, dll). |
| | 🔘 Tambah / Edit / Hapus Jenis Jurnal | Modal Form | `settings.journal_types` | Membuat, mengedit, atau menghapus master jenis jurnal. |
| **Tab 3** | **Master Unit Perusahaan** | `/accounting/units` | `settings.units` | Mengelola daftar unit organisasi perusahaan (KP, RST, KU, dll). |
| | 🔘 Tambah / Edit / Hapus Unit | Modal Form | `settings.units` | Membuat, mengedit, atau menghapus master unit perusahaan. |

---

### 3.2 Menu Navigasi: Pengguna & Hak Akses (`/admin/users`)
- 📌 **Permission Utama Akses Menu**: `admin.users`

| Tingkat UI | Nama Menu / Tab / Aksi | Rute / Parameter | Kode Permission | Deskripsi & Kontrol Hak Akses |
|:---|:---|:---|:---|:---|
| **Tab 1** | **Daftar Pengguna** | `/admin/users` | `admin.users` | Kelola akun pengguna sistem. |
| | 🔘 Tombol Tambah Pengguna Baru | Modal Create User | `admin.users` | Mendaftarkan user baru dalam sistem. |
| | 🔘 Tombol Edit Pengguna & Penugasan Unit | Modal Edit User | `admin.users` | Mengatur Role & Penugasan Unit (*Multi-Tenant Data Isolation*). |
| **Tab 2** | **Peran & Hak Akses (Dynamic RBAC)** | `/admin/roles` | `admin.roles` (atau `settings.manage_roles`) | Kelola peran dinamis & matriks hak akses. |
| | 🔘 Tombol Tambah / Edit Peran | Modal Role Form | `admin.roles` | Membuat peran baru & mencentang matriks permission per modul. |
| | 🔘 Tombol Hapus Peran | Action Delete | `admin.roles` | Menghapus peran dinamis (Peran Super Admin terkunci). |
| **Tab 3** | **Audit Log Aktivitas** | `/admin/audit-logs` | `admin.audit_logs` | Melihat log rekam aktivitas sistem (Audit Trail). |
| | 🔘 Tombol Lihat Modal State JSON Diff | Pop-up Modal Diff | `admin.audit_logs` | Memeriksa perubahan data sebelum & sesudah (*before & after state*). |
