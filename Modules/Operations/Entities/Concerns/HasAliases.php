<?php

namespace Modules\Operations\Entities\Concerns;

/**
 * Catalog entries keep a canonical upper-case name plus alternative spellings.
 */
trait HasAliases
{
    public function initializeHasAliases(): void
    {
        $this->mergeCasts(['aliases' => 'array']);
        $this->mergeFillable(['name', 'aliases']);
    }

    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = mb_strtoupper(trim(preg_replace('/\s+/', ' ', (string) $value)));
    }

    public function setAliasesAttribute($value): void
    {
        $aliases = collect(is_string($value) ? explode(',', $value) : ($value ?? []))
            ->map(fn ($alias) => mb_strtoupper(trim(preg_replace('/\s+/', ' ', (string) $alias))))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $this->attributes['aliases'] = json_encode($aliases);
    }
}
