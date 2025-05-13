<?php

namespace Idkwhoami\FluxTables\Abstracts\Action;

use Idkwhoami\FluxTables\Abstracts\Table\Operation;
use Idkwhoami\FluxTables\Abstracts\Table\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class DirectAction extends Action
{
    protected ?string $operationId = null;
    protected Operation $operation;

    public function tableInitialized(Table $table): void
    {
        $this->operationId = $this->operationId ?? Str::random(8);

        parent::tableInitialized($table);
    }

    public function getOperationId(): ?string
    {
        return $this->operationId;
    }

    public function getOperation(): Operation
    {
        return $this->operation;
    }

    public function operation(Operation $operation): static
    {
        $this->operation = $operation;
        return $this;
    }

    /**
     * @throws \Exception
     */
    public function render(mixed $id): string|HtmlString|View|null
    {
        $this->ensureVariantCompatibility();

        return $this->operation->render($this, $id) ?? '';
    }

}
