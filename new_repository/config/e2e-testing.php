<?php

return [

    /*
    |--------------------------------------------------------------------------
    | E2E Testing Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for E2E test automation system
    |
    */

    'report' => [
        'directory' => storage_path('test-reports'),
        'format' => env('TEST_REPORT_FORMAT', 'html'),
        'keep_history' => env('TEST_REPORT_KEEP_HISTORY', true),
        'max_reports' => env('TEST_REPORT_MAX_REPORTS', 10),
    ],

    'test_generation' => [
        'auto_generate' => env('E2E_AUTO_GENERATE', false),
        'default_type' => env('E2E_DEFAULT_TYPE', 'browser'),
        'skip_existing' => env('E2E_SKIP_EXISTING', true),
    ],

    'execution' => [
        'parallel' => env('E2E_PARALLEL', false),
        'timeout' => env('E2E_TIMEOUT', 60),
        'retry_failed' => env('E2E_RETRY_FAILED', false),
        'retry_count' => env('E2E_RETRY_COUNT', 3),
    ],

    'browser' => [
        'headless' => env('DUSK_HEADLESS', true),
        'no_sandbox' => env('DUSK_NO_SANDBOX', true),
        'disable_gpu' => env('DUSK_DISABLE_GPU', true),
        'window_size' => env('DUSK_WINDOW_SIZE', '1920,1080'),
    ],

    'notifications' => [
        'enabled' => env('E2E_NOTIFICATIONS', false),
        'channels' => [
            'slack' => env('E2E_NOTIFY_SLACK', false),
            'email' => env('E2E_NOTIFY_EMAIL', false),
        ],
    ],

];
