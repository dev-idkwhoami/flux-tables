<?php

namespace Idkwhoami\FluxTables\Search;

use Idkwhoami\FluxTables\Contracts\SearchStrategy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class FullTextSearch implements SearchStrategy
{
    protected string $language = 'english';
    protected bool $useRanking = true;

    public function __construct(string $language = 'english', bool $useRanking = true)
    {
        $this->language = $language;
        $this->useRanking = $useRanking;
    }

    public function search(Builder $query, string $searchTerm, array $searchableColumns): Builder
    {
        if (empty($searchTerm) || empty($searchableColumns)) {
            return $query;
        }

        // Clean search term for PostgreSQL full-text search
        $searchTerm = $this->cleanSearchTerm($searchTerm);

        return $query->where(function (Builder $subQuery) use ($searchTerm, $searchableColumns) {
            foreach ($searchableColumns as $column) {
                $columnName = $this->getColumnName($column);

                // Use PostgreSQL full-text search
                $subQuery->orWhereRaw(
                    "to_tsvector('{$this->language}', {$columnName}) @@ plainto_tsquery('{$this->language}', ?)",
                    [$searchTerm]
                );
            }
        });
    }

    public function getName(): string
    {
        return 'full_text';
    }

    public function supports(array $config = []): bool
    {
        // Only supported on PostgreSQL
        return DB::connection()->getDriverName() === 'pgsql';
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
     * Clean search term for PostgreSQL full-text search
     */
    protected function cleanSearchTerm(string $term): string
    {
        // Remove special characters that could break the query
        return preg_replace('/[^\w\s]/', ' ', $term);
    }

    /**
     * Add ranking support for better relevance
     */
    public function searchWithRanking(Builder $query, string $searchTerm, array $searchableColumns): Builder
    {
        if (!$this->useRanking) {
            return $this->search($query, $searchTerm, $searchableColumns);
        }

        $searchTerm = $this->cleanSearchTerm($searchTerm);

        // Add ranking select
        $rankingExpressions = [];
        foreach ($searchableColumns as $column) {
            $columnName = $this->getColumnName($column);
            $rankingExpressions[] = "ts_rank(to_tsvector('{$this->language}', {$columnName}), plainto_tsquery('{$this->language}', '{$searchTerm}'))";
        }

        $rankingExpression = '(' . implode(' + ', $rankingExpressions) . ')';

        return $query
            ->selectRaw("*, {$rankingExpression} as search_rank")
            ->where(function (Builder $subQuery) use ($searchTerm, $searchableColumns) {
                foreach ($searchableColumns as $column) {
                    $columnName = $this->getColumnName($column);
                    $subQuery->orWhereRaw(
                        "to_tsvector('{$this->language}', {$columnName}) @@ plainto_tsquery('{$this->language}', ?)",
                        [$searchTerm]
                    );
                }
            })
            ->orderByDesc('search_rank');
    }
}
