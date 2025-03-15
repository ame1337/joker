<header class="p-3 mb-3 border-bottom">
    <div class="container">
        <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start">
            <a href="/" class="d-flex align-items-center mb-2 mb-md-0 link-body-emphasis text-decoration-none">
                <img src="{{ asset('storage/imgs/logo.png') }}" width="40" height="32" class="me-2" alt="logo">
            </a>

            <ul class="nav col-12 col-md-auto me-md-auto mb-2 justify-content-center mb-md-0">
                <li>
                    <a href="/" class="nav-link px-2 {{ Request::path() === '/' ? 'link-secondary' : 'link-body-emphasis' }}">
                        <i class="fa-solid fa-house"></i> @lang('Home')
                    </a>
                </li>
                <li>
                    <a href="/contact" class="nav-link px-2 {{ Request::path() === 'contact' ? 'link-secondary' : 'link-body-emphasis' }}">
                        <i class="fa-solid fa-envelope"></i> @lang('Contact')
                    </a>
                </li>
                @auth
                <li>
                    <a href="/lobby" class="nav-link px-2 {{ Request::path() === 'lobby' ? 'link-secondary' : 'link-body-emphasis' }}">
                        <i class="fa-solid fa-table-cells-large"></i> @lang('Lobby')
                    </a>
                </li>
                @endauth
            </ul>
            <div class="text-end">
                <a class="btn btn-outline-primary me-1 rounded-3"
                    href="/{{ Request::path() === '/' ? '' : Request::path() }}?lang={{ App::getLocale() == 'en' ? 'ka' : 'en' }}">
                    {{ App::getLocale() == 'en' ? '🇬🇪' : '🇺🇸' }}
                </a>
            </div>

            <theme-changer></theme-changer>
            
            @guest
            <div class="text-end">
                <a href="/login" class="btn btn-success me-1 rounded-3"><i class="fa-solid fa-right-to-bracket"></i></a>
                <a href="/register" class="btn btn-warning rounded-3"><i class="fa-solid fa-user-plus"></i></a>
            </div>
            @endguest
            @auth
            <div class="dropdown text-end">
                <a href="#" class="d-block dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <img class="border rounded-circle g_avatar"
                        src="{{ Auth::user()->avatar_url }}"
                        title="{{ Auth::user()->username }}"
                        alt="{{ Auth::user()->username }}_avatar">
                </a>
                <ul class="dropdown-menu text-small">
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                            <i class="fas fa-user"></i> @lang('Profile')
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('logout') }}"
                            onclick="event.preventDefault();
                            document.getElementById('logout-form').submit();">
                            <i class="fas fa-sign-out-alt"></i> @lang('Sign out')
                        </a>
                    </li>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </ul>
            </div>
            @endauth

        </div>
    </div>
</header>