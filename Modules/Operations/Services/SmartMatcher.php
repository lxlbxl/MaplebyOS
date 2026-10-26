<?php

namespace Modules\Operations\Services;

use Illuminate\Support\Collection;
use Modules\CRM\Entities\Client;
use Modules\Operations\Entities\Port;
use Modules\Operations\Entities\PurchasePlatform;
use Modules\Operations\Entities\ShippingLine;
use Modules\Operations\Entities\VehicleMake;
use Modules\Operations\Entities\VehicleModel;

/**
 * Resolves free text to canonical catalog values and ranks suggestions.
 *
 * Matching order: exact name or alias (ignoring case, spaces and punctuation),
 * then prefix, then typo distance (Damerau-Levenshtein, so swapped letters like
 * CARMY -> CAMRY count as one typo). A typo match is only auto-accepted when it
 * is close and clearly better than the runner-up.
 */
class SmartMatcher
{
    public const TYPES = ['makes', 'models', 'shipping_lines', 'platforms', 'ports', 'customers'];

    /** @var array<string, Collection> */
    private array $cache = [];

    public static function key(?string $value): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $value));
    }

    /**
     * Candidates for a type: [['id', 'value', 'label', 'keys' => [normalized names/aliases], 'meta' => []]].
     */
    public function candidates(string $type, array $context = []): Collection
    {
        $cacheKey = $type . ':' . json_encode($context);
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $entry = fn ($model, array $meta = []) => [
            'id' => $model->id,
            'value' => $model->name,
            'label' => $model->name,
            'keys' => collect([$model->name])->merge($model->aliases ?? [])->map(fn ($v) => self::key($v))->filter()->unique()->values()->all(),
            'meta' => $meta,
        ];

        $items = match ($type) {
            'makes' => VehicleMake::orderBy('name')->get()->map(fn ($m) => $entry($m)),
            'models' => $this->modelCandidates($context['make'] ?? null)->map(fn ($m) => $entry($m, ['make' => $m->make?->name])),
            'shipping_lines' => ShippingLine::where('is_active', true)->orderBy('name')->get()->map(fn ($l) => $entry($l, ['scac' => $l->scac])),
            'platforms' => PurchasePlatform::orderBy('name')->get()->map(fn ($p) => $entry($p)),
            'ports' => Port::orderBy('name')->get()
                ->when(!empty($context['kind']), fn ($c) => $c->where('kind', $context['kind']))
                ->map(fn ($p) => $entry($p, ['country' => $p->country])),
            'customers' => Client::query()->select('id', 'company_name', 'contact_person', 'phone', 'email')->limit(5000)->get()
                ->map(fn ($c) => [
                    'id' => $c->id,
                    'value' => (string) $c->id,
                    'label' => $c->company_name ? "{$c->company_name} ({$c->contact_person})" : $c->contact_person,
                    'keys' => collect([$c->company_name, $c->contact_person, $c->phone, $c->email])->map(fn ($v) => self::key($v))->filter()->unique()->values()->all(),
                    'meta' => ['phone' => $c->phone, 'email' => $c->email],
                ]),
            default => throw new \InvalidArgumentException("Unknown lookup type [{$type}]."),
        };

        return $this->cache[$cacheKey] = $items->values();
    }

    private function modelCandidates(?string $make): Collection
    {
        $query = VehicleModel::with('make')->orderBy('name');
        if ($make) {
            $resolved = $this->resolve('makes', $make);
            if (!$resolved['match']) {
                return collect();
            }
            $query->where('vehicle_make_id', $resolved['match']['id']);
        }

        return $query->get();
    }

    /**
     * Ranked suggestions for what the user has typed.
     */
    public function suggest(string $type, ?string $query, array $context = [], int $limit = 8): array
    {
        $candidates = $this->candidates($type, $context);
        $q = self::key($query);

        if ($q === '') {
            return $candidates->take($limit)->map(fn ($c) => $this->present($c, 0, 'list'))->values()->all();
        }

        return $candidates
            ->map(fn ($c) => $this->score($c, $q))
            ->filter(fn ($r) => $r['score'] > 0)
            ->sortByDesc('score')
            ->take($limit)
            ->map(fn ($r) => $this->present($r['candidate'], $r['score'], $r['reason']))
            ->values()
            ->all();
    }

    /**
     * Resolve text to one canonical value.
     *
     * @return array{input:?string, match:?array, value:?string, confidence:string, suggestions:array}
     *         confidence: exact | alias | typo | none | ambiguous | empty
     */
    public function resolve(string $type, ?string $input, array $context = []): array
    {
        $q = self::key($input);
        $result = ['input' => $input, 'match' => null, 'value' => null, 'confidence' => 'empty', 'suggestions' => []];
        if ($q === '') {
            return $result;
        }

        $scored = $this->candidates($type, $context)
            ->map(fn ($c) => $this->score($c, $q))
            ->filter(fn ($r) => $r['score'] > 0)
            ->sortByDesc('score')
            ->values();

        $result['suggestions'] = $scored->take(5)->map(fn ($r) => $this->present($r['candidate'], $r['score'], $r['reason']))->all();
        $best = $scored->first();

        if (!$best) {
            $result['confidence'] = 'none';

            return $result;
        }

        $accept = function (string $confidence) use (&$result, $best) {
            $result['match'] = $best['candidate'];
            $result['value'] = $best['candidate']['value'];
            $result['confidence'] = $confidence;

            return $result;
        };

        if (in_array($best['reason'], ['exact', 'alias'], true)) {
            return $accept($best['reason']);
        }

        if ($best['reason'] === 'typo') {
            $runnerUp = $scored->get(1);
            $clearWinner = !$runnerUp || $runnerUp['reason'] !== 'typo' || $runnerUp['distance'] > $best['distance'];
            if ($clearWinner) {
                return $accept('typo');
            }
            $result['confidence'] = 'ambiguous';

            return $result;
        }

        $result['confidence'] = 'none';

        return $result;
    }

    private function score(array $candidate, string $q): array
    {
        $best = ['candidate' => $candidate, 'score' => 0, 'reason' => null, 'distance' => PHP_INT_MAX];

        foreach ($candidate['keys'] as $i => $key) {
            if ($key === $q) {
                return ['candidate' => $candidate, 'score' => $i === 0 ? 100 : 95, 'reason' => $i === 0 ? 'exact' : 'alias', 'distance' => 0];
            }

            $options = [];
            if (str_starts_with($key, $q)) {
                $options[] = [70 + min(20, (int) round(20 * strlen($q) / max(1, strlen($key)))), 'prefix', PHP_INT_MAX];
            } elseif (strlen($q) >= 3 && str_contains($key, $q)) {
                $options[] = [55, 'contains', PHP_INT_MAX];
            }

            if (strlen($q) >= 3) {
                $distance = self::distance($q, $key);
                $allowed = max(1, intdiv(max(strlen($key), strlen($q)), 4));
                if ($distance <= $allowed) {
                    $options[] = [80 - ($distance * 10), 'typo', $distance];
                }
            }

            foreach ($options as [$score, $reason, $distance]) {
                if ($score > $best['score'] || ($score === $best['score'] && $distance < $best['distance'])) {
                    $best = ['candidate' => $candidate, 'score' => $score, 'reason' => $reason, 'distance' => $distance];
                }
            }
        }

        return $best;
    }

    private function present(array $candidate, int $score, ?string $reason): array
    {
        return [
            'id' => $candidate['id'],
            'value' => $candidate['value'],
            'label' => $candidate['label'],
            'score' => $score,
            'reason' => $reason,
            'meta' => $candidate['meta'],
        ];
    }

    /**
     * Optimal string alignment distance (Levenshtein + adjacent transpositions).
     */
    public static function distance(string $a, string $b): int
    {
        $la = strlen($a);
        $lb = strlen($b);
        if ($la === 0) {
            return $lb;
        }
        if ($lb === 0) {
            return $la;
        }

        $d = [];
        for ($i = 0; $i <= $la; $i++) {
            $d[$i] = [$i];
        }
        for ($j = 0; $j <= $lb; $j++) {
            $d[0][$j] = $j;
        }

        for ($i = 1; $i <= $la; $i++) {
            for ($j = 1; $j <= $lb; $j++) {
                $cost = $a[$i - 1] === $b[$j - 1] ? 0 : 1;
                $d[$i][$j] = min($d[$i - 1][$j] + 1, $d[$i][$j - 1] + 1, $d[$i - 1][$j - 1] + $cost);
                if ($i > 1 && $j > 1 && $a[$i - 1] === $b[$j - 2] && $a[$i - 2] === $b[$j - 1]) {
                    $d[$i][$j] = min($d[$i][$j], $d[$i - 2][$j - 2] + 1);
                }
            }
        }

        return $d[$la][$lb];
    }
}
