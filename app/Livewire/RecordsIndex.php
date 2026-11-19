<?php

namespace App\Livewire;

use App\Monitoring\MonitoringAccess;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

abstract class RecordsIndex extends Component
{
    use WithPagination;

    #[Url(as: 'env', history: true)]
    public string $environment = 'all';

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(history: true)]
    public string $status = 'all';

    #[Url(history: true)]
    public string $order = 'newest';

    #[Url(history: true)]
    public string $record = '';

    abstract protected function query(array $ids): Builder;

    abstract protected function filters(): array;

    abstract protected function kind(): string;

    abstract protected function searchColumns(): array;

    abstract protected function idColumn(): string;

    abstract protected function dateColumn(): string;

    public function updated(string $property): void
    {
        if (in_array($property, ['environment', 'search', 'status', 'order', 'severity'], true)) {
            $this->record = '';
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['environment', 'search', 'status', 'order', 'record']);
        $this->resetPage();
    }

    public function openRecord(string $id): void
    {
        $this->record = $id;
    }

    public function closeRecord(): void
    {
        $this->record = '';
    }

    public function render(): View
    {
        try {
            return view('livewire.records-index', $this->tableData());
        } catch (QueryException $exception) {
            report($exception);

            return view('livewire.records-index', [
                'kind' => $this->kind(),
                'environments' => collect(),
                'filters' => $this->filters(),
                'records' => new LengthAwarePaginator([], 0, config('signal.per_page')),
                'selectedRecord' => null,
                'unavailable' => true,
            ]);
        }
    }

    private function tableData(): array
    {
        $access = app(MonitoringAccess::class);
        $environments = $access->environments();
        $this->environment = $access->selection($environments, $this->environment);
        $baseQuery = $this->query($access->ids($environments, $this->environment));
        $needle = mb_substr(trim($this->search), 0, config('template.search.max_length'));
        $direction = $this->order === 'oldest' ? 'asc' : 'desc';
        $filters = $this->filters();
        $query = clone $baseQuery;

        foreach ($filters as $filter) {
            $selected = $this->{$filter['property']};
            if (array_key_exists($selected, $filter['options'])) {
                $query->where($filter['column'], $selected);
            }
        }
        if ($needle !== '') {
            $query->where(function (Builder $matches) use ($needle): void {
                foreach ($this->searchColumns() as $column) {
                    $matches->orWhereLike($column, '%'.$needle.'%');
                }
            });
        }

        return [
            'kind' => $this->kind(),
            'environments' => $environments,
            'unavailable' => false,
            'filters' => $filters,
            'records' => $query->orderBy($this->dateColumn(), $direction)->orderBy($this->idColumn(), $direction)->paginate(config('signal.per_page')),
            'selectedRecord' => ctype_digit($this->record)
                ? $baseQuery->where($this->idColumn(), (int) $this->record)->first()
                : null,
        ];
    }
}
