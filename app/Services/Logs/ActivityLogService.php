<?php
namespace App\Services\Logs;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogService
{
    public static function log(
        string $action,
        string $description,
        ?int $userId = null,
        ?array $metadata = null
    ): void {
        try {
            $request = request();
            ActivityLog::create([
                'user_id' => $userId ?? auth()->id(),
                'action' => $action,
                'description' => $description,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'metadata' => $metadata ? json_encode($metadata) : null,
                'created_at'  => now(),
            ]);
        } catch (\Exception $e) {
            \Log::error('ActivityLog failed: ' . $e->getMessage());
        }
    }
}
