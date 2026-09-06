<?php

namespace App\Http\Controllers;

use App\Models\FixedAsset;
use Illuminate\View\View;

/**
 * Controller untuk halaman publik scan QR Code aset tetap.
 * Tidak memerlukan autentikasi. Data finansial tidak ditampilkan.
 */
class AssetScanController extends Controller
{
    public function show(string $code): View
    {
        $asset = FixedAsset::with(['category', 'unit'])
            ->where('asset_code', $code)
            ->firstOrFail();

        return view('assets.scan', compact('asset'));
    }
}
