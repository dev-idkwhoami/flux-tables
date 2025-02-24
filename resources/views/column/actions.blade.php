@props([
    'actions' => [],
    'value',
])
@php
    /** @var \Idkwhoami\FluxTables\Abstracts\Action\Action $action */
    $linkActions = array_filter($actions, fn(\Idkwhoami\FluxTables\Abstracts\Action\Action $action) => $action->isLink());
    $dropdownActions = array_filter($actions, fn(\Idkwhoami\FluxTables\Abstracts\Action\Action $action) => !$action->isLink() && $action->isVisible($value))
@endphp
<div class="flex justify-end space-x-2">
    @foreach($linkActions as $action)
        @if($action->isVisible($value))
            {!! $action->render($value?->id) !!}
        @endif
    @endforeach

    @if(!empty($dropdownActions))
        <flux:dropdown>
            <flux:button square variant="ghost" icon="ellipsis"></flux:button>

            <flux:menu>
                @foreach($dropdownActions as $action)
                    {!! $action->render($value?->id) !!}
                @endforeach
            </flux:menu>
        </flux:dropdown>
    @endif
</div>
