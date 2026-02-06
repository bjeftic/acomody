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
            userInteracted: false, // Flag to track if user interacted in current movement
            isInitialLoad: true, // Flag to skip first automatic moveend event
            lastEmittedBounds: null, // Store last emitted bounds to avoid duplicate emissions
            hasUserEverInteracted: false, // Flag to track if user has ever interacted with the map
            isProgrammaticMove: false, // Flag to distinguish programmatic moves from user interactions
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
    beforeDestroy() {
        if (this.boundsChangeTimeout) {
            clearTimeout(this.boundsChangeTimeout);
        }
        if (this.map) {
            this.map.remove();
            this.map = null;
        }
    },
    methods: {
        ...mapActions("search", ["searchListings", "setLoading"]),

        initializeMap() {
            // Initialize Leaflet map
            this.map = L.map(this.$refs.mapContainer, {
                center: [this.center.lat, this.center.lng],
                zoom: this.zoom,
                zoomControl: false,
                attributionControl: true,
            });

            // Add OpenStreetMap tile layer
            L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                attribution:
                    '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 19,
            }).addTo(this.map);

            this.markersLayer = L.featureGroup().addTo(this.map);

            // Track user drag interactions - only real user drags, not programmatic
            this.map.on("dragstart", () => {
                if (!this.isProgrammaticMove) {
                    this.userInteracted = true;
                    this.hasUserEverInteracted = true;
                }
            });

            // Track user zoom interactions - only real user zooms, not programmatic
            this.map.on("zoomstart", () => {
                if (!this.isProgrammaticMove) {
                    this.userInteracted = true;
                    this.hasUserEverInteracted = true;
                }
            });

            // Emit bounds after map movement stops
            this.map.on("moveend", () => {
                // Skip first moveend that is triggered automatically on map init
                if (this.isInitialLoad) {
                    this.isInitialLoad = false;
                    this.isProgrammaticMove = false;
                    return;
                }

                // Reset programmatic flag and skip emission for programmatic moves
                if (this.isProgrammaticMove) {
                    this.isProgrammaticMove = false;
                    return;
                }

                // Only emit bounds if user has ever interacted and interacted in this movement
                if (this.hasUserEverInteracted && this.userInteracted) {
                    this.debouncedEmitBounds();
                    this.userInteracted = false;
                }
            });
        },
        updateMarkers(results) {
    // Early return if no results
    if (!results || results.length === 0) {
        this.clearMarkers();
        return;
    }

    // Konvertuj sve ID-jeve u stringove za konzistentnost
    const newIds = new Set(results.map((r) => String(r.id)));
    const existingIds = Object.keys(this.markers);

    // Obriši markere koji više ne postoje u results
    existingIds.forEach((id) => {
        if (!newIds.has(String(id))) {
            const marker = this.markers[id];
            if (marker) {
                // Unmount Vue instances
                if (marker._markerApp) {
                    marker._markerApp.unmount();
                }
                if (marker._popupApp) {
                    marker._popupApp.unmount();
                }
                // Remove from layer
                this.markersLayer.removeLayer(marker);
                // Delete from markers object
                delete this.markers[id];
            }
        }
    });

    // Dodaj nove markere
    let validMarkerCount = 0;

    results.forEach((accommodation) => {
        const { coordinates, id } = accommodation;

        if (!coordinates?.latitude || !coordinates?.longitude) {
            return;
        }

        // Ako marker već postoji, preskoči
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

    // Auto-fit bounds samo na prvom učitavanju
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
                });
            }
        } catch (error) {
            console.error("Error fitting bounds:", error);
        }
    }
},

        createMarker(accommodation) {
            // Create component for marker
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

            // Create custom marker for map
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

            // Create component for popup
            const popupContainer = document.createElement("div");
            const popupApp = createApp({
                render: () =>
                    h(MapPopup, {
                        accommodation: accommodation,
                        currency: this.selectedCurrency,
                    }),
            });
            popupApp.mount(popupContainer);

            // Add popup
            marker.bindPopup(popupContainer, {
                maxWidth: 250,
                className: "custom-popup",
            });

            // Event listeners
            marker.on("mouseover", () => {
                this.$emit("marker-hover", accommodation.id);
            });

            marker.on("mouseout", () => {
                this.$emit("marker-hover", null);
            });

            marker.on("click", () => {
                marker.openPopup();
            });

            // Save reverence instance for cleanup
            marker._markerApp = markerApp;
            marker._popupApp = popupApp;

            return marker;
        },

        highlightMarker(accommodationId) {
            const marker = this.markers[accommodationId];
            if (!marker) return;

            const accommodation = this.results.find(
                (r) => r.id === accommodationId,
            );
            if (!accommodation) return;

            // Unmount old Vue instance
            if (marker._markerApp) {
                marker._markerApp.unmount();
            }

            // Create new with hovered
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

            // Unmount old vue instance
            if (marker._markerApp) {
                marker._markerApp.unmount();
            }

            // Create new withoid hover
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
            // Zoom in - user interaction will be tracked by zoomstart event
            // this.clearMarkers();
            this.map.zoomIn();
        },

        zoomOut() {
            // Zoom out - user interaction will be tracked by zoomstart event
            // this.clearMarkers();
            this.map.zoomOut();
        },

        clearMarkers() {
            if (this.markersLayer) {
                this.markersLayer.eachLayer((layer) => {
                    if (layer._markerApp) {
                        layer._markerApp.unmount();
                    }
                    if (layer._popupApp) {
                        layer._popupApp.unmount();
                    }
                });
                this.markersLayer.clearLayers();
            }
            this.markers = {};
        },

        recenterMap() {
            // Recenter map to show all markers or default center
            // Mark as programmatic move to prevent bounds emission
            this.isProgrammaticMove = true;

            if (this.results.length > 0) {
                const bounds = this.markersLayer.getBounds();
                if (bounds.isValid()) {
                    this.map.fitBounds(bounds, { padding: [50, 50] });
                }
            } else {
                this.map.setView([this.center.lat, this.center.lng], this.zoom);
            }
        },

        getCurrentMapBounds() {
            // Get current map bounds and center
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
            // Emit map bounds change event to parent component
            const mapBounds = this.getCurrentMapBounds();
            if (!mapBounds) return;

            // Check if bounds changed significantly to avoid unnecessary API calls
            if (this.areBoundsSimilar(mapBounds, this.lastEmittedBounds)) {
                console.log("Bounds not changed significantly, skipping emit");
                return;
            }

            this.lastEmittedBounds = mapBounds;
            this.$emit("map-bounds-changed", mapBounds);
        },

        areBoundsSimilar(bounds1, bounds2) {
            // Compare two bounds to see if they're similar enough
            // Returns true if difference is less than threshold (~100m)
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
            // Debounce bounds emission to avoid too many API calls
            this.setLoading(true);

            if (this.boundsChangeTimeout) {
                clearTimeout(this.boundsChangeTimeout);
            }

            // Set new timeout to emit bounds after 500ms of no movement
            this.boundsChangeTimeout = setTimeout(() => {
                this.emitMapBounds();
            }, 500);
        },
    },
    beforeDestroy() {
        // Cleanup Vue instances
        if (this.markersLayer) {
            this.markersLayer.eachLayer((layer) => {
                if (layer._markerApp) {
                    layer._markerApp.unmount();
                }
                if (layer._popupApp) {
                    layer._popupApp.unmount();
                }
            });
        }

        if (this.boundsChangeTimeout) {
            clearTimeout(this.boundsChangeTimeout);
        }
        if (this.map) {
            this.map.remove();
            this.map = null;
        }
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
/* Custom Marker Styles */
.custom-marker {
    background: transparent;
    border: none;
}

.marker-pin {
    position: relative;
    transition: all 0.2s ease;
}

.marker-price {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 20px;
    padding: 6px 12px;
    font-size: 14px;
    font-weight: 600;
    color: #1f2937;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    white-space: nowrap;
    transition: all 0.2s ease;
}

.marker-pin-hovered .marker-price {
    background: #1f2937;
    color: white;
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    z-index: 1000;
}

/* Custom Popup Styles */
.custom-popup .leaflet-popup-content-wrapper {
    border-radius: 12px;
    padding: 0;
}

.custom-popup .leaflet-popup-content {
    margin: 0;
}
</style>
