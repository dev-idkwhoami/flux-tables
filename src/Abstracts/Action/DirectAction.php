<?php

namespace Idkwhoami\FluxTables\Abstracts\Action;

use Idkwhoami\FluxTables\Abstracts\Table\TableAction;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

class DirectAction extends Action
{
    protected TableAction|string|null $action = null;

    public function getActionable(): TableAction|string|null
    {
        $action = $this->action;

        if (is_string($action)) {
            $action = (new $this->action("table_action_$this->name"));
        }

        return $action;
    }

    public function getAction(): string
    {
        return is_string($this->action) ? $this->action : get_class($this->action);
    }

    public function action(string|TableAction $action): static
    {
        $this->action = $action;
        return $this;
    }

    /**
     * @throws \Exception
     */
    public function render(mixed $id): string|HtmlString|View|null
    {
        $action = $this->getActionable();

        if (!($action instanceof TableAction)) {
            throw new \Exception('Unable to render direct action without a valid action');
        }

        $action->configureAction($this);
        $this->ensureVariantCompatibility();

        return $action->render($this, $id);
    }

}
