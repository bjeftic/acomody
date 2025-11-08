<template>
    <div class="address-map-container">
        <div ref="mapContainer" class="map-wrapper"></div>
        <div v-if="loading" class="map-loading">
            <div class="spinner"></div>
            <p>Loading map...</p>
        </div>
    </div>
</template>

<script>
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

// Fix for default marker icon in Leaflet with webpack
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon-2x.png',
    iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon.png',
    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
});

export default {
    name: 'LeafletMap',
    props: {
        latitude: {
            type: [Number, String],
            default: null,
        },
        longitude: {
            type: [Number, String],
            default: null,
        },
        address: {
            type: Object,
            default: () => ({}),
        },
        readonly: {
            type: Boolean,
            default: false,
        },
        zoom: {
            type: Number,
            default: 13,
        },
        height: {
            type: String,
            default: '400px',
        },
    },
    emits: ['update:latitude', 'update:longitude', 'location-changed'],
    data() {
        return {
            map: null,
            marker: null,
            loading: false,
            isUpdatingFromProps: false,
        };
    },
    watch: {
        latitude(newVal) {
            if (newVal && this.longitude && !this.isUpdatingFromProps) {
                this.updateMarkerPosition(parseFloat(newVal), parseFloat(this.longitude), false);
            }
        },
        longitude(newVal) {
            if (newVal && this.latitude && !this.isUpdatingFromProps) {
                this.updateMarkerPosition(parseFloat(this.latitude), parseFloat(newVal), false);
            }
        },
    },
    mounted() {
        this.$nextTick(() => {
            this.initializeMap();

            // If we have coordinates, use them
            if (this.latitude && this.longitude) {
                this.updateMarkerPosition(
                    parseFloat(this.latitude),
                    parseFloat(this.longitude),
                    false
                );
            }
        });
    },
    beforeUnmount() {
        if (this.map) {
            this.map.remove();
            this.map = null;
        }
    },
    methods: {
        initializeMap() {
            // Default center
            const defaultLat = this.latitude || 44.8176;
            const defaultLng = this.longitude || 20.4633;

            this.map = L.map(this.$refs.mapContainer, {
                center: [defaultLat, defaultLng],
                zoom: this.zoom,
                zoomControl: true,
                attributionControl: true,
            });

            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 19,
            }).addTo(this.map);

            // Add marker if coordinates exist
            if (this.latitude && this.longitude) {
                this.addMarker(parseFloat(this.latitude), parseFloat(this.longitude));
            }

            // Add click event if not readonly
            if (!this.readonly) {
                this.map.on('click', this.onMapClick);
            }

            // Set map height
            this.$refs.mapContainer.style.height = this.height;
        },

        onMapClick(e) {
            const { lat, lng } = e.latlng;
            this.updateMarkerPosition(lat, lng, true);
        },

        addMarker(lat, lng) {
            if (this.marker) {
                this.marker.remove();
            }

            this.marker = L.marker([lat, lng], {
                draggable: !this.readonly,
            }).addTo(this.map);

            if (!this.readonly) {
                this.marker.on('dragend', (e) => {
                    const position = e.target.getLatLng();
                    this.updateMarkerPosition(position.lat, position.lng, true);
                });
            }

            // Add popup with coordinates
            this.marker.bindPopup(`
                <div class="font-sans">
                    <strong>Location</strong><br>
                    Lat: ${lat.toFixed(6)}<br>
                    Lng: ${lng.toFixed(6)}
                </div>
            `);
        },

        updateMarkerPosition(lat, lng, emitEvent = true) {
            if (!this.map) return;

            this.map.setView([lat, lng], this.zoom);
            this.addMarker(lat, lng);

            if (emitEvent) {
                this.emitLocationChange(lat, lng);
            }
        },

        emitLocationChange(lat, lng) {
            this.isUpdatingFromProps = true;

            this.$emit('update:latitude', lat);
            this.$emit('update:longitude', lng);
            this.$emit('location-changed', { latitude: lat, longitude: lng });

            this.$nextTick(() => {
                this.isUpdatingFromProps = false;
            });
        },

        async geocodeAddress(address) {
            if (!address) return;

            this.loading = true;

            try {
                const response = await fetch(
                    `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&limit=1`,
                    {
                        headers: {
                            'User-Agent': 'RentalPropertyApp/1.0',
                        },
                    }
                );

                const data = await response.json();

                if (data && data.length > 0) {
                    const { lat, lon } = data[0];
                    const latitude = parseFloat(lat);
                    const longitude = parseFloat(lon);

                    this.updateMarkerPosition(latitude, longitude, true);
                } else {
                    console.warn('No results found for address:', address);
                }
            } catch (error) {
                console.error('Geocoding error:', error);
            } finally {
                this.loading = false;
            }
        },

        // Public method to manually trigger geocoding
        async searchAddress(address) {
            await this.geocodeAddress(address);
        },

        // Public method to set location programmatically
        setLocation(lat, lng) {
            this.updateMarkerPosition(lat, lng, false);
        },
    },
};
</script>

<style scoped>
.address-map-container {
    position: relative;
    width: 100%;
    border-radius: 0.5rem;
    overflow: hidden;
    border: 1px solid #e5e7eb;
}

.map-wrapper {
    width: 100%;
    height: 400px;
    z-index: 0;
}

.map-loading {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.9);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.spinner {
    border: 3px solid #f3f4f6;
    border-top: 3px solid #3b82f6;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
    margin-bottom: 1rem;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.map-loading p {
    color: #6b7280;
    font-size: 0.875rem;
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .address-map-container {
        border-color: #374151;
    }

    .map-loading {
        background: rgba(31, 41, 55, 0.9);
    }

    .map-loading p {
        color: #d1d5db;
    }
}
</style>
