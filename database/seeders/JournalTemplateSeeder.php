<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Company;
use App\Models\JournalTemplate;
use App\Models\JournalTemplateLine;
use App\Models\JournalType;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;

class JournalTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = Company::firstOrCreate(
            ['code' => 'ARTALEDGER'],
            ['name' => 'PT ArtaLedger Enterprise', 'is_active' => true]
        );

        $creator = User::first() ?? User::factory()->create(['name' => 'Staf Keuangan']);
        $journalTypeKas = JournalType::where('code', 'JK')->first() ?? JournalType::first();
        $journalTypeUmum = JournalType::where('code', 'JU')->first() ?? $journalTypeKas;

        $accKasPusat = Account::where('company_id', $company->id)->where('code', '11.01.01')->first()
            ?? Account::posting()->where('type', 'KAS')->first();

        $accGajiFktp = Account::where('company_id', $company->id)->where('code', '51.01.01')->first()
            ?? Account::posting()->where('report_type', 'laba_rugi')->first();

        $accGajiRs = Account::where('company_id', $company->id)->where('code', '51.01.03')->first()
            ?? $accGajiFktp;

        $accSosialFktp = Account::where('company_id', $company->id)->where('code', '51.01.02')->first();
        $accObatFktp = Account::where('company_id', $company->id)->where('code', '52.01.01')->first();

        $unitFktp = Unit::where('code', 'FKTP-01')->first() ?? Unit::first();
        $unitRs = Unit::where('code', 'RS-TANDUN')->first() ?? Unit::first();

        $templatesData = [
            [
                'template_code' => 'TPL-GAJI-01',
                'name' => 'Template Gaji & Tunjangan Karyawan Bulanan',
                'description' => 'Pembayaran Gaji & Tunjangan Karyawan Bulanan',
                'journal_type_id' => $journalTypeKas?->id,
                'lines' => [
                    [
                        'account_id' => $accGajiFktp?->id,
                        'unit_id' => $unitFktp?->id,
                        'description' => 'Biaya Gaji Karyawan FKTP',
                        'debit' => 0,
                        'credit' => 0,
                    ],
                    [
                        'account_id' => $accGajiRs?->id,
                        'unit_id' => $unitRs?->id,
                        'description' => 'Biaya Gaji Karyawan RS',
                        'debit' => 0,
                        'credit' => 0,
                    ],
                    [
                        'account_id' => $accKasPusat?->id,
                        'unit_id' => null,
                        'description' => 'Pembayaran Kas/Bank Utama',
                        'debit' => 0,
                        'credit' => 0,
                    ],
                ],
            ],
            [
                'template_code' => 'TPL-OBAT-01',
                'name' => 'Template Pemakaian Obat & Bahan Habis Pakai (BHP)',
                'description' => 'Pengakuan Biaya Pemakaian Obat-obatan & BHP Bulan Berjalan',
                'journal_type_id' => $journalTypeUmum?->id,
                'lines' => [
                    [
                        'account_id' => $accObatFktp?->id ?? $accGajiFktp?->id,
                        'unit_id' => $unitFktp?->id,
                        'description' => 'Pemakaian Obat & BHP FKTP',
                        'debit' => 0,
                        'credit' => 0,
                    ],
                    [
                        'account_id' => $accKasPusat?->id,
                        'unit_id' => null,
                        'description' => 'Kredit Kas/Persediaan',
                        'debit' => 0,
                        'credit' => 0,
                    ],
                ],
            ],
            [
                'template_code' => 'TPL-SOSIAL-01',
                'name' => 'Template Biaya Jaminan Sosial & BPJS Karyawan',
                'description' => 'Pembayaran Iuran Jaminan Sosial & BPJS Ketenagakerjaan',
                'journal_type_id' => $journalTypeKas?->id,
                'lines' => [
                    [
                        'account_id' => $accSosialFktp?->id ?? $accGajiFktp?->id,
                        'unit_id' => $unitFktp?->id,
                        'description' => 'Biaya Sosial Karyawan FKTP',
                        'debit' => 0,
                        'credit' => 0,
                    ],
                    [
                        'account_id' => $accKasPusat?->id,
                        'unit_id' => null,
                        'description' => 'Pembayaran via Bank Utama',
                        'debit' => 0,
                        'credit' => 0,
                    ],
                ],
            ],
        ];

        foreach ($templatesData as $tplData) {
            $lines = $tplData['lines'];
            unset($tplData['lines']);

            $template = JournalTemplate::updateOrCreate(
                [
                    'company_id' => $company->id,
                    'template_code' => $tplData['template_code'],
                ],
                array_merge($tplData, [
                    'is_active' => true,
                    'created_by' => $creator->id,
                ])
            );

            $template->lines()->delete();

            foreach ($lines as $index => $line) {
                if (! empty($line['account_id'])) {
                    JournalTemplateLine::create([
                        'journal_template_id' => $template->id,
                        'line_no' => $index + 1,
                        'account_id' => $line['account_id'],
                        'unit_id' => $line['unit_id'] ?? null,
                        'description' => $line['description'] ?? null,
                        'debit' => (float) ($line['debit'] ?? 0),
                        'credit' => (float) ($line['credit'] ?? 0),
                    ]);
                }
            }
        }
    }
}
