<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;

trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(function ($model) {
            $model->recordAuditActivity('created', null, $model->getAttributes());
        });

        static::updated(function ($model) {
            $dirtyFields = $model->getDirty();
            // Jangan catat jika hanya updated_at yang berubah
            if (count($dirtyFields) === 1 && isset($dirtyFields['updated_at'])) {
                return;
            }

            $old = array_intersect_key($model->getOriginal(), $dirtyFields);
            $new = $dirtyFields;
            $model->recordAuditActivity('updated', $old, $new);
        });

        static::deleted(function ($model) {
            $model->recordAuditActivity('deleted', $model->getOriginal(), null);
        });
    }

    protected function recordAuditActivity(string $event, ?array $old, ?array $new): void
    {
        try {
            $userId = Auth::id();
            $branchId = null;
            $actorType = 'User';
            $userAgent = null;
            $ipAddress = null;

            if (Auth::check()) {
                $user = Auth::user();
                $branchId = ($user->b2b_type === 'branch' || $user->role === 'branch') ? $user->id : null;
                $actorType = ($user->role === 'admin') ? 'Admin HQ' : (($user->b2b_type === 'branch') ? 'Branch Staff' : 'Customer / B2B');
                $userAgent = request()?->userAgent();
                $ipAddress = request()?->ip();
            } else {
                // Penanganan Aktor Otomatis / Background Job / Webhook / CLI
                if (App::runningInConsole()) {
                    $commandArgs = implode(' ', $_SERVER['argv'] ?? []);
                    $actorType = 'System: CLI / Queue Worker';
                    $userAgent = 'CLI: [' . ($commandArgs ?: 'Scheduler') . ']';
                    $ipAddress = '127.0.0.1 (CLI)';
                } elseif (request()?->is('api/olsera/*')) {
                    $actorType = 'System: Olsera POS Webhook';
                    $userAgent = 'Olsera Cloud Webhook Ingestion';
                    $ipAddress = request()?->ip();
                } else {
                    $actorType = 'System: Background Process';
                    $userAgent = request()?->userAgent() ?? 'Automated Background Task';
                    $ipAddress = request()?->ip() ?? '127.0.0.1';
                }
            }

            ActivityLog::create([
                'user_id'        => $userId,
                'branch_id'      => $branchId,
                'actor_type'     => $actorType,
                'event'          => $event,
                'auditable_type' => get_class($this),
                'auditable_id'   => $this->getKey(),
                'old_values'     => $old,
                'new_values'     => $new,
                'ip_address'     => $ipAddress,
                'user_agent'     => $userAgent,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Activity Audit Trail logging failed: ' . $e->getMessage());
        }
    }
}
