<template>
    <div class="search-map-container">
        <div ref="mapContainer" class="map-wrapper"></div>

        <!-- Loading Indicator -->
        <div v-if="searchLoading" class="map-loading">
            <div class="spinner"></div>
            <p>Loading properties...</p>
        </div>

        <!-- Map Controls -->
        <div class="absolute top-4 right-4 z-[1000] space-y-2">
            <!-- Zoom In -->
            <button
                @click="zoomIn"
                class="w-10 h-10 bg-white dark:bg-gray-800 rounded-lg shadow-lg flex items-center justify-center hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
            >
                <svg
                    class="w-5 h-5 text-gray-700 dark:text-gray-300"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                    />
                </svg>
            </button>

            <!-- Zoom Out -->
            <button
                @click="zoomOut"
                class="w-10 h-10 bg-white dark:bg-gray-800 rounded-lg shadow-lg flex items-center justify-center hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
            >
                <svg
                    class="w-5 h-5 text-gray-700 dark:text-gray-300"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M20 12H4"
                    />
                </svg>
            </button>

            <!-- Recenter -->
            <button
                @click="recenterMap"
                class="w-10 h-10 bg-white dark:bg-gray-800 rounded-lg shadow-lg flex items-center justify-center hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
            >
                <svg
                    class="w-5 h-5 text-gray-700 dark:text-gray-300"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                    />
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                    />
                </svg>
            </button>
        </div>

        <!-- Results Count Badge -->
        <div class="absolute bottom-4 left-1/2 -translate-x-1/2 z-[1000]">
            <div
                class="px-4 py-2 bg-white dark:bg-gray-800 rounded-full shadow-lg border border-gray-200 dark:border-gray-700"
            >
                <span class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ results.length }} properties in view
                </span>
            </div>
        </div>
    </div>
</template>

<script>
import { mapActions, mapState } from "vuex";
import L from "leaflet";
import "leaflet/dist/leaflet.css";
import { createApp, h } from "vue";
import MapMarker from "@/src/views/search/components/map/MapMarker.vue";
import MapPopup from "@/src/views/search/components/map/MapPopup.vue";

// Fix for default marker icon
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl:
        "https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon-2x.png",
    iconUrl:
        "https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon.png",
    shadowUrl:
        "https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png",
});

export default {
    name: "SearchMap",
    props: {
        results: {
            type: Array,
            default: () => [],
        },
        hoveredCardId: {
            type: [Number, String],
            default: null,
        },
        center: {
            type: Object,
            default: () => ({ lat: 44.8124, lng: 20.4617 }),
        },
        zoom: {
            type: Number,
            default: 12,
        },
        bounds: {
            type: Object,
            default: null,
        },
    },
    components: {
        MapMarker,
        MapPopup,
    },
    computed: {
        ...mapState("ui", ["selectedCurrency"]),
        ...mapState("search", {
            searchLoading: (state) => state.loading,
        }),
    },
    data() {
        return {
            map: null,
            markers: {},
            markersLayer: null,
            boundsChangeTimeout: null,
            userInteracted: false,
            isInitialLoad: true,
            lastEmittedBounds: null,
            hasUserEverInteracted: false,
            isProgrammaticMove: false,
        };
    },
    watch: {
        results: {
            handler(newResults) {
                this.updateMarkers(newResults);
            },
            deep: true,
        },
        hoveredCardId(newId, oldId) {
            if (oldId && this.markers[oldId]) {
                this.unhighlightMarker(oldId);
            }
            if (newId && this.markers[newId]) {
                this.highlightMarker(newId);
            }
        },
    },
    mounted() {
        this.$nextTick(() => {
            this.initializeMap();
            if (this.results.length > 0) {
                this.updateMarkers(this.results);
            }
        });
    },
    methods: {
        ...mapActions("search", ["searchListings", "setLoading"]),

        initializeMap() {
            this.map = L.map(this.$refs.mapContainer, {
                center: [this.center.lat, this.center.lng],
                zoom: this.zoom,
                zoomControl: false,
                attributionControl: true,
                zoomAnimation: true,
                fadeAnimation: true,
                markerZoomAnimation: true,
            });

            L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                attribution:
                    '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 19,
            }).addTo(this.map);

            this.markersLayer = L.featureGroup().addTo(this.map);

            this.map.on("zoomanim", () => {
                this.map.eachLayer((layer) => {
                    if (layer instanceof L.Popup) {
                        const popupElement = layer._container;
                        if (popupElement) {
                            popupElement.style.display = "none";
                        }
                    }
                });
            });

            this.map.on("zoomstart", () => {
                this.map.closePopup();

                if (this.markersLayer) {
                    this.markersLayer.eachLayer((layer) => {
                        if (layer instanceof L.Marker) {
                            if (layer.getPopup && layer.getPopup()) {
                                layer.closePopup();
                            }
                        }
                    });
                }

                if (!this.isProgrammaticMove) {
                    this.userInteracted = true;
                    this.hasUserEverInteracted = true;
                }
            });

            this.map.on("dragstart", () => {
                this.map.closePopup();

                if (!this.isProgrammaticMove) {
                    this.userInteracted = true;
                    this.hasUserEverInteracted = true;
                }
            });

            this.map.on("moveend", () => {
                if (this.isInitialLoad) {
                    this.isInitialLoad = false;
                    this.isProgrammaticMove = false;
                    return;
                }

                if (this.isProgrammaticMove) {
                    this.isProgrammaticMove = false;
                    return;
                }

                if (this.hasUserEverInteracted && this.userInteracted) {
                    this.debouncedEmitBounds();
                    this.userInteracted = false;
                }
            });
        },

        updateMarkers(results) {
            if (!results || results.length === 0) {
                this.clearMarkers();
                return;
            }

            const newIds = new Set(results.map((r) => String(r.id)));
            const existingIds = Object.keys(this.markers);

            existingIds.forEach((id) => {
                if (!newIds.has(String(id))) {
                    const marker = this.markers[id];
                    if (marker) {
                        if (marker.isPopupOpen && marker.isPopupOpen()) {
                            marker.closePopup();
                        }

                        if (marker.getPopup()) {
                            marker.unbindPopup();
                        }

                        if (marker._markerApp) {
                            marker._markerApp.unmount();
                            marker._markerApp = null;
                        }
                        if (marker._popupApp) {
                            marker._popupApp.unmount();
                            marker._popupApp = null;
                        }

                        marker.off();
                        this.markersLayer.removeLayer(marker);
                        delete this.markers[id];
                    }
                }
            });

            let validMarkerCount = 0;

            results.forEach((accommodation) => {
                const { coordinates, id } = accommodation;

                if (!coordinates?.latitude || !coordinates?.longitude) {
                    return;
                }

                if (this.markers[id]) {
                    validMarkerCount++;
                    return;
                }

                try {
                    const marker = this.createMarker(accommodation);
                    this.markers[id] = marker;
                    this.markersLayer.addLayer(marker);
                    validMarkerCount++;
                } catch (error) {
                    console.error(
                        `Error creating marker for accommodation ${id}:`,
                        error,
                    );
                }
            });

            if (
                !this.hasUserEverInteracted &&
                !this.bounds &&
                validMarkerCount > 0
            ) {
                try {
                    const bounds = this.markersLayer.getBounds();

                    if (bounds && bounds.isValid()) {
                        this.isProgrammaticMove = true;
                        this.map.fitBounds(bounds, {
                            padding: [50, 50],
                            maxZoom: 15,
                            animate: false,
                        });
                    }
                } catch (error) {
                    console.error("Error fitting bounds:", error);
                }
            }
        },

        createMarker(accommodation) {
            const markerContainer = document.createElement("div");
            const markerApp = createApp({
                render: () =>
                    h(MapMarker, {
                        accommodation: accommodation,
                        currency: this.selectedCurrency,
                        isHovered: accommodation.id === this.hoveredCardId,
                    }),
            });
            markerApp.mount(markerContainer);

            const customIcon = L.divIcon({
                className: "custom-marker",
                html: markerContainer.innerHTML,
                iconSize: [60, 40],
                iconAnchor: [30, 40],
            });

            const marker = L.marker(
                [
                    accommodation.coordinates.latitude,
                    accommodation.coordinates.longitude,
                ],
                {
                    icon: customIcon,
                },
            );

            const popupContainer = document.createElement("div");
            const popupApp = createApp({
                render: () =>
                    h(MapPopup, {
                        accommodation: accommodation,
                        currency: this.selectedCurrency,
                        onClosePopup: () => {
                            marker.closePopup();
                        },
                    }),
            });
            popupApp.mount(popupContainer);

            // POPUP OPTIONS - Keep inside map
            const popup = L.popup({
                maxWidth: 280,
                minWidth: 280,
                className: "custom-popup",
                closeButton: false,
                autoClose: true,
                closeOnClick: false,
                autoPan: true, // Pan mapa kad popup izlazi van
                autoPanPadding: [10, 10], // Padding od ivice mape
                keepInView: true, // Drži popup unutar view-a
                closeOnEscapeKey: true,
                animate: false,
                zoomAnimation: false,
                // KRITIČNO: Offset popup tako da ne izlazi
                offset: [0, -10], // [x, y] offset od markera
            }).setContent(popupContainer);

            marker.bindPopup(popup);

            // OVERRIDE popup _updatePosition da FORSIRAM da ostane u bounds
            const originalUpdatePosition = popup._updatePosition;
            popup._updatePosition = function () {
                if (!this._map) {
                    return;
                }

                originalUpdatePosition.call(this);

                // Proveri da li popup izlazi van mape
                if (this._container) {
                    const mapSize = this._map.getSize();
                    const popupPoint = this._map.latLngToContainerPoint(
                        this._latlng,
                    );
                    const popupSize = {
                        x: this._container.offsetWidth,
                        y: this._container.offsetHeight,
                    };

                    // Adjust position ako izlazi
                    let adjustX = 0;
                    let adjustY = 0;

                    // Check right edge
                    if (popupPoint.x + popupSize.x / 2 > mapSize.x - 10) {
                        adjustX =
                            mapSize.x - 10 - (popupPoint.x + popupSize.x / 2);
                    }

                    // Check left edge
                    if (popupPoint.x - popupSize.x / 2 < 10) {
                        adjustX = 10 - (popupPoint.x - popupSize.x / 2);
                    }

                    // Check top edge
                    if (popupPoint.y - popupSize.y < 10) {
                        adjustY = 10 - (popupPoint.y - popupSize.y);
                    }

                    // Check bottom edge
                    if (popupPoint.y > mapSize.y - 10) {
                        adjustY = mapSize.y - 10 - popupPoint.y;
                    }

                    // Apply adjustments
                    if (adjustX !== 0 || adjustY !== 0) {
                        const currentPos = L.DomUtil.getPosition(
                            this._container,
                        );
                        L.DomUtil.setPosition(
                            this._container,
                            L.point(
                                currentPos.x + adjustX,
                                currentPos.y + adjustY,
                            ),
                        );
                    }
                }
            };

            popup._animateZoom = function () {
                return;
            };

            const originalAdjustPan = popup._adjustPan;
            popup._adjustPan = function () {
                if (this._map && this._map._panAnim) {
                    originalAdjustPan.call(this);
                }
            };

            marker.on("mouseover", () => {
                this.$emit("marker-hover", accommodation.id);
            });

            marker.on("mouseout", () => {
                this.$emit("marker-hover", null);
            });

            marker.on("click", () => {
                this.map.eachLayer((layer) => {
                    if (layer instanceof L.Marker && layer !== marker) {
                        layer.closePopup();
                    }
                });
                marker.openPopup();
            });

            marker._markerApp = markerApp;
            marker._popupApp = popupApp;
            marker._customPopup = popup;

            return marker;
        },

        highlightMarker(accommodationId) {
            const marker = this.markers[accommodationId];
            if (!marker) return;

            const accommodation = this.results.find(
                (r) => r.id === accommodationId,
            );
            if (!accommodation) return;

            if (marker._markerApp) {
                marker._markerApp.unmount();
            }

            const markerContainer = document.createElement("div");
            const markerApp = createApp({
                render: () =>
                    h(MapMarker, {
                        accommodation: accommodation,
                        currency: this.selectedCurrency,
                        isHovered: true,
                    }),
            });
            markerApp.mount(markerContainer);

            const highlightedIcon = L.divIcon({
                className: "custom-marker",
                html: markerContainer.innerHTML,
                iconSize: [60, 40],
                iconAnchor: [30, 40],
            });

            marker.setIcon(highlightedIcon);
            marker.setZIndexOffset(1000);
            marker._markerApp = markerApp;
        },

        unhighlightMarker(accommodationId) {
            const marker = this.markers[accommodationId];
            if (!marker) return;

            const accommodation = this.results.find(
                (r) => r.id === accommodationId,
            );
            if (!accommodation) return;

            if (marker._markerApp) {
                marker._markerApp.unmount();
            }

            const markerContainer = document.createElement("div");
            const markerApp = createApp({
                render: () =>
                    h(MapMarker, {
                        accommodation: accommodation,
                        currency: this.selectedCurrency,
                        isHovered: false,
                    }),
            });
            markerApp.mount(markerContainer);

            const normalIcon = L.divIcon({
                className: "custom-marker",
                html: markerContainer.innerHTML,
                iconSize: [60, 40],
                iconAnchor: [30, 40],
            });

            marker.setIcon(normalIcon);
            marker.setZIndexOffset(0);
            marker._markerApp = markerApp;
        },

        zoomIn() {
            if (!this.map) return;
            this.map.closePopup();
            this.$nextTick(() => {
                this.map.zoomIn();
            });
        },

        zoomOut() {
            if (!this.map) return;
            this.map.closePopup();
            this.$nextTick(() => {
                this.map.zoomOut();
            });
        },

        clearMarkers() {
            if (this.markersLayer) {
                this.map.closePopup();

                this.markersLayer.eachLayer((layer) => {
                    if (layer.isPopupOpen && layer.isPopupOpen()) {
                        layer.closePopup();
                    }

                    if (layer.getPopup()) {
                        layer.unbindPopup();
                    }

                    if (layer._markerApp) {
                        layer._markerApp.unmount();
                        layer._markerApp = null;
                    }
                    if (layer._popupApp) {
                        layer._popupApp.unmount();
                        layer._popupApp = null;
                    }

                    layer.off();
                });

                this.markersLayer.clearLayers();
            }
            this.markers = {};
        },

        recenterMap() {
            if (!this.map) return;
            this.map.closePopup();
            this.isProgrammaticMove = true;

            if (this.results.length > 0) {
                const bounds = this.markersLayer.getBounds();
                if (bounds.isValid()) {
                    this.map.fitBounds(bounds, {
                        padding: [50, 50],
                        animate: false,
                    });
                }
            } else {
                this.map.setView(
                    [this.center.lat, this.center.lng],
                    this.zoom,
                    { animate: false },
                );
            }
        },

        getCurrentMapBounds() {
            if (!this.map) return null;

            const bounds = this.map.getBounds();
            const center = this.map.getCenter();

            return {
                northEast: {
                    lat: bounds.getNorthEast().lat,
                    lng: bounds.getNorthEast().lng,
                },
                southWest: {
                    lat: bounds.getSouthWest().lat,
                    lng: bounds.getSouthWest().lng,
                },
                center: {
                    lat: center.lat,
                    lng: center.lng,
                },
                zoom: this.map.getZoom(),
            };
        },

        emitMapBounds() {
            const mapBounds = this.getCurrentMapBounds();
            if (!mapBounds) return;

            if (this.areBoundsSimilar(mapBounds, this.lastEmittedBounds)) {
                return;
            }

            this.lastEmittedBounds = mapBounds;
            this.$emit("map-bounds-changed", mapBounds);
        },

        areBoundsSimilar(bounds1, bounds2) {
            if (!bounds1 || !bounds2) return false;

            const threshold = 0.001;

            return (
                Math.abs(bounds1.northEast.lat - bounds2.northEast.lat) <
                    threshold &&
                Math.abs(bounds1.northEast.lng - bounds2.northEast.lng) <
                    threshold &&
                Math.abs(bounds1.southWest.lat - bounds2.southWest.lat) <
                    threshold &&
                Math.abs(bounds1.southWest.lng - bounds2.southWest.lng) <
                    threshold
            );
        },

        debouncedEmitBounds() {
            this.setLoading(true);

            if (this.boundsChangeTimeout) {
                clearTimeout(this.boundsChangeTimeout);
            }

            this.boundsChangeTimeout = setTimeout(() => {
                this.emitMapBounds();
            }, 500);
        },
    },

    beforeUnmount() {
        if (this.map) {
            this.map.closePopup();
        }

        if (this.markersLayer) {
            this.markersLayer.eachLayer((layer) => {
                if (layer.isPopupOpen && layer.isPopupOpen()) {
                    layer.closePopup();
                }

                if (layer.getPopup()) {
                    layer.unbindPopup();
                }

                if (layer._markerApp) {
                    layer._markerApp.unmount();
                    layer._markerApp = null;
                }
                if (layer._popupApp) {
                    layer._popupApp.unmount();
                    layer._popupApp = null;
                }

                layer.off();
            });

            this.markersLayer.clearLayers();
            this.markersLayer = null;
        }

        if (this.boundsChangeTimeout) {
            clearTimeout(this.boundsChangeTimeout);
            this.boundsChangeTimeout = null;
        }

        if (this.map) {
            this.map.off();
            this.map.remove();
            this.map = null;
        }

        this.markers = {};
    },
};
</script>

<style scoped>
.search-map-container {
    position: relative;
    width: 100%;
    height: 100%;
}

.map-wrapper {
    width: 100%;
    height: 100%;
    z-index: 0;
    overflow: hidden !important;
}

.map-wrapper .leaflet-popup-pane {
    overflow: hidden !important;
}

.map-loading {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(255, 255, 255, 0.95);
    padding: 1.5rem 2rem;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    z-index: 1001;
}

.spinner {
    border: 3px solid #f3f4f6;
    border-top: 3px solid #3b82f6;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
    margin-bottom: 0.75rem;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}
</style>

<style>
/* Custom Popup Styles */
.custom-popup .leaflet-popup-content-wrapper {
    border-radius: 12px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    animation: none !important;
    transition: none !important;
}

.custom-popup .leaflet-popup-content {
    margin: 0;
    width: 280px !important;
    max-width: 280px !important;
}

.custom-popup .leaflet-popup-tip-container {
    display: none;
}

.custom-popup .leaflet-popup-close-button {
    display: none;
}

.leaflet-zoom-anim .custom-popup {
    opacity: 0 !important;
    pointer-events: none !important;
}

.custom-popup,
.custom-popup * {
    transition: none !important;
    animation: none !important;
}
</style>
