@if(empty($values))
    <flux:text>—</flux:text>
@else
    <flux:text>
        @foreach($formattedValues as $index => $value)
            @if($clickable && isset($routeKeys[$index]))
                <a href="{{ route($routeName, [$routeKeys[$index]]) }}">{{ $value }}</a>
            @else
                {{ $value }}
            @endif
            @if(!$loop->last){{ $separator }}@endif
        @endforeach

        @if($hasMore)
            <span class="text-sm opacity-70">+{{ $remaining }} more</span>
        @endif

        @if($withCount)
            <span class="text-sm opacity-70">({{ $totalCount }}{{ $countSuffix }})</span>
        @endif
    </flux:text>
@endif