@php
    $classes = [];

    // Text transformation classes (handled by Tailwind CSS)
    if ($uppercase) $classes[] = 'uppercase';
    if ($lowercase) $classes[] = 'lowercase';
    if ($capitalize) $classes[] = 'capitalize';

    // Wrapping classes
    if ($wrap) {
        $classes[] = 'whitespace-normal break-words';
    } else {
        $classes[] = 'whitespace-nowrap';
    }

    // Character truncation with CSS (only if not using word truncation)
    if ($limit && !$words && !$wrap) {
        $classes[] = 'truncate';
    }

    $classString = implode(' ', $classes);
@endphp

@php
    // Smart content type detection
    $isEmail = filter_var($formatted, FILTER_VALIDATE_EMAIL);
    $isUrl = filter_var($formatted, FILTER_VALIDATE_URL);
@endphp

@if($copyable && $formatted)
    <div class="flex items-center gap-2">
        @if($isEmail)
            <flux:link id="copy_{{ $copyId }}" href="mailto:{{ $formatted }}" class="{{ $classString }}">
                {{ $formatted }}
            </flux:link>
        @elseif($isUrl)
            <flux:link id="copy_{{ $copyId }}" href="{{ $formatted }}" target="_blank" class="{{ $classString }}">
                {{ $formatted }}
            </flux:link>
        @else
            <flux:text id="copy_{{ $copyId }}" class="{{ $classString }}">
                {{ $formatted }}
            </flux:text>
        @endif
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
    @if($isEmail)
        <flux:link href="mailto:{{ $formatted }}" class="{{ $classString }}">
            {{ $formatted }}
        </flux:link>
    @elseif($isUrl)
        <flux:link href="{{ $formatted }}" target="_blank" class="{{ $classString }}">
            {{ $formatted }}
        </flux:link>
    @else
        <flux:text class="{{ $classString }}">
            {{ $formatted }}
        </flux:text>
    @endif
@endif