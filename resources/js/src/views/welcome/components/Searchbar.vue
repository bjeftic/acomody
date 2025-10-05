<template>
    <div>
        <n-config-provider :theme="theme">
            <div>
                <!-- Search Bar in One Row -->
                <div class="flex items-end gap-px border border-gray-300 ring-4 ring-secondary-400 rounded-[32px] p-1 flex-wrap">
                    <!-- Destinacija -->
                    <div class="search-field destination-field">
                        <n-select
                            round
                            strong
                            v-model:value="searchForm.destination"
                            placeholder="Where to?"
                            :options="destinationOptions"
                            :bordered="false"
                            filterable
                            clearable
                            size="large"
                        />
                    </div>

                    <!-- Date Range Picker -->
                    <div class="search-field date-range-field" @click="toggleCalendar">
                        <div class="date-display">
                            <div class="date-section">
                                <span v-if="searchForm.checkIn && searchForm.checkOut" class="date-value">
                                    {{ searchForm.checkIn ? formatDate(searchForm.checkIn) : 'Add date' }} - {{ searchForm.checkOut ? formatDate(searchForm.checkOut) : 'Add date' }}
                                </span>
                                <span v-else class="date-value text-gray-400">Add date</span>
                            </div>
                        </div>

                        <!-- Custom Dual Month Calendar -->
                        <div class="calendar-dropdown" v-show="showCalendar" @click.stop>
                            <div class="calendar-header">
                                <button @click="previousMonth" class="nav-button">
                                    <ChevronLeftIcon />
                                </button>
                                <div class="month-display">
                                    <span>{{ getMonthYear(currentMonth) }}</span>
                                    <span>{{ getMonthYear(nextMonth) }}</span>
                                </div>
                                <button @click="nextMonth" class="nav-button">
                                    <ChevronRightIcon />
                                </button>
                            </div>

                            <div class="calendar-body">
                                <!-- First Month -->
                                <div class="month-calendar">
                                    <div class="weekdays">
                                        <div class="weekday" v-for="day in weekDays" :key="day">{{ day }}</div>
                                    </div>
                                    <div class="days-grid">
                                        <div
                                            v-for="day in getMonthDays(currentMonth)"
                                            :key="`${currentMonth.getFullYear()}-${currentMonth.getMonth()}-${day.date}`"
                                            class="day-cell"
                                            :class="getDayClasses(day)"
                                            @click="selectDate(day)"
                                        >
                                            {{ day.date }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Second Month -->
                                <div class="month-calendar">
                                    <div class="weekdays">
                                        <div class="weekday" v-for="day in weekDays" :key="day">{{ day }}</div>
                                    </div>
                                    <div class="days-grid">
                                        <div
                                            v-for="day in getMonthDays(nextMonth)"
                                            :key="`${nextMonth.getFullYear()}-${nextMonth.getMonth()}-${day.date}`"
                                            class="day-cell"
                                            :class="getDayClasses(day)"
                                            @click="selectDate(day)"
                                        >
                                            {{ day.date }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="calendar-footer">
                                <button @click="clearDates" class="clear-button">Clear dates</button>
                                <button @click="closeCalendar" class="close-button">Close</button>
                            </div>
                        </div>
                    </div>

                    <!-- Gosti i deca dropdown -->
                    <div class="search-field guests-field" @click="toggleGuestsDropdown">
                        <div class="guests-selector">
                            <span class="guests-text">{{ guestsDisplayText }}</span>
                            <n-icon class="dropdown-icon">
                                <ChevronDownIcon />
                            </n-icon>
                        </div>

                        <!-- Custom dropdown -->
                        <div class="guests-dropdown" v-show="showGuestsDropdown" @click.stop>
                            <!-- Odrasli -->
                            <div class="guest-row">
                                <div class="guest-info">
                                    <div class="guest-type">Adults</div>
                                    <div class="guest-description">From 13 years and older</div>
                                </div>
                                <div class="counter-controls">
                                    <n-button circle size="small" :disabled="searchForm.adults <= 1" @click="decrementAdults">
                                        -
                                    </n-button>
                                    <span class="counter-value">{{ searchForm.adults }}</span>
                                    <n-button circle size="small" :disabled="searchForm.adults >= 10" @click="incrementAdults">
                                        +
                                    </n-button>
                                </div>
                            </div>

                            <!-- Deca -->
                            <div class="guest-row">
                                <div class="guest-info">
                                    <div class="guest-type">Children</div>
                                    <div class="guest-description">From 2 to 12 years</div>
                                </div>
                                <div class="counter-controls">
                                    <n-button circle size="small" :disabled="searchForm.children <= 0" @click="decrementChildren">
                                        -
                                    </n-button>
                                    <span class="counter-value">{{ searchForm.children }}</span>
                                    <n-button circle size="small" :disabled="searchForm.children >= 10" @click="incrementChildren">
                                        +
                                    </n-button>
                                </div>
                            </div>

                            <!-- Bebe -->
                            <div class="guest-row">
                                <div class="guest-info">
                                    <div class="guest-type">Infants</div>
                                    <div class="guest-description">Under 2 years</div>
                                </div>
                                <div class="counter-controls">
                                    <n-button circle size="small" :disabled="searchForm.infants <= 0" @click="decrementInfants">
                                        -
                                    </n-button>
                                    <span class="counter-value">{{ searchForm.infants }}</span>
                                    <n-button circle size="small" :disabled="searchForm.infants >= 5" @click="incrementInfants">
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
            </div>
        </n-config-provider>
    </div>
</template>

<script setup>
import { ref, computed, h, onMounted, onUnmounted } from "vue";

// Theme
const theme = ref(null);

// Form validation messages
const validationMessage = ref("");

// Dropdown visibility
const showGuestsDropdown = ref(false);
const showCalendar = ref(false);

// Calendar state
const currentMonth = ref(new Date());
const nextMonth = ref(new Date(new Date().getFullYear(), new Date().getMonth() + 1, 1));
const selectionMode = ref('checkin'); // 'checkin' or 'checkout'

// Icons
const SearchIcon = {
    render() {
        return h("svg", { width: "20", height: "20", viewBox: "0 0 24 24", fill: "currentColor" }, [
            h("path", { d: "M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z" })
        ]);
    },
};

const ChevronDownIcon = {
    render() {
        return h("svg", { width: "16", height: "16", viewBox: "0 0 24 24", fill: "currentColor" }, [
            h("path", { d: "M7.41 8.84L12 13.42l4.59-4.58L18 10.25l-6 6-6-6z" })
        ]);
    },
};

const ChevronLeftIcon = {
    render() {
        return h("svg", { width: "20", height: "20", viewBox: "0 0 24 24", fill: "currentColor" }, [
            h("path", { d: "M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z" })
        ]);
    },
};

const ChevronRightIcon = {
    render() {
        return h("svg", { width: "20", height: "20", viewBox: "0 0 24 24", fill: "currentColor" }, [
            h("path", { d: "M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z" })
        ]);
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

// Destination options
const destinationOptions = [
    { label: "Belgrade", value: "beograd" },
    { label: "Novi Sad", value: "novi-sad" },
    { label: "Niš", value: "nis" },
    { label: "Kopaonik", value: "kopaonik" },
    { label: "Zlatibor", value: "zlatibor" },
    { label: "Tara", value: "tara" },
    { label: "Vrnjačka Banja", value: "vrnjacka-banja" },
    { label: "Sokobanja", value: "sokobanja" },
    { label: "Bukovička Banja", value: "bukovicka-banja" },
];

// Calendar constants
const weekDays = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];
const months = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
];

// Computed properties
const guestsDisplayText = computed(() => {
    const totalGuests = searchForm.value.adults + searchForm.value.children;
    let text = `${totalGuests} ${totalGuests === 1 ? "guest" : "guests"}`;
    if (searchForm.value.infants > 0) {
        text += `, ${searchForm.value.infants} ${searchForm.value.infants === 1 ? "infant" : "infants"}`;
    }
    return text;
});

const isFormValid = computed(() => {
    return (
        searchForm.value.destination &&
        searchForm.value.checkIn &&
        searchForm.value.checkOut &&
        searchForm.value.adults > 0 &&
        searchForm.value.checkOut > searchForm.value.checkIn
    );
});

// Calendar functions
const updateNextMonth = () => {
    nextMonth.value = new Date(currentMonth.value.getFullYear(), currentMonth.value.getMonth() + 1, 1);
};

const previousMonth = () => {
    currentMonth.value = new Date(currentMonth.value.getFullYear(), currentMonth.value.getMonth() - 1, 1);
    updateNextMonth();
};

const nextMonth_nav = () => {
    currentMonth.value = new Date(currentMonth.value.getFullYear(), currentMonth.value.getMonth() + 1, 1);
    updateNextMonth();
};

const getMonthYear = (date) => {
    return `${months[date.getMonth()]} ${date.getFullYear()}`;
};

const getMonthDays = (month) => {
    const year = month.getFullYear();
    const monthIndex = month.getMonth();
    const firstDay = new Date(year, monthIndex, 1);
    const lastDay = new Date(year, monthIndex + 1, 0);
    const startDate = new Date(firstDay);
    startDate.setDate(startDate.getDate() - firstDay.getDay());

    const days = [];
    const current = new Date(startDate);

    for (let i = 0; i < 42; i++) {
        const isCurrentMonth = current.getMonth() === monthIndex;
        const isPast = current < new Date().setHours(0, 0, 0, 0);

        days.push({
            date: current.getDate(),
            fullDate: new Date(current),
            isCurrentMonth,
            isPast,
            timestamp: current.getTime()
        });

        current.setDate(current.getDate() + 1);
    }

    return days;
};

const getDayClasses = (day) => {
    const classes = [];

    if (!day.isCurrentMonth) classes.push('other-month');
    if (day.isPast) classes.push('disabled');

    const dayTime = day.timestamp;
    const checkInTime = searchForm.value.checkIn;
    const checkOutTime = searchForm.value.checkOut;

    if (checkInTime && dayTime === checkInTime) classes.push('selected', 'checkin');
    if (checkOutTime && dayTime === checkOutTime) classes.push('selected', 'checkout');

    if (checkInTime && checkOutTime && dayTime > checkInTime && dayTime < checkOutTime) {
        classes.push('in-range');
    }

    if (checkInTime && !checkOutTime && dayTime > checkInTime) {
        classes.push('hoverable');
    }

    return classes;
};

const selectDate = (day) => {
    if (day.isPast || !day.isCurrentMonth) return;

    const timestamp = day.timestamp;

    if (!searchForm.value.checkIn || (searchForm.value.checkIn && searchForm.value.checkOut)) {
        // Start new selection
        searchForm.value.checkIn = timestamp;
        searchForm.value.checkOut = null;
        selectionMode.value = 'checkout';
    } else if (searchForm.value.checkIn && !searchForm.value.checkOut) {
        // Complete selection
        if (timestamp > searchForm.value.checkIn) {
            searchForm.value.checkOut = timestamp;
            setTimeout(() => {
                closeCalendar();
            }, 300);
        } else {
            // If selected date is before check-in, restart selection
            searchForm.value.checkIn = timestamp;
            searchForm.value.checkOut = null;
        }
    }
};

const toggleCalendar = () => {
    showCalendar.value = !showCalendar.value;
    if (showCalendar.value) {
        // Reset to current month when opening
        currentMonth.value = new Date();
        updateNextMonth();
    }
};

const closeCalendar = () => {
    showCalendar.value = false;
};

const clearDates = () => {
    searchForm.value.checkIn = null;
    searchForm.value.checkOut = null;
    selectionMode.value = 'checkin';
};

// Guest functions
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

const toggleGuestsDropdown = () => {
    showGuestsDropdown.value = !showGuestsDropdown.value;
};

const formatDate = (timestamp) => {
    if (!timestamp) return "";
    return new Date(timestamp).toLocaleDateString("en-US", {
        month: 'short',
        day: 'numeric'
    });
};

// Search handler
const handleSearch = () => {
    //
};

// Click outside handlers
const handleClickOutside = (event) => {
    const guestsField = document.querySelector(".guests-field");
    const calendarField = document.querySelector(".date-range-field");

    if (guestsField && !guestsField.contains(event.target)) {
        showGuestsDropdown.value = false;
    }

    if (calendarField && !calendarField.contains(event.target)) {
        showCalendar.value = false;
    }
};

onMounted(() => {
    document.addEventListener("click", handleClickOutside);
    updateNextMonth();
});

onUnmounted(() => {
    document.removeEventListener("click", handleClickOutside);
});
</script>

<style scoped>
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
    border-radius: 32px 0 0 32px;
}

.date-range-field {
    flex: 2.5;
    min-width: 280px;
    border-left: 1px solid #e0e0e0;
    cursor: pointer;
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

.date-display {
    display: flex;
    align-items: center;
    height: 36px;
    gap: 12px;
}

.date-section {
    display: flex;
    flex-direction: column;
    gap: 2px;
    flex: 1;
}

.date-label {
    font-size: 12px;
    font-weight: 600;
    color: #262626;
}

.date-value {
    font-size: 16px;
    color: #717171;
}

.date-separator {
    width: 1px;
    height: 24px;
    background: #e0e0e0;
}

/* Calendar Styles */
.calendar-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    background: white;
    border: 1px solid #ddd;
    border-radius: 12px;
    box-shadow: 0 8px 28px rgba(0, 0, 0, 0.12);
    z-index: 1000;
    margin-top: 8px;
    padding: 24px;
    width: 660px;
}

.calendar-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
}

.month-display {
    display: flex;
    gap: 80px;
    font-size: 16px;
    font-weight: 600;
}

.nav-button {
    background: none;
    border: none;
    cursor: pointer;
    padding: 8px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background-color 0.2s;
}

.nav-button:hover {
    background-color: #f7f7f7;
}

.calendar-body {
    display: flex;
    gap: 24px;
}

.month-calendar {
    flex: 1;
}

.month-title {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 16px;
    text-align: center;
}

.weekdays {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 2px;
    margin-bottom: 8px;
}

.weekday {
    text-align: center;
    font-size: 12px;
    font-weight: 600;
    color: #717171;
    padding: 8px 0;
}

.days-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 2px;
}

.day-cell {
    aspect-ratio: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 14px;
    border-radius: 50%;
    transition: all 0.2s;
    position: relative;
}

.day-cell:hover:not(.disabled):not(.other-month) {
    background-color: #f7f7f7;
}

.day-cell.other-month {
    color: #e0e0e0;
    cursor: not-allowed;
}

.day-cell.disabled {
    color: #e0e0e0;
    cursor: not-allowed;
}

.day-cell.selected {
    background-color: #262626;
    color: white;
}

.day-cell.checkin {
    border-radius: 50% 0 0 50%;
}

.day-cell.checkout {
    border-radius: 0 50% 50% 0;
}

.day-cell.in-range {
    background-color: #f0f0f0;
    border-radius: 0;
}

.calendar-footer {
    display: flex;
    justify-content: space-between;
    margin-top: 24px;
    padding-top: 16px;
    border-top: 1px solid #e0e0e0;
}

.clear-button, .close-button {
    background: none;
    border: none;
    cursor: pointer;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 14px;
    transition: background-color 0.2s;
}

.clear-button:hover, .close-button:hover {
    background-color: #f7f7f7;
}

.close-button {
    background-color: #262626;
    color: white;
}

.close-button:hover {
    background-color: #404040;
}

/* Guest dropdown styles */
.guests-selector {
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 36px;
    cursor: pointer;
    padding: 0 4px;
}

.guests-text {
    font-size: 16px;
    color: #262626;
}

.dropdown-icon {
    color: #e0e0e0;
    transition: transform 0.2s;
    font-size: 24px;
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

    .calendar-dropdown {
        width: 95vw;
        left: 50%;
        transform: translateX(-50%);
    }

    .calendar-body {
        flex-direction: column;
        gap: 16px;
    }

    .month-display {
        flex-direction: column;
        gap: 8px;
        text-align: center;
    }
}

@media (max-width: 768px) {
    .guests-dropdown {
        width: 280px;
        right: 0;
        left: auto;
    }

    .calendar-dropdown {
        width: 90vw;
        padding: 16px;
    }
}</style>
