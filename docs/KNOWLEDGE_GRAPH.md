# Knowledge Graph ArtaLedger System

Dokumen ini memetakan keterhubungan antarmodul dalam sistem **ArtaLedger**.

```mermaid
graph TD
    SubGraph1["Master Data & COA"]
        A1["Route: /accounting/accounts"] --> B1["Livewire: AccountIndex"]
        B1 --> C1["AccountSeederService"]
        B1 --> D1["Model: Account"]

    SubGraph2["Accounting Periods"]
        A2["Route: /accounting/periods"] --> B2["Livewire: PeriodIndex"]
        B2 --> D2["Model: AccountingPeriod"]

    SubGraph3["Manual General Journal"]
        A3["Route: /accounting/journals"] --> B3["Livewire: JournalIndex & JournalForm"]
        B3 --> C3["JournalPostingService & JournalReversalService"]
        C3 --> D3["Model: JournalEntry & JournalLine"]
        C3 --> D1
        C3 --> D2

    SubGraph4["Financial Reports"]
        A4_1["Route: /accounting/reports/general-ledger"] --> B4_1["Livewire: GeneralLedger"]
        A4_2["Route: /accounting/reports/subsidiary-ledger"] --> B4_2["Livewire: SubsidiaryLedger"]
        A4_3["Route: /accounting/reports/trial-balance"] --> B4_3["Livewire: TrialBalance"]
        A4_4["Route: /accounting/reports/profit-loss"] --> B4_4["Livewire: ProfitLoss"]
        A4_5["Route: /accounting/reports/balance-sheet"] --> B4_5["Livewire: BalanceSheet"]
        A4_6["Route: /accounting/reports/cash-flow"] --> B4_6["Livewire: CashFlow"]
        A4_7["Route: /accounting/reports/changes-in-equity"] --> B4_7["Livewire: ChangesInEquity"]
```

## 📂 Peta Modul Fondasi Akuntansi & Laporan

### 1. Master COA
- **Route**: [routes/web.php](file:///d:/Belajar%20Laravel/artaledger/routes/web.php) (`/accounting/accounts`)
- **Livewire**: [app/Livewire/Accounting/Accounts/AccountIndex.php](file:///d:/Belajar%20Laravel/artaledger/app/Livewire/Accounting/Accounts/AccountIndex.php)
- **Model**: [app/Models/Account.php](file:///d:/Belajar%20Laravel/artaledger/app/Models/Account.php), [app/Models/Company.php](file:///d:/Belajar%20Laravel/artaledger/app/Models/Company.php)
- **Data**: [database/data/accounts.json](file:///d:/Belajar%20Laravel/artaledger/database/data/accounts.json)

### 2. Periode Akuntansi
- **Route**: `/accounting/periods`
- **Livewire**: [app/Livewire/Accounting/Periods/PeriodIndex.php](file:///d:/Belajar%20Laravel/artaledger/app/Livewire/Accounting/Periods/PeriodIndex.php)
- **Model**: [app/Models/AccountingPeriod.php](file:///d:/Belajar%20Laravel/artaledger/app/Models/AccountingPeriod.php)

### 3. Jurnal Umum Manual & Reversal
- **Routes**: `/accounting/journals`, `/accounting/journals/create`
- **Livewire**: [JournalIndex.php](file:///d:/Belajar%20Laravel/artaledger/app/Livewire/Accounting/Journals/JournalIndex.php), [JournalForm.php](file:///d:/Belajar%20Laravel/artaledger/app/Livewire/Accounting/Journals/JournalForm.php)
- **Services**: [JournalPostingService.php](file:///d:/Belajar%20Laravel/artaledger/app/Domain/Accounting/Services/JournalPostingService.php), [JournalReversalService.php](file:///d:/Belajar%20Laravel/artaledger/app/Domain/Accounting/Services/JournalReversalService.php)

### 4. Paket Laporan Keuangan
- **Buku Besar (Header)**: `/accounting/reports/general-ledger` ([GeneralLedger.php](file:///d:/Belajar%20Laravel/artaledger/app/Livewire/Accounting/Reports/GeneralLedger.php))
- **Buku Besar Pembantu (Detail)**: `/accounting/reports/subsidiary-ledger` ([SubsidiaryLedger.php](file:///d:/Belajar%20Laravel/artaledger/app/Livewire/Accounting/Reports/SubsidiaryLedger.php))
- **Neraca Saldo**: `/accounting/reports/trial-balance` ([TrialBalance.php](file:///d:/Belajar%20Laravel/artaledger/app/Livewire/Accounting/Reports/TrialBalance.php))
- **Laporan Laba Rugi**: `/accounting/reports/profit-loss` ([ProfitLoss.php](file:///d:/Belajar%20Laravel/artaledger/app/Livewire/Accounting/Reports/ProfitLoss.php))
- **Laporan Neraca**: `/accounting/reports/balance-sheet` ([BalanceSheet.php](file:///d:/Belajar%20Laravel/artaledger/app/Livewire/Accounting/Reports/BalanceSheet.php))
- **Laporan Arus Kas**: `/accounting/reports/cash-flow` ([CashFlow.php](file:///d:/Belajar%20Laravel/artaledger/app/Livewire/Accounting/Reports/CashFlow.php))
- **Laporan Perubahan Ekuitas**: `/accounting/reports/changes-in-equity` ([ChangesInEquity.php](file:///d:/Belajar%20Laravel/artaledger/app/Livewire/Accounting/Reports/ChangesInEquity.php))
