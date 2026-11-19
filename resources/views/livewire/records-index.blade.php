<div class="records-page">
    @php($deployments = $kind === 'deployments')
    <header class="page-heading">
        <div>
            <a href="{{ route('dashboard', array_filter(['env' => $environment === 'all' ? null : $environment])) }}" class="back-link"><x-signal.icon name="arrow-left" /> Infrastructure overview</a>
            <h1>{{ ucfirst($kind) }}</h1>
            <p>{{ $deployments ? 'Review releases and deployment outcomes across services.' : 'Review current and resolved incidents across your infrastructure.' }}</p>
        </div>
    </header>
    <section class="panel records-panel" aria-label="{{ ucfirst($kind) }} records">
        <form class="filter-bar" x-on:submit.prevent>
            <div class="select-field table-search">
                <label for="records-search">Search</label>
                <div class="search-field">
                    <x-signal.icon name="search" />
                    <input class="form-control" id="records-search" type="search" maxlength="{{ config('template.search.max_length') }}" wire:model.live.debounce.300ms="search" placeholder="{{ $deployments ? 'Service, version, or commit…' : 'Incident title or service…' }}">
                </div>
            </div>
            <x-signal.select label="Environment" id="records-environment" wire:model.live="environment">
                <option value="all">All environments</option>
                @foreach ($environments as $option)
                    <option value="{{ $option->slug }}">{{ $option->name }}</option>
                @endforeach
            </x-signal.select>
            @foreach ($filters as $filter)
                <x-signal.select :label="$filter['label']" :id="'records-'.$filter['property']" wire:model.live="{{ $filter['property'] }}">
                    <option value="all">All {{ strtolower($filter['label']) === 'severity' ? 'severities' : 'statuses' }}</option>
                    @foreach ($filter['options'] as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-signal.select>
            @endforeach
            <x-signal.select label="Sort by" id="records-order" wire:model.live="order">
                <option value="newest">Newest first</option>
                <option value="oldest">Oldest first</option>
            </x-signal.select>
            <button class="secondary-button reset-button" type="button" wire:click="clearFilters"><x-signal.icon name="rotate-ccw" /> Reset</button>
        </form>

        <div class="records-summary" aria-live="polite">
            <span>{{ $unavailable ? 'Data unavailable' : number_format($records->total()).' '.Str::plural($deployments ? 'deployment' : 'incident', $records->total()) }}</span>
            <span class="loading-label" wire:loading>Updating…</span>
        </div>
        @if ($unavailable)
            <x-signal.empty message="Records are temporarily unavailable." icon="triangle-alert" role="alert">
                <button class="secondary-button" type="button" wire:click="$refresh">Try again</button>
            </x-signal.empty>
        @elseif ($records->count())
            <div class="table-scroll">
                <table class="data-table">
                    <caption class="sr-only">{{ ucfirst($kind) }} matching your filters</caption>
                    <thead>
                        @if ($deployments)
                            <tr><th scope="col">Service</th><th class="table-extra" scope="col">Environment</th><th class="table-extra" scope="col">Version</th><th class="table-wide" scope="col">Operator</th><th scope="col">Status</th><th scope="col">Deployed</th></tr>
                        @else
                            <tr><th scope="col">Incident</th><th class="table-extra" scope="col">Environment</th><th scope="col">Severity</th><th class="table-extra" scope="col">Status</th><th scope="col">Opened</th></tr>
                        @endif
                    </thead>
                    <tbody>
                        @foreach ($records as $entry)
                            <tr wire:key="row-{{ $entry->id }}">
                                <th scope="row">
                                    <button type="button" class="record-link" wire:click="openRecord('{{ $entry->id }}')">
                                        {{ $deployments ? $entry->service : $entry->title }} <x-signal.icon name="arrow-up-right" />
                                    </button>
                                    <span class="record-subtitle">{{ $deployments ? $entry->commit : $entry->service }}</span>
                                </th>
                                <td class="table-extra">{{ $entry->environment }}</td>
                                @if ($deployments)
                                    <td class="table-extra"><span class="version-tag">v{{ $entry->version }}</span></td>
                                    <td class="table-wide">{{ $entry->actor }}</td>
                                @else
                                    <td><x-signal.badge :status="$entry->severity" /></td>
                                @endif
                                <td @class(['table-extra' => ! $deployments])><x-signal.badge :status="$entry->status" /></td>
                                <td><x-signal.time class="record-time" :value="$deployments ? $entry->deployed_at : $entry->opened_at" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <x-signal.empty :message="'No '.$kind.' match these filters.'">
                <button class="secondary-button" type="button" wire:click="clearFilters">Reset filters</button>
            </x-signal.empty>
        @endif

        @if ($records->hasPages() || $records->currentPage() > 1)
            <nav class="table-pagination" aria-label="Pagination">
                <span>Page {{ $records->currentPage() }} of {{ $records->lastPage() }}</span>
                <div>
                    <button class="secondary-button" type="button" wire:click="previousPage" @disabled($records->onFirstPage())><x-signal.icon name="chevron-left" /> Previous</button>
                    <button class="secondary-button" type="button" wire:click="nextPage" @disabled(! $records->hasMorePages())>Next <x-signal.icon name="chevron-right" /></button>
                </div>
            </nav>
        @endif
    </section>
    @if ($selectedRecord)
        <x-signal.dialog :title="$deployments ? $selectedRecord->service : $selectedRecord->title" title-id="record-details-title" on-close="closeRecord" wire:key="record-details-{{ $selectedRecord->id }}">
            <dl class="detail-list">
                <div><dt>Status</dt><dd><x-signal.badge :status="$selectedRecord->status" /></dd></div>
                <div><dt>Environment</dt><dd>{{ $selectedRecord->environment }}</dd></div>
                @if ($deployments)
                    <div><dt>Version</dt><dd><span class="version-tag">v{{ $selectedRecord->version }}</span></dd></div>
                    <div><dt>Commit</dt><dd class="commit-code">{{ $selectedRecord->commit }}</dd></div>
                    <div><dt>Operator</dt><dd>{{ $selectedRecord->actor }}</dd></div>
                    <div><dt>Deployed</dt><dd><x-signal.time :value="$selectedRecord->deployed_at" absolute /></dd></div>
                @else
                    <div><dt>Severity</dt><dd><x-signal.badge :status="$selectedRecord->severity" /></dd></div>
                    <div><dt>Service</dt><dd>{{ $selectedRecord->service }}</dd></div>
                    <div><dt>Opened</dt><dd><x-signal.time :value="$selectedRecord->opened_at" absolute /></dd></div>
                @endif
            </dl>
            @if (! $deployments && $selectedRecord->description)
                <p class="detail-description">{{ $selectedRecord->description }}</p>
            @endif
        </x-signal.dialog>
    @endif
</div>
