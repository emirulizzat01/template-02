@props(['icon', 'href', 'active' => false])
<a {{ $attributes->class(['nav-link', 'nav-link-active' => $active])->merge(['href' => $href, 'aria-current' => $active ? 'page' : null]) }}>
    <x-signal.icon :name="$icon" />
    <span class="nav-label">{{ $slot }}</span>
</a>
