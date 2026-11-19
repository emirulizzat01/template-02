@props(['value', 'absolute' => false])
@if ($value)
    @php($time = \Carbon\CarbonImmutable::parse($value, 'UTC'))
    <time {{ $attributes }} datetime="{{ $time->toIso8601String() }}" title="{{ $time->format('M j, Y H:i') }} UTC">
        {{ $absolute ? $time->format('M j, Y H:i').' UTC' : $time->diffForHumans(null, false, true) }}
    </time>
@else
    <span {{ $attributes }}>—</span>
@endif
