<?php

$environments = ['production' => 'Production', 'staging' => 'Staging'];
$services = ['api-gateway' => 'API Gateway', 'auth-service' => 'Auth Service', 'db-primary' => 'Primary Database'];
$severities = ['critical' => 'Critical', 'warning' => 'Warning', 'info' => 'Info'];

return [
    'modal' => [
        'pattern' => 'Modal',
        'prompt_field' => 'title',
        'title' => 'Report an incident',
        'description' => 'Capture the essentials for an incident report without leaving the page.',
        'icon' => 'triangle-alert',
        'ideal' => '1–5 fields',
        'maximum' => '6 fields',
        'sections' => [
            [
                'title' => 'Incident details',
                'fields' => [
                    'title' => [
                        'label' => 'Incident title', 'type' => 'text', 'rules' => 'required|string|max:100',
                        'sample' => 'Intermittent gateway errors', 'placeholder' => 'e.g. Intermittent gateway errors',
                    ],
                    'service' => [
                        'label' => 'Affected service', 'type' => 'select', 'rules' => 'required|in:api-gateway,auth-service,db-primary',
                        'sample' => 'api-gateway', 'options' => $services,
                    ],
                    'severity' => [
                        'label' => 'Severity', 'type' => 'select', 'rules' => 'required|in:critical,warning,info',
                        'sample' => 'warning', 'options' => $severities,
                    ],
                    'summary' => [
                        'label' => 'Summary', 'type' => 'textarea', 'rules' => 'nullable|string|max:500',
                        'sample' => 'Some requests returned HTTP 502 after the latest release.',
                        'placeholder' => 'What happened and who was affected?',
                    ],
                ],
            ],
        ],
    ],
    'drawer' => [
        'pattern' => 'Side Drawer',
        'prompt_field' => 'name',
        'title' => 'Configure a service',
        'description' => 'Edit service details and health checks beside the current page.',
        'icon' => 'boxes',
        'ideal' => '5–10 fields',
        'maximum' => '12 fields',
        'sections' => [
            [
                'title' => 'Service details',
                'fields' => [
                    'name' => [
                        'label' => 'Service name', 'type' => 'text', 'rules' => 'required|string|max:80', 'sample' => 'Gateway API',
                    ],
                    'environment' => [
                        'label' => 'Environment', 'type' => 'select', 'rules' => 'required|in:production,staging',
                        'options' => $environments, 'sample' => 'staging',
                    ],
                    'owner' => [
                        'label' => 'Owner email', 'type' => 'email', 'rules' => 'required|email|max:254', 'sample' => 'sre@example.com',
                    ],
                    'description' => [
                        'label' => 'Description', 'type' => 'textarea', 'rules' => 'nullable|string|max:300',
                        'sample' => 'Routes incoming requests to internal services.',
                    ],
                ],
            ],
            [
                'title' => 'Health check',
                'fields' => [
                    'endpoint' => [
                        'label' => 'Health check URL', 'type' => 'url', 'rules' => 'required|url:http,https|max:255',
                        'sample' => 'https://example.com/health',
                    ],
                    'interval' => [
                        'label' => 'Check interval (seconds)', 'type' => 'number', 'rules' => 'required|integer|min:10|max:3600',
                        'sample' => 60,
                    ],
                    'timeout' => [
                        'label' => 'Timeout (seconds)', 'type' => 'number', 'rules' => 'required|integer|min:1|max:120|lt:values.interval',
                        'sample' => 5, 'hint' => 'Use a value below the check interval.',
                    ],
                    'checks' => [
                        'label' => 'Health checks', 'type' => 'select', 'rules' => 'required|in:enabled,paused',
                        'options' => ['enabled' => 'Enabled', 'paused' => 'Paused'], 'sample' => 'enabled',
                    ],
                ],
            ],
        ],
    ],
    'dedicated' => [
        'pattern' => 'Dedicated Page',
        'prompt_field' => 'name',
        'title' => 'Create a monitoring policy',
        'description' => 'Set up monitoring across four sections with room to review every setting.',
        'icon' => 'gauge',
        'ideal' => '10–20 fields',
        'maximum' => '20+ with sections',
        'sections' => [
            [
                'title' => 'Policy details',
                'fields' => [
                    'name' => [
                        'label' => 'Policy name', 'type' => 'text', 'rules' => 'required|string|max:100', 'sample' => 'Gateway availability',
                    ],
                    'service' => [
                        'label' => 'Service', 'type' => 'select', 'rules' => 'required|in:api-gateway,auth-service,db-primary',
                        'options' => $services, 'sample' => 'api-gateway',
                    ],
                    'environment' => [
                        'label' => 'Environment', 'type' => 'select', 'rules' => 'required|in:production,staging',
                        'options' => $environments, 'sample' => 'production',
                    ],
                    'description' => [
                        'label' => 'Description', 'type' => 'textarea', 'rules' => 'nullable|string|max:500',
                        'sample' => 'Check the public gateway and notify the on-call team if it becomes unavailable.',
                    ],
                ],
            ],
            [
                'title' => 'Health check',
                'fields' => [
                    'endpoint' => [
                        'label' => 'Health check URL', 'type' => 'url', 'rules' => 'required|url:http,https|max:255',
                        'sample' => 'https://example.com/health',
                    ],
                    'method' => [
                        'label' => 'HTTP method', 'type' => 'select', 'rules' => 'required|in:GET,HEAD',
                        'options' => ['GET' => 'GET', 'HEAD' => 'HEAD'], 'sample' => 'GET',
                    ],
                    'interval' => [
                        'label' => 'Check interval (seconds)', 'type' => 'number', 'rules' => 'required|integer|min:10|max:3600',
                        'sample' => 60,
                    ],
                    'timeout' => [
                        'label' => 'Timeout (seconds)', 'type' => 'number', 'rules' => 'required|integer|min:1|max:120|lt:values.interval',
                        'sample' => 5, 'hint' => 'Use a value below the check interval.',
                    ],
                ],
            ],
            [
                'title' => 'Alert thresholds',
                'fields' => [
                    'failures' => [
                        'label' => 'Consecutive failures', 'type' => 'number', 'rules' => 'required|integer|min:1|max:20', 'sample' => 3,
                        'hint' => 'Number of failed checks needed to raise an alert.',
                    ],
                    'recoveries' => [
                        'label' => 'Consecutive recoveries', 'type' => 'number', 'rules' => 'required|integer|min:1|max:20', 'sample' => 2,
                        'hint' => 'Number of successful checks needed to clear an alert.',
                    ],
                    'latency' => [
                        'label' => 'Latency limit (ms)', 'type' => 'number', 'rules' => 'required|integer|min:1|max:60000', 'sample' => 800,
                    ],
                    'window' => [
                        'label' => 'Evaluation window (minutes)', 'type' => 'number', 'rules' => 'required|integer|min:1|max:120',
                        'sample' => 10,
                    ],
                ],
            ],
            [
                'title' => 'Notifications',
                'fields' => [
                    'email' => [
                        'label' => 'On-call email', 'type' => 'email', 'rules' => 'required|email|max:254', 'sample' => 'on-call@example.com',
                    ],
                    'severity' => [
                        'label' => 'Minimum alert severity', 'type' => 'select', 'rules' => 'required|in:critical,warning,info',
                        'options' => $severities, 'sample' => 'warning',
                    ],
                    'reminder' => [
                        'label' => 'Reminder interval (minutes)', 'type' => 'number', 'rules' => 'nullable|integer|min:5|max:1440',
                        'sample' => 30, 'hint' => 'Leave blank to disable repeat alerts.',
                    ],
                    'recovery_notice' => [
                        'label' => 'Recovery notifications', 'type' => 'select', 'rules' => 'required|in:enabled,disabled',
                        'options' => ['enabled' => 'Enabled', 'disabled' => 'Disabled'], 'sample' => 'enabled',
                    ],
                ],
            ],
        ],
    ],
];
