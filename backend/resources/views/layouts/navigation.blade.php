@php
    $currentUser = Auth::user();
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
    ];

    if ($currentUser?->isAdmin()) {
        $links[] = [
            'label' => 'Uzytkownicy',
            'description' => 'Admin i operatorzy',
            'route' => route('users.index'),
            'active' => request()->routeIs('users.*'),
        ];

        $links[] = [
            'label' => 'Ustawienia',
            'description' => 'Salon i interfejs',
            'route' => route('settings.edit'),
            'active' => request()->routeIs('settings.*'),
        ];
    }
@endphp

<div
    class="app-sidebar-backdrop xl:hidden"
    x-cloak
    x-show="mobileSidebarOpen"
    x-transition.opacity
    @click="mobileSidebarOpen = false"
></div>

<aside
    class="app-sidebar"
    :class="{ 'is-open': mobileSidebarOpen, 'is-collapsed': desktopSidebarCollapsed }"
    x-cloak
>
    <div class="app-sidebar__inner">
        <button
            type="button"
            class="app-sidebar__desktop-toggle hidden xl:inline-flex"
            @click="desktopSidebarCollapsed = !desktopSidebarCollapsed"
            :aria-label="desktopSidebarCollapsed ? 'Rozwiń sidebar' : 'Zwiń sidebar'"
            :title="desktopSidebarCollapsed ? 'Rozwiń sidebar' : 'Zwiń sidebar'"
        >
            <span></span>
        </button>

        <div class="app-sidebar__mobile-bar xl:hidden">
            <button
                type="button"
                class="app-sidebar__close"
                @click="mobileSidebarOpen = false"
                aria-label="Zamknij menu"
            >
                <span></span>
                <span></span>
            </button>
        </div>

        <div class="app-sidebar__brand">
            <p class="app-sidebar__eyebrow">Studio</p>
            <p class="app-sidebar__salon">{{ $currentUser?->salon?->name ?? 'Panel' }}</p>
            <p class="app-sidebar__user">{{ $currentUser?->name }}</p>
        </div>

        <nav class="app-sidebar__nav">
            @foreach ($links as $link)
                <a
                    href="{{ $link['route'] }}"
                    class="app-sidebar__link {{ $link['active'] ? 'is-active' : '' }}"
                    @click="mobileSidebarOpen = false"
                >
                    <span class="app-sidebar__link-label">{{ $link['label'] }}</span>
                    <span class="app-sidebar__link-description">{{ $link['description'] }}</span>
                </a>
            @endforeach
        </nav>

        <form method="POST" action="{{ route('logout') }}" class="app-sidebar__logout">
            @csrf
            <button
                type="submit"
                class="app-sidebar__logout-button"
                @click="mobileSidebarOpen = false"
            >
                Wyloguj
            </button>
        </form>
    </div>
</aside>
