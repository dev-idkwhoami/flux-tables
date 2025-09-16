@if(is_null($formatted) || $formatted === '')
    <flux:text>—</flux:text>
@elseif($pretty && (is_array($original) || is_object($original)))
    <pre class="text-xs whitespace-pre-wrap"><flux:text>{{ $formatted }}</flux:text></pre>
@elseif(is_array($original) || is_object($original))
    <code class="text-xs"><flux:text>{{ $formatted }}</flux:text></code>
@else
    <flux:text>{{ $formatted }}</flux:text>
@endif