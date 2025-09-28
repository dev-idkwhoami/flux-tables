<?php

namespace Idkwhoami\FluxTables\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;

class EnumFilter extends Filter
{
    protected ?string $enumClass = null;
    protected array $options = [];
    protected bool $multiple = true;
    protected ?string $displayMode = null; // 'checkbox', 'radio', 'select'

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->value = [];
    }

    /**
     * Set the enum class to use for options
     */
    public function enum(string $enumClass): static
    {
        if (enum_exists($enumClass)) {
            $this->enumClass = $enumClass;
            $this->options = $this->getEnumOptions($enumClass);
        }

        return $this;
    }

    /**
     * Set custom options array
     */
    public function options(array $options): static
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Allow multiple selections
     */
    public function multiple(bool $multiple = true): static
    {
        $this->multiple = $multiple;

        // Convert value format based on multiple setting
        if (!$multiple && is_array($this->value) && count($this->value) > 1) {
            $this->value = array_slice($this->value, 0, 1);
        } elseif ($multiple && !is_array($this->value)) {
            $this->value = $this->value ? [$this->value] : [];
        }

        return $this;
    }

    /**
     * Set display mode for the filter
     */
    public function displayMode(string $mode): static
    {
        $validModes = ['checkbox', 'radio', 'select'];
        if (in_array($mode, $validModes)) {
            $this->displayMode = $mode;

            // Auto-adjust multiple setting based on display mode
            if ($mode === 'radio' && $this->multiple) {
                $this->multiple(false);
            }
        }

        return $this;
    }

    /**
     * Set the selected values
     */
    public function selected(array|string $values): static
    {
        if (is_string($values)) {
            $this->value = $this->multiple ? [$values] : $values;
        } else {
            $this->value = $this->multiple ? $values : (array_slice($values, 0, 1)[0] ?? null);
        }

        return $this;
    }

    public function render(): View|string
    {
        $displayMode = $this->displayMode ?? $this->getDefaultDisplayMode();

        return view('flux-tables::filters.enum', [
            'filter' => $this,
            'options' => $this->options,
            'selectedValues' => $this->multiple ? $this->value : [$this->value],
            'multiple' => $this->multiple,
            'displayMode' => $displayMode,
        ]);
    }

    public function apply(Builder $query): Builder
    {
        if (!$this->hasValue()) {
            return $query;
        }

        $values = $this->multiple ? $this->value : [$this->value];
        $values = array_filter($values); // Remove empty values

        if (empty($values)) {
            return $query;
        }

        if (count($values) === 1) {
            return $query->where($this->column, '=', $values[0]);
        }

        return $query->whereIn($this->column, $values);
    }

    public function getValueDescription(): string
    {
        if (!$this->hasValue()) {
            return 'No options selected';
        }

        $values = $this->multiple ? $this->value : [$this->value];
        $values = array_filter($values);

        if (empty($values)) {
            return 'No options selected';
        }

        // Convert values to their labels
        $labels = [];
        foreach ($values as $value) {
            $labels[] = $this->options[$value] ?? $value;
        }

        if (count($labels) === 1) {
            return $labels[0];
        }

        if (count($labels) <= 3) {
            return implode(', ', $labels);
        }

        return implode(', ', array_slice($labels, 0, 2)) . ' and ' . (count($labels) - 2) . ' more';
    }

    public function validate(): bool
    {
        if (!$this->hasValue()) {
            return true; // Empty is valid
        }

        $values = $this->multiple ? $this->value : [$this->value];

        // Check if all values exist in options
        foreach ($values as $value) {
            if ($value !== null && $value !== '' && !array_key_exists($value, $this->options)) {
                return false;
            }
        }

        return true;
    }

    public function hasValue(): bool
    {
        if ($this->multiple) {
            return is_array($this->value) && !empty(array_filter($this->value));
        }

        return $this->value !== null && $this->value !== '';
    }

    /**
     * Get options from an enum class
     */
    protected function getEnumOptions(string $enumClass): array
    {
        if (!enum_exists($enumClass)) {
            return [];
        }

        $options = [];
        $cases = $enumClass::cases();

        foreach ($cases as $case) {
            // Try to use the value for backed enums, name for unit enums
            $key = method_exists($case, 'value') ? $case->value : $case->name;

            // Try to get a label - look for common methods
            $label = $case->name;
            if (method_exists($case, 'getLabel')) {
                $label = $case->getLabel();
            } elseif (method_exists($case, 'label')) {
                $label = $case->label();
            } elseif (method_exists($case, 'toString')) {
                $label = $case->toString();
            } else {
                // Convert case name to human readable
                $label = str($case->name)->title()->replace('_', ' ')->toString();
            }

            $options[$key] = $label;
        }

        return $options;
    }

    /**
     * Get the default display mode based on the number of options
     */
    protected function getDefaultDisplayMode(): string
    {
        if (!$this->multiple) {
            return count($this->options) <= 5 ? 'radio' : 'select';
        }

        return count($this->options) <= 8 ? 'checkbox' : 'select';
    }

    public function getEnumClass(): ?string
    {
        return $this->enumClass;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function isMultiple(): bool
    {
        return $this->multiple;
    }

    public function getDisplayMode(): ?string
    {
        return $this->displayMode;
    }

    /**
     * Add a single option
     */
    public function addOption(string $value, string $label): static
    {
        $this->options[$value] = $label;
        return $this;
    }

    /**
     * Remove an option
     */
    public function removeOption(string $value): static
    {
        unset($this->options[$value]);

        // Remove from current value if selected
        if ($this->multiple && is_array($this->value)) {
            $this->value = array_values(array_filter($this->value, fn ($v) => $v !== $value));
        } elseif (!$this->multiple && $this->value === $value) {
            $this->value = null;
        }

        return $this;
    }

    /**
     * Get configuration for the Livewire component
     */
    public function getComponentConfig(): array
    {
        return [
            'options' => $this->options,
            'multiple' => $this->multiple,
            'display_mode' => $this->displayMode ?? $this->getDefaultDisplayMode(),
        ];
    }
}
