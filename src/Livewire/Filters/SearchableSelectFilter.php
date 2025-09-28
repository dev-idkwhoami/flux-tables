<?php

namespace Idkwhoami\FluxTables\Livewire\Filters;

use Livewire\Component;
use Idkwhoami\FluxTables\Traits\FilterComponent;
use Illuminate\Database\Eloquent\Model;

class SearchableSelectFilter extends Component
{
    use FilterComponent;

    public array $options = [];
    public array $selectedOptions = [];
    public string $searchQuery = '';
    public bool $multiple = true;
    public int $searchLimit = 50;
    public int $minSearchLength = 2;

    // Configuration for the related model
    public ?string $modelClass = null;
    public string $displayColumn = 'name';
    public string $valueColumn = 'id';
    public ?\Closure $queryCallback = null;

    public function mount(
        string $filterName,
        string $masterFilterId,
        mixed $value = null,
        string $label = '',
        ?string $description = null,
        array $config = []
    ): void {
        $this->mountFilterComponent($filterName, $masterFilterId, $value, $label, $description);

        // Apply configuration
        $this->multiple = $config['multiple'] ?? true;
        $this->searchLimit = $config['search_limit'] ?? 50;
        $this->minSearchLength = $config['min_search_length'] ?? 2;
        $this->modelClass = $config['model_class'] ?? null;
        $this->displayColumn = $config['display_column'] ?? 'name';
        $this->valueColumn = $config['value_column'] ?? 'id';
        $this->queryCallback = $config['query_callback'] ?? null;

        // Initialize value format
        if (!$this->multiple && is_array($this->value)) {
            $this->value = $this->value[0] ?? null;
        } elseif ($this->multiple && !is_array($this->value)) {
            $this->value = $this->value ? [$this->value] : [];
        }

        $this->loadSelectedOptions();
    }

    /**
     * Search for options via Livewire action
     */
    public function searchOptions(string $query): array
    {
        if (strlen($query) < $this->minSearchLength || !$this->modelClass) {
            return [];
        }

        $queryBuilder = $this->modelClass::query();

        // Apply custom query callback if provided
        if ($this->queryCallback) {
            $queryBuilder = ($this->queryCallback)($queryBuilder, $query);
        } else {
            // Default search behavior
            $queryBuilder->where($this->displayColumn, 'ILIKE', "%{$query}%");
        }

        return $queryBuilder
            ->select([$this->valueColumn, $this->displayColumn])
            ->limit($this->searchLimit)
            ->get()
            ->map(function (Model $model) {
                return [
                    'id' => $model->{$this->valueColumn},
                    'text' => $model->{$this->displayColumn},
                ];
            })
            ->toArray();
    }

    /**
     * Handle search from Alpine.js frontend
     */
    public function handleSearch(string $query): array
    {
        return $this->searchOptions($query);
    }

    /**
     * Load currently selected options
     */
    protected function loadSelectedOptions(): void
    {
        if (!$this->hasValue() || !$this->modelClass) {
            $this->selectedOptions = [];
            return;
        }

        $values = $this->multiple ? $this->value : [$this->value];
        $values = array_filter($values);

        if (empty($values)) {
            $this->selectedOptions = [];
            return;
        }

        $this->selectedOptions = $this->modelClass::select([$this->valueColumn, $this->displayColumn])
            ->whereIn($this->valueColumn, $values)
            ->get()
            ->map(function (Model $model) {
                return [
                    'id' => $model->{$this->valueColumn},
                    'text' => $model->{$this->displayColumn},
                ];
            })
            ->toArray();
    }

    /**
     * Add option to selection
     */
    public function selectOption(array $option): void
    {
        if ($this->multiple) {
            $values = is_array($this->value) ? $this->value : [];
            if (!in_array($option['id'], $values)) {
                $values[] = $option['id'];
                $this->value = $values;
                $this->selectedOptions[] = $option;
            }
        } else {
            $this->value = $option['id'];
            $this->selectedOptions = [$option];
        }

        $this->searchQuery = '';
    }

    /**
     * Remove option from selection
     */
    public function removeOption($optionId): void
    {
        if ($this->multiple) {
            $this->value = array_values(array_filter($this->value, fn ($id) => $id != $optionId));
            $this->selectedOptions = array_values(array_filter($this->selectedOptions, fn ($opt) => $opt['id'] != $optionId));
        } else {
            $this->value = null;
            $this->selectedOptions = [];
        }
    }

    public function render()
    {
        return view('flux-tables::livewire.filters.searchable-select-filter', [
            'label' => $this->label,
            'description' => $this->description,
        ]);
    }
}
