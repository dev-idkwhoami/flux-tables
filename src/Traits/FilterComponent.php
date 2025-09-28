<?php

namespace Idkwhoami\FluxTables\Traits;

use Livewire\Attributes\Modelable;

trait FilterComponent
{
    #[Modelable]
    public mixed $value = null;

    public string $filterName = '';
    public string $masterFilterId = '';
    public string $label = '';
    public ?string $description = null;
    public ?string $view = null;

    /**
     * Mount the filter component
     */
    public function mountFilterComponent(
        string $filterName,
        string $masterFilterId,
        mixed $value = null,
        string $label = '',
        ?string $description = null
    ): void {
        $this->filterName = $filterName;
        $this->masterFilterId = $masterFilterId;
        $this->value = $value;
        $this->label = $label ?: str($filterName)->title()->replace('_', ' ')->toString();
        $this->description = $description;
    }

    /**
     * Handle value updates and notify the master filter
     */
    public function updatedValue(): void
    {
        $this->dispatch(
            'updatePendingFilter',
            filterName: $this->filterName,
            value: $this->value
        )->to($this->masterFilterId);
    }

    /**
     * Reset this filter
     */
    public function resetFilter(): void
    {
        $this->value = null;
        $this->dispatch(
            'updatePendingFilter',
            filterName: $this->filterName,
            value: null
        )->to($this->masterFilterId);
    }

    /**
     * Check if filter has a value
     */
    public function hasValue(): bool
    {
        return $this->value !== null && $this->value !== '' && $this->value !== [];
    }

    /**
     * Get the filter wrapper view
     */
    public function getFilterWrapperView(): string
    {
        return 'flux-tables::components.filter-wrapper';
    }

    /**
     * Get the default view name for this filter component
     */
    protected function getDefaultView(): string
    {
        // Override in subclasses to provide default view names
        return 'flux-tables::livewire.filters.basic-filter';
    }

    /**
     * Get the view name to use for rendering (custom or default)
     */
    public function getView(): string
    {
        return $this->view ?? $this->getDefaultView();
    }

    /**
     * Set a view for this filter component
     */
    public function view(string $view): static
    {
        $this->view = $view;
        return $this;
    }
}
