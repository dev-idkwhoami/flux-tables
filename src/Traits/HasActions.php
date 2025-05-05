<?php

namespace Idkwhoami\FluxTables\Traits;

use Idkwhoami\FluxTables\Abstracts\Action\DirectAction;
use Idkwhoami\FluxTables\Abstracts\Action\ModalAction;
use Idkwhoami\FluxTables\Abstracts\Column\Column;
use Idkwhoami\FluxTables\Abstracts\Table\TableAction;
use Idkwhoami\FluxTables\Concretes\Column\ActionColumn;
use Illuminate\Contracts\Database\Eloquent\Builder;

trait HasActions
{
    /**
     * @return void
     * @throws \Exception
     */
    public function mountHasActions(): void
    {
        if (!property_exists($this, 'table')) {
            throw new \Exception(__CLASS__.' must have a table property');
        }
    }

    public function applyActions(Builder $query): void
    {
        $columns = array_filter($this->table->getColumns(), fn (Column $column) => $column instanceof ActionColumn);
        /** @var ActionColumn $column */
        foreach ($columns as $column) {
            $actions = array_filter($column->getActions(), fn ($tableAction) => !($tableAction instanceof ModalAction));
            /** @var DirectAction $action */
            foreach ($actions as $action) {
                /** @var TableAction $actionable */
                $actionable = new ($action->getAction());

                if (!($actionable instanceof TableAction)) {
                    continue;
                }

                $actionable->modifyQuery($query);
            }
        }
    }

    public function callAction(mixed $id, string $action): void
    {
        $action = new (base64_decode($action))();

        /** @var TableAction $action */
        if ($action instanceof TableAction) {
            $action->handle($this->table, $id);
        }
    }

}
