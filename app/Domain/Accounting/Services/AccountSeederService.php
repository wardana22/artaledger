<?php

namespace App\Domain\Accounting\Services;

use App\Models\Account;
use App\Models\Company;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class AccountSeederService
{
    public function seedFromData(): int
    {
        $dataPath = database_path('data/accounts.json');

        if (! File::exists($dataPath)) {
            Log::error("Accounts data JSON file not found at: {$dataPath}");

            return 0;
        }

        $company = Company::firstOrCreate(
            ['code' => 'ARTALEDGER'],
            ['name' => 'PT ArtaLedger Enterprise', 'is_active' => true]
        );

        $accountsList = json_decode(File::get($dataPath), true) ?? [];
        $insertedCount = 0;

        foreach ($accountsList as $item) {
            $code = trim($item['code']);
            $name = trim($item['name']);
            $type = $item['type'] ?? null;
            $normalBalance = strtolower($item['normal_balance'] ?? 'debit') === 'credit' ? 'credit' : 'debit';
            $reportType = strtolower($item['report_type'] ?? 'neraca') === 'laba_rugi' ? 'laba_rugi' : 'neraca';
            $openingBalance = (float) ($item['opening_balance'] ?? 0);
            $isGroup = (bool) ($item['is_group'] ?? false);
            $isActive = (bool) ($item['is_active'] ?? true);
            $level = (int) ($item['level'] ?? 1);

            // Determine parent_id (prioritize parent_code from exported json)
            $parentId = null;
            if (! empty($item['parent_code'])) {
                $parentId = Account::where('company_id', $company->id)
                    ->where('code', $item['parent_code'])
                    ->first()?->id;
            } elseif (str_contains($code, '.')) {
                $lastDot = strrpos($code, '.');
                $parentCode = substr($code, 0, $lastDot);
                $parentId = Account::where('company_id', $company->id)->where('code', $parentCode)->first()?->id;
            } elseif (strlen($code) > 1) {
                $parentCode = substr($code, 0, 1);
                $parentId = Account::where('company_id', $company->id)->where('code', $parentCode)->first()?->id;
            }

            $updateData = [
                'parent_id' => $parentId,
                'name' => $name,
                'type' => $type,
                'normal_balance' => $normalBalance,
                'report_type' => $reportType,
                'opening_balance' => $openingBalance,
                'is_group' => $isGroup,
                'is_active' => $isActive,
                'level' => $level,
            ];

            if (isset($item['id'])) {
                $updateData['id'] = $item['id'];
            }

            Account::updateOrCreate(
                [
                    'company_id' => $company->id,
                    'code' => $code,
                ],
                $updateData
            );

            $insertedCount++;
        }

        Log::info("Seeded {$insertedCount} accounts from database/data/accounts.json into ArtaLedger.");

        return $insertedCount;
    }
}
