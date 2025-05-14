@props([
    /** @var \Illuminate\Database\Eloquent\Model */
    'model',
    /** @var \Idkwhoami\FluxTables\Concretes\Table\EloquentTable $table */
    'table',
    'toggledColumns'
])
<flux:table.row
        wire:loading.class="animate-pulse"
        :key="$model->getKey()">
    @foreach($table->getColumns() as /** @var \Idkwhoami\FluxTables\Abstracts\Column\Column $column */ $column)
        <flux:table.cell
                @class(['hidden' => in_array($column->getName(), $toggledColumns)])>
            {{ $column->render($model) }}
        </flux:table.cell>
    @endforeach
</flux:table.row>
