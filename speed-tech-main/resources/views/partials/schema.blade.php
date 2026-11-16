{{-- One JSON-LD block from a PHP array: @include('partials.schema', ['data' => [...]]). JSON_HEX_TAG keeps "</script>" in text harmless. --}}
<script type="application/ld+json">{!! json_encode(['@context' => 'https://schema.org'] + $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
