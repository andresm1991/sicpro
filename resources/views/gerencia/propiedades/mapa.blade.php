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
                // << CAMBIO 1: Variables estáticas para controlar el estado globalmente
                static activeInfoWindow = null; // Guarda la referencia al InfoWindow abierto
                static closeTimer = null; // Guarda el ID del temporizador para poder cancelarlo

                constructor(position, price, map, infoHtml, extraClass = '') {
                    super();
                    this.position = position;
                    this.price = price;
                    this.map = map;
                    this.infoHtml = infoHtml;
                    this.div = null;
                    this.infoWindow = null;
                    this.extraClass = extraClass;
                    this.setMap(map);
                }

                onAdd() {
                    this.div = document.createElement('div');
                    this.div.className = 'price-marker ' + this.extraClass;
                    this.div.innerHTML = this.price;

                    this.infoWindow = new google.maps.InfoWindow({
                        content: this.infoHtml,
                        pixelOffset: new google.maps.Size(0, -30)
                    });

                    this.div.addEventListener('mouseover', () => {
                        clearTimeout(PriceOverlay.closeTimer);
                        if (PriceOverlay.activeInfoWindow && PriceOverlay.activeInfoWindow !== this
                            .infoWindow) {
                            PriceOverlay.activeInfoWindow.close();
                        }
                        this.infoWindow.setPosition(this.position);
                        this.infoWindow.open(this.map);
                        PriceOverlay.activeInfoWindow = this.infoWindow;
                    });

                    this.div.addEventListener('mouseout', () => {
                        this.scheduleClose();
                    });

                    // <<-- CAMBIO IMPORTANTE AQUÍ: ACTIVAMOS EL CARRUSEL -->>
                    google.maps.event.addListener(this.infoWindow, 'domready', () => {
                        const iwContainer = document.querySelector('.gm-style-iw-d').parentElement;

                        iwContainer.addEventListener('mouseover', () => clearTimeout(PriceOverlay
                            .closeTimer));
                        iwContainer.addEventListener('mouseout', () => this.scheduleClose());

                        // Busca el carrusel dentro del InfoWindow que acaba de abrirse
                        const carouselContainer = iwContainer.querySelector('.carousel-container');
                        if (carouselContainer) {
                            this.initCarousel(carouselContainer);
                        }
                    });

                    const panes = this.getPanes();
                    panes.overlayMouseTarget.appendChild(this.div);
                }

                // <<-- NUEVA FUNCIÓN PARA INICIALIZAR EL CARRUSEL -->>
                initCarousel(container) {
                    const imagesContainer = container.querySelector('.carousel-images');
                    const prevButton = container.querySelector('.prev');
                    const nextButton = container.querySelector('.next');
                    const counter = container.querySelector('.carousel-counter');
                    const images = imagesContainer.querySelectorAll('.carousel-image');

                    // Si no hay imágenes o solo hay una, oculta los botones
                    if (images.length <= 1) {
                        if (prevButton) prevButton.style.display = 'none';
                        if (nextButton) nextButton.style.display = 'none';
                        if (counter) counter.style.display = 'none';
                        return;
                    }

                    let currentIndex = 0;

                    function updateCarousel() {
                        imagesContainer.style.transform = `translateX(-${currentIndex * 100}%)`;
                        counter.textContent = `${currentIndex + 1} / ${images.length}`;
                    }

                    nextButton.addEventListener('click', () => {
                        currentIndex = (currentIndex + 1) % images.length;
                        updateCarousel();
                    });

                    prevButton.addEventListener('click', () => {
                        currentIndex = (currentIndex - 1 + images.length) % images.length;
                        updateCarousel();
                    });

                    updateCarousel(); // Llama una vez para establecer el estado inicial
                }

                scheduleClose() {
                    PriceOverlay.closeTimer = setTimeout(() => {
                        if (PriceOverlay.activeInfoWindow) {
                            PriceOverlay.activeInfoWindow.close();
                            PriceOverlay.activeInfoWindow = null;
                        }
                    }, 200);
                }

                draw() {
                    const overlayProjection = this.getProjection();
                    if (!overlayProjection) return;
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
                    scrollwheel: true, // ✅ Zoom con rueda del mouse sin Ctrl
                    gestureHandling: "auto" // ✅ Compatibilidad con touch
                });

                locations.forEach(location => {
                    const lat = parseFloat(location.latitud);
                    const lng = parseFloat(location.longitud);
                    if (isNaN(lat) || isNaN(lng)) {
                        console.error("Invalid coordinates for location:", location);
                        return; // Skip this location if coordinates are invalid
                    }
                    const pos = {
                        lat: lat,
                        lng: lng
                    };

                    // <<-- GENERACIÓN DINÁMICA DEL HTML DEL CARRUSEL -->>
                    let carouselHtml = '';
                    // Verifica si 'imagenes' existe y es un array con elementos
                    if (location.imagenes_propiedades && Array.isArray(location.imagenes_propiedades) &&
                        location.imagenes_propiedades.length >
                        0) {
                        const imagesHtml = location.imagenes_propiedades.map(img =>
                            // Asume que tu objeto imagen tiene una propiedad 'url'. Ajústala si es necesario.
                            `<img src="${img.url}" alt="Imagen de la propiedad" class="carousel-image">`
                        ).join('');
                        carouselHtml = `
                        <div class="carousel-container">
                            <div class="carousel-images">${imagesHtml}</div>
                            <button class="carousel-button prev">&#10094;</button>
                            <button class="carousel-button next">&#10095;</button>
                            <span class="carousel-counter"></span>
                        </div>
                    `;
                    }

                    var infoAdicional = '';
                    if (location.tipo_propiedad_id && location.tipo_propiedad.slug.toLowerCase() ===
                        'tipo.propiedades.casa') {
                        infoAdicional = `
                            <strong>Área Construida:</strong> ${location.metros_construccion || 'Sin definir'}<br>
                            <strong>Frente:</strong> ${location.frente || '-'}<br>
                            <strong>Fondo:</strong> ${location.fondo || '-'}<br>
                        `;

                    }

                    const infoHtml = `
                            <div class="custom-infowindow">
                                ${carouselHtml}
                                <h6>Información de la Propiedad</h6>
                                <strong>${location.nombre}</strong><br>
                                ${location.direccion || 'Sin descripción'}<br>
                                <strong>Teléfono:</strong> ${location.telefono || '-'}<br>
                                <strong>Tipo:</strong> ${location.tipo_propiedad_id && location.tipo_propiedad.descripcion || 'Sin definir'}<br>
                                <strong>Área:</strong> ${location.area || 'Sin definir'}<br>
                                ${infoAdicional}
                                <strong>Precio por m²:</strong> US $${location.precio_por_metros_cuadrados}<br>
                                <strong>Precio de venta:</strong> ${location.precio_venta_formatted ?
                                    `US $${location.precio_venta_formatted}` :
                                    'Sin precio'}<br>
                                <strong>Estado:</strong> ${location.estado || '-'}<br>
                            </div>
                        `;

                    const markerClass = (location.tipo_propiedad_id && location.tipo_propiedad.slug
                        .toLowerCase() === 'tipo.propiedades.casa') ? 'price-marker-casa' : '';


                    new PriceOverlay(pos, `US $${location.precio_por_metros_cuadrados}`, map, infoHtml,
                        markerClass);
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
