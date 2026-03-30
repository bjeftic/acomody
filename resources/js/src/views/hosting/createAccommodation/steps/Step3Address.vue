<template>
    <div>
        <h1 class="text-3xl font-semibold text-gray-900 dark:text-white mb-2">
            {{ $t('heading') }}
        </h1>

        <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">
            {{ $t('subtitle') }}
        </p>

        <hr />

        <div class="py-4 overflow-auto h-[60vh]">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Left Column - Form -->
                <div class="space-y-5">
                    <!-- Address Search (autocomplete) -->
                    <div class="relative" ref="searchWrapper">
                        <label class="block text-sm font-medium text-gray-900 dark:text-white mb-1">
                            {{ $t('search_address') }}
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input
                                v-model="searchQuery"
                                @input="onSearchInput"
                                @keydown.down.prevent="navigateSuggestions(1)"
                                @keydown.up.prevent="navigateSuggestions(-1)"
                                @keydown.enter.prevent="selectHighlighted"
                                @keydown.escape="closeSuggestions"
                                @focus="onSearchFocus"
                                @blur="onSearchBlur"
                                type="text"
                                :placeholder="$t('search_address_placeholder')"
                                class="w-full pl-10 pr-10 py-2.5 border border-gray-300 rounded-lg text-sm bg-white text-gray-900 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                            />
                            <div v-if="isSearching" class="absolute inset-y-0 right-3 flex items-center">
                                <div class="w-4 h-4 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
                            </div>
                            <button
                                v-else-if="searchQuery"
                                @mousedown.prevent="clearSearch"
                                class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Suggestions Dropdown -->
                        <div
                            v-if="showSuggestions"
                            class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg overflow-hidden"
                        >
                            <template v-if="suggestions.length > 0">
                                <button
                                    v-for="(suggestion, index) in suggestions"
                                    :key="suggestion.place_id"
                                    @mousedown.prevent="selectSuggestion(suggestion)"
                                    @mouseenter="highlightedIndex = index"
                                    :class="[
                                        'w-full text-left px-4 py-3 text-sm border-b border-gray-100 dark:border-gray-700 last:border-0 transition-colors',
                                        highlightedIndex === index
                                            ? 'bg-blue-50 dark:bg-blue-900/30'
                                            : 'hover:bg-gray-50 dark:hover:bg-gray-700',
                                    ]"
                                >
                                    <div class="font-medium text-gray-900 dark:text-white truncate">
                                        {{ formatSuggestionTitle(suggestion) }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 truncate mt-0.5">
                                        {{ formatSuggestionSubtitle(suggestion) }}
                                    </div>
                                </button>
                            </template>
                            <div v-else-if="!isSearching && searchQuery.length >= 3" class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                {{ $t('no_results') }}
                            </div>
                        </div>
                    </div>

                    <!-- Divider -->
                    <div class="flex items-center gap-3">
                        <div class="flex-1 h-px bg-gray-200 dark:bg-gray-700"></div>
                        <span class="text-xs text-gray-400 dark:text-gray-500 whitespace-nowrap">
                            {{ $t('or_fill_manually') }}
                        </span>
                        <div class="flex-1 h-px bg-gray-200 dark:bg-gray-700"></div>
                    </div>

                    <!-- Country -->
                    <fwb-select
                        :model-value="formData.address.country"
                        @update:model-value="handleAddressChange('country', $event)"
                        :options="countryOptions"
                        :label="$t('country')"
                        :placeholder="$t('select_country')"
                    />

                    <!-- Street Address -->
                    <BaseInput
                        :model-value="formData.address.street"
                        @update:model-value="handleAddressChange('street', $event)"
                        @blur="onAddressFieldBlur"
                        :label="$t('street')"
                        :placeholder="$t('street')"
                    />

                    <!-- City -->
                    <BaseInput
                        :model-value="formData.address.city"
                        @update:model-value="handleAddressChange('city', $event)"
                        @blur="onAddressFieldBlur"
                        :label="$t('city')"
                        :placeholder="$t('city')"
                    />

                    <!-- Zip Code -->
                    <BaseInput
                        :model-value="formData.address.zipCode"
                        @update:model-value="handleAddressChange('zipCode', $event)"
                        @blur="onAddressFieldBlur"
                        :label="$t('zip_code')"
                        :placeholder="$t('zip_code')"
                    />

                    <!-- Action Buttons -->
                    <div class="flex flex-wrap gap-2">
                        <BaseButton
                            variant="secondary"
                            size="sm"
                            :disabled="!hasCompleteAddress || isGeocoding"
                            :loading="isGeocoding"
                            @click="searchAddressOnMap"
                        >
                            <svg v-if="!isGeocoding" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            {{ isGeocoding ? $t('searching') : $t('search_on_map') }}
                        </BaseButton>

                        <BaseButton
                            v-if="supportsGeolocation"
                            variant="secondary"
                            size="sm"
                            :disabled="gettingLocation"
                            :loading="gettingLocation"
                            @click="useCurrentLocation"
                        >
                            <svg v-if="!gettingLocation" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            {{ gettingLocation ? $t('getting_location') : $t('use_my_location') }}
                        </BaseButton>
                    </div>

                    <!-- Geocode Error -->
                    <div
                        v-if="geocodeError"
                        class="flex items-start gap-2 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 text-amber-700 dark:text-amber-400 rounded-lg text-sm"
                    >
                        <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        {{ geocodeError }}
                    </div>

                    <!-- Pin Set Indicator -->
                    <div
                        v-if="formData.coordinates.latitude && formData.coordinates.longitude"
                        class="flex items-center gap-3 p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg"
                    >
                        <div class="flex items-center justify-center w-8 h-8 bg-green-100 dark:bg-green-800 rounded-full shrink-0">
                            <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-green-700 dark:text-green-400">
                                {{ $t('pin_set') }}
                            </div>
                            <div class="text-xs text-green-600 dark:text-green-500 font-mono">
                                {{ formData.coordinates.latitude.toFixed(6) }},
                                {{ formData.coordinates.longitude.toFixed(6) }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Map -->
                <div class="map-section">
                    <!-- Map hint -->
                    <p class="flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400 mb-2">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ $t('map_hint') }}
                    </p>
                    <leaflet-map
                        ref="addressMap"
                        :latitude="formData.coordinates.latitude"
                        :longitude="formData.coordinates.longitude"
                        :address="formData.address"
                        @update:latitude="updateCoordinate('latitude', $event)"
                        @update:longitude="updateCoordinate('longitude', $event)"
                        @location-changed="onMapLocationChanged"
                        height="420px"
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

const NOMINATIM_SEARCH_URL = "https://nominatim.openstreetmap.org/search";
const NOMINATIM_REVERSE_URL = "https://nominatim.openstreetmap.org/reverse";
const NOMINATIM_HEADERS = { "User-Agent": "Acomody/1.0" };

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
            // Address autocomplete
            searchQuery: "",
            suggestions: [],
            showSuggestions: false,
            isSearching: false,
            highlightedIndex: -1,
            searchTimeout: null,

            // Location state
            gettingLocation: false,
            isGeocoding: false,
            isReverseGeocoding: false,
            geocodeError: null,
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
                this.formData.address.zipCode,
                this.formData.address.country,
            ]
                .filter(Boolean)
                .join(", ");
        },

        allowedCountryCodes() {
            return this.countryOptions.map((c) => c.value.toLowerCase());
        },
    },
    mounted() {
        // Only center map if no coordinates saved yet (don't auto-request permission)
        if (
            !this.formData.coordinates.latitude ||
            !this.formData.coordinates.longitude
        ) {
            this.setDefaultLocation();
        }
    },
    beforeUnmount() {
        if (this.addressChangeTimeout) {
            clearTimeout(this.addressChangeTimeout);
        }
        if (this.searchTimeout) {
            clearTimeout(this.searchTimeout);
        }
    },
    methods: {
        // ─── Address Autocomplete ──────────────────────────────────────────

        onSearchInput() {
            this.highlightedIndex = -1;
            this.suggestions = [];

            if (this.searchTimeout) {
                clearTimeout(this.searchTimeout);
            }

            if (this.searchQuery.length < 3) {
                this.showSuggestions = false;
                return;
            }

            this.searchTimeout = setTimeout(() => {
                this.fetchSuggestions(this.searchQuery);
            }, 400);
        },

        async fetchSuggestions(query) {
            this.isSearching = true;
            this.showSuggestions = true;

            try {
                const params = new URLSearchParams({
                    format: "json",
                    q: query,
                    limit: "5",
                    addressdetails: "1",
                });

                if (this.allowedCountryCodes.length > 0) {
                    params.set("countrycodes", this.allowedCountryCodes.join(","));
                }

                const response = await fetch(
                    `${NOMINATIM_SEARCH_URL}?${params}`,
                    { headers: NOMINATIM_HEADERS }
                );

                if (!response.ok) {
                    throw new Error("Search failed");
                }

                const data = await response.json();
                this.suggestions = data;
                this.showSuggestions = true;
            } catch (error) {
                console.error("Address search error:", error);
                this.suggestions = [];
            } finally {
                this.isSearching = false;
            }
        },

        selectSuggestion(suggestion) {
            const address = suggestion.address || {};

            const street = [address.house_number, address.road || address.street]
                .filter(Boolean)
                .join(" ");
            const city =
                address.city ||
                address.town ||
                address.village ||
                address.municipality ||
                address.county ||
                "";
            const zipCode = address.postcode || "";
            const countryCode = address.country_code ? address.country_code.toUpperCase() : "";
            const country =
                countryCode && this.allowedCountryCodes.includes(address.country_code.toLowerCase())
                    ? countryCode
                    : "";

            const lat = parseFloat(suggestion.lat);
            const lng = parseFloat(suggestion.lon);

            // Update all address fields at once
            this.$emit("update:form-data", {
                ...this.formData,
                address: {
                    ...this.formData.address,
                    ...(street && { street }),
                    ...(city && { city }),
                    ...(zipCode && { zipCode }),
                    ...(country && { country }),
                },
                coordinates: { latitude: lat, longitude: lng },
            });

            this.latitude = lat;
            this.longitude = lng;

            if (this.$refs.addressMap) {
                this.$refs.addressMap.setLocation(lat, lng);
            }

            // Update search query to show selected address label
            this.searchQuery = this.formatSuggestionTitle(suggestion);
            this.lastGeocodedAddress = this.fullAddress;
            this.geocodeError = null;
            this.closeSuggestions();
        },

        clearSearch() {
            this.searchQuery = "";
            this.suggestions = [];
            this.showSuggestions = false;
            this.highlightedIndex = -1;
        },

        closeSuggestions() {
            this.showSuggestions = false;
            this.highlightedIndex = -1;
        },

        onSearchFocus() {
            if (this.suggestions.length > 0) {
                this.showSuggestions = true;
            }
        },

        onSearchBlur() {
            setTimeout(() => {
                this.closeSuggestions();
            }, 150);
        },

        navigateSuggestions(direction) {
            if (!this.showSuggestions || this.suggestions.length === 0) {
                return;
            }
            const max = this.suggestions.length - 1;
            this.highlightedIndex = Math.max(
                0,
                Math.min(max, this.highlightedIndex + direction)
            );
        },

        selectHighlighted() {
            if (
                this.highlightedIndex >= 0 &&
                this.suggestions[this.highlightedIndex]
            ) {
                this.selectSuggestion(this.suggestions[this.highlightedIndex]);
            }
        },

        formatSuggestionTitle(suggestion) {
            const address = suggestion.address || {};
            const road = address.road || address.street;
            const houseNumber = address.house_number;

            if (road) {
                return [houseNumber, road].filter(Boolean).join(" ");
            }

            // Fallback: first part before comma
            return suggestion.display_name?.split(",")[0] || suggestion.display_name;
        },

        formatSuggestionSubtitle(suggestion) {
            const address = suggestion.address || {};
            const city =
                address.city ||
                address.town ||
                address.village ||
                address.municipality ||
                address.county;
            const country = address.country;

            return [city, country].filter(Boolean).join(", ");
        },

        // ─── Address Form ──────────────────────────────────────────────────

        handleAddressChange(field, value) {
            this.updateAddress(field, value);

            if (this.addressChangeTimeout) {
                clearTimeout(this.addressChangeTimeout);
            }
        },

        onAddressFieldBlur() {
            if (this.addressChangeTimeout) {
                clearTimeout(this.addressChangeTimeout);
            }

            this.addressChangeTimeout = setTimeout(() => {
                if (this.hasCompleteAddress) {
                    const currentAddress = this.fullAddress;
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
            if (field === "latitude" && this.latitude === value) return;
            if (field === "longitude" && this.longitude === value) return;

            if (field === "latitude") this.latitude = value;
            if (field === "longitude") this.longitude = value;

            this.$emit("update:form-data", {
                ...this.formData,
                coordinates: {
                    latitude: this.latitude,
                    longitude: this.longitude,
                },
            });
        },

        // ─── Map Interaction ───────────────────────────────────────────────

        async onMapLocationChanged({ latitude, longitude }) {
            if (!this.isReverseGeocoding) {
                await this.reverseGeocode(latitude, longitude, true);
            }
        },

        setDefaultLocation() {
            const defaultLat = 44.8176;
            const defaultLng = 20.4569;

            this.$nextTick(() => {
                if (this.$refs.addressMap) {
                    this.$refs.addressMap.setLocation(defaultLat, defaultLng);
                }
            });
        },

        // ─── Geocoding ─────────────────────────────────────────────────────

        async geocodeCurrentAddress() {
            if (!this.hasCompleteAddress || this.isGeocoding) return;

            this.isGeocoding = true;
            this.geocodeError = null;
            const addressToGeocode = this.fullAddress;
            this.lastGeocodedAddress = addressToGeocode;

            try {
                const params = new URLSearchParams({
                    format: "json",
                    q: addressToGeocode,
                    limit: "1",
                    addressdetails: "1",
                });

                const response = await fetch(
                    `${NOMINATIM_SEARCH_URL}?${params}`,
                    { headers: NOMINATIM_HEADERS }
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
                    this.geocodeError = this.$t("error_address_not_found");
                }
            } catch (error) {
                console.error("Geocoding error:", error);
                this.geocodeError = this.$t("error_geocoding_failed");
            } finally {
                this.isGeocoding = false;
            }
        },

        async searchAddressOnMap() {
            await this.geocodeCurrentAddress();
        },

        async useCurrentLocation() {
            if (!this.supportsGeolocation) {
                this.geocodeError = this.$t("error_no_geolocation");
                return;
            }

            this.gettingLocation = true;
            this.geocodeError = null;

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

                await this.reverseGeocode(latitude, longitude, true);
            } catch (error) {
                console.error("Error getting location:", error);

                if (error.code === error.PERMISSION_DENIED) {
                    this.geocodeError = this.$t("error_location_denied");
                } else if (error.code === error.POSITION_UNAVAILABLE) {
                    this.geocodeError = this.$t("error_location_unavailable");
                } else if (error.code === error.TIMEOUT) {
                    this.geocodeError = this.$t("error_location_timeout");
                } else {
                    this.geocodeError = this.$t("error_location_generic");
                }
            } finally {
                this.gettingLocation = false;
            }
        },

        async reverseGeocode(latitude, longitude, updateForm = false) {
            if (this.isReverseGeocoding) return;

            this.isReverseGeocoding = true;

            try {
                const params = new URLSearchParams({
                    format: "json",
                    lat: latitude,
                    lon: longitude,
                    addressdetails: "1",
                });

                const response = await fetch(
                    `${NOMINATIM_REVERSE_URL}?${params}`,
                    { headers: NOMINATIM_HEADERS }
                );

                if (!response.ok) {
                    throw new Error("Reverse geocoding failed");
                }

                const data = await response.json();

                if (data && data.address && updateForm) {
                    const address = data.address;
                    const updates = {};

                    const road = address.road || address.street;
                    if (road) {
                        updates.street = [address.house_number, road]
                            .filter(Boolean)
                            .join(" ");
                    }

                    const city =
                        address.city ||
                        address.town ||
                        address.village ||
                        address.municipality;
                    if (city) {
                        updates.city = city;
                    }

                    if (address.postcode) {
                        updates.zipCode = address.postcode;
                    }

                    if (address.country_code) {
                        const countryCode = address.country_code.toUpperCase();
                        if (this.allowedCountryCodes.includes(address.country_code.toLowerCase())) {
                            updates.country = countryCode;
                        }
                    }

                    if (Object.keys(updates).length > 0) {
                        this.$emit("update:form-data", {
                            ...this.formData,
                            address: {
                                ...this.formData.address,
                                ...updates,
                            },
                        });
                    }

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

@media (max-width: 1023px) {
    .map-section {
        position: relative;
    }
}
</style>

<i18n lang="yaml">
en:
  heading: "Step 3 — Where's your place located?"
  subtitle: "Your address is only shared with guests after they've made a reservation."
  search_address: Search address
  search_address_placeholder: "Type address, city or place name..."
  no_results: No results found. Try a different search.
  or_fill_manually: or fill in manually
  country: Country
  select_country: Select a country
  street: Street address
  city: City
  zip_code: Zip code
  searching: Searching...
  search_on_map: Search on Map
  getting_location: Getting Location...
  use_my_location: Use My Location
  map_hint: Click on the map or drag the pin to fine-tune your exact location
  pin_set: Pin location set
  error_no_geolocation: Geolocation is not supported by your browser
  error_location_denied: Location permission denied. Please allow location access in your browser settings.
  error_location_unavailable: Location information is unavailable.
  error_location_timeout: Location request timed out. Please try again.
  error_location_generic: Unable to get your location
  error_address_not_found: Address not found. Try a more specific search or adjust the pin manually.
  error_geocoding_failed: Could not search for address. Please try again.
sr:
  heading: "Korak 3 — Gde se nalazi vaš smeštaj?"
  subtitle: Vaša adresa se deli samo s gostima nakon što su napravili rezervaciju.
  search_address: Pretraži adresu
  search_address_placeholder: "Unesite adresu, grad ili naziv mesta..."
  no_results: Nema rezultata. Pokušajte drugačiju pretragu.
  or_fill_manually: ili popunite ručno
  country: Zemlja
  select_country: Odaberite zemlju
  street: Ulica i broj
  city: Grad
  zip_code: Poštanski broj
  searching: Pretraživanje...
  search_on_map: Pretraži na mapi
  getting_location: Dobijanje lokacije...
  use_my_location: Koristi moju lokaciju
  map_hint: Kliknite na mapu ili prevucite pin radi preciznog određivanja lokacije
  pin_set: Lokacija označena
  error_no_geolocation: Geolokacija nije podržana u vašem pregledaču
  error_location_denied: Pristup lokaciji je odbijen. Dozvolite pristup lokaciji u postavkama pregledača.
  error_location_unavailable: Informacije o lokaciji nisu dostupne.
  error_location_timeout: Zahtev za lokacijom je istekao. Pokušajte ponovo.
  error_location_generic: Nije moguće dobiti vašu lokaciju
  error_address_not_found: Adresa nije pronađena. Pokušajte precizniju pretragu ili ručno pomerite pin.
  error_geocoding_failed: Pretraga adrese nije uspela. Pokušajte ponovo.
hr:
  heading: "Korak 3 — Gdje se nalazi vaš smještaj?"
  subtitle: Vaša adresa se dijeli samo s gostima nakon što su napravili rezervaciju.
  search_address: Pretraži adresu
  search_address_placeholder: "Unesite adresu, grad ili naziv mjesta..."
  no_results: Nema rezultata. Pokušajte drugačiju pretragu.
  or_fill_manually: ili ispunite ručno
  country: Zemlja
  select_country: Odaberite zemlju
  street: Ulica i broj
  city: Grad
  zip_code: Poštanski broj
  searching: Pretraživanje...
  search_on_map: Pretraži na karti
  getting_location: Dobivanje lokacije...
  use_my_location: Koristi moju lokaciju
  map_hint: Kliknite na kartu ili povucite pin za precizno određivanje lokacije
  pin_set: Lokacija označena
  error_no_geolocation: Geolokacija nije podržana u vašem pregledniku
  error_location_denied: Pristup lokaciji je odbijen. Dopustite pristup lokaciji u postavkama preglednika.
  error_location_unavailable: Informacije o lokaciji nisu dostupne.
  error_location_timeout: Zahtjev za lokacijom je istekao. Pokušajte ponovo.
  error_location_generic: Nije moguće dobiti vašu lokaciju
  error_address_not_found: Adresa nije pronađena. Pokušajte precizniju pretragu ili ručno pomakните pin.
  error_geocoding_failed: Pretraga adrese nije uspjela. Pokušajte ponovo.
mk:
  heading: "Чекор 3 — Каде се наоѓа вашиот простор?"
  subtitle: Вашата адреса се споделува само со гостите откако ќе направат резервација.
  search_address: Пребарај адреса
  search_address_placeholder: "Внесете адреса, град или назив на место..."
  no_results: Нема резултати. Обидете се со друга пребарување.
  or_fill_manually: или пополнете рачно
  country: Земја
  select_country: Одберете земја
  street: Улица и број
  city: Град
  zip_code: Поштенски број
  searching: Пребарување...
  search_on_map: Пребарај на карта
  getting_location: Добивање на локација...
  use_my_location: Користи ја мојата локација
  map_hint: Кликнете на картата или повлечете го пинот за прецизно одредување на локацијата
  pin_set: Локацијата е означена
  error_no_geolocation: Геолокацијата не е поддржана во вашиот прелистувач
  error_location_denied: Пристапот до локацијата е одбиен. Дозволете пристап до локацијата во поставките на прелистувачот.
  error_location_unavailable: Информациите за локацијата не се достапни.
  error_location_timeout: Барањето за локација истече. Обидете се повторно.
  error_location_generic: Не е можно да се добие вашата локација
  error_address_not_found: Адресата не е пронајдена. Обидете се со попрецизна пребарување или рачно поместете го пинот.
  error_geocoding_failed: Пребарувањето на адресата не успеа. Обидете се повторно.
sl:
  heading: "Korak 3 — Kje se nahaja vaš prostor?"
  subtitle: Vaš naslov se deli z gosti šele po tem, ko so opravili rezervacijo.
  search_address: Iskanje naslova
  search_address_placeholder: "Vnesite naslov, mesto ali ime kraja..."
  no_results: Ni rezultatov. Poskusite z drugačnim iskanjem.
  or_fill_manually: ali izpolnite ročno
  country: Država
  select_country: Izberite državo
  street: Ulica in hišna številka
  city: Mesto
  zip_code: Poštna številka
  searching: Iskanje...
  search_on_map: Poišči na zemljevidu
  getting_location: Pridobivanje lokacije...
  use_my_location: Uporabi mojo lokacijo
  map_hint: Kliknite na zemljevid ali povlecite pin za natančno določitev lokacije
  pin_set: Lokacija označena
  error_no_geolocation: Vaš brskalnik ne podpira geolokacije
  error_location_denied: Dostop do lokacije je zavrnjen. Dovolite dostop do lokacije v nastavitvah brskalnika.
  error_location_unavailable: Informacije o lokaciji niso na voljo.
  error_location_timeout: Zahteva za lokacijo je potekla. Poskusite znova.
  error_location_generic: Vaše lokacije ni mogoče pridobiti
  error_address_not_found: Naslov ni bil najden. Poskusite natančnejše iskanje ali ročno premaknite pin.
  error_geocoding_failed: Iskanje naslova ni uspelo. Poskusite znova.
</i18n>
