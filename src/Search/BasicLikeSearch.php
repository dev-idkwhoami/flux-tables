<?php

namespace Idkwhoami\FluxTables\Search;

use Idkwhoami\FluxTables\Contracts\SearchStrategy;
use Illuminate\Database\Eloquent\Builder;

class BasicLikeSearch implements SearchStrategy
{
    protected bool $caseSensitive = false;
    protected string $wildcardPosition = 'both'; // 'start', 'end', 'both', 'none'

    public function __construct(bool $caseSensitive = false, string $wildcardPosition = 'both')
    {
        $this->caseSensitive = $caseSensitive;
        $this->wildcardPosition = $wildcardPosition;
    }

    public function search(Builder $query, string $searchTerm, array $searchableColumns): Builder
    {
        if (empty($searchTerm) || empty($searchableColumns)) {
            return $query;
        }

        return $query->where(function (Builder $subQuery) use ($searchTerm, $searchableColumns) {
            foreach ($searchableColumns as $column) {
                $columnName = $this->getColumnName($column);
                $operator = $this->caseSensitive ? 'LIKE' : 'ILIKE';
                $searchValue = $this->formatSearchTerm($searchTerm);

                if ($this->isJsonColumn($columnName)) {
                    // Handle JSON columns differently
                    $subQuery->orWhereRaw("({$columnName})::text {$operator} ?", [$searchValue]);
                } else {
                    $subQuery->orWhere($columnName, $operator, $searchValue);
                }
            }
        });
    }

    public function getName(): string
    {
        return 'basic_like';
    }

    public function supports(array $config = []): bool
    {
        // Basic LIKE search is supported everywhere
        return true;
    }

    /**
     * Get the actual column name from column object or string
     */
    protected function getColumnName($column): string
    {
        if (is_object($column) && method_exists($column, 'getName')) {
            return $column->getName();
        }

        return (string) $column;
    }

    /**
     * Format the search term based on wildcard position
     */
    protected function formatSearchTerm(string $term): string
    {
        return match ($this->wildcardPosition) {
            'start' => "%{$term}",
            'end' => "{$term}%",
            'both' => "%{$term}%",
            'none' => $term,
            default => "%{$term}%",
        };
    }

    /**
     * Check if column is a JSON column
     */
    protected function isJsonColumn(string $column): bool
    {
        return str_contains($column, '->') || str_contains($column, '->>');
    }
}
