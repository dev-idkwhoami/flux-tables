<?php

namespace Idkwhoami\FluxTables\Abstracts\Action;

use Idkwhoami\FluxTables\Contracts\TableAction;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

class DirectAction extends Action
{
    protected string $action = '';

    public function getAction(): string
    {
        return $this->action;
    }

    public function action(string $action): static
    {
        $this->action = $action;
        return $this;
    }

    public function render(mixed $id): string|HtmlString|View|null
    {
        $action = (new $this->action());

        if (!($action instanceof TableAction)) {
            throw new \Exception('Unable to render direct action without a valid action');
        }

        return $action->render($this, $id);
    }

}
