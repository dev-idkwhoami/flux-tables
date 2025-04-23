@props([
    'actions' => [],
    'value',
])
@php
    if (!($value instanceof \Illuminate\Database\Eloquent\Model)) {
        $type = get_class($value);
        throw new InvalidArgumentException("The supplied value needs to be of type '\Illuminate\Database\Eloquent\Model' but '$type' was given.");
    }

    /** @var \Idkwhoami\FluxTables\Abstracts\Action\Action $action */
    $linkActions = array_filter($actions, fn(\Idkwhoami\FluxTables\Abstracts\Action\Action $action) => $action->isLink());
    $dropdownActions = array_filter($actions, fn(\Idkwhoami\FluxTables\Abstracts\Action\Action $action) => !$action->isLink() && $action->shouldBeVisible($value))
@endphp
<div class="flex justify-end space-x-2">
    @foreach($linkActions as $action)
        @if($action->shouldBeVisible($value))
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
