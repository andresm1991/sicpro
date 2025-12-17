<div class="contenedor-notificaciones-doble">

    <!-- Notificaciones administrativas -->
    <div class="content-notificaciones" id="notif-2">
        <div class="bell-icon bell-green" tabindex="0">
            <svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="50px" height="30px" viewBox="0 0 50 30">
                <g class="bell-icon__group">
                    <path class="bell-icon__ball" fill-rule="evenodd" stroke-width="1.5" fill="none"
                        stroke="currentColor"
                        d="M28.7,25 c0,1.9-1.7,3.5-3.7,3.5s-3.7-1.6-3.7-3.5s1.7-3.5,3.7-3.5S28.7,23,28.7,25z" />
                    <path class="bell-icon__shell" fill-rule="evenodd" fill="#FFFFFF" stroke="currentColor"
                        stroke-width="2"
                        d="M35.9,21.8c-1.2-0.7-4.1-3-3.4-8.7c0.1-1,0.1-2.1,0-3.1h0c-0.3-4.1-3.9-7.2-8.1-6.9c-3.7,0.3-6.6,3.2-6.9,6.9h0 c-0.1,1-0.1,2.1,0,3.1c0.6,5.7-2.2,8-3.4,8.7c-0.4,0.2-0.6,0.6-0.6,1v1.8c0,0.2,0.2,0.4,0.4,0.4h22.2c0.2,0,0.4-0.2,0.4-0.4v-1.8 C36.5,22.4,36.3,22,35.9,21.8L35.9,21.8z" />
                </g>
            </svg>
            <div class="notification-amount">
                <span>{{ auth()->user()->isNewNotificationsAdmin()->count() < 99 ? auth()->user()->isNewNotificationsAdmin()->count() : '+99' }}</span>
            </div>
        </div>
        <div class="menu" style="width: 250px">
            @if (auth()->user()->unreadNotificationsAdmin()->count() > 0)
                <div class="menu-content">
                    <ul>
                        @foreach (auth()->user()->unreadNotificationsAdmin->sortByDesc('created_at')->take(10) as $item)
                            <li><a href="javascript:void(0);" class="leer-notificacion" data-id="{{ $item->id }}"
                                    data-url="{{ $item->message->url }}"
                                    style="text-align: start; font-size: 12px; padding: 10px">{{ $item->message->title }}</a>
                            </li>
                        @endforeach
                        <li class="view-all">
                            <a href="{{ route('notificacion.index', 2) }}"
                                style="text-align: center;position: absolute;right: 65px;">Ver todo</a>
                        </li>
                    </ul>
                </div>
            @else
                <ul>
                    <li>
                        <a href="javascript:void(0);" class="text-dark"
                            style="text-align: start; font-size: 12px; padding: 10px">No tienes notificaciones
                            pendientes.</a>
                    </li>
                    <li class="view-all" style="text-align: center;position: absolute;top: 40px;left: 65px;">
                        <a href="{{ route('notificacion.index', 2) }}" class="text-center">Ver todo</a>
                    </li>
                </ul>
            @endif
        </div>
    </div>

    <!-- Notificaciones operativas -->
    <div class="content-notificaciones" id="notif-1">
        <div class="bell-icon" tabindex="0">
            <svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="50px" height="30px" viewBox="0 0 50 30">
                <g class="bell-icon__group">
                    <path class="bell-icon__ball" fill-rule="evenodd" stroke-width="1.5" fill="none"
                        stroke="#currentColor"
                        d="M28.7,25 c0,1.9-1.7,3.5-3.7,3.5s-3.7-1.6-3.7-3.5s1.7-3.5,3.7-3.5S28.7,23,28.7,25z" />
                    <path class="bell-icon__shell" fill-rule="evenodd" fill="#FFFFFF" stroke="#currentColor"
                        stroke-width="2"
                        d="M35.9,21.8c-1.2-0.7-4.1-3-3.4-8.7c0.1-1,0.1-2.1,0-3.1h0c-0.3-4.1-3.9-7.2-8.1-6.9c-3.7,0.3-6.6,3.2-6.9,6.9h0 c-0.1,1-0.1,2.1,0,3.1c0.6,5.7-2.2,8-3.4,8.7c-0.4,0.2-0.6,0.6-0.6,1v1.8c0,0.2,0.2,0.4,0.4,0.4h22.2c0.2,0,0.4-0.2,0.4-0.4v-1.8 C36.5,22.4,36.3,22,35.9,21.8L35.9,21.8z" />
                </g>
            </svg>
            <div class="notification-amount">
                <span>{{ auth()->user()->isNewNotifications()->count() < 99 ? auth()->user()->isNewNotifications()->count() : '+99' }}</span>
            </div>
        </div>

        <div class="menu" style="width: 250px">
            @if (auth()->user()->unreadNotifications()->count() > 0)
                <div class="menu-content">
                    <ul>
                        @foreach (auth()->user()->unreadNotifications->sortByDesc('created_at')->take(10) as $item)
                            <li><a href="javascript:void(0);" class="leer-notificacion" data-id="{{ $item->id }}"
                                    data-url="{{ $item->message->url }}"
                                    style="text-align: start; font-size: 12px; padding: 10px">{{ $item->message->title }}</a>
                            </li>
                        @endforeach
                        <li class="view-all">
                            <a href="{{ route('notificacion.index', 1) }}"
                                style="text-align: center;position: absolute;right: 65px;">Ver todo</a>
                        </li>
                    </ul>
                </div>
            @else
                <ul>
                    <li>
                        <a href="javascript:void(0);" class="text-dark"
                            style="text-align: start; font-size: 12px; padding: 10px">No tienes notificaciones
                            pendientes.</a>
                    </li>
                    <li class="view-all" style="text-align: center;position: absolute;top: 40px;left: 65px;">
                        <a href="{{ route('notificacion.index', 1) }}" class="text-center">Ver todo</a>
                    </li>
                </ul>
            @endif
        </div>
    </div>

</div>
