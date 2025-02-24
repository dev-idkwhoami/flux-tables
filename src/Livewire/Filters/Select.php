<?php

namespace Idkwhoami\FluxTables\Livewire\Filters;

use Idkwhoami\FluxTables\Abstracts\Filter\PropertyFilter;
use Idkwhoami\FluxTables\Concretes\Table\EloquentTable;
use Idkwhoami\FluxTables\Traits\InteractsWithTable;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Select extends Component
{
    use InteractsWithTable;

    #[Locked]
    public EloquentTable $table;

    #[Locked]
    public PropertyFilter $filter;

    /** @var mixed[]|null */
    public ?array $state = [];

    public function updatedState(): void
    {
        $this->dispatch("flux-tables::table:{$this->filter->getTable()}:filter:update", $this->filter->getName(), $this->state);
    }

    public function mount(EloquentTable $table, PropertyFilter $filter): void
    {
        $this->table = $table;
        $this->filter = $filter;

        $this->state = $this->filter->hasValue() ? $this->filter->getValue()->getValue() : [];
    }

    /**
     * @return mixed[]
     */
    #[Computed]
    public function options(): array
    {
        $model = $this->table->getEloquentModel();

        return $model::query()->pluck($this->filter->getProperty(), $this->filter->getProperty())->toArray();
    }

    public function render(): View
    {
        return view('flux-tables::livewire.filters.select');
    }

    public function restoreDefault(): void
    {
        $this->state = $this->filter->getDefault() ?? [];
    }
}
