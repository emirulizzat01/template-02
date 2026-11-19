<?php

namespace App\Livewire;

use App\Monitoring\MonitoringRepository;
use Illuminate\Database\Query\Builder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.signal')]
#[Title('Deployments')]
class DeploymentsIndex extends RecordsIndex
{
    protected function query(array $ids): Builder
    {
        return app(MonitoringRepository::class)->deployments($ids);
    }

    protected function filters(): array
    {
        return [
            ['property' => 'status', 'column' => 'd.status', 'label' => 'Status', 'options' => [
                'success' => 'Success', 'rolling' => 'Rolling', 'failed' => 'Failed',
            ]],
        ];
    }

    protected function kind(): string
    {
        return 'deployments';
    }

    protected function searchColumns(): array
    {
        return ['s.name', 'd.version', 'd.commit', 'd.actor'];
    }

    protected function idColumn(): string
    {
        return 'd.id';
    }

    protected function dateColumn(): string
    {
        return 'd.deployed_at';
    }
}
