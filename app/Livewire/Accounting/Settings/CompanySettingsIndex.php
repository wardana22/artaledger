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
#[Title('Pengaturan Perusahaan & Branding - ArtaLedger')]
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
            'logo' => 'nullable|image|max:2048', // 2MB Max
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'tax_number' => 'nullable|string|max:50',
        ]);

        $data = [
            'app_name' => $this->app_name,
            'name' => $this->name,
            'code' => strtoupper($this->code),
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'tax_number' => $this->tax_number,
        ];

        if ($this->logo) {
            // Delete old logo if exists
            if ($this->company->logo_path && Storage::disk('public')->exists($this->company->logo_path)) {
                Storage::disk('public')->delete($this->company->logo_path);
            }

            $logoPath = $this->logo->store('logos', 'public');
            $data['logo_path'] = $logoPath;
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
