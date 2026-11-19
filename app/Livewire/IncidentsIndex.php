<?php

namespace App\Livewire;

use App\Monitoring\MonitoringRepository;
use Illuminate\Database\Query\Builder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;

#[Layout('layouts.signal')]
#[Title('Incidents')]
class IncidentsIndex extends RecordsIndex
{
    #[Url(history: true)]
    public string $severity = 'all';

    public function clearFilters(): void
    {
        $this->reset('severity');
        parent::clearFilters();
    }

    protected function query(array $ids): Builder
    {
        return app(MonitoringRepository::class)->incidents($ids);
    }

    protected function filters(): array
    {
        return [
            ['property' => 'severity', 'column' => 'i.severity', 'label' => 'Severity', 'options' => [
                'critical' => 'Critical', 'warning' => 'Warning', 'info' => 'Info',
            ]],
            ['property' => 'status', 'column' => 'i.status', 'label' => 'Status', 'options' => [
                'open' => 'Open', 'investigating' => 'Investigating', 'monitoring' => 'Monitoring', 'resolved' => 'Resolved',
            ]],
        ];
    }

    protected function kind(): string
    {
        return 'incidents';
    }

    protected function searchColumns(): array
    {
        return ['i.title', 's.name'];
    }

    protected function idColumn(): string
    {
        return 'i.id';
    }

    protected function dateColumn(): string
    {
        return 'i.opened_at';
    }
}
