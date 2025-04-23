<?php

namespace Idkwhoami\FluxTables\Concretes\Column;

use Idkwhoami\FluxTables\Abstracts\Column\PropertyColumn;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class ComponentColumn extends PropertyColumn
{
    protected string $component = '';

    public function getComponent(): string
    {
        return $this->component;
    }

    public function component(string $component): ComponentColumn
    {
        $this->component = $component;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function render(object $value): string|HtmlString|View|null
    {
        if (!$this->component) {
            throw new \Exception('Unable to render component column without a valid component');
        }

        if (!($value instanceof Model)) {
            throw new \Exception('Unable to render component column without a valid value');
        }

        return view('flux-tables::column.component', ['component' => $this->component, 'column' => $this, 'value' => $value]);
    }
}
