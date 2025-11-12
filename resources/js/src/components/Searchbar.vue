<template>
    <div>
        <div class="w-full max-w-6xl mx-auto p-4">
            <div
                class="flex items-center gap-0 border border-gray-300 rounded-full p-1 shadow-lg"
            >
                <!-- Destination Autocomplete -->
                <div
                    ref="autocompleteField"
                    class="flex-1 min-w-[200px] px-4 border-r border-gray-200 relative"
                >
                    <input
                        v-model="destinationSearch"
                        @input="handleDestinationInput"
                        @focus="showDestinationDropdown = true"
                        @keydown.down="navigateDown"
                        @keydown.up="navigateUp"
                        @keydown.enter="selectHighlighted"
                        type="text"
                        placeholder="Where to?"
                        class="w-full py-1 bg-transparent text-gray-700 placeholder-gray-400 custom-input"
                    />

                    <!-- Dropdown -->
                    <div
                        v-if="showDestinationDropdown && filteredDestinations.length > 0"
                        ref="destinationDropdown"
                        class="absolute top-full left-0 right-0 mt-2 bg-white rounded-lg shadow-xl border border-gray-200 max-h-60 overflow-y-auto"
                    >
                        <div
                            v-for="(option, index) in filteredDestinations"
                            :key="option.id"
                            :class="[
                                'px-4 py-3 cursor-pointer transition',
                                highlightedIndex === index ? 'bg-gray-100' : 'hover:bg-gray-50'
                            ]"
                            @click="selectDestination(option)"
                            @mouseenter="highlightedIndex = index"
                        >
                            {{ option.label }}
                        </div>
                    </div>
                </div>

                <!-- Date Range -->
                <div
                    ref="dateField"
                    class="flex-1 min-w-[240px] px-4 py-1 border-r border-gray-200 cursor-pointer relative"
                    @click="toggleCalendar"
                >
                    <div
                        v-if="searchForm.checkIn && searchForm.checkOut"
                        class="text-gray-700"
                    >
                        {{ formatDate(searchForm.checkIn) }} -
                        {{ formatDate(searchForm.checkOut) }}
                    </div>
                    <div v-else class="text-gray-400">Add dates</div>

                    <!-- Calendar Dropdown -->
                    <div
                        v-if="showCalendar"
                        ref="calendarDropdown"
                        class="absolute top-full left-0 mt-2 bg-white rounded-xl shadow-2xl border border-gray-200 p-6"
                        style="width: 660px"
                        @click.stop
                    >
                        <!-- Calendar Header -->
                        <div class="flex items-center justify-between mb-6">
                            <button
                                @click="previousMonth"
                                class="p-2 rounded-full hover:bg-gray-100 transition"
                            >
                                <svg
                                    class="w-5 h-5"
                                    fill="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"
                                    />
                                </svg>
                            </button>
                            <div
                                class="flex gap-20 font-semibold text-gray-800"
                            >
                                <span>{{ getMonthYear(currentMonth) }}</span>
                                <span>{{ getMonthYear(nextMonth) }}</span>
                            </div>
                            <button
                                @click="nextMonthNav"
                                class="p-2 rounded-full hover:bg-gray-100 transition"
                            >
                                <svg
                                    class="w-5 h-5"
                                    fill="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"
                                    />
                                </svg>
                            </button>
                        </div>

                        <!-- Calendar Body -->
                        <div class="flex gap-6">
                            <!-- First Month -->
                            <div class="flex-1">
                                <div class="grid grid-cols-7 gap-1 mb-2">
                                    <div
                                        v-for="day in weekDays"
                                        :key="day"
                                        class="text-center text-xs font-semibold text-gray-500 py-2"
                                    >
                                        {{ day }}
                                    </div>
                                </div>
                                <div class="grid grid-cols-7 gap-1">
                                    <div
                                        v-for="day in getMonthDays(
                                            currentMonth
                                        )"
                                        :key="`current-${day.timestamp}`"
                                        :class="getDayClasses(day)"
                                        @click="selectDate(day)"
                                    >
                                        {{ day.date }}
                                    </div>
                                </div>
                            </div>

                            <!-- Second Month -->
                            <div class="flex-1">
                                <div class="grid grid-cols-7 gap-1 mb-2">
                                    <div
                                        v-for="day in weekDays"
                                        :key="day"
                                        class="text-center text-xs font-semibold text-gray-500 py-2"
                                    >
                                        {{ day }}
                                    </div>
                                </div>
                                <div class="grid grid-cols-7 gap-1">
                                    <div
                                        v-for="day in getMonthDays(nextMonth)"
                                        :key="`next-${day.timestamp}`"
                                        :class="getDayClasses(day)"
                                        @click="selectDate(day)"
                                    >
                                        {{ day.date }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Calendar Footer -->
                        <div
                            class="flex items-center justify-between mt-6 pt-4 border-t border-gray-200"
                        >
                            <button
                                @click="clearDates"
                                class="px-4 py-2 rounded-lg hover:bg-gray-100 transition text-sm font-medium"
                            >
                                Clear dates
                            </button>
                            <button
                                @click="closeCalendar"
                                class="px-6 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-700 transition text-sm font-medium"
                            >
                                Close
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Guests -->
                <div
                    ref="guestsField"
                    class="flex-1 min-w-[160px] px-4 py-3 cursor-pointer relative"
                    @click="toggleGuestsDropdown"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-gray-700">{{
                            guestsDisplayText
                        }}</span>
                        <svg
                            class="w-4 h-4 text-gray-400"
                            fill="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                d="M7.41 8.84L12 13.42l4.59-4.58L18 10.25l-6 6-6-6z"
                            />
                        </svg>
                    </div>

                    <!-- Guests Dropdown -->
                    <div
                        v-if="showGuestsDropdown"
                        ref="guestsDropdown"
                        class="absolute top-full right-0 mt-2 bg-white rounded-lg shadow-xl border border-gray-200 p-4"
                        style="width: 320px"
                        @click.stop
                    >
                        <!-- Adults -->
                        <div
                            class="flex items-center justify-between py-4 border-b border-gray-100"
                        >
                            <div>
                                <div class="font-semibold text-gray-800">
                                    Adults
                                </div>
                                <div class="text-sm text-gray-500">
                                    From 13 years and older
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <button
                                    @click="decrementAdults"
                                    :disabled="searchForm.adults <= 1"
                                    class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center hover:border-gray-900 transition disabled:opacity-30 disabled:cursor-not-allowed"
                                >
                                    -
                                </button>
                                <span class="w-5 text-center font-semibold">{{
                                    searchForm.adults
                                }}</span>
                                <button
                                    @click="incrementAdults"
                                    :disabled="searchForm.adults >= 10"
                                    class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center hover:border-gray-900 transition disabled:opacity-30 disabled:cursor-not-allowed"
                                >
                                    +
                                </button>
                            </div>
                        </div>

                        <!-- Children -->
                        <div
                            class="flex items-center justify-between py-4 border-b border-gray-100"
                        >
                            <div>
                                <div class="font-semibold text-gray-800">
                                    Children
                                </div>
                                <div class="text-sm text-gray-500">
                                    From 2 to 12 years
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <button
                                    @click="decrementChildren"
                                    :disabled="searchForm.children <= 0"
                                    class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center hover:border-gray-900 transition disabled:opacity-30 disabled:cursor-not-allowed"
                                >
                                    -
                                </button>
                                <span class="w-5 text-center font-semibold">{{
                                    searchForm.children
                                }}</span>
                                <button
                                    @click="incrementChildren"
                                    :disabled="searchForm.children >= 10"
                                    class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center hover:border-gray-900 transition disabled:opacity-30 disabled:cursor-not-allowed"
                                >
                                    +
                                </button>
                            </div>
                        </div>

                        <!-- Infants -->
                        <div class="flex items-center justify-between py-4">
                            <div>
                                <div class="font-semibold text-gray-800">
                                    Infants
                                </div>
                                <div class="text-sm text-gray-500">
                                    Under 2 years
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <button
                                    @click="decrementInfants"
                                    :disabled="searchForm.infants <= 0"
                                    class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center hover:border-gray-900 transition disabled:opacity-30 disabled:cursor-not-allowed"
                                >
                                    -
                                </button>
                                <span class="w-5 text-center font-semibold">{{
                                    searchForm.infants
                                }}</span>
                                <button
                                    @click="incrementInfants"
                                    :disabled="searchForm.infants >= 5"
                                    class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center hover:border-gray-900 transition disabled:opacity-30 disabled:cursor-not-allowed"
                                >
                                    +
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Search Button -->
                <div class="p-1">
                    <button
                        @click="handleSearch"
                        :disabled="!isFormValid"
                        class="flex items-center gap-2 bg-blue-600 text-white px-6 py-3 rounded-full hover:bg-blue-700 transition font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <svg
                            class="w-5 h-5"
                            fill="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"
                            />
                        </svg>
                        Search
                    </button>
                </div>
            </div>

            <!-- Validation Message -->
            <div
                v-if="validationMessage"
                class="mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg"
            >
                {{ validationMessage }}
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "SearchBar",
    data() {
        return {
            validationMessage: "",
            showGuestsDropdown: false,
            showCalendar: false,
            showDestinationDropdown: false,
            destinationSearch: "",
            highlightedIndex: -1,
            currentMonth: new Date(),
            nextMonth: new Date(
                new Date().getFullYear(),
                new Date().getMonth() + 1,
                1
            ),
            selectionMode: "checkin",
            searchForm: {
                destination: null,
                checkIn: null,
                checkOut: null,
                adults: 2,
                children: 0,
                infants: 0,
            },
            destinationOptions: [
                { id: 1, label: "Belgrade", value: "beograd" },
                { id: 2, label: "Novi Sad", value: "novi-sad" },
                { id: 3, label: "Niš", value: "nis" },
                { id: 4, label: "Kopaonik", value: "kopaonik" },
                { id: 5, label: "Zlatibor", value: "zlatibor" },
                { id: 6, label: "Tara", value: "tara" },
                { id: 7, label: "Vrnjačka Banja", value: "vrnjacka-banja" },
                { id: 8, label: "Sokobanja", value: "sokobanja" },
                { id: 9, label: "Bukovička Banja", value: "bukovicka-banja" },
            ],
            weekDays: ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa"],
            months: [
                "January",
                "February",
                "March",
                "April",
                "May",
                "June",
                "July",
                "August",
                "September",
                "October",
                "November",
                "December",
            ],
        };
    },
    computed: {
        filteredDestinations() {
            if (!this.destinationSearch) {
                return this.destinationOptions;
            }
            const search = this.destinationSearch.toLowerCase();
            return this.destinationOptions.filter(option =>
                option.label.toLowerCase().includes(search)
            );
        },
        guestsDisplayText() {
            const totalGuests =
                this.searchForm.adults + this.searchForm.children;
            let text = `${totalGuests} ${
                totalGuests === 1 ? "guest" : "guests"
            }`;
            if (this.searchForm.infants > 0) {
                text += `, ${this.searchForm.infants} ${
                    this.searchForm.infants === 1 ? "infant" : "infants"
                }`;
            }
            return text;
        },
        isFormValid() {
            return (
                this.searchForm.destination &&
                this.searchForm.checkIn &&
                this.searchForm.checkOut &&
                this.searchForm.adults > 0 &&
                this.searchForm.checkOut > this.searchForm.checkIn
            );
        },
    },
    mounted() {
        document.addEventListener("click", this.handleClickOutside);
        this.updateNextMonth();
    },
    beforeDestroy() {
        document.removeEventListener("click", this.handleClickOutside);
    },
    methods: {
        handleDestinationInput() {
            this.showDestinationDropdown = true;
            this.highlightedIndex = -1;
        },
        selectDestination(option) {
            this.searchForm.destination = option.value;
            this.destinationSearch = option.label;
            this.showDestinationDropdown = false;
            this.highlightedIndex = -1;
        },
        navigateDown() {
            if (this.highlightedIndex < this.filteredDestinations.length - 1) {
                this.highlightedIndex++;
            }
        },
        navigateUp() {
            if (this.highlightedIndex > 0) {
                this.highlightedIndex--;
            }
        },
        selectHighlighted() {
            if (this.highlightedIndex >= 0 && this.highlightedIndex < this.filteredDestinations.length) {
                this.selectDestination(this.filteredDestinations[this.highlightedIndex]);
            }
        },
        updateNextMonth() {
            this.nextMonth = new Date(
                this.currentMonth.getFullYear(),
                this.currentMonth.getMonth() + 1,
                1
            );
        },
        previousMonth() {
            this.currentMonth = new Date(
                this.currentMonth.getFullYear(),
                this.currentMonth.getMonth() - 1,
                1
            );
            this.updateNextMonth();
        },
        nextMonthNav() {
            this.currentMonth = new Date(
                this.currentMonth.getFullYear(),
                this.currentMonth.getMonth() + 1,
                1
            );
            this.updateNextMonth();
        },
        getMonthYear(date) {
            return `${this.months[date.getMonth()]} ${date.getFullYear()}`;
        },
        getMonthDays(month) {
            const year = month.getFullYear();
            const monthIndex = month.getMonth();
            const firstDay = new Date(year, monthIndex, 1);
            const startDate = new Date(firstDay);
            startDate.setDate(startDate.getDate() - firstDay.getDay());

            const days = [];
            const current = new Date(startDate);

            for (let i = 0; i < 42; i++) {
                const isCurrentMonth = current.getMonth() === monthIndex;
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                const isPast = current < today;

                days.push({
                    date: current.getDate(),
                    fullDate: new Date(current),
                    isCurrentMonth,
                    isPast,
                    timestamp: current.getTime(),
                });

                current.setDate(current.getDate() + 1);
            }

            return days;
        },
        getDayClasses(day) {
            const classes = [
                "flex",
                "items-center",
                "justify-center",
                "w-10",
                "h-10",
                "text-sm",
                "rounded-full",
                "transition",
                "cursor-pointer",
            ];

            if (!day.isCurrentMonth) {
                classes.push("text-gray-300", "cursor-not-allowed");
            }

            if (day.isPast) {
                classes.push("text-gray-300", "cursor-not-allowed");
            }

            const dayTime = day.timestamp;
            const checkInTime = this.searchForm.checkIn;
            const checkOutTime = this.searchForm.checkOut;

            if (checkInTime && dayTime === checkInTime) {
                classes.push("bg-gray-900", "text-white", "rounded-l-full");
            }

            if (checkOutTime && dayTime === checkOutTime) {
                classes.push("bg-gray-900", "text-white", "rounded-r-full");
            }

            if (
                checkInTime &&
                checkOutTime &&
                dayTime > checkInTime &&
                dayTime < checkOutTime
            ) {
                classes.push("bg-gray-100", "rounded-none");
            }

            if (
                !day.isPast &&
                day.isCurrentMonth &&
                !checkInTime &&
                !checkOutTime
            ) {
                classes.push("hover:bg-gray-100");
            }

            return classes.join(" ");
        },
        selectDate(day) {
            if (day.isPast || !day.isCurrentMonth) return;

            const timestamp = day.timestamp;

            if (
                !this.searchForm.checkIn ||
                (this.searchForm.checkIn && this.searchForm.checkOut)
            ) {
                this.searchForm.checkIn = timestamp;
                this.searchForm.checkOut = null;
                this.selectionMode = "checkout";
            } else if (this.searchForm.checkIn && !this.searchForm.checkOut) {
                if (timestamp > this.searchForm.checkIn) {
                    this.searchForm.checkOut = timestamp;
                    setTimeout(() => {
                        this.closeCalendar();
                    }, 300);
                } else {
                    this.searchForm.checkIn = timestamp;
                    this.searchForm.checkOut = null;
                }
            }
        },
        toggleCalendar() {
            this.showCalendar = !this.showCalendar;
            if (this.showCalendar) {
                this.currentMonth = new Date();
                this.updateNextMonth();
            }
        },
        closeCalendar() {
            this.showCalendar = false;
        },
        clearDates() {
            this.searchForm.checkIn = null;
            this.searchForm.checkOut = null;
            this.selectionMode = "checkin";
        },
        incrementAdults() {
            if (this.searchForm.adults < 10) this.searchForm.adults++;
        },
        decrementAdults() {
            if (this.searchForm.adults > 1) this.searchForm.adults--;
        },
        incrementChildren() {
            if (this.searchForm.children < 10) this.searchForm.children++;
        },
        decrementChildren() {
            if (this.searchForm.children > 0) this.searchForm.children--;
        },
        incrementInfants() {
            if (this.searchForm.infants < 5) this.searchForm.infants++;
        },
        decrementInfants() {
            if (this.searchForm.infants > 0) this.searchForm.infants--;
        },
        toggleGuestsDropdown() {
            this.showGuestsDropdown = !this.showGuestsDropdown;
        },
        formatDate(timestamp) {
            if (!timestamp) return "";
            return new Date(timestamp).toLocaleDateString("en-US", {
                month: "short",
                day: "numeric",
            });
        },
        handleSearch() {
            console.log("Search with:", this.searchForm);
        },
        handleClickOutside(event) {
            // Check autocomplete
            if (
                this.showDestinationDropdown &&
                this.$refs.destinationDropdown &&
                this.$refs.autocompleteField
            ) {
                if (
                    !this.$refs.destinationDropdown.contains(event.target) &&
                    !this.$refs.autocompleteField.contains(event.target)
                ) {
                    this.showDestinationDropdown = false;
                }
            }

            // Check calendar
            if (
                this.showCalendar &&
                this.$refs.calendarDropdown &&
                this.$refs.dateField
            ) {
                if (
                    !this.$refs.calendarDropdown.contains(event.target) &&
                    !this.$refs.dateField.contains(event.target)
                ) {
                    this.showCalendar = false;
                }
            }

            // Check guests dropdown
            if (
                this.showGuestsDropdown &&
                this.$refs.guestsDropdown &&
                this.$refs.guestsField
            ) {
                if (
                    !this.$refs.guestsDropdown.contains(event.target) &&
                    !this.$refs.guestsField.contains(event.target)
                ) {
                    this.showGuestsDropdown = false;
                }
            }
        },
    },
};
</script>

<style scoped>
.custom-input {
    outline: none !important;
    border: none !important;
    box-shadow: none !important;
}

.custom-input:focus {
    outline: none !important;
    border: none !important;
    box-shadow: none !important;
}
</style>
