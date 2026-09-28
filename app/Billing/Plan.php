<?php

namespace App\Billing;

/**
 * A plan defined in config/plans.php.
 */
final class Plan
{
    /**
     * @param  array<string, int|null>  $limits  null means unlimited
     * @param  list<string>  $features
     */
    public function __construct(
        public readonly string $key,
        public readonly string $name,
        public readonly ?string $priceId,
        public readonly int $monthlyPrice,
        public readonly array $limits,
        public readonly array $features,
    ) {}

    /**
     * @return list<self>
     */
    public static function all(): array
    {
        $plans = [];

        foreach (config('plans.plans', []) as $key => $plan) {
            $plans[] = new self(
                key: $key,
                name: $plan['name'],
                priceId: $plan['price_id'] ?? null,
                monthlyPrice: $plan['monthly_price'] ?? 0,
                limits: $plan['limits'] ?? [],
                features: $plan['features'] ?? [],
            );
        }

        return $plans;
    }

    public static function find(string $key): ?self
    {
        foreach (self::all() as $plan) {
            if ($plan->key === $key) {
                return $plan;
            }
        }

        return null;
    }

    public static function forPrice(?string $priceId): ?self
    {
        if ($priceId === null || $priceId === '') {
            return null;
        }

        foreach (self::all() as $plan) {
            if ($plan->priceId === $priceId) {
                return $plan;
            }
        }

        return null;
    }

    public static function default(): self
    {
        return self::find(config('plans.default', 'free')) ?? self::all()[0];
    }

    public function isFree(): bool
    {
        return $this->monthlyPrice === 0;
    }

    public function limit(string $resource): ?int
    {
        return $this->limits[$resource] ?? null;
    }

    public function allows(string $resource, int $currentCount): bool
    {
        $limit = $this->limit($resource);

        return $limit === null || $currentCount < $limit;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'name' => $this->name,
            'monthly_price' => $this->monthlyPrice,
            'limits' => $this->limits,
            'features' => $this->features,
            'is_free' => $this->isFree(),
        ];
    }
}
