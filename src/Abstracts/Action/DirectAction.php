<?php

namespace Idkwhoami\FluxTables\Abstracts\Action;

use Idkwhoami\FluxTables\Abstracts\Table\Operation;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

class DirectAction extends Action
{
    protected string $operation;

    public function getOperationId(): string
    {
        return $this->operation;
    }

    public function operation(Operation $operation): static
    {
        Operation::store($operation);
        $this->operation = $operation->uniqueId();
        return $this;
    }

    /**
     * @throws \Exception
     */
    public function render(mixed $id): string|HtmlString|View|null
    {
        $operation = Operation::get($this->operation);

        if (!$operation) {
            throw new \Exception('Unable to render direct action without a valid operation');
        }

        $operation->configureAction($this);
        $this->ensureVariantCompatibility();

        return $operation->render($this, $id);
    }

}
