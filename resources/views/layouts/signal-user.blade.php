<div class="sidebar-user">
    <span class="user-avatar" aria-hidden="true">{{ auth()->check() ? mb_strtoupper(mb_substr(auth()->user()->name, 0, 2)) : 'DO' }}</span>
    <div class="user-label"><strong>{{ auth()->user()?->name ?? 'Demo Operator' }}</strong><span>Monitoring access</span></div>
    @auth
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="icon-button" type="submit" title="Sign out" aria-label="Sign out"><x-signal.icon name="log-out" /></button>
        </form>
    @endauth
</div>
