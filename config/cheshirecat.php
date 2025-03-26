<?php
return [
    // The base URI for the Cheshire Cat API
    'base_uri' => env('CHESHIRE_CAT_BASE_URI', 'https://api.cheshirecat.ai'),
    // The WebSocket base URI for the Cheshire Cat API
    'ws_base_uri' => env('CHESHIRE_CAT_WS_BASE_URI', 'wss://ws.cheshirecat.ai'), // Nuovo parametro
    // The API key for authenticating with the Cheshire Cat API
    'api_key' => env('CHESHIRE_CAT_API_KEY'),
];

