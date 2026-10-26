<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Operations\Services\SmartMatcher;
use Tests\TestCase;

class SmartMatcherTest extends TestCase
{
    use RefreshDatabase;

    private function resolve(string $type, string $input, array $context = []): array
    {
        return app(SmartMatcher::class)->resolve($type, $input, $context);
    }

    public function test_sheet_misspellings_resolve_to_canonical_names(): void
    {
        $cases = [
            ['makes', 'hyudai', [], 'HYUNDAI'],
            ['makes', 'Lexux', [], 'LEXUS'],
            ['makes', 'benz', [], 'MERCEDES-BENZ'],
            ['makes', 'toyta', [], 'TOYOTA'],
            ['models', 'carmy', ['make' => 'TOYOTA'], 'CAMRY'],
            ['models', 'corrola', ['make' => 'TOYOTA'], 'COROLLA'],
            ['models', 'pathfiner', ['make' => 'NISSAN'], 'PATHFINDER'],
            ['shipping_lines', 'hapalioyd', [], 'HAPAG-LLOYD'],
            ['shipping_lines', 'msc', [], 'MSC'],
        ];

        foreach ($cases as [$type, $input, $context, $expected]) {
            $result = $this->resolve($type, $input, $context);
            $this->assertSame($expected, $result['value'], "{$type}: {$input}");
            $this->assertContains($result['confidence'], ['exact', 'alias', 'typo']);
        }
    }

    public function test_unknown_value_is_not_auto_accepted(): void
    {
        $result = $this->resolve('makes', 'zzzzqq');
        $this->assertNull($result['value']);
        $this->assertSame('none', $result['confidence']);
    }

    public function test_models_are_scoped_to_make(): void
    {
        $result = $this->resolve('models', 'camry', ['make' => 'HONDA']);
        $this->assertNotSame('CAMRY', $result['value']);
    }

    public function test_suggest_returns_prefix_matches(): void
    {
        $suggestions = app(SmartMatcher::class)->suggest('makes', 'mer');
        $this->assertSame('MERCEDES-BENZ', $suggestions[0]['value']);
    }
}
