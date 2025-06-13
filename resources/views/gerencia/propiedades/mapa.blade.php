@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        <div class="mi">
            @include('partials.alerts')
            <div class="card">
                <div class="card-body ">
                    <div class="row">
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <h4>Mis Ubicaciones</h4>
                            </div>
                        </div>
                    </div>

                    <!-- El div donde se mostrará el mapa -->
                    <div id="map"></div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        function initMap() {
            // Convertimos los datos de PHP (Laravel) a un objeto JavaScript.
            // Usamos para que Blade no escape los caracteres JSON.
            // json es una directiva de Blade que convierte el array/colección a JSON de forma segura.
            const locations = {!! json_encode($propiedades) !!};
            // 1. Creamos un objeto LatLngBounds. Este será nuestro "rectángulo" mágico.
            const bounds = new google.maps.LatLngBounds();
            // Coordenadas iniciales para el centro del mapa (puedes usar la primera ubicación o un punto fijo)
            const initialCoords = {
                lat: -0.25035893576944057,
                lng: -79.19136065070049
            };

            // Creamos el mapa y lo centramos
            const map = new google.maps.Map(document.getElementById("map"), {
                zoom: 13,
                center: initialCoords,
            });

            // Creamos un objeto InfoWindow para mostrar información al hacer clic en un marcador
            const infoWindow = new google.maps.InfoWindow();

            // Iteramos sobre cada ubicación para crear un marcador
            locations.forEach(location => {
                const position = {
                    lat: parseFloat(location.latitud),
                    lng: parseFloat(location.longitud)
                };

                const marker = new google.maps.Marker({
                    // Usamos parseFloat para asegurarnos de que lat y lng son números
                    position: position,
                    map: map,
                    title: location.nombre, // El texto que aparece al pasar el cursor sobre el marcador
                    icon: {
                        url: "{{ asset('images/pin-ubicacion-propiedades.png') }}",
                        // Opcional: ajusta el tamaño del icono
                        scaledSize: new google.maps.Size(60, 60), // Ancho y alto en píxeles
                        // Opcional: el punto del icono que se anclará a la coordenada del mapa
                        anchor: new google.maps.Point(20, 40) // La punta inferior central del icono
                    }

                });

                bounds.extend(position);
                // Añadimos un listener para el evento 'click' en cada marcador
                marker.addListener('click', () => {
                    // Creamos el contenido del InfoWindow con el nombre y la descripción <!--<img src="${imageUrl}" alt="${location.nombre}">-->
                    const content = `
                        <div class="custom-infowindow">
                            
                            <h6>${location.nombre}</h6>
                            <p>${location.direccion || 'Sin descripción.'}</p>
                        </div>
                    `;
                    const infoWindow = new google.maps.InfoWindow();
                    infoWindow.setContent(content);
                    infoWindow.open(map, marker);
                });
            });

            // --- INICIO DE LA LÓGICA DE GEOLOCALIZACIÓN ---
            getLocation().then(function(coordenadas) {
                // Obtenemos las coordenadas del usuario
                const userLocation = {
                    lat: parseFloat(coordenadas.latitud),
                    lng: parseFloat(coordenadas.longitud)
                };

                // Centramos el mapa en la ubicación del usuario
                map.setCenter(userLocation);
                // Opcional: Añadimos un marcador especial para la ubicación del usuario
                /*new google.maps.Marker({
                    position: userLocation,
                    map: map,
                    title: "Tu ubicación actual",
                    // Usamos un ícono diferente para distinguirlo
                    /*icon: {
                        url: "http://maps.google.com/mapfiles/ms/icons/blue-dot.png"
                    }*/
                // });
            }).catch(function(error) {
                alert(
                    "No se pudo obtener la ubicación. Asegúrate de que los permisos de ubicación estén habilitados."
                );
            });

        }
    </script>

    <!--
                                                                                                                                                                                                                    Cargamos la API de Google Maps.
                                                                                                                                                                                                                    - `key` usa la configuración que definimos en `config/services.php`.
                                                                                                                                                                                                                    - `callback=initMap` le dice a la API que ejecute la función `initMap` cuando esté completamente cargada.
                                                                                                                                                                                                                    - `async` y `defer` aseguran que el script se cargue sin bloquear la renderización de la página.
                                                                                                                                                                                                                    -->
    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&callback=initMap"
        async defer></script>
@endsection

<style>
    /* Es importante definir un alto para el contenedor del mapa */
    #map {
        height: 600px;
        width: 100%;
    }
</style>
