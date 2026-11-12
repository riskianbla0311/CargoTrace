<?php
return [
    'delay_low_hours' => (int) env('CARGOTRACE_DELAY_LOW_HOURS', 6),
    'delay_medium_hours' => (int) env('CARGOTRACE_DELAY_MEDIUM_HOURS', 24),
    'openweather_api_key' => env('OPENWEATHER_API_KEY'),
];
