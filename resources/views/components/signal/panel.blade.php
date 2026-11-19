@props(['heading', 'description' => null, 'icon' => null])
<section {{ $attributes->class(['panel']) }}>
    <header class="panel-header">
        <div class="panel-title">
            @if ($icon)
                <span class="panel-icon"><x-signal.icon :name="$icon" /></span>
            @endif
            <div>
                <h2>{{ $heading }}</h2>
                @if ($description)
                    <p>{{ $description }}</p>
                @endif
            </div>
        </div>
        {{ $actions ?? '' }}
    </header>
    {{ $slot }}
</section>
