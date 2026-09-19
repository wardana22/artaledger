---
name: accounting-core-guardian
description: Pengawas aturan pembukuan ganda (double-entry), kepatuhan audit trail, validasi balance jurnal, dan integritas periode akuntansi ArtaLedger.
repository: https://github.com/msitarzewski/agency-agents
---

# Accounting Core Guardian - ArtaLedger

Skill ini diadaptasi dari keahlian **Bookkeeper & Controller Specialist (The Agency)**, dirancang khusus untuk mengawal integritas data finansial, standar akuntansi pembukuan ganda (*double-entry bookkeeping*), dan keamanan audit transaksi pada sistem **ArtaLedger**.

---

## 🏛️ 5 Pilar Saklek Integritas Finansial (Hard Constraints)

Setiap agen yang memodifikasi atau membuat logika transaksi keuangan di ArtaLedger **WAJIB** mematuhi 5 pilar berikut:

### 1. Dual-Entry Zero-Tolerance (Keseimbangan Mutlak)
- **Aturan**: Total Debit dan Total Kredit pada setiap transaksi jurnal **HARUS PERSIS SAMA** (`sum(debit) === sum(credit)`).
- **Toleransi**: Toleransi selisih adalah **0 (Nol)**. Tidak diperkenankan adanya pembulatan sepihak yang menghasilkan jurnal gantung atau selisih 1 sen/rupiah tanpa akun penampung (*balancing account*) yang sah.
- **Tingkat Database**: Transaksi harus dibungkus dalam `DB::transaction()` agar jika salah satu baris gagal, seluruh transaksi di-rollback secara utuh.

### 2. Period Lock Enforcer (Gembok Periode Akuntansi)
- **Aturan**: Tidak boleh ada jurnal baru, edit jurnal, atau hapus jurnal pada tanggal transaksi yang berada di dalam `accounting_periods` berstatus `closed` atau `locked`.
- **Validasi**: Sebelum menyimpan baris jurnal, wajib memvalidasi tanggal transaksi:
  ```php
  // Cek apakah tanggal transaksi berada di periode yang terkunci
  $isPeriodLocked = AccountingPeriod::where('status', 'closed')
      ->whereDate('start_date', '<=', $entryDate)
      ->whereDate('end_date', '>=', $entryDate)
      ->exists();

  if ($isPeriodLocked) {
      throw new ValidationException('Periode akuntansi untuk tanggal ini sudah ditutup.');
  }
  ```

### 3. COA Integrity & Hierarchy Guard
- **Aturan**:
  - Hanya akun dengan tipe **`posting`** (bukan header/kategori induk) yang boleh memiliki mutasi transaksi.
  - Akun yang berstatus **`inactive` (nonaktif)** dilarang menerima mutasi baru.
  - Mata uang dan subakun (jika digunakan) harus valid dan terdaftar di sistem.

### 4. Immutable Audit Trail (Jejak Audit Abadi)
- **Aturan**: Jurnal yang sudah berstatus `posted` tidak boleh dihapus secara permanen (*hard-delete*) begitu saja.
- **Koreksi Transaksi**: Koreksi pembukuan harus dilakukan melalui mekanisme **Jurnal Pembalik (*Reversal Journal*)** atau pembatalan berstempel audit dengan mencatat alasan koreksi, waktu, dan `user_id` eksekutor.
- Setiap nomor voucher jurnal harus berurutan dan unik.

### 5. Bank & Cash Reconciliation Rigor
- **Aturan**: Saldo kas dan mutasi bank harus dapat direkonsiliasi hingga baris terkecil.
- Selisih rekonsiliasi kas/bank wajib diidentifikasi penyebabnya (misal: setoran dalam perjalanan, biaya admin bank, atau cek beredar), bukan disembunyikan.

---

## 📋 Checklist Pra-Implementasi Fitur Finansial
Sebelum mengakhiri tugas yang berkaitan dengan modul Jurnal, Kas/Bank, Hutang/Piutang, atau Laporan Keuangan, verifikasi pertanyaan ini:
1. [ ] Apakah fungsi ini telah menguji skenario input nominal bernilai `0` atau negatif?
2. [ ] Apakah ada potensi selisih desimal pada pembagian proporsional (pajak/diskon)?
3. [ ] Apakah pengecekan periode tutup buku (*closed period*) sudah aktif?
4. [ ] Apakah transaksi dibungkus `DB::transaction()` dengan proteksi rollback?
