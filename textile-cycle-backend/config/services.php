<?php

return [
    'ai' => [
        'url' => env('AI_SERVICE_URL', 'http://localhost:8001'),
        'secret' => env('AI_SERVICE_SECRET', 'textilecycle_secret_key_2026'),
    ],
    'defect_ai' => [
        'driver' => env('DEFECT_AI_DRIVER', 'flask'),
    ],
    'flask_ai' => [
        'url' => env('FLASK_AI_URL', 'http://127.0.0.1:5000'),
    ],
    'depot_ai' => [
    'url' => env('DEPOT_AI_URL', 'http://127.0.0.1:8002'),
],
];
