<div>
    @if($getState())
        <pre class="text-xs bg-gray-100 p-2 rounded">{{ json_encode($getState(), JSON_PRETTY_PRINT) }}</pre>
    @else
        <span class="text-gray-500">No metadata</span>
    @endif
</div>
