<?php

namespace Idkwhoami\FluxTables\Columns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;

class BooleanColumn extends Column
{
    protected string $trueIcon = 'check-circle';
    protected string $falseIcon = 'x-circle';
    protected string $nullIcon = 'question-mark-circle';
    protected string $trueColor = 'text-green-500';
    protected string $falseColor = 'text-red-500';
    protected string $nullColor = 'text-gray-400';
    protected ?string $trueLabel = null;
    protected ?string $falseLabel = null;
    protected ?string $nullLabel = null;
    protected bool $showLabels = false;

    public function trueIcon(string $icon, string $color = 'text-green-500'): static
    {
        $this->trueIcon = $icon;
        $this->trueColor = $color;
        return $this;
    }

    public function falseIcon(string $icon, string $color = 'text-red-500'): static
    {
        $this->falseIcon = $icon;
        $this->falseColor = $color;
        return $this;
    }

    public function nullIcon(string $icon, string $color = 'text-gray-400'): static
    {
        $this->nullIcon = $icon;
        $this->nullColor = $color;
        return $this;
    }

    public function trueLabel(string $label): static
    {
        $this->trueLabel = $label;
        return $this;
    }

    public function falseLabel(string $label): static
    {
        $this->falseLabel = $label;
        return $this;
    }

    public function nullLabel(string $label): static
    {
        $this->nullLabel = $label;
        return $this;
    }

    public function showLabels(bool $show = true): static
    {
        $this->showLabels = $show;
        return $this;
    }

    public function labels(string $trueLabel, string $falseLabel, ?string $nullLabel = null): static
    {
        $this->trueLabel = $trueLabel;
        $this->falseLabel = $falseLabel;
        $this->nullLabel = $nullLabel;
        $this->showLabels = true;
        return $this;
    }

    public function applySearch(Builder $query, string $search): void
    {
        if (!$this->isSearchable()) {
            return;
        }

        $column = $this->getSearchColumn($query);
        $search = strtolower(trim($search));

        // Convert common boolean search terms
        $trueValues = ['true', '1', 'yes', 'on', 'active', 'enabled'];
        $falseValues = ['false', '0', 'no', 'off', 'inactive', 'disabled'];

        if (in_array($search, $trueValues)) {
            $query->orWhere($column, true);
        } elseif (in_array($search, $falseValues)) {
            $query->orWhere($column, false);
        } elseif ($search === 'null' || $search === 'none' || $search === '') {
            $query->orWhereNull($column);
        }

        // Also search by custom labels if set
        if ($this->trueLabel && str_contains(strtolower($this->trueLabel), $search)) {
            $query->orWhere($column, true);
        }
        if ($this->falseLabel && str_contains(strtolower($this->falseLabel), $search)) {
            $query->orWhere($column, false);
        }
        if ($this->nullLabel && str_contains(strtolower($this->nullLabel), $search)) {
            $query->orWhereNull($column);
        }
    }

    protected function getIconAndColor(mixed $value): array
    {
        if (is_null($value)) {
            return [$this->nullIcon, $this->nullColor, $this->nullLabel];
        }

        $boolValue = (bool) $value;

        if ($boolValue) {
            return [$this->trueIcon, $this->trueColor, $this->trueLabel];
        } else {
            return [$this->falseIcon, $this->falseColor, $this->falseLabel];
        }
    }

    public function render(object $record): View
    {
        $value = $this->getValue($record);
        [$icon, $color, $label] = $this->getIconAndColor($value);

        return view('flux-tables::columns.boolean', [
            'icon' => $icon,
            'label' => $label,
            'showLabels' => $this->showLabels,
            'value' => $value,
        ]);
    }
}
