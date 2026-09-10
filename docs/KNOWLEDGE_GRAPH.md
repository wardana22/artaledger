# Knowledge Graph ArtaLedger System

Dokumen ini memetakan keterhubungan antarmodul dalam sistem **ArtaLedger** (*Directed Acyclic Graph / DAG*), mencakup seluruh rute HTTP, komponen Livewire, controller, domain service layer, model Eloquent, dan pengujian Pest PHP.

```mermaid
graph TD
    subgraph SG_Dashboard["0. Dasbor Finansial Eksekutif"]
        A_DASH["Route: /dashboard"] --> B_DASH["Livewire: DashboardIndex"]
        A_DASH_SET["Route: /dashboard/settings"] --> B_DASH_SET["Livewire: DashboardSettingsIndex"]
        
        B_DASH --> S_DMS["Service: DashboardMetricService"]
        B_DASH_SET --> S_DMS
        S_DMS --> M_DKPI["Model: DashboardKpi (12 Metrik Baku)"]
        S_DMS --> M_DCH["Model: DashboardChart (ApexCharts Multi-Series)"]
        S_DMS --> M_AGRP["Model: AccountGroup (6 Grup Khusus Dasbor)"]
        S_DMS --> M_JE["Model: JournalEntry"]
        S_DMS --> M_JL["Model: JournalLine"]
    end

    subgraph SG_Core["1. Master Data & Pengaturan"]
        A_COA["Route: /accounting/accounts"] --> B_COA["Livewire: AccountIndex"]
        A_GRP["Route: /accounting/account-groups"] --> B_GRP["Livewire: AccountGroupIndex"]
        A_UNT["Route: /accounting/units"] --> B_UNT["Livewire: UnitIndex"]
        A_JTY["Route: /accounting/journal-types"] --> B_JTY["Livewire: JournalTypeIndex"]
        A_CMP["Route: /accounting/settings/company"] --> B_CMP["Livewire: CompanySettingsIndex"]
        A_INI["Route: /accounting/initial-balance"] --> B_INI["Livewire: InitialBalanceIndex"]
        
        B_COA --> M_ACC["Model: Account"]
        B_GRP --> M_AGRP["Model: AccountGroup"]
        B_UNT --> M_UNT["Model: Unit"]
        B_JTY --> M_JTY["Model: JournalType"]
        B_CMP --> M_CMP["Model: Company (Branding & Signers)"]
        B_INI --> M_JE["Model: JournalEntry (SA-...)"]
        B_INI --> M_JL["Model: JournalLine"]
    end

    subgraph SG_Journal["2. Jurnal & Penyesuaian"]
        A_JRN["Route: /accounting/journals"] --> B_JRN["Livewire: JournalIndex, JournalForm"]
        A_ADJ["Route: /accounting/adjustments"] --> B_ADJ["Livewire: AdjustmentIndex, AdjustmentForm"]
        A_TPL["Route: /accounting/journals/templates"] --> B_TPL["Livewire: JournalTemplateIndex"]
        
        B_JRN --> M_JE["Model: JournalEntry"]
        B_JRN --> M_JL["Model: JournalLine"]
        B_ADJ --> M_JE
        B_TPL --> M_JTPL["Model: JournalTemplate"]
    end

    subgraph SG_Import["3. Modul Impor Excel"]
        A_IMP["Route: /accounting/import"] --> B_IMP["Livewire: JournalImportWizard"]
        B_IMP --> S_IMP1["Service: ExcelImportService"]
        B_IMP --> S_IMP2["Service: ImportValidationService"]
        B_IMP --> S_IMP3["Service: ImportCommitService"]
        S_IMP1 --> M_IMP["Models: ImportBatch, ImportFile, ImportRow"]
        S_IMP3 --> M_JE
        S_IMP3 --> M_JL
    end

    subgraph SG_Recon["4. Rekonsiliasi Bank BRI CMS"]
        A_REC["Route: /accounting/reconciliation"] --> B_REC["Livewire: BankReconciliationIndex"]
        A_RECD["Route: /accounting/reconciliation/{statement}"] --> B_RECD["Livewire: BankReconciliationDetail"]
        A_RECP["Route: /accounting/reconciliation/{statement}/pdf"] --> C_RECP["Controller: BankReconciliationPdfController"]
        
        B_REC --> S_BRI["Service: BriCmsPdfParserService"]
        B_RECD --> S_REC["Service: BankReconciliationService"]
        S_REC --> M_BST["Model: BankStatement"]
        S_REC --> M_BSL["Model: BankStatementLine"]
        S_REC --> M_BMG["Model: BankReconciliationMatchGroup"]
        S_REC --> M_JL
    end

    subgraph SG_Assets["5. Manajemen Aset Tetap & Depresiasi"]
        A_AST["Route: /accounting/fixed-assets"] --> B_AST["Livewire: FixedAssetIndex"]
        A_DEP["Route: /accounting/fixed-assets/depreciation"] --> B_DEP["Livewire: DepreciationRun"]
        A_SCN["Public Route: /a/{code}"] --> C_SCN["Controller: AssetScanController"]
        
        B_DEP --> S_DEP["Service: FixedAssetDepreciationService"]
        S_DEP --> M_AST["Model: FixedAsset"]
        S_DEP --> M_ACAT["Model: AssetCategory"]
        S_DEP --> M_ADEP["Model: AssetDepreciation"]
        S_DEP --> M_JE
    end

    subgraph SG_Reports["6. Paket Laporan Keuangan & Ekspor"]
        A_REP_GL["Route: .../reports/general-ledger"] --> B_REP_GL["Livewire: GeneralLedger"]
        A_REP_SL["Route: .../reports/subsidiary-ledger"] --> B_REP_SL["Livewire: SubsidiaryLedger"]
        A_REP_OB["Route: .../reports/opening-balance"] --> B_REP_OB["Livewire: OpeningBalanceIndex"]
        A_REP_WS["Route: .../reports/worksheet"] --> B_REP_WS["Livewire: Worksheet"]
        A_REP_TB["Route: .../reports/trial-balance"] --> B_REP_TB["Livewire: TrialBalance"]
        A_REP_PL["Route: .../reports/profit-loss"] --> B_REP_PL["Livewire: ProfitLoss"]
        A_REP_BS["Route: .../reports/balance-sheet"] --> B_REP_BS["Livewire: BalanceSheet"]
        A_REP_CF["Route: .../reports/cash-flow"] --> B_REP_CF["Livewire: CashFlow"]
        A_REP_EQ["Route: .../reports/changes-in-equity"] --> B_REP_EQ["Livewire: ChangesInEquity"]
        A_REP_AG["Route: .../reports/aging"] --> B_REP_AG["Livewire: AgingReport"]
        
        A_EXP_PDF["Route: .../reports/export/pdf/{type}"] --> C_PDF["Controller: FinancialReportPdfController"]
        A_EXP_XLS["Route: .../reports/export/excel/{type}"] --> C_XLS["Controller: FinancialReportExcelController"]
        
        B_REP_CF --> S_CF["Service: CashFlowService"]
        B_REP_AG --> S_AG["Service: AgingReportService"]
        B_REP_AG --> S_AGM["Service: AgingInvoiceManagerService"]
        C_PDF --> S_RPDF["Service: FinancialReportPdfService"]
        C_XLS --> S_RXLS["Service: FinancialReportExcelService"]
        S_RPDF --> S_CF
        S_RXLS --> S_CF
        S_RPDF --> S_AG
        S_RXLS --> S_AG
        
        S_AG --> M_INV["Model: ApArInvoice"]
        S_AG --> M_SET["Model: ApArSettlement"]
        S_AG --> M_PIV["Model: ApArInvoiceJournalLine (Pivot M:N)"]
        S_AGM --> M_INV
        S_AGM --> M_SET
        S_AGM --> M_PIV
        
        S_CF --> M_CFR["Model: CashFlowRow"]
        S_CF --> M_AGRP
        S_CF --> M_ACC
        S_CF --> M_JL
        
        B_REP_TB --> M_ACC
        B_REP_TB --> M_JL
        S_RPDF --> M_CMP
        S_RXLS --> M_CMP
    end
```

---

## 📂 Peta Modul & Keterhubungan File

### 0. Dasbor Finansial Eksekutif (`/dashboard` & `/dashboard/settings`)
- **Tampilan Utama Dasbor**: `/dashboard` $\rightarrow$ [DashboardIndex.php](file:///d:/Belajar%20Laravel/artaledger/app/Livewire/Dashboard/DashboardIndex.php) $\rightarrow$ [dashboard-index.blade.php](file:///d:/Belajar%20Laravel/artaledger/resources/views/livewire/dashboard/dashboard-index.blade.php)
  - **12 Kartu Metrik Baku Eksekutif**: Pendapatan, HPP/COGS, Laba Bersih, EBITDA, SGA to Sales, COGS to Sales, Laba Operasional, Beban SGA, EBT, NPM, ITO (Inventory Turnover), dan DSI (Days Sales of Inventory).
  - **3 Rasio Finansial Utama**: Current Ratio (Rasio Lancar), Net Profit Margin (Marjin Laba Bersih), dan Debt to Equity Ratio (DER).
  - **Visualisasi Tren Interaktif ApexCharts**: Tren Pendapatan vs Beban vs Laba 12 Bulan dan Tren Metrik Finansial multi-series.
  - **Tabel Transaksi Bernilai Signifikan**: Menampilkan 10 transaksi jurnal dengan nilai terbesar pada periode terpilih untuk kontrol eksekutif langsung.
  - **Filter Terpadu 4 Kolom**: Filter Unit Bisnis, Bulan Mulai, Bulan Selesai, dan Tahun Kalender.
- **Pengaturan & Personalisasi Dasbor**: `/dashboard/settings` $\rightarrow$ [DashboardSettingsIndex.php](file:///d:/Belajar%20Laravel/artaledger/app/Livewire/Dashboard/DashboardSettingsIndex.php) $\rightarrow$ [dashboard-settings-index.blade.php](file:///d:/Belajar%20Laravel/artaledger/resources/views/livewire/dashboard/dashboard-settings-index.blade.php)
  - Dilengkapi 3 tab navigasi terpadu: **Pengaturan Tampilan & Kartu KPI**, **Pengaturan Grafik Tren** (CRUD Grafik, tipe Area/Bar/Line, lebar 1/2 kolom, rentang bulan, multi-select metrik terhubung), serta **Grup Akun COA Kustom**. Urutan dan visibilitas dikontrol secara terpusat tanpa mengganggu tampilan dasbor utama.
- **Domain Service Layer**:
  - [DashboardMetricService.php](file:///d:/Belajar%20Laravel/artaledger/app/Domain/Dashboard/Services/DashboardMetricService.php): Engine kalkulasi 12 KPI dengan memoization transaksi, sinkronisasi 6 grup akun kustom (`DASH_COGS`, `DASH_SGA`, `DASH_EBITDA_ADJ`, `DASH_TAX`, `DASH_INVENTORY`, `DASH_COGS_INV`), kalkulasi rasio keuangan, dan pemrosesan multi-series grafik 12 bulan.
- **Model Eloquent**:
  - [DashboardKpi.php](file:///d:/Belajar%20Laravel/artaledger/app/Models/DashboardKpi.php) (Evaluasi rumus formula dinamis, urutan `order_index`, dan status visibilitas).
  - [DashboardChart.php](file:///d:/Belajar%20Laravel/artaledger/app/Models/DashboardChart.php) (Konfigurasi grafik ApexCharts dan pemetaan metrik).

### 1. Master Data & Pengaturan Perusahaan
- **Chart of Accounts (COA)**: [routes/web.php](file:///d:/Belajar%20Laravel/artaledger/routes/web.php) (`/accounting/accounts`) $\rightarrow$ [AccountIndex.php](file:///d:/Belajar%20Laravel/artaledger/app/Livewire/Accounting/Accounts/AccountIndex.php) $\rightarrow$ [Account.php](file:///d:/Belajar%20Laravel/artaledger/app/Models/Account.php)
- **Account Groups**: `/accounting/account-groups` $\rightarrow$ [AccountGroupIndex.php](file:///d:/Belajar%20Laravel/artaledger/app/Livewire/Accounting/Accounts/AccountGroupIndex.php) $\rightarrow$ [AccountGroup.php](file:///d:/Belajar%20Laravel/artaledger/app/Models/AccountGroup.php)
- **Unit Perusahaan**: `/accounting/units` $\rightarrow$ [UnitIndex.php](file:///d:/Belajar%20Laravel/artaledger/app/Livewire/Accounting/Settings/UnitIndex.php) $\rightarrow$ [Unit.php](file:///d:/Belajar%20Laravel/artaledger/app/Models/Unit.php)
- **Jenis Jurnal**: `/accounting/journal-types` $\rightarrow$ [JournalTypeIndex.php](file:///d:/Belajar%20Laravel/artaledger/app/Livewire/Accounting/Settings/JournalTypeIndex.php) $\rightarrow$ [JournalType.php](file:///d:/Belajar%20Laravel/artaledger/app/Models/JournalType.php)
- **Branding & Penandatangan Dokumen**: `/accounting/settings/company` $\rightarrow$ [CompanySettingsIndex.php](file:///d:/Belajar%20Laravel/artaledger/app/Livewire/Accounting/Settings/CompanySettingsIndex.php) $\rightarrow$ [Company.php](file:///d:/Belajar%20Laravel/artaledger/app/Models/Company.php)
  - Mengatur Logo Perusahaan, Favicon dinamis, Nama Perusahaan, dan Pejabat Penandatangan Laporan Keuangan (*Disusun*, *Diperiksa*, *Disetujui*).
- **Saldo Awal Perdana (Wizard Initial Balance)**: `/accounting/initial-balance` $\rightarrow$ [InitialBalanceIndex.php](file:///d:/Belajar%20Laravel/artaledger/app/Livewire/Accounting/Settings/InitialBalanceIndex.php) $\rightarrow$ [initial-balance-index.blade.php](file:///d:/Belajar%20Laravel/artaledger/resources/views/livewire/accounting/settings/initial-balance-index.blade.php)
  - Formulir terpandu setup saldo awal neraca sekali pakai (*initial cut-off setup*).
  - Dilengkapi perhitungan **Auto-Balance** real-time yang menyeimbangkan selisih Debit/Kredit secara otomatis ke akun **Laba Ditahan (Retained Earnings)**.
  - Mekanisme penguncian resmi (**Locking Mechanism**): Berstatus terkunci (*Locked*) saat saldo awal sudah terdaftar (`SA-...`), dengan verifikasi wajib password Super Admin dan pencatatan alasan audit (*Audit Trail*) saat membuka kunci darurat (*reopen/unlock*).

### 2. Jurnal Umum, Penyesuaian & Template
- **Jurnal Umum**: `/accounting/journals` $\rightarrow$ [JournalIndex.php](file:///d:/Belajar%20Laravel/artaledger/app/Livewire/Accounting/Journals/JournalIndex.php), [JournalForm.php](file:///d:/Belajar%20Laravel/artaledger/app/Livewire/Accounting/Journals/JournalForm.php)
- **Jurnal Penyesuaian**: `/accounting/adjustments` $\rightarrow$ [AdjustmentIndex.php](file:///d:/Belajar%20Laravel/artaledger/app/Livewire/Accounting/Journals/AdjustmentIndex.php), [AdjustmentForm.php](file:///d:/Belajar%20Laravel/artaledger/app/Livewire/Accounting/Journals/AdjustmentForm.php)
- **Template Jurnal**: `/accounting/journals/templates` $\rightarrow$ [JournalTemplateIndex.php](file:///d:/Belajar%20Laravel/artaledger/app/Livewire/Accounting/Journals/JournalTemplateIndex.php)
- **Model Inti**: [JournalEntry.php](file:///d:/Belajar%20Laravel/artaledger/app/Models/JournalEntry.php), [JournalLine.php](file:///d:/Belajar%20Laravel/artaledger/app/Models/JournalLine.php)

### 3. Modul Impor Jurnal Excel (`/accounting/import`)
- **Controller Web**: [JournalImportWizard.php](file:///d:/Belajar%20Laravel/artaledger/app/Livewire/Accounting/Import/JournalImportWizard.php)
- **Domain Services**:
  - [ExcelImportService.php](file:///d:/Belajar%20Laravel/artaledger/app/Domain/Import/Services/ExcelImportService.php) (Parsing Sheet & VLOOKUP Engine)
  - [ImportValidationService.php](file:///d:/Belajar%20Laravel/artaledger/app/Domain/Import/Services/ImportValidationService.php) (Validasi Kode Akun & Balance)
  - [ImportCommitService.php](file:///d:/Belajar%20Laravel/artaledger/app/Domain/Import/Services/ImportCommitService.php) (Posting ke Buku Besar)
  - [UnitMappingService.php](file:///d:/Belajar%20Laravel/artaledger/app/Domain/Import/Services/UnitMappingService.php) (Deteksi Kata Kunci Unit Otomatis)

### 4. Rekonsiliasi Bank BRI CMS (`/accounting/reconciliation`)
- **Daftar Rekening Koran**: `/accounting/reconciliation` $\rightarrow$ [BankReconciliationIndex.php](file:///d:/Belajar%20Laravel/artaledger/app/Livewire/Accounting/Reconciliation/BankReconciliationIndex.php)
- **Workspace Pencocokan**: `/accounting/reconciliation/{statement}` $\rightarrow$ [BankReconciliationDetail.php](file:///d:/Belajar%20Laravel/artaledger/app/Livewire/Accounting/Reconciliation/BankReconciliationDetail.php)
- **Domain Services**:
  - [BriCmsPdfParserService.php](file:///d:/Belajar%20Laravel/artaledger/app/Domain/Banking/Services/BriCmsPdfParserService.php) (Ekstraksi mutasi rekening koran format BRI Cash Management System)
  - [BankReconciliationService.php](file:///d:/Belajar%20Laravel/artaledger/app/Domain/Banking/Services/BankReconciliationService.php) (Pencocokan N:M fleksibel, toleransi pembulatan Rp 2, penanganan cek beredar/outstanding check, dan pelacakan transaksi lintas periode)
- **Ekspor PDF Berita Acara**: [BankReconciliationPdfController.php](file:///d:/Belajar%20Laravel/artaledger/app/Http/Controllers/BankReconciliationPdfController.php) $\rightarrow$ [reconciliation-report.blade.php](file:///d:/Belajar%20Laravel/artaledger/resources/views/pdf/reports/reconciliation-report.blade.php)

### 5. Manajemen Aset Tetap & Depresiasi Otomatis (`/accounting/fixed-assets`)
- **Register Aset Tetap**: `/accounting/fixed-assets` $\rightarrow$ [FixedAssetIndex.php](file:///d:/Belajar%20Laravel/artaledger/app/Livewire/Accounting/Assets/FixedAssetIndex.php)
  - CRUD Kategori Aset & Pemetaan Akun GL ([AssetCategory.php](file:///d:/Belajar%20Laravel/artaledger/app/Models/AssetCategory.php))
  - Upload Foto Aset & Manajemen Nilai Sisa/Umur Ekonomis ([FixedAsset.php](file:///d:/Belajar%20Laravel/artaledger/app/Models/FixedAsset.php))
  - Cetak Label QR Code & Barcode Industri (Mode Roll Thermal 80x50mm & Mode Grid Lembar A4)
- **Eksekusi Depresiasi Periodik**: `/accounting/fixed-assets/depreciation` $\rightarrow$ [DepreciationRun.php](file:///d:/Belajar%20Laravel/artaledger/app/Livewire/Accounting/Assets/DepreciationRun.php)
  - [FixedAssetDepreciationService.php](file:///d:/Belajar%20Laravel/artaledger/app/Domain/Asset/Services/FixedAssetDepreciationService.php) (Perhitungan metode Garis Lurus / Straight-Line dan penjurnalan otomatis ke Buku Besar)
- **Halaman Pemindaian Publik**: `/a/{code}` $\rightarrow$ [AssetScanController.php](file:///d:/Belajar%20Laravel/artaledger/app/Http/Controllers/AssetScanController.php) $\rightarrow$ [scan.blade.php](file:///d:/Belajar%20Laravel/artaledger/resources/views/assets/scan.blade.php)

### 6. Paket Laporan Keuangan & Ekspor Multi-Format
Modul pelaporan keuangan dilengkapi tombol dropdown ekspor terpadu ([report-export-dropdown.blade.php](file:///d:/Belajar%20Laravel/artaledger/resources/views/components/report-export-dropdown.blade.php)):
1. **Buku Besar (General Ledger)**: `/accounting/reports/general-ledger` ([GeneralLedger.php](file:///d:/Belajar%20Laravel/artaledger/app/Livewire/Accounting/Reports/GeneralLedger.php))
2. **Buku Besar Pembantu (Subsidiary Ledger)**: `/accounting/reports/subsidiary-ledger` ([SubsidiaryLedger.php](file:///d:/Belajar%20Laravel/artaledger/app/Livewire/Accounting/Reports/SubsidiaryLedger.php))
3. **Neraca Lajur (Worksheet)**: `/accounting/reports/worksheet` ([Worksheet.php](file:///d:/Belajar%20Laravel/artaledger/app/Livewire/Accounting/Reports/Worksheet.php))
   - Menggunakan prinsip *Strict Normal Balance Placement*: saldo akun (NS, ATB, Laba Rugi, Neraca) secara konsisten ditempatkan pada kolom saldo normalnya (debit/kredit) meskipun bernilai negatif/minus, tanpa otomatis dilempar ke kolom lawannya, sehingga total dan tata letak kolom sinkron sempurna dengan kertas kerja Excel.
4. **Neraca Saldo (Trial Balance)**: `/accounting/reports/trial-balance` ([TrialBalance.php](file:///d:/Belajar%20Laravel/artaledger/app/Livewire/Accounting/Reports/TrialBalance.php))
5. **Laba Rugi (Profit & Loss)**: `/accounting/reports/profit-loss` ([ProfitLoss.php](file:///d:/Belajar%20Laravel/artaledger/app/Livewire/Accounting/Reports/ProfitLoss.php))
   - Pemisahan murni akun Beban Pajak Penghasilan (`90`/`9`) yang menghasilkan banner inline **Laba/Rugi Setelah Pajak (Net Profit After Tax)**, diikuti oleh akun Pendapatan Komprehensif Lain (`91` - OCI PSAK 24) dan total akhir **Total Laba/Rugi Komprehensif Periode Berjalan** yang selaras 100% dengan standar PSAK dan lembar kerja Excel.
6. **Neraca (Balance Sheet)**: `/accounting/reports/balance-sheet` ([BalanceSheet.php](file:///d:/Belajar%20Laravel/artaledger/app/Livewire/Accounting/Reports/BalanceSheet.php))
7. **Arus Kas (Cash Flow - Direct Method)**: `/accounting/reports/cash-flow` ([CashFlow.php](file:///d:/Belajar%20Laravel/artaledger/app/Livewire/Accounting/Reports/CashFlow.php))
   - Menggunakan [CashFlowService.php](file:///d:/Belajar%20Laravel/artaledger/app/Domain/Accounting/Services/CashFlowService.php) dan entitas dinamis [CashFlowRow.php](file:///d:/Belajar%20Laravel/artaledger/app/Models/CashFlowRow.php).
   - Format 3 aktivitas utama (*Operasi*, *Investasi*, *Pembiayaan*) dengan rekonsiliasi Kenaikan Bersih Kas, Saldo Kas Awal, dan Saldo Kas Akhir.
   - Perhitungan otomatis Metode Langsung tersinkronisasi 100% dengan standar referensi akuntansi:
     - **Penerimaan Pelanggan**: Pendapatan neto dikurangi kenaikan piutang usaha.
     - **Pembayaran Karyawan**: Mutasi netto beban gaji & tunjangan operasional (`51.01`, `61.01` s/d `61.05`).
     - **Pembayaran Pemasok**: Rekonsiliasi modal kerja terpadu (Beban Operasional non-gaji + Akumulasi Penyusutan + Mutasi Persediaan + Beban Dibayar Dimuka + Aset Lancar Lainnya + Mutasi Hutang).
     - **Bunga & Pajak**: Mutasi akun beban bunga/administrasi dan panjar PPh 25.
   - Fitur drilldown accordion interaktif per baris menampilkan rincian akun-akun pembentuk nilai (Debit, Kredit, Netto).
   - Modal kustomisasi CRUD baris, urutan (*order index*), penentuan grup akun sumber, akun spesifik, atau rumus kalkulasi kustom (*formula expression*).
8. **Perubahan Ekuitas (Changes in Equity)**: `/accounting/reports/changes-in-equity` ([ChangesInEquity.php](file:///d:/Belajar%20Laravel/artaledger/app/Livewire/Accounting/Reports/ChangesInEquity.php))
9. **Saldo Awal**: `/accounting/reports/opening-balance` ([OpeningBalanceIndex.php](file:///d:/Belajar%20Laravel/artaledger/app/Livewire/Accounting/OpeningBalance/OpeningBalanceIndex.php))
10. **Laporan Umur Piutang & Hutang (Aging AR/AP)**: `/accounting/reports/aging` ([AgingReport.php](file:///d:/Belajar%20Laravel/artaledger/app/Livewire/Accounting/Reports/AgingReport.php))
    - Menggunakan [AgingReportService.php](file:///d:/Belajar%20Laravel/artaledger/app/Domain/Accounting/Services/AgingReportService.php) dan [AgingInvoiceManagerService.php](file:///d:/Belajar%20Laravel/artaledger/app/Domain/Accounting/Services/AgingInvoiceManagerService.php).
    - Model pendukung: [ApArInvoice.php](file:///d:/Belajar%20Laravel/artaledger/app/Models/ApArInvoice.php), [ApArSettlement.php](file:///d:/Belajar%20Laravel/artaledger/app/Models/ApArSettlement.php), dan [ApArInvoiceJournalLine.php](file:///d:/Belajar%20Laravel/artaledger/app/Models/ApArInvoiceJournalLine.php).
    - Fitur canggih:
      - **Specific Invoice Matching**: Menghindari salah sasaran pelunasan FIFO; pembayaran ditargetkan secara presisi ke faktur spesifik.
      - **Many-to-Many Relational Flexibility**:
        - *Split Invoicing (1 Jurnal Banyak Invoice)*: Memecah 1 baris pengakuan jurnal menjadi banyak nomor invoice dengan kalkulator validasi nominal real-time.
        - *Multi-Journal Consolidation (Banyak Jurnal 1 Invoice)*: Menggabungkan 2 atau lebih jurnal pengiriman/pekerjaan bertahap menjadi 1 lembar faktur fisik gabungan via seleksi checkbox.
        - *Multi-Journal to Multi-Invoice (Banyak Jurnal Banyak Invoice / M:N)*: Memecah akumulasi beberapa transaksi jurnal sekaligus menjadi beberapa lembar nomor faktur fisik dengan alokasi pivot proporsional dan kalkulator penyeimbang nominal real-time.
      - **Post-Period Invoice Assignment**: Menginput/menugaskan nomor faktur fisik yang terbit menyusul tanpa merusak kunci periode tutup buku akuntansi.
      - **Invoice Correction & Unlink Engine**:
        - *Edit Data Faktur Terdaftar*: Kemampuan mengoreksi nomor invoice, nama rekanan, tanggal faktur, tanggal jatuh tempo (otomatis menggeser bucket umur piutang/hutang), nominal (dengan validasi perlindungan batas pelunasan), dan memo catatan.
        - *Unlink (Batalkan Penugasan)*: Menghapus record faktur yang salah input dan mengembalikan baris jurnal terkait ke status *Unassigned/Belum Bernomor* untuk ditugaskan ulang, dengan proteksi ketat menolak pembatalan jika faktur telah memiliki riwayat pelunasan (*settlement*).
      - **Interactive Settlement UI Modal (Pencatatan & Pelunasan Faktur)**:
        - *Quick Cash/Bank Settle*: Memilih akun Kas/Bank tujuan/sumber, mencatat nominal pelunasan (penuh atau cicilan parsial), tanggal bayar, dan memo transfer, yang secara otomatis memposting jurnal transaksi ke buku besar dan mengalokasikan pelunasan ke faktur secara instan.
        - *Smart Payment Line Picker (Link Existing Payment Line)*: Pencarian real-time baris jurnal pembayaran yang sudah ada di buku besar tanpa limit kaku (mencari berdasarkan nomor jurnal, nomor referensi/dokumen fisik, atau keterangan transfer), dilengkapi filter rentang tanggal transaksi, filter sembunyikan baris yang habis teralokasi, kalkulasi sisa saldo kapasitas bayar (*available amount*), serta seleksi satu-klik otomatis mengisi nominal sisa tagihan.
        - *Settlement History & Rollback*: Menampilkan riwayat transaksi pembayaran terdahulu dengan opsi pembatalan pelunasan (*cancel settlement*) yang secara aman mengembalikan status dan sisa tagihan faktur.
      - **Dual-Tab Controller**: Beralih instan antara Piutang Usaha (AR) dan Hutang Usaha (AP).
      - **Executive KPI Cards & Bucket Umur**: Total Saldo Terbuka, Lancar/Current, Overdue 1-30, 31-60, 61-90, dan >90 hari.
      - **Drill-down Accordion**: Menampilkan rincian invoice per akun, nomor jurnal pembentuk, dan riwayat pelunasannya.
- **Ekspor Dokumen PDF**: [FinancialReportPdfController.php](file:///d:/Belajar%20Laravel/artaledger/app/Http/Controllers/FinancialReportPdfController.php) $\rightarrow$ [FinancialReportPdfService.php](file:///d:/Belajar%20Laravel/artaledger/app/Domain/Accounting/Services/FinancialReportPdfService.php) (Layout A4 landscape cetak siap tanda tangan)
- **Ekspor Dokumen Excel**: [FinancialReportExcelController.php](file:///d:/Belajar%20Laravel/artaledger/app/Http/Controllers/FinancialReportExcelController.php) $\rightarrow$ [FinancialReportExcelService.php](file:///d:/Belajar%20Laravel/artaledger/app/Domain/Accounting/Services/FinancialReportExcelService.php) (Spreadsheet multi-kolom bucket dengan format akuntansi)

### 7. Suite Pengujian Otomatis Pest PHP
- [tests/Feature/Accounting/AgingReportServiceTest.php](file:///d:/Belajar%20Laravel/artaledger/tests/Feature/Accounting/AgingReportServiceTest.php) (Validasi Single Assign, Split Invoicing 1-ke-Banyak, Specific Invoice Settlement, dan Akurasi Bucket Umur)
- [tests/Feature/Accounting/AgingReportLivewireTest.php](file:///d:/Belajar%20Laravel/artaledger/tests/Feature/Accounting/AgingReportLivewireTest.php) (Uji UI Livewire AgingReport, Tab Switcher, Modal Penugasan Invoice, serta Ekspor PDF & Excel)
- [tests/Feature/Dashboard/DashboardMetricEngineTest.php](file:///d:/Belajar%20Laravel/artaledger/tests/Feature/Dashboard/DashboardMetricEngineTest.php) (Validasi Engine 12 Metrik Eksekutif, Rasio Finansial, Multi-series Tren 12 Bulan, dan Seeder Grup Akun Kustom)
- [tests/Feature/Dashboard/DashboardSettingsTest.php](file:///d:/Belajar%20Laravel/artaledger/tests/Feature/Dashboard/DashboardSettingsTest.php) (Validasi Pengaturan Kartu KPI, Visibilitas, Urutan, dan Aksen Warna)
- [tests/Feature/Accounting/CashFlowDirectMethodTest.php](file:///d:/Belajar%20Laravel/artaledger/tests/Feature/Accounting/CashFlowDirectMethodTest.php) (Perhitungan Metode Langsung, Drilldown Akun Pembentuk, CRUD Baris & Rumus, serta Ekspor PDF/Excel)
- [tests/Feature/Accounting/TrialBalanceBalanceVerificationTest.php](file:///d:/Belajar%20Laravel/artaledger/tests/Feature/Accounting/TrialBalanceBalanceVerificationTest.php) (Verifikasi Neraca Saldo Seimbang)
- [tests/Feature/Accounting/FinancialReportPdfTest.php](file:///d:/Belajar%20Laravel/artaledger/tests/Feature/Accounting/FinancialReportPdfTest.php) (Validasi Cetak PDF Laporan Keuangan)
- [tests/Feature/Accounting/FinancialReportExcelTest.php](file:///d:/Belajar%20Laravel/artaledger/tests/Feature/Accounting/FinancialReportExcelTest.php) (Validasi Ekspor Excel Laporan Keuangan)
- [tests/Feature/Banking/BankReconciliationTest.php](file:///d:/Belajar%20Laravel/artaledger/tests/Feature/Banking/BankReconciliationTest.php) (Pencocokan Rekonsiliasi Bank)
- [tests/Feature/Banking/BankReconciliationMultiMatchTest.php](file:///d:/Belajar%20Laravel/artaledger/tests/Feature/Banking/BankReconciliationMultiMatchTest.php) (Pencocokan Multi N:M & Toleransi)
- [tests/Feature/Banking/BankReconciliationCrossPeriodTest.php](file:///d:/Belajar%20Laravel/artaledger/tests/Feature/Banking/BankReconciliationCrossPeriodTest.php) (Pencatatan Lintas Periode)
- [tests/Feature/Asset/FixedAssetTest.php](file:///d:/Belajar%20Laravel/artaledger/tests/Feature/Asset/FixedAssetTest.php) (Depresiasi & Label Aset Tetap)
- [tests/Feature/Accounting/CompanySettingsTest.php](file:///d:/Belajar%20Laravel/artaledger/tests/Feature/Accounting/CompanySettingsTest.php) (Pengaturan Branding & Signers)
- [tests/Feature/Accounting/FinancialReportsTest.php](file:///d:/Belajar%20Laravel/artaledger/tests/Feature/Accounting/FinancialReportsTest.php) (Pengujian Komponen Laporan Keuangan & Validasi Penempatan Kolom Strict Neraca Lajur)
- [tests/Feature/Accounting/OpeningBalanceTest.php](file:///d:/Belajar%20Laravel/artaledger/tests/Feature/Accounting/OpeningBalanceTest.php)
- [tests/Feature/Accounting/ImportJournalTest.php](file:///d:/Belajar%20Laravel/artaledger/tests/Feature/Accounting/ImportJournalTest.php)

---

### 8. Laporan Audit & Kepatuhan Sistem
- [docs/AUDIT_REPORT.md](file:///d:/Belajar%20Laravel/artaledger/docs/AUDIT_REPORT.md) (Laporan audit sistem menyeluruh: arsitektur, keamanan RBAC, integritas akuntansi, performa basis data, dan kualitas kode)
