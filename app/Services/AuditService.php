<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Scrittura del log immutabile. Non registra mai password, token o payload sensibili:
 * chi chiama passa solo i campi rilevanti per la ricostruzione dell'azione.
 */
class AuditService
{
    public function log(string $action, ?Model $subject = null, array $payload = [], ?User $user = null): AuditLog
    {
        $user ??= Auth::user();

        return AuditLog::create([
            'user_id' => $user?->id,
            'action' => $action,
            'auditable_type' => $subject ? $subject::class : null,
            'auditable_id' => $subject?->getKey(),
            'payload' => $this->sanitize($payload),
            'ip_address' => $this->safeIp(),
            'user_agent' => substr((string) $this->safeUserAgent(), 0, 255) ?: null,
            'created_at' => now(),
        ]);
    }

    private function sanitize(array $payload): array
    {
        $forbidden = ['password', 'password_confirmation', 'token', 'remember_token', 'secret'];

        return collect($payload)
            ->reject(fn ($value, $key) => in_array(strtolower((string) $key), $forbidden, true))
            ->all();
    }

    private function safeIp(): ?string
    {
        try {
            return Request::ip();
        } catch (\Throwable) {
            return null;    // contesto console/queue
        }
    }

    private function safeUserAgent(): ?string
    {
        try {
            return Request::userAgent();
        } catch (\Throwable) {
            return null;
        }
    }
}
