<template>
    <div class="search-map-container">
        <div ref="mapContainer" class="map-wrapper"></div>

        <!-- Loading Indicator -->
        <div v-if="loading" class="map-loading">
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
                <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
            </button>

            <!-- Zoom Out -->
            <button
                @click="zoomOut"
                class="w-10 h-10 bg-white dark:bg-gray-800 rounded-lg shadow-lg flex items-center justify-center hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
            >
                <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                </svg>
            </button>

            <!-- Recenter -->
            <button
                @click="recenterMap"
                class="w-10 h-10 bg-white dark:bg-gray-800 rounded-lg shadow-lg flex items-center justify-center hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
            >
                <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </button>
        </div>

        <!-- Results Count Badge -->
        <div class="absolute bottom-4 left-1/2 -translate-x-1/2 z-[1000]">
            <div class="px-4 py-2 bg-white dark:bg-gray-800 rounded-full shadow-lg border border-gray-200 dark:border-gray-700">
                <span class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ results.length }} properties in view
                </span>
            </div>
        </div>
    </div>
</template>

<script>
import { mapActions, mapState } from "vuex";
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import 'leaflet.markercluster';
import 'leaflet.markercluster/dist/MarkerCluster.css';
import 'leaflet.markercluster/dist/MarkerCluster.Default.css';

// Fix for default marker icon
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon-2x.png',
    iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon.png',
    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
});

export default {
    name: 'SearchMap',
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
        currentMapBounds: {
            type: Object,
            default: null,
        },
    },
    computed: {
        ...mapState("ui", ["selectedCurrency"]),
    },
    data() {
        return {
            map: null,
            markers: {},
            markerCluster: null,
            loading: false,
            boundsChangeTimeout: null,
            userInteracted: false,
            isInitialLoad: true,
            lastEmittedBounds: null,
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
        ...mapActions("search", ["searchListings"]),

        initializeMap() {
            // Initialize map
            this.map = L.map(this.$refs.mapContainer, {
                center: [this.center.lat, this.center.lng],
                zoom: this.zoom,
                zoomControl: false,
                attributionControl: true,
            });

            // Add tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 19,
            }).addTo(this.map);

            // Initialize marker cluster group
            this.markerCluster = L.markerClusterGroup({
                maxClusterRadius: 60,
                spiderfyOnMaxZoom: true,
                showCoverageOnHover: false,
                zoomToBoundsOnClick: true,
                iconCreateFunction: (cluster) => {
                    const count = cluster.getChildCount();
                    let className = 'marker-cluster-small';
                    if (count >= 10) className = 'marker-cluster-medium';
                    if (count >= 50) className = 'marker-cluster-large';

                    return L.divIcon({
                        html: `<div class="marker-cluster-inner">${count}</div>`,
                        className: `marker-cluster ${className}`,
                        iconSize: L.point(40, 40),
                    });
                },
            });

            this.map.addLayer(this.markerCluster);

            // Track user interaction
            this.map.on('dragstart zoomstart', () => {
                this.userInteracted = true;
            });

            // Emit bounds after map movement stops
            this.map.on('moveend', () => {
                // Skip first moveend that is triggered automaticaly
                if (this.isInitialLoad) {
                    this.isInitialLoad = false;
                    return;
                }

                // only if user moved map
                if (this.userInteracted) {
                    this.debouncedEmitBounds();
                    this.userInteracted = false;
                }
            });
        },

        updateMarkers(results) {
            // Clear existing markers
            this.markerCluster.clearLayers();
            this.markers = {};

            if (!results || results.length === 0) return;

            // Add new markers
            results.forEach((accommodation) => {
                if (accommodation.coordinates?.latitude && accommodation.coordinates?.longitude) {
                    const marker = this.createMarker(accommodation);
                    this.markers[accommodation.id] = marker;
                    this.markerCluster.addLayer(marker);
                }
            });

            // Fit bounds only on initial load
            if (!this.currentMapBounds && results.length > 0) {
                const bounds = this.markerCluster.getBounds();
                if (bounds.isValid()) {
                    this.map.fitBounds(bounds, { padding: [50, 50] });
                }
            }
        },

        createMarker(accommodation) {
            const customIcon = L.divIcon({
                className: 'custom-marker',
                html: `
                    <div class="marker-pin ${accommodation.id === this.hoveredCardId ? 'marker-pin-hovered' : ''}">
                        <div class="marker-price">
                            ${accommodation.price} ${this.selectedCurrency.symbol}
                        </div>
                    </div>
                `,
                iconSize: [60, 40],
                iconAnchor: [30, 40],
            });

            const marker = L.marker([accommodation.coordinates.latitude, accommodation.coordinates.longitude], {
                icon: customIcon,
            });

            // Add popup
            marker.bindPopup(this.createPopupContent(accommodation), {
                maxWidth: 250,
                className: 'custom-popup',
            });

            // Add event listeners
            marker.on('mouseover', () => {
                this.$emit('marker-hover', accommodation.id);
            });

            marker.on('mouseout', () => {
                this.$emit('marker-hover', null);
            });

            marker.on('click', () => {
                this.$emit('marker-click', accommodation.id);
            });

            return marker;
        },

        createPopupContent(accommodation) {
            return `
                <div class="p-2">
                    <div class="font-semibold text-sm mb-1">${accommodation.title}</div>
                    <div class="text-xs text-gray-600 mb-1">${accommodation.location || ''}</div>
                    <div class="flex items-center justify-between">
                        <div class="text-sm font-bold">${accommodation.price} ${this.selectedCurrency.symbol}/night</div>
                        ${accommodation.rating ? `
                            <div class="flex items-center text-xs">
                                <svg class="w-3 h-3 text-yellow-400 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                ${accommodation.rating}
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;
        },

        highlightMarker(accommodationId) {
            const marker = this.markers[accommodationId];
            if (!marker) return;

            const icon = marker.getIcon();
            const newHtml = icon.options.html.replace('marker-pin"', 'marker-pin marker-pin-hovered"');

            const highlightedIcon = L.divIcon({
                ...icon.options,
                html: newHtml,
            });

            marker.setIcon(highlightedIcon);
            marker.setZIndexOffset(1000);
        },

        unhighlightMarker(accommodationId) {
            const marker = this.markers[accommodationId];
            if (!marker) return;

            const icon = marker.getIcon();
            const newHtml = icon.options.html.replace(' marker-pin-hovered', '');

            const normalIcon = L.divIcon({
                ...icon.options,
                html: newHtml,
            });

            marker.setIcon(normalIcon);
            marker.setZIndexOffset(0);
        },

        zoomIn() {
            this.userInteracted = true;
            this.map.zoomIn();
        },

        zoomOut() {
            this.userInteracted = true;
            this.map.zoomOut();
        },

        recenterMap() {
            if (this.results.length > 0) {
                const bounds = this.markerCluster.getBounds();
                if (bounds.isValid()) {
                    this.map.fitBounds(bounds, { padding: [50, 50] });
                }
            } else {
                this.map.setView([this.center.lat, this.center.lng], this.zoom);
            }
        },

        getCurrentMapBounds() {
            if (!this.map) return null;

            const bounds = this.map.getBounds();
            const center = this.map.getCenter();

            return {
                northEast: {
                    lat: bounds.getNorthEast().lat,
                    lng: bounds.getNorthEast().lng
                },
                southWest: {
                    lat: bounds.getSouthWest().lat,
                    lng: bounds.getSouthWest().lng
                },
                center: {
                    lat: center.lat,
                    lng: center.lng
                },
                zoom: this.map.getZoom()
            };
        },

        emitMapBounds() {
            const mapBounds = this.getCurrentMapBounds();
            if (!mapBounds) return;

            // Check if the bounds are significantly diff than before
            if (this.areBoundsSimilar(mapBounds, this.lastEmittedBounds)) {
                console.log('Bounds not changed significantly, skipping emit');
                return;
            }

            this.lastEmittedBounds = mapBounds;
            this.$emit('map-bounds-changed', mapBounds);
        },

        areBoundsSimilar(bounds1, bounds2) {
            if (!bounds1 || !bounds2) return false;

            const threshold = 0.001; // ~100m diff

            return (
                Math.abs(bounds1.northEast.lat - bounds2.northEast.lat) < threshold &&
                Math.abs(bounds1.northEast.lng - bounds2.northEast.lng) < threshold &&
                Math.abs(bounds1.southWest.lat - bounds2.southWest.lat) < threshold &&
                Math.abs(bounds1.southWest.lng - bounds2.southWest.lng) < threshold
            );
        },

        debouncedEmitBounds() {
            this.loading = true;

            if (this.boundsChangeTimeout) {
                clearTimeout(this.boundsChangeTimeout);
            }

            // set new timeout
            this.boundsChangeTimeout = setTimeout(() => {
                this.emitMapBounds();
                this.loading = false;
            }, 500);
        },
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
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
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

/* Marker Cluster Styles */
.marker-cluster {
    background-color: rgba(59, 130, 246, 0.6);
    border-radius: 50%;
}

.marker-cluster-inner {
    background-color: rgb(59, 130, 246);
    color: white;
    border-radius: 50%;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 14px;
}

.marker-cluster-small {
    background-color: rgba(34, 197, 94, 0.6);
}

.marker-cluster-small .marker-cluster-inner {
    background-color: rgb(34, 197, 94);
}

.marker-cluster-medium {
    background-color: rgba(251, 146, 60, 0.6);
}

.marker-cluster-medium .marker-cluster-inner {
    background-color: rgb(251, 146, 60);
}

.marker-cluster-large {
    background-color: rgba(239, 68, 68, 0.6);
}

.marker-cluster-large .marker-cluster-inner {
    background-color: rgb(239, 68, 68);
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
