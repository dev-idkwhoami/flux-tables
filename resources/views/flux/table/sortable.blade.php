@props([
    'direction' => null,
    'sorted' => false,
])

@php
    $classes = Flux::classes()
        ->add('group/sortable flex items-center gap-1 -my-1 -ml-2 -mr-2 px-2 py-1 ')
        ->add('group-[]/right-align:flex-row-reverse group-[]/right-align:-mr-2 group-[]/right-align:-ml-8')
        ;
@endphp

<button type="button" {{ $attributes->class($classes) }} data-flux-table-sortable>
    {{ $slot }}

    <div class="rounded ml-2 text-zinc-400 group-hover/sortable:text-zinc-800 dark:group-hover/sortable:text-white">
        @if ($sorted)
            @if ($direction === 'asc')
                <flux:icon.arrow-up-narrow-wide variant="micro"/>
            @elseif ($direction === 'desc')
                <flux:icon.arrow-down-wide-narrow variant="micro"/>
            @else
                <flux:icon.arrow-up-down variant="micro"/>
            @endif
        @else
            <div class="opacity-0 group-hover/sortable:opacity-100">
                <flux:icon.arrow-up-down variant="micro"/>
            </div>
        @endif
    </div>
</button>
