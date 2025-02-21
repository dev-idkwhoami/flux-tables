@props([
    'actions' => [],
    'value',
])
@php
    /** @var \Idkwhoami\FluxTables\Abstracts\Action\Action $action */
@endphp
<div class="flex justify-between space-x-2">
    @foreach($actions as $action)
        @if($action->isVisible($value))
            {!! $action->render($value?->id) !!}
        @endif
    @endforeach
</div>
