<?php

namespace App\Monitoring;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MonitoringAccess
{
    public function demo(): bool
    {
        return app()->environment(['local', 'testing']) && config('signal.demo') === true;
    }

    public function environments(): Collection
    {
        $userId = auth()->id();

        return DB::table('environments')
            ->when(! $this->demo(), function (Builder $query) use ($userId): void {
                $query->whereIn('id', DB::table('environment_user')
                    ->select('environment_id')
                    ->where('user_id', $userId));
            })
            ->orderBy('id')
            ->get(['id', 'slug', 'name']);
    }

    public function ids(Collection $environments, string $selected): array
    {
        return $environments
            ->when($selected !== 'all', fn (Collection $items) => $items->where('slug', $selected))
            ->pluck('id')
            ->all();
    }

    public function selection(Collection $environments, string $selected): string
    {
        return $environments->contains('slug', $selected) ? $selected : 'all';
    }
}
