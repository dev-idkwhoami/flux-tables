<?php

namespace Idkwhoami\FluxTables\Livewire;

use Idkwhoami\FluxTables\Configuration\TableConfiguration;
use Idkwhoami\FluxTables\Data\TableDataRepository;
use Idkwhoami\FluxTables\Data\TableQueryBuilder;
use Idkwhoami\FluxTables\Services\TableStateService;
use Idkwhoami\FluxTables\Traits\HasTableConfiguration;
use Idkwhoami\FluxTables\Traits\HasSearching;
use Idkwhoami\FluxTables\Traits\HasSorting;
use Idkwhoami\FluxTables\Traits\HasPagination;
use Idkwhoami\FluxTables\Traits\HasFiltering;
use Idkwhoami\FluxTables\Traits\HasActions;
use Idkwhoami\FluxTables\Traits\HasColumnToggling;
use Idkwhoami\FluxTables\Traits\HasCreate;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class BaseTable extends Component
{
    use WithPagination,
        HasTableConfiguration,
        HasSearching,
        HasSorting,
        HasPagination,
        HasFiltering,
        HasActions,
        HasColumnToggling,
        HasCreate;

    // Store service instances (not serialized with Livewire)
    protected ?TableStateService $stateService = null;
    protected ?TableDataRepository $dataRepository = null;
    protected ?Container $container = null;

    public function mount(?Container $container = null): void
    {
        $this->container = $container ?? app();
        $this->tableId = $this->generateTableId();
    }

    public function boot(): void
    {
        // Ensure container is available after Livewire hydration
        if (!$this->container) {
            $this->container = app();
        }
    }

    public function hydrate(): void
    {
        // Reset services after Livewire hydration to ensure they're fresh
        $this->stateService = null;
        $this->dataRepository = null;

        // Ensure container is available
        if (!$this->container) {
            $this->container = app();
        }
    }

    #[Computed]
    public function models(): LengthAwarePaginator
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return $this->emptyPaginator();
        }

        $query = $this->buildQuery();
        if (!$query) {
            return $this->emptyPaginator();
        }

        $dataRepository = $this->getDataRepository($configuration);
        if (!$dataRepository) {
            return $this->emptyPaginator();
        }

        return $dataRepository->paginate($query, $this->perPage);
    }

    protected function buildQuery(): ?TableQueryBuilder
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return null;
        }

        $dataRepository = $this->getDataRepository($configuration);
        if (!$dataRepository) {
            return null;
        }

        $query = $dataRepository->query();
        sleep(2);

        // Apply filters
        if ($this->hasFiltering() && $this->hasActiveFilters()) {
            $query = $query->applyFilters(
                $configuration->getFilters(),
                $this->getActiveFilters()
            );
        }

        // Apply search
        if ($this->hasActiveSearch() && $this->hasSearchableColumns()) {
            $searchColumns = $this->getSearchableColumns();
            $query = $query->applySearch($searchColumns, $this->search);
        }

        // Apply sorting
        if ($this->hasActiveSort()) {
            $query = $query->applySort($this->sortColumn, $this->sortDirection);
        }

        return $query;
    }

    protected function getStateService(): ?TableStateService
    {
        if ($this->stateService === null && $this->container) {
            $this->stateService = $this->container->makeWith(TableStateService::class, [
                'tableId' => $this->tableId
            ]);
        }

        return $this->stateService;
    }

    protected function getDataRepository(?TableConfiguration $configuration = null): ?TableDataRepository
    {
        if ($this->dataRepository === null && $this->container) {
            $config = $configuration ?? $this->getConfiguration();
            if (!$config) {
                return null;
            }

            $modelClass = $config->getModelClass();
            if (!$modelClass) {
                return null;
            }

            $this->dataRepository = $this->container->makeWith(TableDataRepository::class, [
                'modelClass' => $modelClass
            ]);
        }

        return $this->dataRepository;
    }

    protected function getTableType(): string
    {
        return 'base_table';
    }

    public function render(): View
    {
        return view('flux-tables::livewire.base-table', [
            'configuration' => $this->getConfiguration(),
        ]);
    }
}
