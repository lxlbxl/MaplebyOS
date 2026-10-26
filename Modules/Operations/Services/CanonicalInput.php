<?php

namespace Modules\Operations\Services;

use Illuminate\Validation\ValidationException;

/**
 * Server-side guard behind the smart-suggestion inputs: every catalog field is
 * stored under its canonical spelling, and unknown values are rejected with
 * "did you mean" suggestions instead of being saved as new spellings.
 */
class CanonicalInput
{
    public function __construct(private SmartMatcher $matcher)
    {
    }

    /**
     * @param  array<string, array{0:string, 1:string, 2?:array}>  $fields  field => [lookup type, human label, context]
     */
    public function apply(array $data, array $fields): array
    {
        $errors = [];

        foreach ($fields as $field => $spec) {
            [$type, $label] = $spec;
            $context = $spec[2] ?? [];
            $value = $data[$field] ?? null;
            if ($value === null || trim((string) $value) === '') {
                $data[$field] = null;
                continue;
            }

            $result = $this->matcher->resolve($type, (string) $value, $context);
            if ($result['value'] !== null) {
                $data[$field] = $result['value'];
                continue;
            }

            $suggestions = array_column($result['suggestions'], 'value');
            $errors[$field] = $suggestions
                ? "Unknown {$label} \"{$value}\". Did you mean " . implode(', ', array_slice($suggestions, 0, 3)) . '? Pick from the list, or add it to the catalog first.'
                : "Unknown {$label} \"{$value}\". Pick from the list, or add it to the catalog first.";
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        return $data;
    }
}
