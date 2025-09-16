<?php

namespace Idkwhoami\FluxTables\Livewire;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;

/**
 * SimpleTable - The most basic table implementation
 *
 * Features:
 * ✅ Data display
 * ✅ Search functionality
 * ✅ Pagination
 * ❌ No sorting
 * ❌ No filtering
 * ❌ No actions
 * ❌ No column toggling
 */
class SimpleTable extends BaseTable
{
    public function mount(?Container $container = null): void
    {
        if (!$this->modelClass) {
            throw new \InvalidArgumentException('modelClass must be provided');
        }

        $this->initializeBaseTable($container);
    }

    protected function getTableType(): string
    {
        return 'simple_table';
    }

    #[Computed]
    public function models()
    {
        $config = $this->getConfiguration();
        if (!$config) {
            return $this->emptyPaginator();
        }

        // Get the base query from configuration
        $query = $config->getBaseQuery();

        // Apply search if provided
        if (!empty($this->search) && $config->hasSearchableColumns()) {
            $searchColumns = $config->getSearchableColumns();
            if (!empty($searchColumns)) {
                $query->where(function ($q) use ($searchColumns) {
                    foreach ($searchColumns as $column) {
                        $q->orWhere($column, 'LIKE', '%' . $this->search . '%');
                    }
                });
            }
        }

        // Return paginated results
        return $query->paginate($this->perPage, ['*'], 'page');
    }

    public function render(): View
    {
        return view('flux-tables::livewire.table.simple', [
            'configuration' => $this->getConfiguration(),
        ]);
    }
}
