<?php

namespace Idkwhoami\FluxTables\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface SearchStrategy
{
    /**
     * Apply search to the query builder
     */
    public function search(Builder $query, string $searchTerm, array $searchableColumns): Builder;

    /**
     * Get the name of this search strategy
     */
    public function getName(): string;

    /**
     * Check if this strategy supports the given configuration
     */
    public function supports(array $config = []): bool;
}
