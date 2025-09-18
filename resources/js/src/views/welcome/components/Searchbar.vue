<template>
    <div>
        <n-config-provider :theme="theme">
            <div>
                <!-- Search Bar in One Row -->
                <div class="search-container">
                    <!-- Destinacija -->
                    <div class="search-field destination-field">
                        <label class="field-label">Gde idete?</label>
                        <n-select
                            round
                            strong
                            v-model:value="searchForm.destination"
                            placeholder="Destinacija ili smeštaj"
                            :options="destinationOptions"
                            filterable
                            clearable
                            size="large"
                        />
                    </div>

                    <!-- Check-in datum -->
                    <div class="search-field date-field">
                        <label class="field-label">Datum dolaska</label>
                        <n-date-picker
                            v-model:value="searchForm.checkIn"
                            type="date"
                            placeholder="Dodajte datum"
                            size="large"
                            :is-date-disabled="disablePastDates"
                            format="dd.MM.yyyy"
                        />
                    </div>

                    <!-- Check-out datum -->
                    <div class="search-field date-field">
                        <label class="field-label">Datum odlaska</label>
                        <n-date-picker
                            v-model:value="searchForm.checkOut"
                            type="date"
                            placeholder="Dodajte datum"
                            size="large"
                            :is-date-disabled="disableCheckoutDates"
                            format="dd.MM.yyyy"
                        />
                    </div>

                    <!-- Gosti i deca dropdown -->
                    <div
                        class="search-field guests-field"
                        @click="toggleGuestsDropdown"
                    >
                        <label class="field-label">Gosti</label>
                        <div class="guests-selector">
                            <span class="guests-text">
                                {{ guestsDisplayText }}
                            </span>
                            <n-icon class="dropdown-icon">
                                <ChevronDownIcon />
                            </n-icon>
                        </div>

                        <!-- Custom dropdown -->
                        <div
                            class="guests-dropdown"
                            v-show="showGuestsDropdown"
                            @click.stop
                        >
                            <!-- Odrasli -->
                            <div class="guest-row">
                                <div class="guest-info">
                                    <div class="guest-type">Odrasli</div>
                                    <div class="guest-description">
                                        Od 13 godina naviše
                                    </div>
                                </div>
                                <div class="counter-controls">
                                    <n-button
                                        circle
                                        size="small"
                                        :disabled="searchForm.adults <= 1"
                                        @click="decrementAdults"
                                    >
                                        -
                                    </n-button>
                                    <span class="counter-value">{{
                                        searchForm.adults
                                    }}</span>
                                    <n-button
                                        circle
                                        size="small"
                                        :disabled="searchForm.adults >= 10"
                                        @click="incrementAdults"
                                    >
                                        +
                                    </n-button>
                                </div>
                            </div>

                            <!-- Deca -->
                            <div class="guest-row">
                                <div class="guest-info">
                                    <div class="guest-type">Deca</div>
                                    <div class="guest-description">
                                        Od 2 do 12 godina
                                    </div>
                                </div>
                                <div class="counter-controls">
                                    <n-button
                                        circle
                                        size="small"
                                        :disabled="searchForm.children <= 0"
                                        @click="decrementChildren"
                                    >
                                        -
                                    </n-button>
                                    <span class="counter-value">{{
                                        searchForm.children
                                    }}</span>
                                    <n-button
                                        circle
                                        size="small"
                                        :disabled="searchForm.children >= 10"
                                        @click="incrementChildren"
                                    >
                                        +
                                    </n-button>
                                </div>
                            </div>

                            <!-- Bebe -->
                            <div class="guest-row">
                                <div class="guest-info">
                                    <div class="guest-type">Bebe</div>
                                    <div class="guest-description">
                                        Do 2 godine
                                    </div>
                                </div>
                                <div class="counter-controls">
                                    <n-button
                                        circle
                                        size="small"
                                        :disabled="searchForm.infants <= 0"
                                        @click="decrementInfants"
                                    >
                                        -
                                    </n-button>
                                    <span class="counter-value">{{
                                        searchForm.infants
                                    }}</span>
                                    <n-button
                                        circle
                                        size="small"
                                        :disabled="searchForm.infants >= 5"
                                        @click="incrementInfants"
                                    >
                                        +
                                    </n-button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Search button -->
                    <div class="search-button-container">
                        <n-button
                            round
                            type="primary"
                            size="large"
                            @click="handleSearch"
                            :disabled="!isFormValid"
                            class="search-button"
                        >
                            <template #icon>
                                <n-icon>
                                    <SearchIcon />
                                </n-icon>
                            </template>
                            Search
                        </n-button>
                    </div>
                </div>

                <!-- Validation message -->
                <div v-if="validationMessage" style="margin-top: 16px">
                    <n-alert type="error" :title="validationMessage" />
                </div>

                <!-- Prikaz rezultata pretrage -->
                <div v-if="searchResult" style="margin-top: 20px">
                    <n-alert type="info" title="Parametri pretrage:">
                        <ul style="margin: 0; padding-left: 20px">
                            <li>
                                <strong>Destinacija:</strong>
                                {{
                                    getDestinationLabel(
                                        searchResult.destination
                                    )
                                }}
                            </li>
                            <li>
                                <strong>Dolazak:</strong>
                                {{ formatDate(searchResult.checkIn) }}
                            </li>
                            <li>
                                <strong>Odlazak:</strong>
                                {{ formatDate(searchResult.checkOut) }}
                            </li>
                            <li>
                                <strong>Odrasli:</strong>
                                {{ searchResult.adults }}
                            </li>
                            <li v-if="searchResult.children > 0">
                                <strong>Deca:</strong>
                                {{ searchResult.children }}
                            </li>
                            <li v-if="searchResult.infants > 0">
                                <strong>Bebe:</strong>
                                {{ searchResult.infants }}
                            </li>
                            <li>
                                <strong>Broj noći:</strong>
                                {{
                                    calculateNights(
                                        searchResult.checkIn,
                                        searchResult.checkOut
                                    )
                                }}
                            </li>
                        </ul>
                    </n-alert>
                </div>
            </div>
        </n-config-provider>
    </div>
</template>

<script setup>
import { ref, computed, h, onMounted, onUnmounted } from "vue";
import {
    NConfigProvider,
    NCard,
    NSelect,
    NDatePicker,
    NButton,
    NIcon,
    NAlert,
} from "naive-ui";

// Theme
const theme = ref(null);

// Form validation messages
const validationMessage = ref("");

// Guests dropdown visibility
const showGuestsDropdown = ref(false);

// Search icon component
const SearchIcon = {
    render() {
        return h(
            "svg",
            {
                width: "20",
                height: "20",
                viewBox: "0 0 24 24",
                fill: "currentColor",
            },
            [
                h("path", {
                    d: "M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z",
                }),
            ]
        );
    },
};

const ChevronDownIcon = {
    render() {
        return h(
            "svg",
            {
                width: "16",
                height: "16",
                viewBox: "0 0 24 24",
                fill: "currentColor",
            },
            [
                h("path", {
                    d: "M7.41 8.84L12 13.42l4.59-4.58L18 10.25l-6 6-6-6z",
                }),
            ]
        );
    },
};

// Form data
const searchForm = ref({
    destination: null,
    checkIn: null,
    checkOut: null,
    adults: 2,
    children: 0,
    infants: 0,
});

// Search result
const searchResult = ref(null);

// Destination options
const destinationOptions = [
    { label: "Beograd", value: "beograd" },
    { label: "Novi Sad", value: "novi-sad" },
    { label: "Niš", value: "nis" },
    { label: "Kopaonik", value: "kopaonik" },
    { label: "Zlatibor", value: "zlatibor" },
    { label: "Tara", value: "tara" },
    { label: "Vrnjačka Banja", value: "vrnjacka-banja" },
    { label: "Sokobanja", value: "sokobanja" },
    { label: "Bukovička Banja", value: "bukovicka-banja" },
];

// Computed for guests display text
const guestsDisplayText = computed(() => {
    const totalGuests = searchForm.value.adults + searchForm.value.children;
    let text = `${totalGuests} ${
        totalGuests === 1 ? "gost" : totalGuests < 5 ? "gosta" : "gostiju"
    }`;

    if (searchForm.value.infants > 0) {
        text += `, ${searchForm.value.infants} ${
            searchForm.value.infants === 1 ? "beba" : "beba"
        }`;
    }

    return text;
});

// Form validation
const isFormValid = computed(() => {
    return (
        searchForm.value.destination &&
        searchForm.value.checkIn &&
        searchForm.value.checkOut &&
        searchForm.value.adults > 0 &&
        searchForm.value.checkOut > searchForm.value.checkIn
    );
});

// Date validation functions
const disablePastDates = (timestamp) => {
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    return timestamp < today.getTime();
};

const disableCheckoutDates = (timestamp) => {
    if (!searchForm.value.checkIn) return false;
    return timestamp <= searchForm.value.checkIn;
};

// Guest counter functions
const incrementAdults = () => {
    if (searchForm.value.adults < 10) searchForm.value.adults++;
};

const decrementAdults = () => {
    if (searchForm.value.adults > 1) searchForm.value.adults--;
};

const incrementChildren = () => {
    if (searchForm.value.children < 10) searchForm.value.children++;
};

const decrementChildren = () => {
    if (searchForm.value.children > 0) searchForm.value.children--;
};

const incrementInfants = () => {
    if (searchForm.value.infants < 5) searchForm.value.infants++;
};

const decrementInfants = () => {
    if (searchForm.value.infants > 0) searchForm.value.infants--;
};

// Guests dropdown functions
const toggleGuestsDropdown = () => {
    showGuestsDropdown.value = !showGuestsDropdown.value;
};

const closeGuestsDropdown = () => {
    showGuestsDropdown.value = false;
};

// Click outside handler
const handleClickOutside = (event) => {
    const guestsField = document.querySelector(".guests-field");
    if (guestsField && !guestsField.contains(event.target)) {
        closeGuestsDropdown();
    }
};

onMounted(() => {
    document.addEventListener("click", handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener("click", handleClickOutside);
});

// Helper functions
const getDestinationLabel = (value) => {
    return (
        destinationOptions.find((option) => option.value === value)?.label ||
        value
    );
};

const formatDate = (timestamp) => {
    if (!timestamp) return "";
    return new Date(timestamp).toLocaleDateString("sr-RS");
};

const calculateNights = (checkIn, checkOut) => {
    if (!checkIn || !checkOut) return 0;
    const diffTime = Math.abs(checkOut - checkIn);
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
};

// Search handler
const handleSearch = () => {
    validationMessage.value = "";

    if (!isFormValid.value) {
        validationMessage.value = "Molimo popunite sva potrebna polja";
        return;
    }

    if (searchForm.value.checkOut <= searchForm.value.checkIn) {
        validationMessage.value =
            "Datum odlaska mora biti nakon datuma dolaska";
        return;
    }

    searchResult.value = { ...searchForm.value };
    validationMessage.value = "";
    closeGuestsDropdown();

    // Ovde biste pozvali API za pretragu
    console.log("Search parameters:", searchResult.value);
};
</script>

<style scoped>
.search-container {
    display: flex;
    align-items: flex-end;
    gap: 1px;
    border: 1px solid #e0e0e0;
    border-radius: 4px;
    padding: 4px;
    flex-wrap: wrap;
}

.search-field {
    background: white;
    padding: 12px;
    min-height: 64px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
}

.destination-field {
    flex: 2;
    min-width: 240px;
    border-radius: 4px 0 0 4px;
}

.date-field {
    flex: 1.2;
    min-width: 140px;
    border-left: 1px solid #e0e0e0;
}

.guests-field {
    flex: 1.5;
    min-width: 160px;
    border-left: 1px solid #e0e0e0;
    cursor: pointer;
    position: relative;
}

.search-button-container {
    background: transparent;
    padding: 4px;
}

.field-label {
    font-size: 12px;
    font-weight: 600;
    color: #262626;
    margin-bottom: 4px;
    display: block;
}

.guests-selector {
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 36px;
    cursor: pointer;
    padding: 0 4px;
}

.guests-text {
    font-size: 14px;
    color: #262626;
}

.dropdown-icon {
    color: #717171;
    transition: transform 0.2s;
}

.guests-dropdown {
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
    padding: 16px;
    width: 320px;
    position: absolute;
    top: 100%;
    left: 0;
    z-index: 1000;
    margin-top: 8px;
}

.guest-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 0;
    border-bottom: 1px solid #f0f0f0;
}

.guest-row:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.guest-info {
    flex: 1;
}

.guest-type {
    font-size: 16px;
    font-weight: 600;
    color: #262626;
    margin-bottom: 4px;
}

.guest-description {
    font-size: 14px;
    color: #717171;
}

.counter-controls {
    display: flex;
    align-items: center;
    gap: 16px;
}

.counter-value {
    min-width: 20px;
    text-align: center;
    font-size: 16px;
    font-weight: 600;
}

.search-button {
    height: 56px;
    padding: 0 24px;
    font-weight: 600;
    font-size: 16px;
}

/* Responsive design */
@media (max-width: 1024px) {
    .search-container {
        flex-direction: column;
        align-items: stretch;
    }

    .search-field {
        border-radius: 0 !important;
        border-left: none !important;
        border-bottom: 1px solid #e0e0e0;
    }

    .search-field:first-child {
        border-radius: 4px 4px 0 0 !important;
    }

    .search-field:last-of-type {
        border-bottom: none;
    }

    .search-button-container {
        padding: 4px;
    }
}

@media (max-width: 768px) {
    .guests-dropdown {
        width: 280px;
        right: 0;
        left: auto;
    }
}
</style>
