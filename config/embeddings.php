<?php

return [
    'provider' => env('EMBED_PROVIDER', 'huggingface'), // 'openai' o 'huggingface'
    'dim'      => (int) env('EMBED_DIM', 1536),
];