<?php

namespace Idkwhoami\FluxTables\Abstracts\Action;

use Closure;
use Idkwhoami\FluxTables\Contracts\HasContext;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\HtmlString;

class ModalAction extends Action implements HasContext
{
    protected string $modalClasses = 'w-full';
    protected ?string $modalVariant = 'default';
    protected ?string $modalPosition = null;
    protected bool $dismissible = false;
    protected string $component = '';

    protected ?Closure $modelQuery = null;
    protected ?Closure $componentData = null;

    public function modalUniqueName(mixed $id): string
    {
        return str($this->label)->lower()->snake()->append(
            '-',
            md5($this->component),
            '-',
            strval($id),
            '-modal'
        )->toString();
    }

    public function modelQuery(Closure $modelQuery): static
    {
        $this->modelQuery = $modelQuery;
        return $this;
    }

    public function component(string $component, Closure $componentData = null): static
    {
        $this->component = $component;
        $this->componentData = $componentData;
        return $this;
    }

    public function modalClasses(string $classes): static
    {
        $this->modalClasses = $classes;
        return $this;
    }

    public function dismissible(bool $dismissible = true): static
    {
        $this->dismissible = $dismissible;
        return $this;
    }

    public function modalFlyoutPosition(string $position): static
    {
        if (!in_array($position, ['left', 'right', 'top', 'bottom'])) {
            throw new \Exception('Flyout position must be left, right, top or bottom.');
        }

        $this->modalPosition = $position;
        return $this;
    }

    public function modalVariantFlyout(): static
    {
        $this->modalVariant = 'flyout';
        return $this;
    }

    public function modalVariantBare(): static
    {
        $this->modalVariant = 'bare';
        return $this;
    }

    public function getModel(mixed $id): ?Model
    {
        if (!is_null($id) && !is_null($this->modelQuery)) {
            Context::addIf($this->contextKey('model', $id), $this->modelQuery->call($this, $id, $this));
        }

        return Context::get($this->contextKey('model', $id));
    }

    public function getComponent(): string
    {
        return $this->component;
    }

    /**
     * @return array<string, mixed>
     * @throws \Exception
     */
    public function getComponentData(mixed $id): array
    {
        $modelOrId = $id;

        if (!is_null($this->modelQuery)) {
            $modelOrId = $this->getModel($id) ?? $id;
        }

        $data = $this->componentData?->call($this, $modelOrId, $this) ?? [];

        if (!is_array($data)) {
            throw new \Exception('Component data must return an array.');
        }

        if (isset($data['action']) || isset($data['id']) || (isset($data['model']) && $modelOrId instanceof Model)) {
            throw new \Exception('Component data cannot contain  \'action\', \'model\' or \'id\' keys.');
        }

        return $data;
    }

    /**
     * @throws \Exception
     */
    public function render(mixed $id): string|HtmlString|View|null
    {
        $this->ensureVariantCompatibility();

        return view('flux-tables::modal.index', [
            'action' => $this,
            'id' => $id,
            'modalClasses' => $this->modalClasses,
            'modalVariant' => $this->modalVariant,
            'modalPosition' => $this->modalPosition,
            'dismissible' => $this->dismissible
        ]);
    }

}
