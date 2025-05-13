<?php

namespace Idkwhoami\FluxTables\Traits;

use Idkwhoami\FluxTables\Abstracts\Action\DirectAction;
use Idkwhoami\FluxTables\Abstracts\Action\ModalAction;
use Idkwhoami\FluxTables\Abstracts\Column\Column;
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
                $action->getOperation()
                    ->modifyQuery($query);
            }
        }
    }

    final public function callAction(mixed $id, string $operationId): void
    {
        $directActions = array_merge(
            ...array_map(
                fn (ActionColumn $column) => $column->getActionsByClass(DirectAction::class),
                array_filter(
                    $this->table->getColumns(),
                    fn (
                        Column $column
                    ) => $column instanceof ActionColumn && !empty($column->getActionsByClass(DirectAction::class))
                )
            )
        );

        /** @var DirectAction $action */
        foreach ($directActions as $action) {
            if ($action->getOperationId() === $operationId) {
                $action->getOperation()->handle($this->table, $id);
            }
        }

    }

}
