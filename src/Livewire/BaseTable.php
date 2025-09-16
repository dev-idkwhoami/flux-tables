<?php

namespace Idkwhoami\FluxTables\Livewire;

use Idkwhoami\FluxTables\Configuration\TableConfiguration;
use Idkwhoami\FluxTables\Data\TableDataRepository;
use Idkwhoami\FluxTables\Services\TableConfigurationManager;
use Idkwhoami\FluxTables\Services\TableStateService;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

abstract class BaseTable extends Component
{
    use WithPagination;

    // Common properties
    public ?string $modelClass = null;

    #[Locked]
    public ?string $configurationId = null;

    #[Locked]
    public string $tableId;

    public string $search = '';
    public int $perPage = 15;

    // Store service instances (not serialized with Livewire)
    protected ?TableStateService $stateService = null;
    protected ?TableDataRepository $dataRepository = null;
    protected ?Container $container = null;

    protected function initializeBaseTable(?Container $container = null): void
    {
        $this->container = $container ?? app();
        $this->tableId = uniqid($this->getTableType() . '_');

        // Create or use provided configuration
        $configuration = $this->createTableConfiguration();

        // Set table instance context
        $configuration->setTableInstance($this);

        // Store configuration server-side and keep only the ID for Livewire serialization
        $this->configurationId = TableConfigurationManager::store($configuration);

        // Initialize component state from configuration
        $this->initializeFromConfiguration($configuration);
    }

    protected function createTableConfiguration(): TableConfiguration
    {
        if ($this->modelClass) {
            return TableConfiguration::forModel($this->modelClass);
        }

        throw new \InvalidArgumentException('modelClass must be provided');
    }

    protected function initializeFromConfiguration(TableConfiguration $configuration): void
    {
        $this->perPage = $configuration->getPaginationDefaultPerPage();
    }

    public function getConfiguration(): ?TableConfiguration
    {
        if ($this->configurationId && TableConfigurationManager::has($this->configurationId)) {
            return TableConfigurationManager::get($this->configurationId);
        }

        return null;
    }

    public function getPaginationOptions(): array
    {
        $config = $this->getConfiguration();
        return $config ? $config->getPerPageOptions() : [10, 15, 25, 50];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function updatePerPage(int $perPage): void
    {
        $this->perPage = $perPage;
        $this->resetPage();
    }

    protected function emptyPaginator()
    {
        return new \Illuminate\Pagination\LengthAwarePaginator(
            collect([]),
            0,
            $this->perPage,
            1,
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );
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

    protected function getDataRepository(): ?TableDataRepository
    {
        if ($this->dataRepository === null && $this->modelClass && $this->container) {
            $this->dataRepository = $this->container->makeWith(TableDataRepository::class, [
                'modelClass' => $this->modelClass
            ]);
        }

        return $this->dataRepository;
    }

    protected function hasAdvancedFeatures(): bool
    {
        return method_exists($this, 'sortBy') || method_exists($this, 'updateFilter');
    }

    abstract protected function getTableType(): string;
}
