<?php

namespace Idkwhoami\FluxTables\Traits;

use Idkwhoami\FluxTables\Configuration\TableConfiguration;
use Idkwhoami\FluxTables\Services\TableConfigurationManager;
use Livewire\Attributes\Locked;

trait HasTableConfiguration
{
    #[Locked]
    public ?string $configurationId = null;

    #[Locked]
    public string $tableId;

    /**
     * Get the current table configuration
     */
    public function getConfiguration(): ?TableConfiguration
    {
        if ($this->configurationId && TableConfigurationManager::has($this->configurationId)) {
            return TableConfigurationManager::get($this->configurationId);
        }

        return null;
    }

    /**
     * Set the table configuration and store it in the manager
     */
    protected function setConfiguration(TableConfiguration $configuration): void
    {
        // Set table instance context
        $configuration->setTableInstance($this);

        // Store configuration and keep only the ID for Livewire serialization
        $this->configurationId = TableConfigurationManager::store($configuration);

        // Initialize component state from configuration
        $this->initializeFromConfiguration($configuration);
    }

    /**
     * Initialize component properties from configuration
     */
    protected function initializeFromConfiguration(TableConfiguration $configuration): void
    {
        // Set basic pagination from configuration
        if (property_exists($this, 'perPage')) {
            $this->perPage = $configuration->getPaginationDefaultPerPage();
        }

        // Initialize sorting if configuration has default sort
        if (method_exists($this, 'initializeSorting')) {
            $this->initializeSorting($configuration);
        }

        // Initialize other features that have default values
        if (method_exists($this, 'initializeSearch')) {
            $this->initializeSearch($configuration);
        }
    }

    /**
     * Generate a unique table ID for this instance
     */
    protected function generateTableId(): string
    {
        return uniqid($this->getTableType() . '_');
    }

    /**
     * Get the table type identifier (to be implemented by concrete classes)
     */
    abstract protected function getTableType(): string;
}