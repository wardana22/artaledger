<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Models\Account;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\File;

$accounts = Account::with('parent')->orderBy('id')->get();

if ($accounts->isEmpty()) {
    echo "No accounts found in database.\n";
    exit(1);
}

$exported = [];
foreach ($accounts as $acc) {
    $exported[] = [
        'id' => $acc->id,
        'code' => $acc->code,
        'name' => $acc->name,
        'parent_code' => $acc->parent?->code,
        'type' => $acc->type,
        'normal_balance' => $acc->normal_balance,
        'report_type' => $acc->report_type,
        'opening_balance' => (float) $acc->opening_balance,
        'is_group' => (bool) $acc->is_group,
        'is_active' => (bool) $acc->is_active,
        'level' => (int) $acc->level,
    ];
}

$targetPath = database_path('data/accounts.json');

if (! File::isDirectory(dirname($targetPath))) {
    File::makeDirectory(dirname($targetPath), 0777, true);
}

File::put($targetPath, json_encode($exported, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

echo 'Successfully exported '.count($exported)." accounts from MySQL table 'accounts' to database/data/accounts.json!\n";
