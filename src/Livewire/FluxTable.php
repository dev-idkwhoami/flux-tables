<?php

namespace Idkwhoami\FluxTables\Livewire;

use Idkwhoami\FluxTables\Configuration\TableConfiguration;
use Illuminate\Contracts\Container\Container;

class FluxTable extends BaseTable
{
    public ?string $modelClass = null;

    public function mount(?Container $container = null, ?string $modelClass = null, ?TableConfiguration $configuration = null): void
    {
        // Initialize the base table first
        parent::mount($container);

        // If configuration is provided, use it and extract model class if needed
        if ($configuration) {
            $this->setConfiguration($configuration);

            // Set model class from configuration if not provided
            if (!$modelClass && $configuration->getModelClass()) {
                $this->modelClass = $configuration->getModelClass();
            } elseif ($modelClass) {
                $this->modelClass = $modelClass;
            }

            // Validate that we have a model class when configuration is provided
            if (!$this->modelClass) {
                throw new \InvalidArgumentException('Configuration must contain a model class or modelClass must be provided to FluxTable');
            }
        } else {
            // Set the model class if provided
            if ($modelClass) {
                $this->modelClass = $modelClass;
            }

            // Validate that we have a model class when no configuration is provided
            if (!$this->modelClass) {
                throw new \InvalidArgumentException('modelClass must be provided to FluxTable when no configuration is given');
            }

            // Create configuration from model and set it
            $configuration = TableConfiguration::forModel($this->modelClass);
            $this->setConfiguration($configuration);
        }
    }

    protected function getTableType(): string
    {
        return 'flux_table';
    }
}
