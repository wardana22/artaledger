<?php

namespace App\Livewire\Accounting\Settings;

use App\Models\Company;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('Pengaturan Perusahaan & Branding')]
class CompanySettingsIndex extends Component
{
    use WithFileUploads;

    public ?Company $company = null;

    public string $app_name = '';

    public string $name = '';

    public string $code = '';

    public $logo;

    public string $address = '';

    public string $phone = '';

    public string $email = '';

    public string $tax_number = '';

    // Penandatangan Laporan Keuangan
    public string $prepared_by_name = '';

    public string $prepared_by_title = '';

    public string $reviewed_by_name = '';

    public string $reviewed_by_title = '';

    public string $approved_by_name = '';

    public string $approved_by_title = '';

    public function mount(): void
    {
        if (auth()->check() && ! auth()->user()->can('settings.company') && ! auth()->user()->can('settings.manage') && ! auth()->user()->hasRole('Super Admin')) {
            abort(403, 'THIS ACTION IS UNAUTHORIZED.');
        }

        $this->company = Company::firstOrCreate([], [
            'code' => 'ALT',
            'name' => 'PT Arta Ledger',
            'app_name' => 'ArtaLedger',
        ]);

        $this->app_name = $this->company->app_name ?? config('app.name', 'ArtaLedger');
        $this->name = $this->company->name ?? '';
        $this->code = $this->company->code ?? '';
        $this->address = $this->company->address ?? '';
        $this->phone = $this->company->phone ?? '';
        $this->email = $this->company->email ?? '';
        $this->tax_number = $this->company->tax_number ?? '';

        $this->prepared_by_name = $this->company->prepared_by_name ?? 'Staff Akuntansi';
        $this->prepared_by_title = $this->company->prepared_by_title ?? 'Bagian Keuangan & Akuntansi';
        $this->reviewed_by_name = $this->company->reviewed_by_name ?? 'Manager Akuntansi';
        $this->reviewed_by_title = $this->company->reviewed_by_title ?? 'Accounting & Tax Lead';
        $this->approved_by_name = $this->company->approved_by_name ?? 'Direktur Keuangan';
        $this->approved_by_title = $this->company->approved_by_title ?? 'Chief Financial Officer (CFO)';
    }

    public function updatedLogo()
    {
        $this->validate([
            'logo' => 'nullable|file|mimes:jpeg,jpg,png,webp,svg,bmp,ico,gif',
        ]);
    }

    public function save()
    {
        if (auth()->check() && ! auth()->user()->can('settings.company') && ! auth()->user()->can('settings.manage') && ! auth()->user()->hasRole('Super Admin')) {
            abort(403, 'THIS ACTION IS UNAUTHORIZED.');
        }

        $this->validate([
            'app_name' => 'required|string|max:100',
            'name' => 'required|string|max:150',
            'code' => 'required|string|max:20|unique:companies,code,'.$this->company->id,
            'logo' => 'nullable|file|mimes:jpeg,jpg,png,webp,svg,bmp,ico,gif',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'tax_number' => 'nullable|string|max:50',
            'prepared_by_name' => 'nullable|string|max:100',
            'prepared_by_title' => 'nullable|string|max:100',
            'reviewed_by_name' => 'nullable|string|max:100',
            'reviewed_by_title' => 'nullable|string|max:100',
            'approved_by_name' => 'nullable|string|max:100',
            'approved_by_title' => 'nullable|string|max:100',
        ]);

        $data = [
            'app_name' => $this->app_name,
            'name' => $this->name,
            'code' => strtoupper($this->code),
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'tax_number' => $this->tax_number,
            'prepared_by_name' => $this->prepared_by_name,
            'prepared_by_title' => $this->prepared_by_title,
            'reviewed_by_name' => $this->reviewed_by_name,
            'reviewed_by_title' => $this->reviewed_by_title,
            'approved_by_name' => $this->approved_by_name,
            'approved_by_title' => $this->approved_by_title,
        ];

        if ($this->logo) {
            // Delete old logo if exists
            if ($this->company->logo_path && Storage::disk('public')->exists($this->company->logo_path)) {
                Storage::disk('public')->delete($this->company->logo_path);
            }

            $logoPath = $this->logo->store('logos', 'public');
            $data['logo_path'] = $logoPath;

            try {
                $fullPath = Storage::disk('public')->path($logoPath);
                if (file_exists($fullPath)) {
                    $imgInfo = @getimagesize($fullPath);
                    if ($imgInfo) {
                        $src = match ($imgInfo[2]) {
                            IMAGETYPE_JPEG => @imagecreatefromjpeg($fullPath),
                            IMAGETYPE_PNG => @imagecreatefrompng($fullPath),
                            IMAGETYPE_WEBP => @imagecreatefromwebp($fullPath),
                            default => null,
                        };
                        if ($src) {
                            $w = imagesx($src);
                            $h = imagesy($src);
                            $dst = imagecreatetruecolor(64, 64);
                            imagealphablending($dst, false);
                            imagesavealpha($dst, true);
                            imagecopyresampled($dst, $src, 0, 0, 0, 0, 64, 64, $w, $h);
                            imagepng($dst, public_path('favicon.png'));
                            imagepng($dst, public_path('favicon.ico'));
                            imagepng($dst, public_path('apple-touch-icon.png'));
                            imagedestroy($dst);
                            imagedestroy($src);
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Silently continue if GD format is unsupported (e.g. SVG)
            }
        }

        $this->company->update($data);
        $this->logo = null;

        AuditLogService::record(
            'company.updated',
            'Memperbarui Pengaturan Branding & Perusahaan ('.$this->name.')',
            $this->company
        );

        session()->flash('message', 'Pengaturan Branding & Perusahaan berhasil diperbarui.');

        return redirect()->route('accounting.settings.company.index');
    }

    public function removeLogo()
    {
        if (auth()->check() && ! auth()->user()->can('settings.company') && ! auth()->user()->can('settings.manage') && ! auth()->user()->hasRole('Super Admin')) {
            abort(403, 'THIS ACTION IS UNAUTHORIZED.');
        }

        if ($this->company->logo_path && Storage::disk('public')->exists($this->company->logo_path)) {
            Storage::disk('public')->delete($this->company->logo_path);
        }

        $this->company->update(['logo_path' => null]);
        $this->logo = null;

        AuditLogService::record(
            'company.logo_deleted',
            'Menghapus Logo Perusahaan',
            $this->company
        );

        session()->flash('message', 'Logo perusahaan berhasil dihapus.');

        return redirect()->route('accounting.settings.company.index');
    }

    public function render()
    {
        return view('livewire.accounting.settings.company-settings-index');
    }
}
