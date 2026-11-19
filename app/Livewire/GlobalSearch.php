<?php

namespace App\Livewire;

use App\Monitoring\MonitoringAccess;
use App\Monitoring\MonitoringRepository;
use App\Support\Navigation;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\QueryException;
use Livewire\Component;

class GlobalSearch extends Component
{
    public string $query = '';

    public function render(): View
    {
        $term = mb_substr(trim($this->query), 0, config('template.search.max_length'));
        $groups = [];
        $unavailable = false;
        if (mb_strlen($term) >= config('template.search.min_length')) {
            $pages = app(Navigation::class)->searchablePages();
            foreach ($pages as $label => $url) {
                if (mb_stripos($label, $term) !== false) {
                    $groups['Pages'][] = ['label' => $label, 'url' => $url, 'meta' => ''];
                }
            }
            try {
                if (config('template.modules.monitoring')) {
                    $groups += $this->searchRecords($term);
                }
            } catch (QueryException $exception) {
                report($exception);
                $unavailable = true;
            }
        }

        return view('livewire.global-search', compact('term', 'groups', 'unavailable'));
    }

    private function searchRecords(string $term): array
    {
        $access = app(MonitoringAccess::class);
        $ids = $access->ids($access->environments(), 'all');
        $repository = app(MonitoringRepository::class);
        $pattern = '%'.$term.'%';

        $services = $repository->services($ids)->whereLike('s.name', $pattern)->orderBy('s.name')->limit(config('template.search.results_per_group'))->get()
            ->map(fn (object $record): array => [
                'label' => $record->name,
                'meta' => $record->environment,
                'url' => route('dashboard', ['env' => $record->environment_slug, 'service' => $record->id]).'#services',
            ]);
        $deployments = $repository->deployments($ids)
            ->where(fn (Builder $query) => $query->whereLike('s.name', $pattern)->orWhereLike('d.version', $pattern)->orWhereLike('d.commit', $pattern))
            ->orderByDesc('d.deployed_at')->limit(config('template.search.results_per_group'))->get()
            ->map(fn (object $record): array => [
                'label' => $record->service.' v'.$record->version,
                'meta' => $record->environment,
                'url' => route('deployments', ['env' => $record->environment_slug, 'record' => $record->id]),
            ]);
        $incidents = $repository->incidents($ids)->whereLike('i.title', $pattern)->orderByDesc('i.opened_at')->limit(config('template.search.results_per_group'))->get()
            ->map(fn (object $record): array => [
                'label' => $record->title,
                'meta' => $record->environment,
                'url' => route('incidents', ['env' => $record->environment_slug, 'record' => $record->id]),
            ]);

        return array_filter(['Services' => $services->all(), 'Deployments' => $deployments->all(), 'Incidents' => $incidents->all()]);
    }
}
