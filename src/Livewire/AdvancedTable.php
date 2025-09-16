<?php

namespace Idkwhoami\FluxTables\Livewire;

use Idkwhoami\FluxTables\Configuration\TableConfiguration;
use Idkwhoami\FluxTables\Data\TableDataRepository;
use Idkwhoami\FluxTables\Data\TableQueryBuilder;
use Idkwhoami\FluxTables\Services\TableStateService;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;

/**
 * AdvancedTable - Table with sorting and search capabilities
 *
 * Features:
 * ✅ Data display
 * ✅ Search functionality
 * ✅ Sorting (column headers clickable)
 * ✅ Pagination
 * ✅ Column toggling
 * ❌ No filtering (use EnhancedTable for filters)
 * ❌ No actions (use EnhancedTable for actions)
 */
class AdvancedTable extends BaseTable
{
    public string $sortColumn = '';
    public string $sortDirection = 'asc';

    public function mount(?Container $container = null): void
    {
        if (!$this->modelClass) {
            throw new \InvalidArgumentException('modelClass must be provided');
        }

        $this->initializeBaseTable($container);

        // Initialize component state from configuration
        $configuration = $this->getConfiguration();
        if ($configuration) {
            $defaultSort = $configuration->getSortingDefaultSort();
            if ($defaultSort) {
                $this->sortColumn = $defaultSort['column'];
                $this->sortDirection = $defaultSort['direction'];
                $state = $this->getStateService();
                if ($state) {
                    $state->setSort($this->sortColumn, $this->sortDirection);
                }
            }
        }
    }

    protected function getTableType(): string
    {
        return 'advanced_table';
    }

    protected function createTableConfiguration(): TableConfiguration
    {
        if ($this->modelClass) {
            return TableConfiguration::forModel($this->modelClass)
                ->sortingAllowMultiSort()
                ->columnShowToggleButton();
        }

        throw new \InvalidArgumentException('modelClass must be provided');
    }

    #[Computed]
    public function models(): LengthAwarePaginator
    {
        $dataRepository = $this->getDataRepository();
        if (!$dataRepository) {
            return $this->emptyPaginator();
        }

        $query = $this->buildQuery();
        return $dataRepository->paginate($query, $this->perPage);
    }

    protected function buildQuery(): TableQueryBuilder
    {
        $dataRepository = $this->getDataRepository();
        $configuration = $this->getConfiguration();

        if (!$dataRepository || !$configuration) {
            throw new \RuntimeException('Data repository or configuration not available');
        }

        $query = $dataRepository->query();

        // Apply search
        if (!empty($this->search) && $configuration->hasSearchableColumns()) {
            $query = $query->applySearch(
                $configuration->getSearchableColumns(),
                $this->search
            );
        }

        // Apply sorting
        if (!empty($this->sortColumn)) {
            $query = $query->applySort($this->sortColumn, $this->sortDirection);
        }

        return $query;
    }

    public function updatedSearch(): void
    {
        $state = $this->getStateService();
        if ($state) {
            $state->setSearch($this->search);
        }
        parent::updatedSearch();
    }

    public function sortBy(string $column): void
    {
        // Find the column configuration to check if it's sortable
        $configuration = $this->getConfiguration();
        $columnConfig = collect($configuration->getColumns())
            ->firstWhere('name', $column);

        // Return early if column is not sortable
        if (!$columnConfig || !$columnConfig->isSortable()) {
            return;
        }

        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortColumn = $column;
            $this->sortDirection = 'asc';
        }

        $state = $this->getStateService();
        if ($state) {
            $state->setSort($this->sortColumn, $this->sortDirection);
        }
        $this->resetPage();
    }

    public function toggleColumn(string $columnName): void
    {
        $state = $this->getStateService();
        if ($state) {
            $state->toggleColumn($columnName);
        }
    }

    public function updatePerPage(int $perPage): void
    {
        $state = $this->getStateService();
        if ($state) {
            $state->setPerPage($perPage);
        }
        parent::updatePerPage($perPage);
    }

    public function getState(): ?TableStateService
    {
        return $this->getStateService();
    }

    public function getToggleableColumns(): array
    {
        $config = $this->getConfiguration();
        return $config ? $config->getToggleable() : [];
    }

    public function getToggledColumns(): array
    {
        $state = $this->getStateService();
        return $state ? $state->getToggledColumns() : [];
    }

    public function getPaginationOptions(): array
    {
        $config = $this->getConfiguration();
        return $config ? $config->getPerPageOptions() : [10, 15, 25, 50];
    }

    public function render(): View
    {
        return view('flux-tables::livewire.table.advanced', [
            'configuration' => $this->getConfiguration(),
            'state' => $this->getStateService(),
        ]);
    }
}
