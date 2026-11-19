<a class="brand" href="{{ route('dashboard') }}" aria-label="{{ config('app.name') }} home">
    <span class="brand-mark"><x-signal.icon :name="config('template.brand.icon')" /></span>
    <span class="brand-text"><strong>{{ config('app.name') }}</strong><span>{{ config('template.brand.subtitle') }}</span></span>
</a>
