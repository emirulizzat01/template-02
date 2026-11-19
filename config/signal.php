<?php

return [
    'demo' => (bool) env('SIGNAL_DEMO', false),
    'stale_after_minutes' => max(1, (int) env('MONITORING_STALE_MINUTES', 5)),
    'poll_seconds' => max(10, (int) env('MONITORING_POLL_SECONDS', 45)),
    'recent_limit' => max(1, min(25, (int) env('MONITORING_RECENT_LIMIT', 5))),
    'per_page' => max(1, min(100, (int) env('MONITORING_PER_PAGE', 10))),
    'periods' => [6, 24, 72],
    'default_period' => 24,
    'active_incident_statuses' => ['open', 'investigating', 'monitoring'],
];
