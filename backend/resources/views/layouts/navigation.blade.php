@php
    $links = [
        [
            'label' => 'Dashboard',
            'description' => 'Przeglad dnia',
            'route' => route('dashboard'),
            'active' => request()->routeIs('dashboard'),
        ],
        [
            'label' => 'Wizyty',
            'description' => 'Kalendarz i terminy',
            'route' => route('appointments.index'),
            'active' => request()->routeIs('appointments.*'),
        ],
        [
            'label' => 'Klienci',
            'description' => 'Baza klientow',
            'route' => route('clients.index'),
            'active' => request()->routeIs('clients.*'),
        ],
        [
            'label' => 'Pracownicy',
            'description' => 'Zespol i grafik',
            'route' => route('workers.index'),
            'active' => request()->routeIs('workers.*'),
        ],
        [
            'label' => 'Uslugi',
            'description' => 'Oferta salonu',
            'route' => route('services.index'),
            'active' => request()->routeIs('services.*'),
        ],
        [
            'label' => 'Profil',
            'description' => 'Konto uzytkownika',
            'route' => route('profile.edit'),
            'active' => request()->routeIs('profile.*'),
        ],
        [
            'label' => 'Ustawienia',
            'description' => 'Salon i interfejs',
            'route' => route('settings.edit'),
            'active' => request()->routeIs('settings.*'),
        ],
    ];
@endphp

<aside class="app-sidebar">
    <div class="app-sidebar__inner">
        <div class="app-sidebar__brand">
            <p class="app-sidebar__eyebrow">Studio</p>
            <p class="app-sidebar__salon">{{ Auth::user()->salon?->name ?? 'Panel' }}</p>
            <p class="app-sidebar__user">{{ Auth::user()->name }}</p>
        </div>

        <nav class="app-sidebar__nav">
            @foreach ($links as $link)
                <a
                    href="{{ $link['route'] }}"
                    class="app-sidebar__link {{ $link['active'] ? 'is-active' : '' }}"
                >
                    <span class="app-sidebar__link-label">{{ $link['label'] }}</span>
                    <span class="app-sidebar__link-description">{{ $link['description'] }}</span>
                </a>
            @endforeach
        </nav>

        <form method="POST" action="{{ route('logout') }}" class="app-sidebar__logout">
            @csrf
            <button type="submit" class="app-sidebar__logout-button">Wyloguj</button>
        </form>
    </div>
</aside>
