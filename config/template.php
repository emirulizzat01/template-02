<?php

return [
    'storage_key' => env('APP_STORAGE_KEY', 'template-02'),
    'description' => env('APP_DESCRIPTION', 'Infrastructure monitoring and reusable TALL components.'),
    'brand' => [
        'subtitle' => env('APP_BRAND_SUBTITLE', 'Dashboard'),
        'icon' => env('APP_BRAND_ICON', 'activity'),
    ],
    'modules' => [
        'monitoring' => (bool) env('MONITORING_ENABLED', true),
        'examples' => (bool) env('EXAMPLES_ENABLED', true),
    ],
    'search' => [
        'max_length' => 100,
        'min_length' => 2,
        'results_per_group' => max(1, min(20, (int) env('SEARCH_RESULTS_PER_GROUP', 4))),
    ],
    'navigation' => [
        'Overview' => [
            ['label' => 'Dashboard', 'search_label' => 'Infrastructure Overview', 'route' => 'dashboard', 'icon' => 'layout-dashboard', 'active' => 'dashboard'],
        ],
        'Infrastructure' => [
            ['label' => 'Service Status', 'route' => 'dashboard', 'fragment' => 'services', 'icon' => 'boxes', 'module' => 'monitoring'],
        ],
        'Operations' => [
            ['label' => 'Deployments', 'route' => 'deployments', 'icon' => 'git-branch', 'active' => 'deployments', 'module' => 'monitoring'],
            ['label' => 'Incidents', 'route' => 'incidents', 'icon' => 'triangle-alert', 'active' => 'incidents', 'module' => 'monitoring'],
        ],
        'Tools' => [
            ['label' => 'Form Examples', 'route' => 'form-examples.index', 'icon' => 'clipboard-list', 'active' => 'form-examples.*', 'module' => 'examples'],
        ],
    ],
];
