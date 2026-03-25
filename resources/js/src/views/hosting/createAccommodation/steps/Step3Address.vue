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
                <div class="space-y-6">
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

                    <!-- State/Province & Zip Code -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <BaseInput
                            :model-value="formData.address.state"
                            @update:model-value="handleAddressChange('state', $event)"
                            @blur="onAddressFieldBlur"
                            :label="$t('state')"
                            :placeholder="$t('state')"
                        />
                        <BaseInput
                            :model-value="formData.address.zipCode"
                            @update:model-value="handleAddressChange('zipCode', $event)"
                            @blur="onAddressFieldBlur"
                            :label="$t('zip_code')"
                            :placeholder="$t('zip_code')"
                        />
                    </div>

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

                    <!-- Coordinates Display -->
                    <div
                        v-if="
                            formData.coordinates.latitude &&
                            formData.coordinates.longitude
                        "
                        class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg"
                    >
                        <div class="text-xs text-gray-600 dark:text-gray-400">
                            <strong>{{ $t('coordinates') }}</strong>
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
                alert(this.$t('error_no_geolocation'));
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
                let errorMessage = this.$t('error_location_generic');

                switch (error.code) {
                    case error.PERMISSION_DENIED:
                        errorMessage = this.$t('error_location_denied');
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMessage = this.$t('error_location_unavailable');
                        break;
                    case error.TIMEOUT:
                        errorMessage = this.$t('error_location_timeout');
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

<i18n lang="yaml">
en:
  heading: "Step 3 — Where's your place located?"
  subtitle: "Your address is only shared with guests after they've made a reservation."
  country: Country
  select_country: Select a country
  street: Street address
  city: City
  state: State
  zip_code: Zip code
  searching: Searching...
  search_on_map: Search on Map
  getting_location: Getting Location...
  use_my_location: Use My Location
  coordinates: "Coordinates:"
  error_no_geolocation: Geolocation is not supported by your browser
  error_location_denied: Location permission denied. Please allow location access in your browser settings.
  error_location_unavailable: Location information is unavailable.
  error_location_timeout: Location request timed out. Please try again.
  error_location_generic: Unable to get your location
sr:
  heading: "Korak 3 — Gde se nalazi vaš smeštaj?"
  subtitle: Vaša adresa se deli samo s gostima nakon što su napravili rezervaciju.
  country: Zemlja
  select_country: Odaberite zemlju
  street: Ulica i broj
  city: Grad
  state: Država / Pokrajina
  zip_code: Poštanski broj
  searching: Pretraživanje...
  search_on_map: Pretraži na mapi
  getting_location: Dobijanje lokacije...
  use_my_location: Koristi moju lokaciju
  coordinates: "Koordinate:"
  error_no_geolocation: Geolokacija nije podržana u vašem pregledaču
  error_location_denied: Pristup lokaciji je odbijen. Dozvolite pristup lokaciji u postavkama pregledača.
  error_location_unavailable: Informacije o lokaciji nisu dostupne.
  error_location_timeout: Zahtev za lokacijom je istekao. Pokušajte ponovo.
  error_location_generic: Nije moguće dobiti vašu lokaciju
hr:
  heading: "Korak 3 — Gdje se nalazi vaš smještaj?"
  subtitle: Vaša adresa se dijeli samo s gostima nakon što su napravili rezervaciju.
  country: Zemlja
  select_country: Odaberite zemlju
  street: Ulica i broj
  city: Grad
  state: Država / Pokrajina
  zip_code: Poštanski broj
  searching: Pretraživanje...
  search_on_map: Pretraži na karti
  getting_location: Dobivanje lokacije...
  use_my_location: Koristi moju lokaciju
  coordinates: "Koordinate:"
  error_no_geolocation: Geolokacija nije podržana u vašem pregledniku
  error_location_denied: Pristup lokaciji je odbijen. Dopustite pristup lokaciji u postavkama preglednika.
  error_location_unavailable: Informacije o lokaciji nisu dostupne.
  error_location_timeout: Zahtjev za lokacijom je istekao. Pokušajte ponovo.
  error_location_generic: Nije moguće dobiti vašu lokaciju
mk:
  heading: "Чекор 3 — Каде се наоѓа вашиот простор?"
  subtitle: Вашата адреса се споделува само со гостите откако ќе направат резервација.
  country: Земја
  select_country: Одберете земја
  street: Улица и број
  city: Град
  state: Држава / Покраина
  zip_code: Поштенски број
  searching: Пребарување...
  search_on_map: Пребарај на карта
  getting_location: Добивање на локација...
  use_my_location: Користи ја мојата локација
  coordinates: "Координати:"
  error_no_geolocation: Геолокацијата не е поддржана во вашиот прелистувач
  error_location_denied: Пристапот до локацијата е одбиен. Дозволете пристап до локацијата во поставките на прелистувачот.
  error_location_unavailable: Информациите за локацијата не се достапни.
  error_location_timeout: Барањето за локација истече. Обидете се повторно.
  error_location_generic: Не е можно да се добие вашата локација
sl:
  heading: "Korak 3 — Kje se nahaja vaš prostor?"
  subtitle: Vaš naslov se deli z gosti šele po tem, ko so opravili rezervacijo.
  country: Država
  select_country: Izberite državo
  street: Ulica in hišna številka
  city: Mesto
  state: Regija / Pokrajina
  zip_code: Poštna številka
  searching: Iskanje...
  search_on_map: Poišči na zemljevidu
  getting_location: Pridobivanje lokacije...
  use_my_location: Uporabi mojo lokacijo
  coordinates: "Koordinate:"
  error_no_geolocation: Vaš brskalnik ne podpira geolokacije
  error_location_denied: Dostop do lokacije je zavrnjen. Dovolite dostop do lokacije v nastavitvah brskalnika.
  error_location_unavailable: Informacije o lokaciji niso na voljo.
  error_location_timeout: Zahteva za lokacijo je potekla. Poskusite znova.
  error_location_generic: Vaše lokacije ni mogoče pridobiti
</i18n>
