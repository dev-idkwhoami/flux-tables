<?php

namespace Idkwhoami\FluxTables\Traits;

trait HasTableBadgeConfiguration
{
    /**
     * Get the badge configuration for this enum value in tables.
     * Override this method in your enum to customize appearance.
     *
     * @return array{color?: string, variant?: string, size?: string, icon?: string, label?: string}
     */
    public function getTableBadgeConfig(): array
    {
        return [
            'color' => $this->getTableBadgeColor(),
            'variant' => $this->getTableBadgeVariant(),
            'size' => $this->getTableBadgeSize(),
            'icon' => $this->getTableBadgeIcon(),
            'label' => $this->getTableBadgeLabel(),
        ];
    }

    /**
     * Get the badge color for this enum value.
     * Override this method to customize colors.
     */
    public function getTableBadgeColor(): string
    {
        return match ($this) {
            default => 'zinc'
        };
    }

    /**
     * Get the badge variant for this enum value.
     * Available variants: solid, outline, soft
     */
    public function getTableBadgeVariant(): string
    {
        return 'outline';
    }

    /**
     * Get the badge size for this enum value.
     * Available sizes: xs, sm, md, lg
     */
    public function getTableBadgeSize(): string
    {
        return 'sm';
    }

    /**
     * Get the badge icon for this enum value.
     * Return null for no icon.
     */
    public function getTableBadgeIcon(): ?string
    {
        return null;
    }

    /**
     * Get the badge label for this enum value.
     * Defaults to a formatted version of the enum name/value.
     */
    public function getTableBadgeLabel(): string
    {
        if ($this instanceof \BackedEnum) {
            return str($this->value)->title()->replace('_', ' ')->toString();
        }

        return str($this->name)->title()->replace('_', ' ')->toString();
    }

    /**
     * Get all badge configurations for all cases of this enum.
     * Used by EnumColumn to build the badges array.
     */
    public static function getTableBadgeConfigurations(): array
    {
        $configurations = [];

        foreach (static::cases() as $case) {
            $key = $case instanceof \BackedEnum ? $case->value : $case->name;
            $configurations[$key] = $case->getTableBadgeConfig();
        }

        return $configurations;
    }
}
