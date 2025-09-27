<?php

namespace Idkwhoami\FluxTables\Columns;

use Closure;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

/**
 * Base abstract class for table columns
 *
 * Provides common functionality for column configuration including display,
 * sorting, searching, visibility, and transformations. All column types
 * extend this base class.
 *
 * @package Idkwhoami\FluxTables\Columns
 */
abstract class Column
{
    protected string $name;
    protected string $label;
    protected ?string $property = null;

    // Search and sort configuration
    protected bool $sortable = false;
    protected bool $searchable = false;
    protected ?string $searchColumn = null;
    protected ?string $searchTable = null;
    protected ?string $sortColumn = null;
    protected ?string $sortTable = null;

    // Display configuration
    protected bool $visible = true;
    protected bool $toggleable = false;
    protected bool $toggled = false;
    protected mixed $default = null;
    protected ?Closure $displayTransform = null;
    protected ?Closure $visibility = null;
    protected ?string $tooltip = null;
    protected array $attributes = [];
    protected array $classes = [];

    /**
     * Create a new column instance
     *
     * @param string $name The column name (usually database field name)
     */
    public function __construct(string $name)
    {
        $this->name = $name;
        $this->label = str($name)->title()->replace('_', ' ')->toString();
    }

    /**
     * Create a new column instance using static factory method
     *
     * @param string $name The column name
     * @return static Returns new instance of the calling column class
     */
    public static function make(string $name): static
    {
        return new static($name);
    }

    public function label(string $label): static
    {
        $this->label = $label;
        return $this;
    }

    public function property(string $property): static
    {
        $this->property = $property;
        return $this;
    }

    public function sortable(bool $sortable = true): static
    {
        $this->sortable = $sortable;
        // Disable sorting if transform is applied without explicit sort column
        if ($sortable && $this->displayTransform && !$this->sortColumn) {
            $this->sortable = false;
        }
        return $this;
    }

    public function sortUsing(string $column, ?string $table = null): static
    {
        $this->sortColumn = $column;
        $this->sortTable = $table;
        $this->sortable = true; // Enable sorting when explicitly set
        return $this;
    }

    public function searchable(bool $searchable = true): static
    {
        $this->searchable = $searchable;
        // Disable searching if transform is applied without explicit search column
        if ($searchable && $this->displayTransform && !$this->searchColumn) {
            $this->searchable = false;
        }
        return $this;
    }

    public function searchUsing(string $column, ?string $table = null): static
    {
        $this->searchColumn = $column;
        $this->searchTable = $table;
        $this->searchable = true; // Enable searching when explicitly set
        return $this;
    }

    public function visible(bool|Closure $visible = true): static
    {
        if ($visible instanceof Closure) {
            $this->visibility = $visible;
        } else {
            $this->visible = $visible;
        }
        return $this;
    }

    public function toggleable(bool $toggleable = true): static
    {
        $this->toggleable = $toggleable;
        return $this;
    }

    public function toggled(bool $toggled = true): static
    {
        $this->toggled = $toggled;
        return $this;
    }

    public function default(mixed $default): static
    {
        $this->default = $default;
        return $this;
    }

    public function transform(Closure $transform): static
    {
        $this->displayTransform = $transform;
        // Auto-disable search/sort if not explicitly configured
        if (!$this->searchColumn) {
            $this->searchable = false;
        }
        if (!$this->sortColumn) {
            $this->sortable = false;
        }
        return $this;
    }

    public function tooltip(string $tooltip): static
    {
        $this->tooltip = $tooltip;
        return $this;
    }

    public function attributes(array $attributes): static
    {
        $this->attributes = array_merge($this->attributes, $attributes);
        return $this;
    }

    public function classes(array|string $classes): static
    {
        if (is_string($classes)) {
            $classes = explode(' ', $classes);
        }
        $this->classes = array_merge($this->classes, $classes);
        return $this;
    }

    // Getters
    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getProperty(): string
    {
        return $this->property ?? $this->name;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    public function isVisible(?object $record = null): bool
    {
        if ($this->visibility instanceof Closure) {
            return ($this->visibility)($record);
        }
        return $this->visible;
    }

    public function isToggleable(): bool
    {
        return $this->toggleable;
    }

    public function isToggled(): bool
    {
        return $this->toggled;
    }

    public function hasTooltip(): bool
    {
        return !empty($this->tooltip);
    }

    public function getTooltip(): ?string
    {
        return $this->tooltip;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getClasses(): array
    {
        return $this->classes;
    }

    /**
     * Get the qualified column name for searching
     */
    public function getSearchColumn(?Builder $query = null): string
    {
        $column = $this->searchColumn ?? $this->property ?? $this->name;
        return $this->qualifyColumn($column, $this->searchTable, $query);
    }

    /**
     * Get the qualified column name for sorting
     */
    public function getSortColumn(?Builder $query = null): string
    {
        $column = $this->sortColumn ?? $this->property ?? $this->name;
        return $this->qualifyColumn($column, $this->sortTable, $query);
    }

    /**
     * Qualify a column with table name if needed
     */
    protected function qualifyColumn(string $column, ?string $table = null, ?Builder $query = null): string
    {
        // If table explicitly provided
        if ($table) {
            return "{$table}.{$column}";
        }

        // If column already qualified (contains .)
        if (str_contains($column, '.')) {
            return $column;
        }

        // If JSON operator detected (PostgreSQL)
        if (str_contains($column, '->')) {
            return $column;
        }

        // Auto-qualify with current query table if available
        if ($query && $query->getModel()) {
            return $query->getModel()->getTable() . '.' . $column;
        }

        return $column;
    }

    /**
     * Get the value from a record for display
     */
    protected function getValue(object $record): mixed
    {
        $property = $this->getProperty();

        // Handle nested properties (e.g., 'user.name')
        if (str_contains($property, '.')) {
            $value = data_get($record, $property);
        } else {
            // Handle direct property access
            $value = $record->{$property} ?? null;
        }

        // Apply display transformation if defined
        if ($this->displayTransform instanceof Closure) {
            $value = ($this->displayTransform)($value, $record);
        }

        // Return default if value is null/empty
        return $value ?? $this->default;
    }

    /**
     * Apply search condition to query
     */
    public function applySearch(Builder $query, string $search): void
    {
        if (!$this->isSearchable()) {
            return;
        }

        $column = $this->getSearchColumn($query);
        $query->orWhere($column, 'ILIKE', "%{$search}%");
    }

    /**
     * Apply sort to query
     */
    public function applySort(Builder $query, string $direction = 'asc'): void
    {
        if (!$this->isSortable()) {
            return;
        }

        $column = $this->getSortColumn($query);
        $query->orderBy($column, $direction);
    }

    abstract public function render(object $record): string|Htmlable|View;
}
