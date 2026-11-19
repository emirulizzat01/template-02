@component('layouts.signal', ['title' => 'Dashboard'])
    <header class="page-heading">
        <div>
            <h1>{{ config('app.name') }}</h1>
            <p>{{ config('template.description') }}</p>
        </div>
    </header>
    @if (config('template.modules.examples'))
        <a class="primary-button" href="{{ route('form-examples.index') }}">Explore form examples <x-signal.icon name="arrow-up-right" /></a>
    @endif
@endcomponent
