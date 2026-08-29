<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Company;

class AuditLogService
{
    /**
     * Record an audit log entry.
     */
    public static function record(
        string $eventType,
        string $description,
        mixed $auditable = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?int $userId = null
    ): AuditLog {
        $user = auth()->user();
        $actualUserId = $userId ?? $user?->id;
        $companyId = Company::first()?->id;

        return AuditLog::create([
            'company_id' => $companyId,
            'user_id' => $actualUserId,
            'event_type' => $eventType,
            'description' => $description,
            'auditable_type' => $auditable ? get_class($auditable) : null,
            'auditable_id' => $auditable ? $auditable->id : null,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
