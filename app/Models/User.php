<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function units(): BelongsToMany
    {
        return $this->belongsToMany(Unit::class, 'unit_user');
    }

    /**
     * Check if user has global unit access (SuperAdmin or unassigned user)
     */
    public function hasGlobalUnitAccess(): bool
    {
        if ($this->hasRole('Super Admin')) {
            return true;
        }

        return $this->units()->count() === 0;
    }

    /**
     * Get array of unit IDs user is allowed to access. Returns [] for global access.
     */
    public function allowedUnitIds(): array
    {
        if ($this->hasGlobalUnitAccess()) {
            return [];
        }

        return array_map('intval', $this->allowedUnits()->pluck('id')->toArray());
    }

    /**
     * Get Collection of units the user is allowed to see in dropdown selectors.
     */
    public function allowedUnits()
    {
        if ($this->hasGlobalUnitAccess()) {
            return Unit::orderBy('code')->get();
        }

        return $this->units()->orderBy('code')->get();
    }

    /**
     * Get primary assigned unit or default first unit.
     */
    public function primaryUnit(): ?Unit
    {
        return $this->units()->first() ?? Unit::first();
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        $initials = Str::initials($this->name, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1).Str::substr($initials, -1)
            : $initials;
    }
}
