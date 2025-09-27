<?php

namespace Idkwhoami\FluxTables\Services;

use Idkwhoami\FluxTables\Filters\Filter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class FilterStateService
{
    protected const CACHE_PREFIX = 'flux_table_filters:';
    protected const DEFAULT_TTL = 86400; // 24 hours

    protected int $ttl;

    public function __construct(int $ttl = self::DEFAULT_TTL)
    {
        $this->ttl = $ttl;
    }

    /**
     * Generate a unique filter ID for sharing
     */
    public function generateFilterId(): string
    {
        return Str::random(12);
    }

    /**
     * Store filter state and return unique ID for sharing
     */
    public function storeFilterState(array $filters, ?string $tableId = null): string
    {
        $filterId = $this->generateFilterId();
        $filterData = $this->serializeFilters($filters);

        $stateData = [
            'filters' => $filterData,
            'table_id' => $tableId,
            'created_at' => now()->toISOString(),
            'expires_at' => now()->addSeconds($this->ttl)->toISOString(),
        ];

        Cache::put(
            $this->getCacheKey($filterId),
            $stateData,
            $this->ttl
        );

        return $filterId;
    }

    /**
     * Retrieve filter state by ID
     */
    public function getFilterState(string $filterId): ?array
    {
        $cacheKey = $this->getCacheKey($filterId);
        $stateData = Cache::get($cacheKey);

        if (!$stateData) {
            return null;
        }

        return [
            'filters' => $this->deserializeFilters($stateData['filters']),
            'table_id' => $stateData['table_id'] ?? null,
            'created_at' => $stateData['created_at'] ?? null,
            'expires_at' => $stateData['expires_at'] ?? null,
        ];
    }

    /**
     * Update existing filter state
     */
    public function updateFilterState(string $filterId, array $filters): bool
    {
        $cacheKey = $this->getCacheKey($filterId);
        $existingData = Cache::get($cacheKey);

        if (!$existingData) {
            return false;
        }

        $existingData['filters'] = $this->serializeFilters($filters);

        Cache::put($cacheKey, $existingData, $this->ttl);

        return true;
    }

    /**
     * Delete filter state
     */
    public function deleteFilterState(string $filterId): bool
    {
        return Cache::forget($this->getCacheKey($filterId));
    }

    /**
     * Check if filter ID exists and is valid
     */
    public function isValidFilterId(string $filterId): bool
    {
        return Cache::has($this->getCacheKey($filterId));
    }

    /**
     * Extend TTL for existing filter state
     */
    public function extendFilterState(string $filterId, int $additionalSeconds = null): bool
    {
        $additionalSeconds = $additionalSeconds ?? $this->ttl;
        $cacheKey = $this->getCacheKey($filterId);
        $stateData = Cache::get($cacheKey);

        if (!$stateData) {
            return false;
        }

        $stateData['expires_at'] = now()->addSeconds($additionalSeconds)->toISOString();

        Cache::put($cacheKey, $stateData, $additionalSeconds);

        return true;
    }

    /**
     * Clean up expired filter states (can be called via scheduled task)
     */
    public function cleanupExpiredFilters(): int
    {
        // This is a basic implementation. In production, you might want to use
        // a more sophisticated approach with Redis SCAN or database-based tracking
        $cleaned = 0;

        // Note: This is not efficient for large numbers of keys
        // Consider implementing a separate tracking mechanism for production

        return $cleaned;
    }

    /**
     * Get cache statistics
     */
    public function getCacheStats(): array
    {
        return [
            'ttl' => $this->ttl,
            'cache_driver' => config('cache.default'),
            'prefix' => self::CACHE_PREFIX,
        ];
    }

    /**
     * Serialize filters for storage
     */
    protected function serializeFilters(array $filters): array
    {
        $serialized = [];

        foreach ($filters as $filter) {
            if ($filter instanceof Filter && $filter->isActive()) {
                $serialized[] = $filter->toArray();
            }
        }

        return $serialized;
    }

    /**
     * Deserialize filters from storage
     */
    protected function deserializeFilters(array $serializedFilters): array
    {
        $filters = [];

        foreach ($serializedFilters as $filterData) {
            if (!isset($filterData['type']) || !class_exists($filterData['type'])) {
                continue;
            }

            $filterClass = $filterData['type'];

            if (!is_subclass_of($filterClass, Filter::class)) {
                continue;
            }

            $filter = new $filterClass($filterData['name']);
            $filter->fromArray($filterData);

            $filters[] = $filter;
        }

        return $filters;
    }

    /**
     * Generate cache key for filter ID
     */
    protected function getCacheKey(string $filterId): string
    {
        return self::CACHE_PREFIX . $filterId;
    }

    /**
     * Set custom TTL
     */
    public function setTtl(int $ttl): static
    {
        $this->ttl = $ttl;
        return $this;
    }

    /**
     * Get current TTL
     */
    public function getTtl(): int
    {
        return $this->ttl;
    }
}