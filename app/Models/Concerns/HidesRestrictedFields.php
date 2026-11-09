<?php

namespace App\Models\Concerns;

/**
 * Hides fields from the signed-in user unless they hold the matching permission.
 *
 * Applied when a model is turned into an array/JSON, so it covers every
 * Inertia page and JSON endpoint without each controller remembering to do it.
 * With no signed-in user (console, queue, public endpoints that pick their own
 * fields) nothing is hidden.
 *
 * Models define: protected static function restrictedFields(): array
 *   ['permission.name' => ['field', 'relation', ...]]
 */
trait HidesRestrictedFields
{
    abstract protected static function restrictedFields(): array;

    public function toArray()
    {
        $user = auth()->user();
        if ($user) {
            foreach (static::restrictedFields() as $permission => $fields) {
                if (!$user->can($permission)) {
                    $this->makeHidden($fields);
                }
            }
        }

        return parent::toArray();
    }
}
