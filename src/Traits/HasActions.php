<?php

namespace Idkwhoami\FluxTables\Traits;

use Idkwhoami\FluxTables\Abstracts\Action\Action;
use Idkwhoami\FluxTables\Abstracts\Column\Column;
use Idkwhoami\FluxTables\Concretes\Column\ActionColumn;
use Idkwhoami\FluxTables\Contracts\TableAction;
use Illuminate\Contracts\Database\Query\Builder;

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
        $columns = array_filter($this->table->getColumns(), fn(Column $column) => $column instanceof ActionColumn);
        /** @var ActionColumn $column */
        foreach ($columns as $column) {
            /** @var Action $action */
            foreach ($column->getActions() as $action) {
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
        $action = base64_decode($action);
        (new $action)->handle($this->table, $id);
    }

}
