<header class="header">
    <div class="container-fluid">
        <div class="row d-flex align-items-center">
            <div class="col-8 ">
                @if (request()->routeIs('home'))
                    <div class="text-white header-text">Bienvenido al Sistema de Control de Proyectos </div>
                @else
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb custom-breadcrumb text-uppercase">
                            @foreach ($breadcrumbs as $breadcrumb)
                                @if (!$loop->last)
                                    <!-- Si no es el último, es un enlace -->
                                    <li class="breadcrumb-item header-text">
                                        <a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['name'] }}</a>
                                    </li>
                                @else
                                    <!-- Si es el último, es el actual -->
                                    <li class="breadcrumb-item header-text active" aria-current="page">
                                        {{ $breadcrumb['name'] }}</li>
                                @endif
                            @endforeach

                        </ol>
                    </nav>

                    {{--  <a href="{{ isset($back_route) ? $back_route : route('home') }}"
                        class="text-decoration-none text-white">
                        <i class="fa-solid fa-arrow-left-from-line fa-2x"></i> <span
                            class="text-white header-text text-capitalize">{{ isset($title_page) ? $title_page : 'Bienvenido al Sistema de Control de Proyectos' }}</span></a>
                            --}}
                @endif

            </div>

            <div class="content-notificaciones">
                <div class="bell-icon" tabindex="0">
                    <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                        x="0px" y="0px" width="50px" height="30px" viewBox="0 0 50 30"
                        enable-background="new 0 0 50 30" xml:space="preserve">
                        <g class="bell-icon__group">
                            <path class="bell-icon__ball" id="ball" fill-rule="evenodd" stroke-width="1.5"
                                clip-rule="evenodd" fill="none" stroke="#currentColor" stroke-miterlimit="10"
                                d="M28.7,25 c0,1.9-1.7,3.5-3.7,3.5s-3.7-1.6-3.7-3.5s1.7-3.5,3.7-3.5S28.7,23,28.7,25z" />
                            <path class="bell-icon__shell" id="shell" fill-rule="evenodd" clip-rule="evenodd"
                                fill="#FFFFFF" stroke="#currentColor" stroke-width="2" stroke-miterlimit="10"
                                d="M35.9,21.8c-1.2-0.7-4.1-3-3.4-8.7c0.1-1,0.1-2.1,0-3.1h0c-0.3-4.1-3.9-7.2-8.1-6.9c-3.7,0.3-6.6,3.2-6.9,6.9h0 c-0.1,1-0.1,2.1,0,3.1c0.6,5.7-2.2,8-3.4,8.7c-0.4,0.2-0.6,0.6-0.6,1v1.8c0,0.2,0.2,0.4,0.4,0.4h22.2c0.2,0,0.4-0.2,0.4-0.4v-1.8 C36.5,22.4,36.3,22,35.9,21.8L35.9,21.8z" />
                        </g>
                    </svg>
                    <div class="notification-amount">
                        @if (auth()->user()->unreadNotifications()->count() > 0)
                            <span>{{ auth()->user()->unreadNotifications()->count() < 99 ? auth()->user()->unreadNotifications()->count() : '+99' }}</span>
                        @else
                            <span>0</span>
                        @endif
                    </div>
                </div>
                <div class="menu" style="width: 250px">
                    @if (auth()->user()->unreadNotifications()->count() > 0)
                        <div class="menu-content">
                            <ul>
                                @foreach (auth()->user()->unreadNotifications->sortByDesc('created_at')->take(10) as $item)
                                    <li><a href="javascript:void(0);" class="leer-notificacion"
                                            data-id="{{ $item->id }}" data-url="{{ $item->message->url }}"
                                            style="text-align: start; font-size: 12px; padding: 10px">{{ $item->message->title }}</a>
                                    </li>
                                @endforeach
                                <!-- Enlace para ver todas las notificaciones -->
                                <li class="view-all">
                                    <a href="{{ route('notificacion.index') }}"
                                        style="text-align: center;position: absolute;right: 65px;">Ver
                                        todo</a>
                                </li>
                            </ul>
                        </div>
                    @else
                        <ul>
                            <li>
                                <a href="javascript:void(0);" class="text-dark"
                                    style="text-align: start; font-size: 12px; padding: 10px">No
                                    tienes notificaciones pendientes.</a>
                            </li>
                            <li class="view-all" style="text-align: center;position: absolute;top: 40px;left: 65px;">
                                <a href="{{ route('notificacion.index') }}" class="text-center">Ver
                                    todo</a>
                            </li>
                        </ul>
                    @endif

                </div>
            </div>
            <div class="col-4">
                <div class="profile">

                    <div class="user  mt-3">
                        <h3>{{ auth()->user()->nombre }}</h3>
                        <p></p>
                    </div>
                    <div class="img-box">
                        <img src="{{ asset('images/usuario.png') }}" alt="some user image">
                    </div>
                    <div class="menu">
                        <ul>
                            <li><a href="{{ route('perfil.show') }}"><i class="fa-light fa-user"></i>&nbsp;Perfil</a>
                            </li>
                            <li><a href="{{ route('logout') }}"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i
                                        class="fa-light fa-arrow-right-from-bracket"></i>&nbsp;Cerrar
                                    Sesión</a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>

            </div>

        </div>
    </div>
</header>
