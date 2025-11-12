<template>
    <div>
        <h1 class="text-3xl font-semibold text-gray-900 dark:text-white mb-2">
            Step: 3 - Where's your place located?
        </h1>

        <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">
            Your address is only shared with guests after they've made a
            reservation.
        </p>

        <hr />

        <div class="py-4 overflow-auto h-[60vh]">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Left Column - Form -->
                <div class="space-y-6">
                    <!-- Country -->
                    <fwb-select
                        :model-value="formData.address.country"
                        @update:model-value="handleAddressChange('country', $event)"
                        :options="countryOptions"
                        label="Country"
                        placeholder="Select a country"
                    />

                    <!-- Street Address -->
                    <fwb-input
                        :model-value="formData.address.street"
                        @update:model-value="handleAddressChange('street', $event)"
                        @blur="onAddressFieldBlur"
                        placeholder="Street address"
                        label="Street address"
                    />

                    <!-- City -->
                    <fwb-input
                        :model-value="formData.address.city"
                        @update:model-value="handleAddressChange('city', $event)"
                        @blur="onAddressFieldBlur"
                        placeholder="City"
                        label="City"
                    />

                    <!-- State/Province & Zip Code -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <fwb-input
                                :model-value="formData.address.state"
                                @update:model-value="handleAddressChange('state', $event)"
                                @blur="onAddressFieldBlur"
                                placeholder="State"
                                label="State"
                            />
                        </div>
                        <div>
                            <fwb-input
                                :model-value="formData.address.zipCode"
                                @update:model-value="handleAddressChange('zipCode', $event)"
                                @blur="onAddressFieldBlur"
                                placeholder="Zip code"
                                label="Zip code"
                            />
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-wrap gap-2">
                        <fwb-button
                            @click="searchAddressOnMap"
                            color="alternative"
                            size="sm"
                            :disabled="!hasCompleteAddress || isGeocoding"
                        >
                            <template #prefix>
                                <svg
                                    v-if="!isGeocoding"
                                    class="w-4 h-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                    />
                                </svg>
                                <svg
                                    v-else
                                    class="w-4 h-4 animate-spin"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"
                                    ></circle>
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                    ></path>
                                </svg>
                            </template>
                            {{ isGeocoding ? "Searching..." : "Search on Map" }}
                        </fwb-button>

                        <fwb-button
                            v-if="supportsGeolocation"
                            @click="useCurrentLocation"
                            color="alternative"
                            size="sm"
                            :disabled="gettingLocation"
                        >
                            <template #prefix>
                                <svg
                                    v-if="!gettingLocation"
                                    class="w-4 h-4"
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
                                <svg
                                    v-else
                                    class="w-4 h-4 animate-spin"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"
                                    ></circle>
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                    ></path>
                                </svg>
                            </template>
                            {{
                                gettingLocation
                                    ? "Getting Location..."
                                    : "Use My Location"
                            }}
                        </fwb-button>
                    </div>

                    <!-- Coordinates Display -->
                    <div
                        v-if="
                            formData.coordinates.latitude &&
                            formData.coordinates.longitude
                        "
                        class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg"
                    >
                        <div class="text-xs text-gray-600 dark:text-gray-400">
                            <strong>Coordinates:</strong>
                            {{ formData.coordinates.latitude.toFixed(6) }},
                            {{ formData.coordinates.longitude.toFixed(6) }}
                        </div>
                    </div>
                </div>

                <!-- Right Column - Map -->
                <div class="map-section">
                    <leaflet-map
                        ref="addressMap"
                        :latitude="formData.coordinates.latitude"
                        :longitude="formData.coordinates.longitude"
                        :address="formData.address"
                        @update:latitude="updateCoordinate('latitude', $event)"
                        @update:longitude="updateCoordinate('longitude', $event)"
                        @location-changed="onMapLocationChanged"
                        height="450px"
                        :zoom="15"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { mapState } from "vuex";
import LeafletMap from "@/src/components/common/LeafletMap.vue";

export default {
    name: "Step3Address",
    components: {
        LeafletMap,
    },
    props: {
        formData: {
            type: Object,
            required: true,
        },
        errors: {
            type: Object,
            default: () => ({}),
        },
    },
    emits: ["update:form-data"],
    data() {
        return {
            gettingLocation: false,
            isGeocoding: false,
            isReverseGeocoding: false,
            isInitialized: false,
            addressChangeTimeout: null,
            lastGeocodedAddress: null,
            latitude: null,
            longitude: null,
        };
    },
    computed: {
        ...mapState("ui", ["countries"]),

        countryOptions() {
            if (!this.countries) return [];
            return this.countries.map((country) => ({
                value: country.iso_code_2,
                name: country.name,
            }));
        },

        hasCompleteAddress() {
            const { street, city, country } = this.formData.address;
            return street && city && country;
        },

        supportsGeolocation() {
            return "geolocation" in navigator;
        },

        fullAddress() {
            return [
                this.formData.address.street,
                this.formData.address.city,
                this.formData.address.state,
                this.formData.address.zipCode,
                this.formData.address.country,
            ]
                .filter(Boolean)
                .join(", ");
        },
    },
    mounted() {
        // Initialize location if not set
        if (
            !this.formData.coordinates.latitude ||
            !this.formData.coordinates.longitude
        ) {
            this.initializeLocation();
        } else {
            this.isInitialized = true;
        }
    },
    beforeUnmount() {
        if (this.addressChangeTimeout) {
            clearTimeout(this.addressChangeTimeout);
        }
    },
    methods: {
        handleAddressChange(field, value) {
            this.updateAddress(field, value);

            // Debounce automatic geocoding when address changes
            if (this.addressChangeTimeout) {
                clearTimeout(this.addressChangeTimeout);
            }
        },

        onAddressFieldBlur() {
            // When user finishes editing an address field, geocode after a delay
            if (this.addressChangeTimeout) {
                clearTimeout(this.addressChangeTimeout);
            }

            this.addressChangeTimeout = setTimeout(() => {
                if (this.hasCompleteAddress && this.isInitialized) {
                    const currentAddress = this.fullAddress;
                    // Only geocode if address has changed
                    if (currentAddress !== this.lastGeocodedAddress) {
                        this.geocodeCurrentAddress();
                    }
                }
            }, 500);
        },

        updateAddress(field, value) {
            this.$emit("update:form-data", {
                ...this.formData,
                address: {
                    ...this.formData.address,
                    [field]: value,
                },
            });
        },

        updateCoordinate(field, value) {
            if(field === "latitude" && this.latitude === value) return;
            if(field === "longitude" && this.longitude === value) return;

            if(field === "latitude") this.latitude = value;
            if(field === "longitude") this.longitude = value;

            this.$emit("update:form-data", {
                ...this.formData,
                coordinates: {
                    latitude: this.latitude,
                    longitude: this.longitude,
                },
            });
        },

        async onMapLocationChanged({ latitude, longitude }) {
            // When user clicks/drags on map, update coordinates and reverse geocode
            if (this.isInitialized && !this.isReverseGeocoding) {
                await this.reverseGeocode(latitude, longitude, true);
            }
        },

        async initializeLocation() {
            if (this.supportsGeolocation) {
                try {
                    const position = await new Promise((resolve, reject) => {
                        navigator.geolocation.getCurrentPosition(
                            resolve,
                            reject,
                            {
                                enableHighAccuracy: false,
                                timeout: 5000,
                                maximumAge: 300000,
                            }
                        );
                    });

                    const { latitude, longitude } = position.coords;

                    this.updateCoordinate("latitude", latitude);
                    this.updateCoordinate("longitude", longitude);

                    if (this.$refs.addressMap) {
                        this.$refs.addressMap.setLocation(latitude, longitude);
                    }

                    // Reverse geocode to populate address fields
                    await this.reverseGeocode(latitude, longitude, true);
                } catch (error) {
                    console.log("Could not get user location:", error.message);
                    this.setDefaultLocation();
                }
            } else {
                this.setDefaultLocation();
            }

            this.isInitialized = true;
        },

        setDefaultLocation() {
            const defaultLat = 44.8176;
            const defaultLng = 20.4569;

            this.updateCoordinate("latitude", defaultLat);
            this.updateCoordinate("longitude", defaultLng);

            if (this.$refs.addressMap) {
                this.$refs.addressMap.setLocation(defaultLat, defaultLng);
            }
        },

        async geocodeCurrentAddress() {
            if (!this.hasCompleteAddress || this.isGeocoding) return;

            this.isGeocoding = true;
            const addressToGeocode = this.fullAddress;
            this.lastGeocodedAddress = addressToGeocode;

            try {
                const response = await fetch(
                    `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(
                        addressToGeocode
                    )}&limit=1&addressdetails=1`,
                    {
                        headers: {
                            "User-Agent": "RentalPropertyApp/1.0",
                        },
                    }
                );

                if (!response.ok) {
                    throw new Error("Geocoding failed");
                }

                const data = await response.json();

                if (data && data.length > 0) {
                    const { lat, lon } = data[0];
                    const latitude = parseFloat(lat);
                    const longitude = parseFloat(lon);

                    this.updateCoordinate("latitude", latitude);
                    this.updateCoordinate("longitude", longitude);

                    if (this.$refs.addressMap) {
                        this.$refs.addressMap.setLocation(latitude, longitude);
                    }
                } else {
                    console.warn("No results found for address:", addressToGeocode);
                }
            } catch (error) {
                console.error("Geocoding error:", error);
            } finally {
                this.isGeocoding = false;
            }
        },

        async searchAddressOnMap() {
            await this.geocodeCurrentAddress();
        },

        async useCurrentLocation() {
            if (!this.supportsGeolocation) {
                alert("Geolocation is not supported by your browser");
                return;
            }

            this.gettingLocation = true;

            try {
                const position = await new Promise((resolve, reject) => {
                    navigator.geolocation.getCurrentPosition(resolve, reject, {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0,
                    });
                });

                const { latitude, longitude } = position.coords;

                this.updateCoordinate("latitude", latitude);
                this.updateCoordinate("longitude", longitude);

                if (this.$refs.addressMap) {
                    this.$refs.addressMap.setLocation(latitude, longitude);
                }

                // Reverse geocode to populate address fields
                await this.reverseGeocode(latitude, longitude, true);
            } catch (error) {
                console.error("Error getting location:", error);
                let errorMessage = "Unable to get your location";

                switch (error.code) {
                    case error.PERMISSION_DENIED:
                        errorMessage =
                            "Location permission denied. Please allow location access in your browser settings.";
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMessage = "Location information is unavailable.";
                        break;
                    case error.TIMEOUT:
                        errorMessage =
                            "Location request timed out. Please try again.";
                        break;
                }

                alert(errorMessage);
            } finally {
                this.gettingLocation = false;
            }
        },

        async reverseGeocode(latitude, longitude, updateForm = false) {
            if (this.isReverseGeocoding) return;

            this.isReverseGeocoding = true;

            try {
                const response = await fetch(
                    `https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}&addressdetails=1`,
                    {
                        headers: {
                            "User-Agent": "RentalPropertyApp/1.0",
                        },
                    }
                );

                if (!response.ok) {
                    throw new Error("Reverse geocoding failed");
                }

                const data = await response.json();

                if (data && data.address && updateForm) {
                    const address = data.address;

                    // Update all address fields with reverse geocoded data
                    const updates = {};

                    if (address.road || address.street) {
                        updates.street = address.road || address.street;
                    }

                    if (address.city || address.town || address.village) {
                        updates.city = address.city || address.town || address.village;
                    }

                    if (address.state) {
                        updates.state = address.state;
                    }

                    if (address.postcode) {
                        updates.zipCode = address.postcode;
                    }

                    if (address.country_code) {
                        updates.country = address.country_code.toUpperCase();
                    }

                    // Update all fields at once
                    Object.keys(updates).forEach(field => {
                        this.updateAddress(field, updates[field]);
                    });

                    // Update last geocoded address to prevent immediate re-geocoding
                    this.$nextTick(() => {
                        this.lastGeocodedAddress = this.fullAddress;
                    });
                }
            } catch (error) {
                console.error("Reverse geocoding error:", error);
            } finally {
                this.isReverseGeocoding = false;
            }
        },
    },
};
</script>

<style scoped>
.map-section {
    position: sticky;
    top: 0;
}

/* Ensure the map is visible */
:deep(.address-map-container) {
    min-height: 450px;
}

/* Responsive: stack on small screens */
@media (max-width: 1023px) {
    .map-section {
        position: relative;
    }
}
</style>
