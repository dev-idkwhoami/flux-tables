<?php

namespace Idkwhoami\FluxTables\Configuration;

use Idkwhoami\FluxTables\Services\ClosureManager;
use Idkwhoami\FluxTables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Wireable;
use Closure;

class TableConfiguration implements Wireable
{
    // Core table properties
    protected ?string $modelClass = null;
    protected ?Builder $query = null;
    protected string|Closure $title = '';
    protected string|Closure $description = '';
    protected array|Closure $columns = [];
    protected array|Closure $filters = [];
    protected array|Closure $actions = [];

    // Dynamic configuration support
    protected ?object $tableInstance = null;
    protected mixed $currentUser = null;

    // Pagination properties
    protected int|Closure $paginationDefaultPerPage = 15;
    protected array|Closure $paginationPerPageOptions = [10, 15, 25, 50, 100];
    protected bool|Closure $paginationShowPerPageSelector = true;
    protected bool|Closure $paginationShowPageInfo = true;
    protected bool|Closure $paginationShowFirstLastButtons = true;
    protected string $paginationPageName = 'page';
    protected array $paginationTexts = [
        'showing' => 'flux-tables::pagination.showing',
        'to' => 'flux-tables::pagination.to',
        'of' => 'flux-tables::pagination.of',
        'results' => 'flux-tables::pagination.results',
        'per_page' => 'flux-tables::pagination.per_page',
        'previous' => 'flux-tables::pagination.previous',
        'next' => 'flux-tables::pagination.next',
        'first' => 'flux-tables::pagination.first',
        'last' => 'flux-tables::pagination.last',
        'page' => 'flux-tables::pagination.page',
        'go_to_page' => 'flux-tables::pagination.go_to_page',
        'items_per_page' => 'flux-tables::pagination.items_per_page',
        'all' => 'flux-tables::pagination.all',
    ];

    // Sorting properties
    protected ?string $sortingDefaultColumn = null;
    protected string $sortingDefaultDirection = 'asc';
    protected bool $sortingMultiSort = false;
    protected bool $sortingPersistSort = true;
    protected string $sortingSortParameter = 'sort';
    protected array $sortingTexts = [
        'sort_by' => 'flux-tables::sorting.sort_by',
        'ascending' => 'flux-tables::sorting.ascending',
        'descending' => 'flux-tables::sorting.descending',
        'sort_ascending' => 'flux-tables::sorting.sort_ascending',
        'sort_descending' => 'flux-tables::sorting.sort_descending',
        'clear_sort' => 'flux-tables::sorting.clear_sort',
        'sorted_by' => 'flux-tables::sorting.sorted_by',
        'click_to_sort' => 'flux-tables::sorting.click_to_sort',
        'multi_sort' => 'flux-tables::sorting.multi_sort',
    ];

    // Column properties
    protected array $columnDefaultHidden = [];
    protected array $columnDefaultToggled = [];
    protected array $columnToggleable = [];
    protected array $columnOrder = [];
    protected bool $columnShowToggleButton = true;
    protected bool $columnRememberToggleState = true;
    protected string $columnToggleButtonPosition = 'header';
    protected array $columnTexts = [
        'toggle_columns' => 'flux-tables::columns.toggle_columns',
        'show_all' => 'flux-tables::columns.show_all',
        'hide_all' => 'flux-tables::columns.hide_all',
        'visible_columns' => 'flux-tables::columns.visible_columns',
        'hidden_columns' => 'flux-tables::columns.hidden_columns',
        'column_visibility' => 'flux-tables::columns.column_visibility',
        'show_column' => 'flux-tables::columns.show_column',
        'hide_column' => 'flux-tables::columns.hide_column',
        'reorder_columns' => 'flux-tables::columns.reorder_columns',
        'reset_columns' => 'flux-tables::columns.reset_columns',
    ];

    // Search properties
    protected bool|Closure $searchEnabled = true;
    protected string|Closure $searchPlaceholder = 'Search...';
    protected bool|Closure $searchCaseSensitive = false;
    protected bool|Closure $searchWholeWords = false;
    protected string $searchParameter = 'search';
    protected string $searchDefaultQuery = '';
    protected int $searchDebounce = 500;
    protected bool $searchClearable = true;
    protected bool $searchHighlightMatches = false;
    protected string $searchIcon = 'search';
    protected array $searchTexts = [
        'placeholder' => 'flux-tables::search.placeholder',
        'no_results' => 'flux-tables::search.no_results',
        'clear' => 'flux-tables::search.clear',
        'searching' => 'flux-tables::search.searching',
        'found' => 'flux-tables::search.found',
        'no_matches' => 'flux-tables::search.no_matches',
        'search_in' => 'flux-tables::search.search_in',
    ];

    // UI properties
    protected string|Closure $uiTheme = 'default';
    protected bool|Closure $uiShowHeader = true;
    protected bool|Closure $uiShowFooter = true;
    protected bool|Closure $uiStriped = false;
    protected bool|Closure $uiBordered = true;
    protected string|Closure $uiSize = 'default';
    protected string|Closure $uiCreateComponent = '';
    protected string|Closure $uiCreateText = '';
    protected bool $uiLoadingStates = true;
    protected string $uiLoadingIcon = 'spinner';
    protected string $uiEmptyStateIcon = 'inbox';
    protected string $uiErrorIcon = 'exclamation-triangle';
    protected string $uiCreateIcon = 'plus';
    protected string $uiCreateVariant = 'filled';
    protected string $uiCreateSize = 'sm';
    protected array $uiModalConfig = [
        'dismissible' => true,
        'variant' => 'default',
        'size' => 'md',
    ];
    protected array $uiTexts = [
        'loading' => 'flux-tables::general.loading',
        'empty_state' => 'flux-tables::general.empty_state',
        'error' => 'flux-tables::general.error',
        'success' => 'flux-tables::general.success',
        'warning' => 'flux-tables::general.warning',
        'info' => 'flux-tables::general.info',
        'refresh' => 'flux-tables::general.refresh',
        'reload' => 'flux-tables::general.reload',
        'try_again' => 'flux-tables::general.try_again',
        'something_went_wrong' => 'flux-tables::general.something_went_wrong',
        'please_wait' => 'flux-tables::general.please_wait',
        'processing' => 'flux-tables::general.processing',
        'create_button' => 'flux-tables::create.button',
        'create_title' => 'flux-tables::create.title',
        'create_success' => 'flux-tables::create.success',
        'create_failed' => 'flux-tables::create.failed',
        'creating' => 'flux-tables::create.creating',
    ];

    // Filter properties
    protected bool|Closure $filterEnabled = false;
    protected string $filterPosition = 'sidebar';
    protected bool $filterCollapsible = true;
    protected bool $filterDefaultExpanded = true;
    protected array $filterDefaultValues = [];
    protected bool $filterPersistFilters = true;
    protected string $filterModalPosition = 'center';
    protected bool $filterModalDismissible = true;
    protected bool $filterShowClearAll = true;
    protected bool $filterShowActiveCount = true;
    protected array $filterTexts = [
        'title' => 'flux-tables::filters.title',
        'clear_all' => 'flux-tables::filters.clear_all',
        'apply' => 'flux-tables::filters.apply',
        'reset' => 'flux-tables::filters.reset',
        'done' => 'flux-tables::filters.done',
        'cancel' => 'flux-tables::filters.cancel',
        'no_active_filters' => 'flux-tables::filters.no_active_filters',
        'active_filters' => 'flux-tables::filters.active_filters',
        'filter_by' => 'flux-tables::filters.filter_by',
        'add_filter' => 'flux-tables::filters.add_filter',
        'remove_filter' => 'flux-tables::filters.remove_filter',
        'filter_applied' => 'flux-tables::filters.filter_applied',
        'filters_applied' => 'flux-tables::filters.filters_applied',
        'show_all' => 'flux-tables::filters.show_all',
    ];

    // Action properties
    protected bool|Closure $actionEnabled = false;
    protected string $actionPosition = 'right';
    protected bool $actionSticky = false;
    protected array $actionBulkActions = [];
    protected bool $actionShowBulkActions = true;
    protected bool $actionConfirmDestructive = true;
    protected string $actionConfirmationModalVariant = 'danger';
    protected array $actionTexts = [
        'actions' => 'flux-tables::actions.actions',
        'edit' => 'flux-tables::actions.edit',
        'delete' => 'flux-tables::actions.delete',
        'view' => 'flux-tables::actions.view',
        'create' => 'flux-tables::actions.create',
        'save' => 'flux-tables::actions.save',
        'cancel' => 'flux-tables::actions.cancel',
        'close' => 'flux-tables::actions.close',
        'confirm' => 'flux-tables::actions.confirm',
        'bulk_actions' => 'flux-tables::actions.bulk_actions',
        'select_all' => 'flux-tables::actions.select_all',
        'select_none' => 'flux-tables::actions.select_none',
        'selected_items' => 'flux-tables::actions.selected_items',
        'no_items_selected' => 'flux-tables::actions.no_items_selected',
        'action_completed' => 'flux-tables::actions.action_completed',
        'action_failed' => 'flux-tables::actions.action_failed',
        'delete_confirmation' => 'flux-tables::modals.delete_confirmation',
        'bulk_delete_confirmation' => 'flux-tables::modals.bulk_delete_confirmation',
        'confirm_title' => 'flux-tables::modals.confirm_title',
        'confirm_message' => 'flux-tables::modals.confirm_message',
    ];

    // Localization properties
    protected array $localizationTexts = [];
    protected string $localizationDefaultLocale = 'en';

    // Scope management
    protected array $withoutScopes = [];
    protected bool $withoutGlobalScopes = false;

    public function __construct()
    {
        $this->currentUser = auth()->user();
    }

    public static function make(): static
    {
        return new static();
    }

    public static function forModel(string $modelClass): static
    {
        $instance = new static();
        $instance->modelClass = $modelClass;

        // Auto-generate basic columns if none are provided
        if (empty($instance->getColumns())) {
            $instance->columns = $instance->generateDefaultColumns($modelClass);
        }

        return $instance;
    }

    public static function forQuery(Builder $query): static
    {
        $instance = new static();
        $instance->query = $query;
        $instance->modelClass = get_class($query->getModel());
        return $instance;
    }

    public function model(string $modelClass): static
    {
        $this->modelClass = $modelClass;
        $this->query = null;
        return $this;
    }

    public function query(Builder $query): static
    {
        $this->query = $query;
        $this->modelClass = get_class($query->getModel());
        return $this;
    }

    public function setTableInstance(object $tableInstance): static
    {
        $this->tableInstance = $tableInstance;
        return $this;
    }

    public function columns(array $columns): static
    {
        $this->columns = $columns;
        return $this;
    }

    // Core getter methods
    public function getModelClass(): ?string
    {
        return $this->modelClass;
    }

    public function getQuery(): ?Builder
    {
        return $this->query;
    }

    public function getBaseQuery(): Builder
    {
        if ($this->query) {
            return clone $this->query;
        }

        if (!$this->modelClass) {
            throw new \RuntimeException('No model class or query specified for table configuration');
        }

        return (new $this->modelClass())->newQuery();
    }

    public function getColumns(): array
    {
        return $this->resolveValue($this->columns);
    }


    public function hasFilters(): bool
    {
        return !empty($this->resolveValue($this->filters));
    }

    public function getFilters(): array
    {
        return $this->resolveValue($this->filters);
    }

    public function hasLabel(): bool
    {
        return !empty($this->resolveValue($this->title));
    }

    public function getLabel(): string
    {
        return $this->resolveValue($this->title);
    }

    public function hasCreate(): bool
    {
        return !empty($this->resolveValue($this->uiCreateComponent ?? ''));
    }

    public function getCreateText(): ?string
    {
        return $this->resolveValue($this->uiCreateText ?? null);
    }

    public function getCreateComponent(): ?string
    {
        return $this->resolveValue($this->uiCreateComponent ?? null);
    }

    public function hasActions(): bool
    {
        return !empty($this->resolveValue($this->actions));
    }

    public function getActions(): array
    {
        return $this->resolveValue($this->actions);
    }

    // Pagination methods
    public function paginationDefaultPerPage(int|Closure $perPage): static
    {
        $this->paginationDefaultPerPage = $perPage;
        return $this;
    }

    public function paginationPerPageOptions(array|Closure $options): static
    {
        $this->paginationPerPageOptions = $options;
        return $this;
    }

    public function getPaginationDefaultPerPage(): int
    {
        return $this->resolveValue($this->paginationDefaultPerPage);
    }

    public function getPaginationPerPageOptions(): array
    {
        return $this->resolveValue($this->paginationPerPageOptions);
    }

    // Sorting methods
    public function sortingDefaultColumn(?string $column): static
    {
        $this->sortingDefaultColumn = $column;
        return $this;
    }

    public function sortingDefaultDirection(string $direction): static
    {
        $this->sortingDefaultDirection = $direction;
        return $this;
    }

    public function sortingAllowMultiSort(bool $allow = true): static
    {
        $this->sortingMultiSort = $allow;
        return $this;
    }

    public function getSortingDefaultColumn(): ?string
    {
        return $this->sortingDefaultColumn;
    }

    public function getSortingDefaultDirection(): string
    {
        return $this->sortingDefaultDirection;
    }

    public function getSortingDefaultSort(): ?array
    {
        if (!$this->sortingDefaultColumn) {
            return null;
        }

        return [
            'column' => $this->sortingDefaultColumn,
            'direction' => $this->sortingDefaultDirection,
        ];
    }

    // Column methods
    public function columnShowToggleButton(bool $show = true): static
    {
        $this->columnShowToggleButton = $show;
        return $this;
    }

    public function columnToggleable(array $columns): static
    {
        $this->columnToggleable = $columns;
        return $this;
    }

    public function getColumnToggleable(): array
    {
        return $this->columnToggleable;
    }

    public function getColumnShowToggleButton(): bool
    {
        return $this->resolveValue($this->columnShowToggleButton);
    }

    public function getColumnToggleButtonPosition(): string
    {
        return $this->resolveValue($this->columnToggleButtonPosition);
    }

    public function getColumnRememberToggleState(): bool
    {
        return $this->resolveValue($this->columnRememberToggleState);
    }

    public function getColumnTexts(): array
    {
        return $this->resolveValue($this->columnTexts);
    }

    public function getColumnOrder(): array
    {
        return $this->resolveValue($this->columnOrder);
    }

    // Filter getters
    public function getFilterEnabled(): bool
    {
        return $this->resolveValue($this->filterEnabled);
    }

    public function getFilterPosition(): string
    {
        return $this->resolveValue($this->filterPosition);
    }

    public function getFilterCollapsible(): bool
    {
        return $this->resolveValue($this->filterCollapsible);
    }

    public function getFilterDefaultExpanded(): bool
    {
        return $this->resolveValue($this->filterDefaultExpanded);
    }

    public function getFilterDefaultValues(): array
    {
        return $this->resolveValue($this->filterDefaultValues);
    }

    public function getFilterPersistFilters(): bool
    {
        return $this->resolveValue($this->filterPersistFilters);
    }

    public function getFilterModalPosition(): string
    {
        return $this->resolveValue($this->filterModalPosition);
    }

    public function getFilterModalDismissible(): bool
    {
        return $this->resolveValue($this->filterModalDismissible);
    }

    public function getFilterShowClearAll(): bool
    {
        return $this->resolveValue($this->filterShowClearAll);
    }

    public function getFilterShowActiveCount(): bool
    {
        return $this->resolveValue($this->filterShowActiveCount);
    }

    public function getFilterTexts(): array
    {
        return $this->resolveValue($this->filterTexts);
    }

    // Action getters
    public function getActionEnabled(): bool
    {
        return $this->resolveValue($this->actionEnabled);
    }

    public function getActionPosition(): string
    {
        return $this->resolveValue($this->actionPosition);
    }

    public function getActionSticky(): bool
    {
        return $this->resolveValue($this->actionSticky);
    }

    public function getActionBulkActions(): array
    {
        return $this->resolveValue($this->actionBulkActions);
    }

    public function getActionShowBulkActions(): bool
    {
        return $this->resolveValue($this->actionShowBulkActions);
    }

    public function getActionConfirmDestructive(): bool
    {
        return $this->resolveValue($this->actionConfirmDestructive);
    }

    public function getActionConfirmationModalVariant(): string
    {
        return $this->resolveValue($this->actionConfirmationModalVariant);
    }

    public function getActionTexts(): array
    {
        return $this->resolveValue($this->actionTexts);
    }

    // Pagination getters
    public function getPaginationShowPerPageSelector(): bool
    {
        return $this->resolveValue($this->paginationShowPerPageSelector);
    }

    public function getPaginationShowPageInfo(): bool
    {
        return $this->resolveValue($this->paginationShowPageInfo);
    }

    public function getPaginationShowFirstLastButtons(): bool
    {
        return $this->resolveValue($this->paginationShowFirstLastButtons);
    }

    public function getPaginationPageName(): string
    {
        return $this->resolveValue($this->paginationPageName);
    }

    public function getPaginationTexts(): array
    {
        return $this->resolveValue($this->paginationTexts);
    }

    // Sorting getters
    public function getSortingMultiSort(): bool
    {
        return $this->resolveValue($this->sortingMultiSort);
    }

    // Search getters
    public function getSearchEnabled(): bool
    {
        return $this->resolveValue($this->searchEnabled);
    }

    public function getSearchPlaceholder(): string
    {
        return $this->resolveValue($this->searchPlaceholder);
    }

    public function getSearchDefaultQuery(): string
    {
        return $this->resolveValue($this->searchDefaultQuery);
    }

    // UI getters
    public function getUiCreateIcon(): string
    {
        return $this->resolveValue($this->uiCreateIcon);
    }

    public function getUiCreateVariant(): string
    {
        return $this->resolveValue($this->uiCreateVariant);
    }

    public function getUiCreateSize(): string
    {
        return $this->resolveValue($this->uiCreateSize);
    }

    public function getUiModalConfig(): array
    {
        return $this->resolveValue($this->uiModalConfig);
    }

    public function getUiTexts(): array
    {
        return $this->resolveValue($this->uiTexts);
    }

    // Helper methods for backward compatibility
    public function paginationConfig(): self
    {
        return $this;
    }

    public function sortingConfig(): self
    {
        return $this;
    }

    public function columnConfig(): self
    {
        return $this;
    }

    public function getDefaultPerPage(): int
    {
        return $this->getPaginationDefaultPerPage();
    }

    public function getPerPageOptions(): array
    {
        return $this->getPaginationPerPageOptions();
    }

    public function allowMultiSort(bool $allow = true): static
    {
        return $this->sortingAllowMultiSort($allow);
    }

    public function showToggleButton(bool $show = true): static
    {
        return $this->columnShowToggleButton($show);
    }

    public function getToggleable(): array
    {
        return $this->getColumnToggleable();
    }

    public function getDefaultSort(): ?array
    {
        return $this->getSortingDefaultSort();
    }

    protected function resolveValue(mixed $value): mixed
    {
        if ($value instanceof Closure) {
            return $value($this->currentUser, $this->tableInstance, $this);
        }

        return $value;
    }

    protected function generateDefaultColumns(string $modelClass): array
    {
        $columns = [];

        try {
            $model = new $modelClass();
            $fillable = $model->getFillable();

            // Filter out sensitive fields that shouldn't be displayed in tables
            $hiddenFields = ['password', 'password_confirmation', 'remember_token', 'email_verified_at'];
            $fillable = array_diff($fillable, $hiddenFields);

            foreach ($fillable as $attribute) {
                $columns[] = TextColumn::make($attribute)
                    ->label(str($attribute)->title()->toString())
                    ->sortable(true)
                    ->searchable(true)
                    ->visible(true);
            }

            // Add id column if not in fillable
            if (!in_array('id', $fillable)) {
                array_unshift(
                    $columns,
                    TextColumn::make('id')
                    ->label('ID')
                    ->sortable(true)
                    ->searchable(false)
                    ->visible(true)
                );
            }

        } catch (\Exception $e) {
            // Fallback to basic columns if model introspection fails
            $columns = [
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(true)
                    ->searchable(false)
                    ->visible(true),
            ];
        }

        return $columns;
    }

    public function toLivewire(): array
    {
        $data = [];
        $reflection = new \ReflectionClass($this);

        foreach ($reflection->getProperties(\ReflectionProperty::IS_PROTECTED) as $property) {
            $propertyName = $property->getName();

            // Skip certain properties that don't need serialization
            if (in_array($propertyName, ['tableInstance', 'currentUser', 'query'])) {
                continue;
            }

            $property->setAccessible(true);
            $value = $property->getValue($this);

            // Handle closures with ClosureManager, arrays and basic types directly
            if ($value instanceof \Closure) {
                $data[$propertyName] = ClosureManager::storeIfClosure($value);
            } elseif (is_array($value)) {
                // Recursively handle arrays that might contain closures
                $data[$propertyName] = $this->serializeArrayValue($value);
            } else {
                $data[$propertyName] = $value;
            }
        }

        return $data;
    }

    protected function serializeArrayValue(array $array): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            if ($value instanceof \Closure) {
                $result[$key] = ClosureManager::storeIfClosure($value);
            } elseif (is_array($value)) {
                $result[$key] = $this->serializeArrayValue($value);
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    protected function deserializeArrayValue(array $array): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (is_array($value) && isset($value['__closure_id'])) {
                $result[$key] = ClosureManager::restoreIfClosure($value);
            } elseif (is_array($value)) {
                $result[$key] = $this->deserializeArrayValue($value);
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    public static function fromLivewire($value): static
    {
        $instance = new static();
        $reflection = new \ReflectionClass($instance);

        foreach ($reflection->getProperties(\ReflectionProperty::IS_PROTECTED) as $property) {
            $propertyName = $property->getName();

            // Skip certain properties that should be initialized separately
            if (in_array($propertyName, ['tableInstance', 'query'])) {
                continue;
            }

            if (array_key_exists($propertyName, $value)) {
                $property->setAccessible(true);

                $propertyValue = $value[$propertyName];

                // Restore closures if they were stored
                if (is_array($propertyValue) && isset($propertyValue['__closure_id'])) {
                    $property->setValue($instance, ClosureManager::restoreIfClosure($propertyValue));
                } elseif (is_array($propertyValue)) {
                    // Recursively handle arrays that might contain closures
                    $property->setValue($instance, $instance->deserializeArrayValue($propertyValue));
                } else {
                    $property->setValue($instance, $propertyValue);
                }
            }
        }

        // Restore current user context
        $instance->currentUser = auth()->user();

        return $instance;
    }
}
