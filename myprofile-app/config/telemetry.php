<?php

return [
    'token' => env('TELEMETRY_TOKEN', ''),
    'ttl'   => (int) env('TELEMETRY_TTL', 180),
];
