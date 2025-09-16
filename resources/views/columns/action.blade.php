@if(empty($actions))
    <flux:text>—</flux:text>
@else
    <div class="flex items-center gap-2 {{ $alignmentClass }} {{ $stickyClass }}">
        @foreach($actions as $action)
            {!! $action !!}
        @endforeach
    </div>
@endif