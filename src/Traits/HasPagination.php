<?php

namespace Idkwhoami\FluxTables\Traits;

use Idkwhoami\FluxTables\Configuration\TableConfiguration;

trait HasPagination
{
    public int $perPage = 15;

    /**
     * Handle per page updates
     */
    public function updatedPerPage(): void
    {
        $this->resetPage();

        // Store per page state if state service is available
        if (method_exists($this, 'getStateService') && $this->getStateService()) {
            $this->getStateService()->setPerPage($this->perPage);
        }

        // Clear computed properties to force refresh
        unset($this->models);
    }

    /**
     * Update per page value
     */
    public function updatePerPage(int $perPage): void
    {
        $this->perPage = $perPage;
        $this->resetPage();

        if (method_exists($this, 'getStateService') && $this->getStateService()) {
            $this->getStateService()->setPerPage($perPage);
        }

        // Clear computed properties to force refresh
        unset($this->models);
    }

    /**
     * Get pagination options from configuration
     */
    public function getPaginationOptions(): array
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return [10, 15, 25, 50];
        }

        return $configuration->getPaginationPerPageOptions();
    }

    /**
     * Get default per page value from configuration
     */
    public function getDefaultPerPage(): int
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return 15;
        }

        return $configuration->getPaginationDefaultPerPage();
    }

    /**
     * Check if per page selector should be shown
     */
    public function showPerPageSelector(): bool
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return true;
        }

        return $configuration->getPaginationShowPerPageSelector();
    }

    /**
     * Check if page info should be shown
     */
    public function showPageInfo(): bool
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return true;
        }

        return $configuration->getPaginationShowPageInfo();
    }

    /**
     * Check if first/last buttons should be shown
     */
    public function showFirstLastButtons(): bool
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return true;
        }

        return $configuration->getPaginationShowFirstLastButtons();
    }

    /**
     * Get pagination page name
     */
    public function getPaginationPageName(): string
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return 'page';
        }

        return $configuration->getPaginationPageName();
    }

    /**
     * Get pagination texts for localization
     */
    public function getPaginationTexts(): array
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return [
                'showing' => 'flux-tables::pagination.showing',
                'to' => 'flux-tables::pagination.to',
                'of' => 'flux-tables::pagination.of',
                'results' => 'flux-tables::pagination.results',
                'per_page' => 'flux-tables::pagination.per_page',
                'previous' => 'flux-tables::pagination.previous',
                'next' => 'flux-tables::pagination.next',
                'first' => 'flux-tables::pagination.first',
                'last' => 'flux-tables::pagination.last',
            ];
        }

        return $configuration->getPaginationTexts();
    }

    /**
     * Create an empty paginator for when no data is available
     */
    protected function emptyPaginator()
    {
        return new \Illuminate\Pagination\LengthAwarePaginator(
            collect([]),
            0,
            $this->perPage,
            1,
            [
                'path' => request()->url(),
                'pageName' => $this->getPaginationPageName(),
            ]
        );
    }

    /**
     * Check if pagination is enabled
     */
    public function hasPagination(): bool
    {
        // Pagination is always available, but this allows for future configuration
        return true;
    }
}