<?php

return [
    'host' => env('ELASTICSEARCH_HOST'),
    'api_key' => env('ELASTICSEARCH_API_KEY'),
    'index_prefix' => env('ELASTICSEARCH_INDEX_PREFIX', 'apisense'),
    'timeout' => env('ELASTICSEARCH_TIMEOUT', 30),
];

