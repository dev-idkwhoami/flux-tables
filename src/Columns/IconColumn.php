<?php

namespace Idkwhoami\FluxTables\Columns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;

class IconColumn extends Column
{
    protected array $icons = [];
    protected string $defaultIcon = 'question-mark-circle';
    protected string $defaultColor = 'gray';
    protected string $defaultSize = 'md';
    protected bool $withTooltip = false;
    protected array $tooltips = [];

    public function icons(array $icons): static
    {
        $this->icons = $icons;
        return $this;
    }

    public function icon(string $value, string $icon, array $config = []): static
    {
        $this->icons[$value] = array_merge(['icon' => $icon], $config);
        return $this;
    }

    public function defaultIcon(string $icon): static
    {
        $this->defaultIcon = $icon;
        return $this;
    }

    public function defaultColor(string $color): static
    {
        $this->defaultColor = $color;
        return $this;
    }

    public function defaultSize(string $size): static
    {
        $this->defaultSize = $size;
        return $this;
    }

    public function withTooltips(array $tooltips = []): static
    {
        $this->withTooltip = true;
        $this->tooltips = $tooltips;
        return $this;
    }

    public function tooltip(string $value, string $tooltip): static
    {
        $this->tooltips[$value] = $tooltip;
        $this->withTooltip = true;
        return $this;
    }

    public function applySearch(Builder $query, string $search): void
    {
        if (!$this->isSearchable()) {
            return;
        }

        $column = $this->getSearchColumn($query);
        $search = strtolower(trim($search));

        // Search by value
        $query->orWhere($column, 'ILIKE', "%{$search}%");

        // Search by tooltip labels
        foreach ($this->tooltips as $value => $tooltip) {
            if (str_contains(strtolower($tooltip), $search)) {
                $query->orWhere($column, $value);
            }
        }
    }

    protected function getIconConfig(mixed $value): array
    {
        // Convert enum instances to their value
        if ($value instanceof \BackedEnum) {
            $value = $value->value;
        } elseif ($value instanceof \UnitEnum) {
            $value = $value->name;
        }

        $stringValue = (string) $value;

        // Get icon configuration for this value
        $config = $this->icons[$stringValue] ?? [];

        return [
            'icon' => $config['icon'] ?? $this->defaultIcon,
            'color' => $config['color'] ?? $this->defaultColor,
            'size' => $config['size'] ?? $this->defaultSize,
            'tooltip' => $this->withTooltip ? ($this->tooltips[$stringValue] ?? $stringValue) : null,
        ];
    }

    public function render(object $record): View
    {
        $value = $this->getValue($record);

        if (is_null($value)) {
            return view('flux-tables::columns.icon', [
                'config' => [
                    'icon' => $this->defaultIcon,
                    'color' => $this->defaultColor,
                    'size' => $this->defaultSize,
                    'tooltip' => null,
                ],
                'value' => null,
            ]);
        }

        $config = $this->getIconConfig($value);

        return view('flux-tables::columns.icon', [
            'config' => $config,
            'value' => $value,
        ]);
    }
}
