<header class="header">
    <div class="container-fluid">
        <div class="row d-flex align-items-center">
            <div class="col-8 ">
                @if (request()->routeIs('home'))
                    <div class="text-white header-text">Bienvenido al Sistema de Control de Proyectos </div>
                @else
                    <a href="{{ isset($back_route) ? $back_route : route('home') }}"
                        class="text-decoration-none text-white">
                        <i class="fa-solid fa-arrow-left-from-line fa-2x"></i> <span
                            class="text-white header-text">{{ isset($title_page) ? $title_page : 'Bienvenido al Sistema de Control de Proyectos' }}</span></a>
                @endif

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
                </div>
                <div class="menu">
                    <ul>
                        <li><a href="{{ route('perfil.show') }}"><i class="fa-light fa-user"></i>&nbsp;Perfil</a></li>
                        <li><a href="#"><i class="fa-light fa-envelope"></i>&nbsp;Notificaciones</a></li>
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
</header>
