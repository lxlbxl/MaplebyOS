<?php

namespace Modules\Operations\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Operations\Entities\Concerns\HasAliases;

class ShippingLine extends Model
{
    use HasAliases;

    protected $fillable = ['scac', 'container_prefixes', 'tracking_url', 'is_active'];

    protected $casts = [
        'container_prefixes' => 'array',
        'is_active' => 'boolean',
    ];

    public function containers()
    {
        return $this->hasMany(Container::class);
    }

    public function trackingUrlFor(?string $containerNumber): ?string
    {
        if (!$this->tracking_url || !$containerNumber) {
            return null;
        }

        return str_replace('{number}', urlencode($containerNumber), $this->tracking_url);
    }

    /**
     * Guess the line from a container's owner prefix (first four letters).
     */
    public static function guessFromContainerNumber(?string $number): ?self
    {
        $prefix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', (string) $number), 0, 4));
        if (strlen($prefix) < 4) {
            return null;
        }

        return static::all()->first(fn ($line) => in_array($prefix, $line->container_prefixes ?? [], true));
    }
}
