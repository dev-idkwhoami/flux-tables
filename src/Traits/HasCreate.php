<?php

namespace Idkwhoami\FluxTables\Traits;

use Idkwhoami\FluxTables\Configuration\TableConfiguration;

trait HasCreate
{
    /**
     * Open the create modal
     */
    public function openCreateModal(): void
    {
        $this->dispatch('open-modal', $this->getCreateModalName());
    }

    /**
     * Close the create modal
     */
    public function closeCreateModal(): void
    {
        $this->dispatch('close-modal', $this->getCreateModalName());
    }

    /**
     * Handle successful creation (can be called by create components)
     */
    public function onCreateSuccess(string $message = null): void
    {
        $this->closeCreateModal();
        $this->dispatch('$refresh'); // Refresh the table data
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $message ?? __('flux-tables::create.success'),
        ]);
    }

    /**
     * Handle creation failure (can be called by create components)
     */
    public function onCreateFailed(string $message = null): void
    {
        $this->dispatch('notify', [
            'type' => 'error',
            'message' => $message ?? __('flux-tables::create.failed'),
        ]);
    }

    /**
     * Check if create functionality is enabled
     */
    public function hasCreate(): bool
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return false;
        }

        return $configuration->hasCreate();
    }

    /**
     * Get the create component name
     */
    public function getCreateComponent(): ?string
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return null;
        }

        return $configuration->getCreateComponent();
    }

    /**
     * Get the create button text
     */
    public function getCreateText(): string
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return 'flux-tables::create.button';
        }

        return $configuration->getCreateText() ?? 'flux-tables::create.button';
    }

    /**
     * Get the create modal name for Livewire wire:model
     */
    public function getCreateModalName(): string
    {
        return "flux-tables::table:{$this->tableId}:create:modal";
    }

    /**
     * Get create button icon
     */
    public function getCreateIcon(): string
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return 'plus';
        }

        return $configuration->getUiCreateIcon();
    }

    /**
     * Get create button variant
     */
    public function getCreateVariant(): string
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return 'filled';
        }

        return $configuration->getUiCreateVariant();
    }

    /**
     * Get create button size
     */
    public function getCreateSize(): string
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return 'sm';
        }

        return $configuration->getUiCreateSize();
    }

    /**
     * Get create modal configuration
     */
    public function getCreateModalConfig(): array
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return [
                'dismissible' => true,
                'variant' => 'default',
                'size' => 'md',
            ];
        }

        return $configuration->getUiModalConfig();
    }

    /**
     * Check if create modal is dismissible
     */
    public function isCreateModalDismissible(): bool
    {
        $modalConfig = $this->getCreateModalConfig();
        return $modalConfig['dismissible'] ?? true;
    }

    /**
     * Get create modal variant
     */
    public function getCreateModalVariant(): string
    {
        $modalConfig = $this->getCreateModalConfig();
        return $modalConfig['variant'] ?? 'default';
    }

    /**
     * Get create modal size
     */
    public function getCreateModalSize(): string
    {
        $modalConfig = $this->getCreateModalConfig();
        return $modalConfig['size'] ?? 'md';
    }

    /**
     * Get create texts for localization
     */
    public function getCreateTexts(): array
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return [
                'button' => 'flux-tables::create.button',
                'title' => 'flux-tables::create.title',
                'success' => 'flux-tables::create.success',
                'failed' => 'flux-tables::create.failed',
                'creating' => 'flux-tables::create.creating',
            ];
        }

        return array_filter([
            'button' => $configuration->getUiTexts()['create_button'] ?? 'flux-tables::create.button',
            'title' => $configuration->getUiTexts()['create_title'] ?? 'flux-tables::create.title',
            'success' => $configuration->getUiTexts()['create_success'] ?? 'flux-tables::create.success',
            'failed' => $configuration->getUiTexts()['create_failed'] ?? 'flux-tables::create.failed',
            'creating' => $configuration->getUiTexts()['creating'] ?? 'flux-tables::create.creating',
        ]);
    }

    /**
     * Trigger a refresh of the table after creation
     */
    public function refreshAfterCreate(): void
    {
        $this->dispatch('$refresh');
    }

    /**
     * Check if create button should be shown in header
     */
    public function showCreateInHeader(): bool
    {
        return $this->hasCreate();
    }

    /**
     * Get create button classes for styling
     */
    public function getCreateButtonClasses(): string
    {
        $size = $this->getCreateSize();
        $variant = $this->getCreateVariant();

        return "btn-{$variant} btn-{$size}";
    }
}