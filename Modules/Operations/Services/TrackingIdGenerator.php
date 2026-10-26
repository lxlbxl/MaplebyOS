<?php

namespace Modules\Operations\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Generates tracking IDs in the business's existing format: MAP{YY}-{MM}-{seq},
 * e.g. MAP26-10-023. The sequence restarts every month and never reuses a number.
 */
class TrackingIdGenerator
{
    public const PREFIX = 'MAP';

    public const PATTERN = '/^MAP\d{2}-\d{2}-\d{3,}$/';

    public function prefixFor(Carbon $date): string
    {
        return self::PREFIX . $date->format('y') . '-' . $date->format('m') . '-';
    }

    public function next(?Carbon $date = null): string
    {
        $prefix = $this->prefixFor($date ?? Carbon::today());

        $max = DB::table('shipments')
            ->where('tracking_id', 'like', $prefix . '%')
            ->pluck('tracking_id')
            ->map(fn ($id) => (int) substr($id, strlen($prefix)))
            ->max() ?? 0;

        return $prefix . str_pad((string) ($max + 1), 3, '0', STR_PAD_LEFT);
    }

    public function isTaken(string $trackingId): bool
    {
        return DB::table('shipments')->where('tracking_id', $trackingId)->exists();
    }

    public static function isWellFormed(?string $trackingId): bool
    {
        return (bool) preg_match(self::PATTERN, (string) $trackingId);
    }
}
