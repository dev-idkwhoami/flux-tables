<?php

namespace Idkwhoami\FluxTables\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\View\View;
use Closure;

class RelationshipFilter extends Filter
{
    protected ?string $relationshipName = null;
    protected ?string $relatedModel = null;
    protected array $displayColumns = ['id', 'name'];
    protected bool $multiple = true;
    protected bool $searchable = true;
    protected ?Closure $queryCallback = null;
    protected int $searchLimit = 50;
    protected int $minSearchLength = 2;

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->value = [];
        $this->relationshipName = $name;
    }

    /**
     * Set the relationship name to filter on
     */
    public function relationship(string $relationshipName): static
    {
        $this->relationshipName = $relationshipName;
        return $this;
    }

    /**
     * Set the related model class
     */
    public function model(string $modelClass): static
    {
        $this->relatedModel = $modelClass;
        return $this;
    }

    /**
     * Set the columns to display in the search results
     */
    public function displayColumns(array $columns): static
    {
        $this->displayColumns = $columns;
        return $this;
    }

    /**
     * Allow multiple selections
     */
    public function multiple(bool $multiple = true): static
    {
        $this->multiple = $multiple;

        // Convert value format based on multiple setting
        if (!$multiple && is_array($this->value) && count($this->value) > 1) {
            $this->value = array_slice($this->value, 0, 1);
        } elseif ($multiple && !is_array($this->value)) {
            $this->value = $this->value ? [$this->value] : [];
        }

        return $this;
    }

    /**
     * Enable or disable search functionality
     */
    public function searchable(bool $searchable = true): static
    {
        $this->searchable = $searchable;
        return $this;
    }

    /**
     * Set the maximum number of search results
     */
    public function searchLimit(int $limit): static
    {
        $this->searchLimit = max(1, $limit);
        return $this;
    }

    /**
     * Set the minimum search string length
     */
    public function minSearchLength(int $length): static
    {
        $this->minSearchLength = max(1, $length);
        return $this;
    }

    /**
     * Set a custom query callback for loading options
     */
    public function query(Closure $callback): static
    {
        $this->queryCallback = $callback;
        return $this;
    }

    /**
     * Set the selected values (model IDs)
     */
    public function selected(array|string|int $values): static
    {
        if (is_string($values) || is_int($values)) {
            $this->value = $this->multiple ? [$values] : $values;
        } else {
            $this->value = $this->multiple ? $values : (array_slice($values, 0, 1)[0] ?? null);
        }

        return $this;
    }

    public function render(): View|string
    {
        $selectedOptions = $this->getSelectedOptions();
        $selectedValues = $this->multiple ? $this->value : [$this->value];

        return view('flux-tables::filters.relationship', [
            'filter' => $this,
            'selectedOptions' => $selectedOptions,
            'selectedValues' => array_filter($selectedValues),
            'multiple' => $this->multiple,
            'searchable' => $this->searchable,
            'searchLimit' => $this->searchLimit,
            'minSearchLength' => $this->minSearchLength,
        ]);
    }

    public function apply(Builder $query): Builder
    {
        if (!$this->hasValue()) {
            return $query;
        }

        $values = $this->multiple ? $this->value : [$this->value];
        $values = array_filter($values); // Remove empty values

        if (empty($values)) {
            return $query;
        }

        $relationshipName = $this->relationshipName;

        if (count($values) === 1) {
            return $query->whereHas($relationshipName, function (Builder $query) use ($values) {
                $query->where($this->getRelatedKeyName(), '=', $values[0]);
            });
        }

        return $query->whereHas($relationshipName, function (Builder $query) use ($values) {
            $query->whereIn($this->getRelatedKeyName(), $values);
        });
    }

    public function getValueDescription(): string
    {
        if (!$this->hasValue()) {
            return 'No items selected';
        }

        $selectedOptions = $this->getSelectedOptions();

        if ($selectedOptions->isEmpty()) {
            return 'No items selected';
        }

        $labels = $selectedOptions->pluck($this->getDisplayColumn())->toArray();

        if (count($labels) === 1) {
            return $labels[0];
        }

        if (count($labels) <= 3) {
            return implode(', ', $labels);
        }

        return implode(', ', array_slice($labels, 0, 2)) . ' and ' . (count($labels) - 2) . ' more';
    }

    public function validate(): bool
    {
        if (!$this->hasValue()) {
            return true; // Empty is valid
        }

        $values = $this->multiple ? $this->value : [$this->value];

        // All values must be valid IDs (numeric or string)
        foreach ($values as $value) {
            if ($value !== null && !is_numeric($value) && !is_string($value)) {
                return false;
            }
        }

        return true;
    }

    public function hasValue(): bool
    {
        if ($this->multiple) {
            return is_array($this->value) && !empty(array_filter($this->value));
        }

        return $this->value !== null && $this->value !== '';
    }

    /**
     * Search for related models based on query string
     */
    public function search(string $query): \Illuminate\Support\Collection
    {
        if (strlen($query) < $this->minSearchLength) {
            return collect([]);
        }

        $modelClass = $this->getRelatedModelClass();
        if (!$modelClass) {
            return collect([]);
        }

        $queryBuilder = $modelClass::query();

        // Apply custom query callback if provided
        if ($this->queryCallback) {
            $queryBuilder = ($this->queryCallback)($queryBuilder, $query);
        } else {
            // Default search behavior
            $searchColumn = $this->getDisplayColumn();
            $queryBuilder->where($searchColumn, 'like', "%{$query}%");
        }

        return $queryBuilder
            ->limit($this->searchLimit)
            ->get()
            ->map(function (Model $model) {
                return [
                    'id' => $model->getKey(),
                    'text' => $this->getModelDisplayText($model),
                ];
            });
    }

    /**
     * Get the currently selected options
     */
    public function getSelectedOptions(): \Illuminate\Support\Collection
    {
        if (!$this->hasValue()) {
            return collect([]);
        }

        $modelClass = $this->getRelatedModelClass();
        if (!$modelClass) {
            return collect([]);
        }

        $values = $this->multiple ? $this->value : [$this->value];
        $values = array_filter($values);

        if (empty($values)) {
            return collect([]);
        }

        return $modelClass::whereIn($this->getRelatedKeyName(), $values)->get();
    }

    /**
     * Get the related model class
     */
    protected function getRelatedModelClass(): ?string
    {
        return $this->relatedModel;
    }

    /**
     * Get the primary key name for the related model
     */
    protected function getRelatedKeyName(): string
    {
        $modelClass = $this->getRelatedModelClass();
        if ($modelClass) {
            return (new $modelClass())->getKeyName();
        }

        return 'id';
    }

    /**
     * Get the primary display column
     */
    protected function getDisplayColumn(): string
    {
        return $this->displayColumns[1] ?? $this->displayColumns[0] ?? 'name';
    }

    /**
     * Get display text for a model
     */
    protected function getModelDisplayText(Model $model): string
    {
        $displayColumn = $this->getDisplayColumn();

        if ($model->hasGetMutator($displayColumn) || $model->hasAttributeGetMutator($displayColumn)) {
            return $model->{$displayColumn};
        }

        return $model->getAttribute($displayColumn) ?? $model->getKey();
    }

    public function getRelationshipName(): ?string
    {
        return $this->relationshipName;
    }

    public function getRelatedModel(): ?string
    {
        return $this->relatedModel;
    }

    public function getDisplayColumns(): array
    {
        return $this->displayColumns;
    }

    public function isMultiple(): bool
    {
        return $this->multiple;
    }

    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    public function getSearchLimit(): int
    {
        return $this->searchLimit;
    }

    public function getMinSearchLength(): int
    {
        return $this->minSearchLength;
    }
}