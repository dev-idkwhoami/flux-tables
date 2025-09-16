<?php

namespace Idkwhoami\FluxTables\Columns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;

class JsonColumn extends Column
{
    protected string $path;
    protected bool $asText = false;
    protected bool $asNumeric = false;
    protected bool $asBoolean = false;
    protected mixed $defaultValue = null;
    protected bool $pretty = false;
    protected array $searchablePaths = [];

    public function path(string $path): static
    {
        $this->path = $path;
        return $this;
    }

    public function asText(bool $asText = true): static
    {
        $this->asText = $asText;
        $this->asNumeric = false;
        $this->asBoolean = false;
        return $this;
    }

    public function asNumeric(bool $asNumeric = true): static
    {
        $this->asNumeric = $asNumeric;
        $this->asText = false;
        $this->asBoolean = false;
        return $this;
    }

    public function asBoolean(bool $asBoolean = true): static
    {
        $this->asBoolean = $asBoolean;
        $this->asText = false;
        $this->asNumeric = false;
        return $this;
    }

    public function default(mixed $value): static
    {
        $this->defaultValue = $value;
        return $this;
    }

    public function pretty(bool $pretty = true): static
    {
        $this->pretty = $pretty;
        return $this;
    }

    public function searchablePaths(array $paths): static
    {
        $this->searchablePaths = $paths;
        return $this;
    }

    public function addSearchablePath(string $path): static
    {
        $this->searchablePaths[] = $path;
        return $this;
    }

    public function applySort(Builder $query, string $direction): void
    {
        if (!$this->isSortable()) {
            return;
        }

        $column = $this->getSortColumn($query);
        $path = $this->path ?? '';

        // Use PostgreSQL JSON operators for sorting
        if ($this->asText) {
            $query->orderByRaw("({$column}->>{$this->escapeJsonPath($path)}) {$direction}");
        } elseif ($this->asNumeric) {
            $query->orderByRaw("(({$column}->>{$this->escapeJsonPath($path)})::numeric) {$direction}");
        } elseif ($this->asBoolean) {
            $query->orderByRaw("(({$column}->>{$this->escapeJsonPath($path)})::boolean) {$direction}");
        } else {
            // Default to JSON path ordering
            $query->orderByRaw("({$column}->{$this->escapeJsonPath($path)}) {$direction}");
        }
    }

    public function applySearch(Builder $query, string $search): void
    {
        if (!$this->isSearchable()) {
            return;
        }

        $column = $this->getSearchColumn($query);
        $search = trim($search);

        $query->orWhere(function (Builder $subQuery) use ($column, $search) {
            // Search in the main path
            if (isset($this->path)) {
                $this->addJsonSearchCondition($subQuery, $column, $this->path, $search);
            }

            // Search in additional searchable paths
            foreach ($this->searchablePaths as $path) {
                $this->addJsonSearchCondition($subQuery, $column, $path, $search);
            }
        });
    }

    protected function addJsonSearchCondition(Builder $query, string $column, string $path, string $search): void
    {
        $escapedPath = $this->escapeJsonPath($path);

        if ($this->asText) {
            $query->orWhere($column . '->>' . $escapedPath, 'ILIKE', "%{$search}%");
        } elseif ($this->asNumeric && is_numeric($search)) {
            $query->orWhereRaw("({$column}->>{$escapedPath})::numeric = ?", [(float) $search]);
        } elseif ($this->asBoolean) {
            $boolValue = $this->parseBooleanSearch($search);
            if ($boolValue !== null) {
                $query->orWhereRaw("({$column}->>{$escapedPath})::boolean = ?", [$boolValue]);
            }
        } else {
            // Default JSON search - try both text and exact match
            $query->orWhere($column . '->>' . $escapedPath, 'ILIKE', "%{$search}%")
                  ->orWhereJsonContains($column . '->' . $escapedPath, $search);
        }
    }

    protected function parseBooleanSearch(string $search): ?bool
    {
        $search = strtolower(trim($search));

        if (in_array($search, ['true', '1', 'yes', 'on'])) {
            return true;
        }

        if (in_array($search, ['false', '0', 'no', 'off'])) {
            return false;
        }

        return null;
    }

    protected function escapeJsonPath(string $path): string
    {
        // Escape path for PostgreSQL JSON operators
        return "'" . str_replace("'", "''", $path) . "'";
    }

    protected function extractJsonValue(mixed $record): mixed
    {
        $jsonData = $this->getValue($record);

        if (is_null($jsonData)) {
            return $this->defaultValue;
        }

        // If it's already decoded JSON
        if (is_array($jsonData) || is_object($jsonData)) {
            return $this->getNestedValue($jsonData, $this->path ?? '');
        }

        // If it's a JSON string, decode it
        if (is_string($jsonData)) {
            $decoded = json_decode($jsonData, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $this->getNestedValue($decoded, $this->path ?? '');
            }
        }

        return $this->defaultValue;
    }

    protected function getNestedValue(mixed $data, string $path): mixed
    {
        if (empty($path)) {
            return $data;
        }

        $keys = explode('.', $path);
        $current = $data;

        foreach ($keys as $key) {
            if (is_array($current) && array_key_exists($key, $current)) {
                $current = $current[$key];
            } elseif (is_object($current) && property_exists($current, $key)) {
                $current = $current->$key;
            } else {
                return $this->defaultValue;
            }
        }

        return $current;
    }

    protected function formatJsonValue(mixed $value): string
    {
        if (is_null($value)) {
            return '';
        }

        // Handle different type casting
        if ($this->asText) {
            return (string) $value;
        }

        if ($this->asNumeric && is_numeric($value)) {
            return number_format((float) $value, 2);
        }

        if ($this->asBoolean) {
            $boolValue = (bool) $value;
            return $boolValue ? 'true' : 'false';
        }

        // Default JSON formatting
        if (is_array($value) || is_object($value)) {
            return json_encode($value, $this->pretty ? JSON_PRETTY_PRINT : 0);
        }

        return (string) $value;
    }

    public function render(object $record): View
    {
        $original = $this->extractJsonValue($record);
        $formatted = $this->formatJsonValue($original);

        return view('flux-tables::columns.json', [
            'original' => $original,
            'formatted' => $formatted,
            'pretty' => $this->pretty,
        ]);
    }
}
