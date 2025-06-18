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
        function loadGoogleMapsScript() {
            return new Promise((resolve, reject) => {
                if (window.google && window.google.maps) {
                    resolve();
                    return;
                }
                const script = document.createElement('script');
                script.src =
                    "https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}";
                script.async = true;
                script.defer = true;
                script.onload = resolve;
                script.onerror = reject;
                document.head.appendChild(script);
            });
        }

        loadGoogleMapsScript().then(() => {
            // ⬇️ Ahora que google está definido, podemos usarlo

            class PriceOverlay extends google.maps.OverlayView {
                constructor(position, price, map, infoHtml) {
                    super();
                    this.position = position;
                    this.price = price;
                    this.map = map;
                    this.infoHtml = infoHtml;
                    this.div = null;
                    this.infoWindow = null;
                    this.hoverTimeout = null;
                    this.isHovering = false;
                    this.setMap(map);
                }

                onAdd() {
                    this.div = document.createElement('div');
                    this.div.className = 'price-marker';
                    this.div.innerHTML = this.price;

                    this.infoWindow = new google.maps.InfoWindow({
                        content: this.infoHtml,
                        pixelOffset: new google.maps.Size(0, -30) // Ajusta la posición vertical
                    });

                    this.div.addEventListener('mouseover', () => {
                        this.isHovering = true;
                        clearTimeout(this.hoverTimeout); // Cancelar cierre si aún está en hover
                        this.infoWindow.setPosition(this.position);
                        this.infoWindow.open(this.map);
                    });

                    this.div.addEventListener('mouseout', () => {
                        this.isHovering = false;
                        // Cerrar después de un pequeño retraso (evita parpadeo)
                        this.hoverTimeout = setTimeout(() => {
                            if (!this.isHovering) {
                                this.infoWindow.close();
                            }
                        }, 100);
                    });

                    const panes = this.getPanes();
                    panes.overlayMouseTarget.appendChild(this.div);
                }

                draw() {
                    const overlayProjection = this.getProjection();
                    const point = overlayProjection.fromLatLngToDivPixel(
                        new google.maps.LatLng(this.position.lat, this.position.lng)
                    );
                    if (point && this.div) {
                        this.div.style.left = point.x + 'px';
                        this.div.style.top = point.y + 'px';
                    }
                }

                onRemove() {
                    if (this.div) {
                        this.div.parentNode.removeChild(this.div);
                        this.div = null;
                    }
                    if (this.infoWindow) {
                        this.infoWindow.close();
                        this.infoWindow = null;
                    }
                }
            }

            function initMap() {
                const locations = {!! json_encode($propiedades) !!};
                const bounds = new google.maps.LatLngBounds();

                const map = new google.maps.Map(document.getElementById("map"), {
                    zoom: 13,
                    center: {
                        lat: -0.25,
                        lng: -79.19
                    },
                });

                locations.forEach(location => {
                    const pos = {
                        lat: parseFloat(location.latitud),
                        lng: parseFloat(location.longitud)
                    };

                    const infoHtml = `
                            <div class="custom-infowindow" style="padding: 10px; font-family: Arial, sans-serif;">
                                <strong>${location.nombre}</strong><br>
                                ${location.direccion || 'Sin descripción'}<br>
                                <strong>US$${location.precio_venta}</strong>
                            </div>
                        `;

                    new PriceOverlay(pos, `US$${location.precio_por_metros_cuadrados}`, map, infoHtml);
                    bounds.extend(pos);
                });

            }

            // ⬇️ Ya puedes llamar a initMap
            initMap();

        }).catch(() => {
            alert("Error al cargar Google Maps. Verifica tu clave API.");
        });
    </script>

@endsection
