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
     * Generate a deterministic table ID based on configuration
     */
    protected function generateTableId(): string
    {
        // Allow override of table ID for custom use cases
        if (method_exists($this, 'getCustomTableId') && $customId = $this->getCustomTableId()) {
            return $customId;
        }

        // Create a hash based on the table configuration to ensure idempotency
        $identifier = $this->buildTableIdentifier();
        $hash = substr(md5($identifier), 0, 8);
        return $this->getTableType() . '_' . $hash;
    }

    /**
     * Build a unique identifier string for this table configuration
     *
     * This creates an idempotent identifier by combining:
     * - Component class name (e.g., App\Livewire\UsersTable)
     * - Route context (name or path)
     * - Model class being displayed
     * - Custom identifier (if getCustomTableIdentifier() method exists)
     *
     * The same configuration will always produce the same identifier,
     * ensuring persistence works correctly across page loads.
     */
    protected function buildTableIdentifier(): string
    {
        $parts = [
            // Component class name
            static::class,

            // Route information for context
            request()->route()?->getName() ?? request()->path(),

            // Model class if configured
            $this->getConfiguration()?->getModelClass() ?? 'unknown',
        ];

        // Add any custom identifier parts
        if (method_exists($this, 'getCustomTableIdentifier')) {
            $parts[] = $this->getCustomTableIdentifier();
        }

        return implode('|', array_filter($parts));
    }

    /**
     * Get the table type identifier (to be implemented by concrete classes)
     */
    abstract protected function getTableType(): string;
}