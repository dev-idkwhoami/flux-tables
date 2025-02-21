<?php

namespace Idkwhoami\FluxTables\Concretes\Column;

use Idkwhoami\FluxTables\Abstracts\Action\Action;
use Idkwhoami\FluxTables\Abstracts\Column\Column;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class ActionColumn extends Column
{
    public function isSortable(): bool
    {
        return false;
    }

    public function isToggleable(): bool
    {
        return false;
    }

    protected bool $dropdown = false;
    /** @var array */
    protected array $actions = [];

    /**
     * @param  Action[]  $actions
     * @return $this
     */
    public function actions(array $actions = []): static
    {
        $this->actions = $actions;
        return $this;
    }

    /**
     * @return Action[]
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * @inheritDoc
     */
    public function render(object $value): string|HtmlString|View|null
    {
        if(!($value instanceof Model)) {
            throw new \Exception('Unable to render action column without a valid value');
        }

        $actions = array_filter($this->actions, fn(Action $action) => $action->hasAccess(Auth::user(), $value));

        return view('flux-tables::column.actions', compact(['actions', 'value']));
    }
}
