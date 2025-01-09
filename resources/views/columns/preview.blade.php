@props([
    'column',
    'row'
])
@php
    /**
     * @var $column \Idkwhoami\FluxTables\Columns\PreviewColumn
     */

    $supportedTypes = ['audio', 'video', 'image'];

    $useTruncatedFilename = $column->useTruncatedFilename();
    $value = $column->resolveValue($row);

    /** @var \Illuminate\Filesystem\FilesystemManager $disk */
    $disk = \Illuminate\Support\Facades\Storage::disk($column->getDisk() ?? 'local');
    $src = $disk->url($value);
    $mime = $disk->mimeType($value);

    $modalId = \Illuminate\Support\Facades\Hash::make($value);

    $displayValue = $useTruncatedFilename
        ? pathinfo($value, PATHINFO_BASENAME)
        : $value;

    $type = str($mime)->before('/')->value();

    if(!in_array($type, $supportedTypes)) {
        throw new Exception("Unsupported mime type \"{$mime}\". Preview column only supports " . collect($supportedTypes)->join(", "));
    }
@endphp
<flux:cell :align="$column->getAlignment()->asCellAlignment()">
    <flux:modal.trigger name="{{ $modalId }}">
        <flux:button icon="" size="xs" variant="ghost">
            {{ $displayValue }}
        </flux:button>
    </flux:modal.trigger>

    @teleport('body')
    <flux:modal class="max-w-[85svw] min-w-[60svw] max-h-[85svh] h-min w-min overflow-hidden" :dismissible="false"
                name="{{ $modalId }}">
        <div class="w-full h-full flex items-center justify-center box-border pt-6">
            {{ view('flux-tables::columns.preview.media', ['src' => $src, 'type' => $type]) }}
        </div>
    </flux:modal>
    @endteleport
</flux:cell>
