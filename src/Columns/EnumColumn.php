<?php

namespace Idkwhoami\FluxTables\Columns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;
use BackedEnum;
use UnitEnum;

class EnumColumn extends Column
{
    protected array $badges = [];
    protected string $defaultColor = 'zinc';
    protected string $defaultVariant = 'outline';
    protected string $defaultSize = 'sm';
    protected bool $withIcon = false;

    public function badges(array $badges): static
    {
        $this->badges = $badges;
        return $this;
    }

    public function badge(string $value, array $config): static
    {
        $this->badges[$value] = $config;
        return $this;
    }

    public function defaultColor(string $color): static
    {
        $this->defaultColor = $color;
        return $this;
    }

    public function defaultVariant(string $variant): static
    {
        $this->defaultVariant = $variant;
        return $this;
    }

    public function defaultSize(string $size): static
    {
        $this->defaultSize = $size;
        return $this;
    }

    public function withIcon(bool $withIcon = true): static
    {
        $this->withIcon = $withIcon;
        return $this;
    }

    public function applySearch(Builder $query, string $search): void
    {
        if (!$this->isSearchable()) {
            return;
        }

        $column = $this->getSearchColumn($query);
        $search = strtolower(trim($search));

        // Search by enum value
        $query->orWhere($column, 'ILIKE', "%{$search}%");

        // Search by badge labels
        foreach ($this->badges as $value => $config) {
            $label = $config['label'] ?? $value;
            if (str_contains(strtolower($label), $search)) {
                $query->orWhere($column, $value);
            }
        }
    }

    protected function getBadgeConfig(mixed $value): array
    {
        $originalValue = $value;

        // Handle enum instances
        if ($value instanceof BackedEnum) {
            $value = $value->value;
        } elseif ($value instanceof UnitEnum) {
            $value = $value->name;
        }

        $stringValue = (string) $value;

        // Check if the original enum instance has the table badge configuration trait
        if ($originalValue instanceof \UnitEnum && method_exists($originalValue, 'getTableBadgeConfig')) {
            $traitConfig = $originalValue->getTableBadgeConfig();
            // Merge trait config with manual overrides (manual takes precedence)
            $config = array_merge($traitConfig, $this->badges[$stringValue] ?? []);
        } else {
            // Get badge configuration for this value
            $config = $this->badges[$stringValue] ?? [];
        }

        return [
            'label' => $config['label'] ?? str($stringValue)->title()->replace('_', ' ')->toString(),
            'color' => $config['color'] ?? $this->defaultColor,
            'variant' => $config['variant'] ?? $this->defaultVariant,
            'size' => $config['size'] ?? $this->defaultSize,
            'icon' => $config['icon'] ?? null,
        ];
    }

    public function render(object $record): View
    {
        $value = $this->getValue($record);
        $config = is_null($value) ? null : $this->getBadgeConfig($value);

        return view('flux-tables::columns.enum', [
            'value' => $value,
            'config' => $config,
        ]);
    }
}
