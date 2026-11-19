<?php

namespace App\Livewire;

use App\Monitoring\MonitoringAccess;
use App\Monitoring\MonitoringRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.signal')]
#[Title('Infrastructure Overview')]
class InfrastructureOverview extends Component
{
    #[Url(as: 'env', history: true)]
    public string $environment = 'all';

    #[Url(history: true)]
    public string $period = '24';

    #[Url(history: true)]
    public string $service = '';

    #[Locked]
    public array $environmentOptions = [];

    #[Locked]
    public array $snapshot = [];

    #[Locked]
    public bool $unavailable = false;

    #[Locked]
    public ?string $snapshotScope = null;

    public function mount(): void
    {
        if (! request()->has('period')) {
            $this->period = (string) config('signal.default_period');
        }
        $this->loadSnapshot();
    }

    public function hydrate(): void
    {
        $this->loadSnapshot();
    }

    public function updatedEnvironment(): void
    {
        $this->service = '';
        $this->refresh();
    }

    public function updatedPeriod(): void
    {
        $this->refresh();
    }

    public function openService(string $id): void
    {
        $this->refresh();
        $this->service = $id;
    }

    public function closeService(): void
    {
        $this->service = '';
    }

    public function refresh(): void
    {
        $this->loadSnapshot();
        $this->dispatch('signal-chart', series: $this->snapshot['chart'] ?? null);
    }

    public function render(): View
    {
        $access = app(MonitoringAccess::class);
        $services = collect($this->snapshot['services'] ?? []);
        $selectedService = $services->first(fn (array $service): bool => (string) $service['id'] === $this->service);

        return view('livewire.infrastructure-overview', [
            'demo' => $access->demo(),
            'selectedService' => $selectedService,
        ]);
    }

    private function loadSnapshot(): void
    {
        $access = app(MonitoringAccess::class);
        $this->period = in_array((int) $this->period, config('signal.periods'), true)
            ? (string) (int) $this->period
            : (string) config('signal.default_period');
        $scope = null;

        try {
            $environments = $access->environments();
            $this->environment = $access->selection($environments, $this->environment);
            $this->environmentOptions = $environments->map(fn (object $item): array => (array) $item)->all();
            $ids = $access->ids($environments, $this->environment);
            $scope = $this->period.':'.implode(',', $ids);
            $this->snapshot = app(MonitoringRepository::class)->overview($ids, (int) $this->period);
            $this->snapshotScope = $scope;
            $this->unavailable = false;
        } catch (QueryException $exception) {
            report($exception);
            if ($scope === null) {
                $this->environmentOptions = [];
            }
            if ($scope === null || $scope !== $this->snapshotScope) {
                $this->snapshot = [];
                $this->snapshotScope = null;
            }
            $this->unavailable = true;
        }
    }
}
