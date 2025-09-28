<?php

namespace Idkwhoami\FluxTables\Search;

use Idkwhoami\FluxTables\Contracts\SearchStrategy;
use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;

class SearchManager
{
    protected array $strategies = [];
    protected ?SearchStrategy $defaultStrategy = null;

    public function __construct()
    {
        // Register default strategies
        $this->registerStrategy(new BasicLikeSearch());
        $this->registerStrategy(new FullTextSearch());

        // Set basic like as default
        $this->setDefaultStrategy('basic_like');
    }

    /**
     * Register a search strategy
     */
    public function registerStrategy(SearchStrategy $strategy): static
    {
        $this->strategies[$strategy->getName()] = $strategy;
        return $this;
    }

    /**
     * Set the default search strategy
     */
    public function setDefaultStrategy(string $strategyName): static
    {
        if (!isset($this->strategies[$strategyName])) {
            throw new InvalidArgumentException("Search strategy '{$strategyName}' is not registered.");
        }

        $this->defaultStrategy = $this->strategies[$strategyName];
        return $this;
    }

    /**
     * Get a specific search strategy
     */
    public function getStrategy(string $name): ?SearchStrategy
    {
        return $this->strategies[$name] ?? null;
    }

    /**
     * Get the default search strategy
     */
    public function getDefaultStrategy(): ?SearchStrategy
    {
        return $this->defaultStrategy;
    }

    /**
     * Get all registered strategies
     */
    public function getStrategies(): array
    {
        return $this->strategies;
    }

    /**
     * Get all supported strategies for the current environment
     */
    public function getSupportedStrategies(array $config = []): array
    {
        return array_filter($this->strategies, function (SearchStrategy $strategy) use ($config) {
            return $strategy->supports($config);
        });
    }

    /**
     * Perform search using the specified or default strategy
     */
    public function search(
        Builder $query,
        string $searchTerm,
        array $searchableColumns,
        ?string $strategyName = null
    ): Builder {
        if (empty($searchTerm)) {
            return $query;
        }

        $strategy = $strategyName ? $this->getStrategy($strategyName) : $this->defaultStrategy;

        if (!$strategy) {
            throw new InvalidArgumentException("Search strategy '{$strategyName}' not found.");
        }

        if (!$strategy->supports()) {
            // Fallback to basic like search if the requested strategy is not supported
            $strategy = $this->getStrategy('basic_like');
        }

        return $strategy->search($query, $searchTerm, $searchableColumns);
    }

    /**
     * Auto-select the best search strategy based on configuration and environment
     */
    public function autoSelectStrategy(array $config = []): SearchStrategy
    {
        $supportedStrategies = $this->getSupportedStrategies($config);

        // Preference order: full_text > basic_like
        if (isset($supportedStrategies['full_text'])) {
            return $supportedStrategies['full_text'];
        }

        if (isset($supportedStrategies['basic_like'])) {
            return $supportedStrategies['basic_like'];
        }

        throw new InvalidArgumentException('No supported search strategies found.');
    }

    /**
     * Factory method to create strategy instances
     */
    public static function createStrategy(string $type, array $config = []): SearchStrategy
    {
        return match ($type) {
            'basic_like' => new BasicLikeSearch(
                $config['case_sensitive'] ?? false,
                $config['wildcard_position'] ?? 'both'
            ),
            'full_text' => new FullTextSearch(
                $config['language'] ?? 'english',
                $config['use_ranking'] ?? true
            ),
            default => throw new InvalidArgumentException("Unknown search strategy type: {$type}")
        };
    }
}
