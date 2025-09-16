@php
    $classes = [];

    // Text transformation classes
    if ($uppercase) $classes[] = 'uppercase';
    if ($lowercase) $classes[] = 'lowercase';
    if ($capitalize) $classes[] = 'capitalize';

    // Wrapping classes
    if ($wrap) {
        $classes[] = 'whitespace-normal';
    } else {
        $classes[] = 'whitespace-nowrap';
    }

    // Truncation classes
    if ($limit && !$wrap) {
        $classes[] = 'truncate';
    }

    $classString = implode(' ', $classes);
@endphp

@if($copyable && $formatted)
    <div class="flex items-center gap-2">
        <flux:text id="copy_{{ $copyId }}" class="{{ $classString }}">
            {!! $formatted !!}
        </flux:text>
        <flux:button
            onclick="navigator.clipboard.writeText(document.getElementById('copy_{{ $copyId }}').textContent)"
            variant="ghost"
            size="sm"
            icon="clipboard"
            title="Copy to clipboard"
        />
    </div>
@elseif(empty($formatted))
    <flux:text>—</flux:text>
@else
    <flux:text class="{{ $classString }}">
        {!! $formatted !!}
    </flux:text>
@endif