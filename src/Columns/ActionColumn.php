<?php

namespace Idkwhoami\FluxTables\Columns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;
use Closure;

class ActionColumn extends Column
{
    protected array $actions = [];
    protected string $alignment = 'right';
    protected bool $sticky = false;
    protected string $position = 'right';

    public function __construct(string $name = 'actions')
    {
        parent::__construct($name);
        $this->searchable = false;
        $this->sortable = false;
    }

    public function alignment(string $alignment): static
    {
        $this->alignment = $alignment;
        return $this;
    }

    public function sticky(bool $sticky = true, string $position = 'right'): static
    {
        $this->sticky = $sticky;
        $this->position = $position;
        return $this;
    }

    public function action(string $name): ActionBuilder
    {
        $action = new ActionBuilder($name);
        $this->actions[] = $action;
        return $action;
    }

    public function button(string $label): ActionBuilder
    {
        return $this->action($label)->type('button');
    }

    public function link(string $label, string $route): ActionBuilder
    {
        return $this->action($label)->type('link')->route($route);
    }

    public function dropdown(string $label): ActionBuilder
    {
        return $this->action($label)->type('dropdown');
    }

    public function modal(string $label): ActionBuilder
    {
        return $this->action($label)->type('modal');
    }

    public function applySort(Builder $query, string $direction): void
    {
        // Actions are not sortable
    }

    public function applySearch(Builder $query, string $search): void
    {
        // Actions are not searchable
    }

    protected function renderActions(object $record): array
    {
        if (empty($this->actions)) {
            return [];
        }

        $renderedActions = [];

        foreach ($this->actions as $action) {
            if ($action->shouldShow($record)) {
                $renderedActions[] = $action->render($record);
            }
        }

        return $renderedActions;
    }

    public function render(object $record): View
    {
        $actions = $this->renderActions($record);

        $alignmentClass = match ($this->alignment) {
            'left' => 'justify-start',
            'center' => 'justify-center',
            'right' => 'justify-end',
            default => 'justify-end'
        };

        $stickyClass = $this->sticky ? "sticky {$this->position}-0 bg-white z-10" : '';

        return view('flux-tables::columns.action', [
            'actions' => $actions,
            'alignmentClass' => $alignmentClass,
            'stickyClass' => $stickyClass,
        ]);
    }
}

class ActionBuilder
{
    protected string $name;
    protected string $type = 'button';
    protected string $label;
    protected ?string $icon = null;
    protected string $variant = 'primary';
    protected string $size = 'sm';
    protected string $color = 'primary';
    protected ?string $route = null;
    protected array $routeParams = [];
    protected ?string $url = null;
    protected ?string $wireAction = null;
    protected array $wireParams = [];
    protected ?string $modalTarget = null;
    protected ?Closure $visible = null;
    protected ?Closure $disabled = null;
    protected ?string $confirm = null;
    protected array $attributes = [];
    protected array $dropdownItems = [];

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->label = $name;
    }

    public function type(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function label(string $label): static
    {
        $this->label = $label;
        return $this;
    }

    public function icon(string $icon): static
    {
        $this->icon = $icon;
        return $this;
    }

    public function variant(string $variant): static
    {
        $this->variant = $variant;
        return $this;
    }

    public function size(string $size): static
    {
        $this->size = $size;
        return $this;
    }

    public function color(string $color): static
    {
        $this->color = $color;
        return $this;
    }

    public function route(string $route, array $params = []): static
    {
        $this->route = $route;
        $this->routeParams = $params;
        return $this;
    }

    public function url(string $url): static
    {
        $this->url = $url;
        return $this;
    }

    public function wire(string $action, array $params = []): static
    {
        $this->wireAction = $action;
        $this->wireParams = $params;
        return $this;
    }

    public function modal(string $target): static
    {
        $this->type = 'modal';
        $this->modalTarget = $target;
        return $this;
    }

    public function visible(Closure $callback): static
    {
        $this->visible = $callback;
        return $this;
    }

    public function disabled(Closure $callback): static
    {
        $this->disabled = $callback;
        return $this;
    }

    public function confirm(string $message): static
    {
        $this->confirm = $message;
        return $this;
    }

    public function attributes(array $attributes): static
    {
        $this->attributes = array_merge($this->attributes, $attributes);
        return $this;
    }

    public function dropdownItem(string $label): DropdownItemBuilder
    {
        $item = new DropdownItemBuilder($label);
        $this->dropdownItems[] = $item;
        return $item;
    }

    public function shouldShow(object $record): bool
    {
        if ($this->visible) {
            return ($this->visible)($record);
        }

        return true;
    }

    public function isDisabled(object $record): bool
    {
        if ($this->disabled) {
            return ($this->disabled)($record);
        }

        return false;
    }

    protected function resolveRouteParams(object $record): array
    {
        $resolved = [];

        foreach ($this->routeParams as $key => $value) {
            if ($value instanceof Closure) {
                $resolved[$key] = $value($record);
            } elseif (is_string($value) && str_starts_with($value, '$')) {
                $property = substr($value, 1);
                $resolved[$key] = data_get($record, $property);
            } else {
                $resolved[$key] = $value;
            }
        }

        return $resolved;
    }

    protected function resolveWireParams(object $record): array
    {
        $resolved = [];

        foreach ($this->wireParams as $key => $value) {
            if ($value instanceof Closure) {
                $resolved[$key] = $value($record);
            } elseif (is_string($value) && str_starts_with($value, '$')) {
                $property = substr($value, 1);
                $resolved[$key] = data_get($record, $property);
            } else {
                $resolved[$key] = $value;
            }
        }

        return $resolved;
    }

    public function render(object $record): string
    {
        if (!$this->shouldShow($record)) {
            return '';
        }

        return match ($this->type) {
            'button' => $this->renderButton($record),
            'link' => $this->renderLink($record),
            'dropdown' => $this->renderDropdown($record),
            'modal' => $this->renderModal($record),
            default => $this->renderButton($record)
        };
    }

    protected function renderButton(object $record): string
    {
        $disabled = $this->isDisabled($record) ? 'disabled' : '';
        $icon = $this->icon ? sprintf('<flux:icon name="%s" class="w-4 h-4" />', $this->icon) : '';
        $content = $icon ? ($icon . ' ' . htmlspecialchars($this->label)) : htmlspecialchars($this->label);

        $attributes = sprintf(
            'variant="%s" size="%s" %s',
            $this->variant,
            $this->size,
            $disabled
        );

        if ($this->wireAction) {
            $params = $this->resolveWireParams($record);
            $wireCall = $this->wireAction;
            if (!empty($params)) {
                $wireCall .= '(' . implode(', ', array_map(fn ($p) => is_string($p) ? "'{$p}'" : $p, $params)) . ')';
            }

            $attributes .= sprintf(' wire:click="%s"', htmlspecialchars($wireCall));

            if ($this->confirm) {
                $attributes .= sprintf(' wire:confirm="%s"', htmlspecialchars($this->confirm));
            }
        }

        return sprintf('<flux:button %s>%s</flux:button>', $attributes, $content);
    }

    protected function renderLink(object $record): string
    {
        $href = $this->url;

        if ($this->route) {
            $params = $this->resolveRouteParams($record);
            $href = route($this->route, $params);
        }

        $disabled = $this->isDisabled($record) ? 'disabled' : '';
        $icon = $this->icon ? sprintf('<flux:icon name="%s" class="w-4 h-4" />', $this->icon) : '';
        $content = $icon ? ($icon . ' ' . htmlspecialchars($this->label)) : htmlspecialchars($this->label);

        $attributes = sprintf(
            'href="%s" variant="%s" size="%s" %s',
            htmlspecialchars($href ?? '#'),
            $this->variant,
            $this->size,
            $disabled
        );

        return sprintf('<flux:button %s>%s</flux:button>', $attributes, $content);
    }

    protected function renderDropdown(object $record): string
    {
        $disabled = $this->isDisabled($record) ? 'disabled' : '';
        $icon = $this->icon ? sprintf('<flux:icon name="%s" class="w-4 h-4" />', $this->icon) : '';
        $content = $icon ? ($icon . ' ' . htmlspecialchars($this->label)) : htmlspecialchars($this->label);

        $items = '';
        foreach ($this->dropdownItems as $item) {
            if ($item->shouldShow($record)) {
                $items .= $item->render($record);
            }
        }

        return sprintf(
            '<flux:dropdown>
                <flux:button variant="%s" size="%s" %s>%s <flux:icon name="chevron-down" class="w-4 h-4" /></flux:button>
                <flux:menu>%s</flux:menu>
            </flux:dropdown>',
            $this->variant,
            $this->size,
            $disabled,
            $content,
            $items
        );
    }

    protected function renderModal(object $record): string
    {
        $disabled = $this->isDisabled($record) ? 'disabled' : '';
        $icon = $this->icon ? sprintf('<flux:icon name="%s" class="w-4 h-4" />', $this->icon) : '';
        $content = $icon ? ($icon . ' ' . htmlspecialchars($this->label)) : htmlspecialchars($this->label);

        $attributes = sprintf(
            'variant="%s" size="%s" %s x-on:click="$dispatch(\'open-modal\', \'%s\')"',
            $this->variant,
            $this->size,
            $disabled,
            htmlspecialchars($this->modalTarget ?? 'modal')
        );

        return sprintf('<flux:button %s>%s</flux:button>', $attributes, $content);
    }
}

class DropdownItemBuilder
{
    protected string $label;
    protected ?string $icon = null;
    protected ?string $route = null;
    protected array $routeParams = [];
    protected ?string $url = null;
    protected ?string $wireAction = null;
    protected array $wireParams = [];
    protected ?Closure $visible = null;
    protected ?Closure $disabled = null;
    protected ?string $confirm = null;
    protected string $color = 'gray';

    public function __construct(string $label)
    {
        $this->label = $label;
    }

    public function icon(string $icon): static
    {
        $this->icon = $icon;
        return $this;
    }

    public function route(string $route, array $params = []): static
    {
        $this->route = $route;
        $this->routeParams = $params;
        return $this;
    }

    public function url(string $url): static
    {
        $this->url = $url;
        return $this;
    }

    public function wire(string $action, array $params = []): static
    {
        $this->wireAction = $action;
        $this->wireParams = $params;
        return $this;
    }

    public function visible(Closure $callback): static
    {
        $this->visible = $callback;
        return $this;
    }

    public function disabled(Closure $callback): static
    {
        $this->disabled = $callback;
        return $this;
    }

    public function confirm(string $message): static
    {
        $this->confirm = $message;
        return $this;
    }

    public function color(string $color): static
    {
        $this->color = $color;
        return $this;
    }

    public function shouldShow(object $record): bool
    {
        if ($this->visible) {
            return ($this->visible)($record);
        }

        return true;
    }

    public function isDisabled(object $record): bool
    {
        if ($this->disabled) {
            return ($this->disabled)($record);
        }

        return false;
    }

    protected function resolveRouteParams(object $record): array
    {
        $resolved = [];

        foreach ($this->routeParams as $key => $value) {
            if ($value instanceof Closure) {
                $resolved[$key] = $value($record);
            } elseif (is_string($value) && str_starts_with($value, '$')) {
                $property = substr($value, 1);
                $resolved[$key] = data_get($record, $property);
            } else {
                $resolved[$key] = $value;
            }
        }

        return $resolved;
    }

    protected function resolveWireParams(object $record): array
    {
        $resolved = [];

        foreach ($this->wireParams as $key => $value) {
            if ($value instanceof Closure) {
                $resolved[$key] = $value($record);
            } elseif (is_string($value) && str_starts_with($value, '$')) {
                $property = substr($value, 1);
                $resolved[$key] = data_get($record, $property);
            } else {
                $resolved[$key] = $value;
            }
        }

        return $resolved;
    }

    public function render(object $record): string
    {
        if (!$this->shouldShow($record)) {
            return '';
        }

        $disabled = $this->isDisabled($record) ? 'disabled' : '';
        $icon = $this->icon ? sprintf('<flux:icon name="%s" class="w-4 h-4" />', $this->icon) : '';
        $content = $icon ? ($icon . ' ' . htmlspecialchars($this->label)) : htmlspecialchars($this->label);

        $attributes = $disabled;

        if ($this->route) {
            $params = $this->resolveRouteParams($record);
            $href = route($this->route, $params);
            $attributes .= sprintf(' href="%s"', htmlspecialchars($href));
        } elseif ($this->url) {
            $attributes .= sprintf(' href="%s"', htmlspecialchars($this->url));
        } elseif ($this->wireAction) {
            $params = $this->resolveWireParams($record);
            $wireCall = $this->wireAction;
            if (!empty($params)) {
                $wireCall .= '(' . implode(', ', array_map(fn ($p) => is_string($p) ? "'{$p}'" : $p, $params)) . ')';
            }

            $attributes .= sprintf(' wire:click="%s"', htmlspecialchars($wireCall));

            if ($this->confirm) {
                $attributes .= sprintf(' wire:confirm="%s"', htmlspecialchars($this->confirm));
            }
        }

        return sprintf('<flux:menu.item %s>%s</flux:menu.item>', $attributes, $content);
    }
}
