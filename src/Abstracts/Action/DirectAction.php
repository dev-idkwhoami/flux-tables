<?php

namespace Idkwhoami\FluxTables\Abstracts\Action;

use Idkwhoami\FluxTables\Abstracts\Table\Operation;
use Idkwhoami\FluxTables\Abstracts\Table\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

class DirectAction extends Action
{
    private ?Operation $handle = null;
    protected string $operation;

    public function getOperationId(): string
    {
        return $this->operation;
    }

    public function tableInitialized(Table $table): void
    {
        Operation::store($table->name, $this->handle);
        $this->operation = $this->handle->uniqueId($table->name);

        parent::tableInitialized($table);
    }

    public function operation(Operation $operation): static
    {
        $this->handle = $operation;
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
