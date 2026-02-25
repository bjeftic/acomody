<template>
    <div class="max-w-4xl mx-auto py-12">
        <template v-if="calendarLoading">
            <form-skeleton />
        </template>
        <template v-else>
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-semibold text-gray-900 dark:text-white mb-2">
                    Calendar
                </h1>
                <p class="text-base text-gray-600 dark:text-gray-400">
                    Track bookings and availability for your properties.
                </p>
            </div>

            <!-- Calendar Card -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden mb-6">
                <!-- Month navigation -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <button
                        @click="prevMonth"
                        class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400 transition-colors"
                        aria-label="Previous month"
                    >
                        <icon-loader name="ChevronLeftIcon" :size="20" />
                    </button>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ monthName }} {{ currentYear }}
                    </h2>
                    <button
                        @click="nextMonth"
                        class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400 transition-colors"
                        aria-label="Next month"
                    >
                        <icon-loader name="ChevronRightIcon" :size="20" />
                    </button>
                </div>

                <!-- Week day headers -->
                <div class="grid grid-cols-7 border-b border-gray-200 dark:border-gray-700">
                    <div
                        v-for="day in weekDays"
                        :key="day"
                        class="py-2 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                    >
                        {{ day }}
                    </div>
                </div>

                <!-- Calendar grid -->
                <div class="grid grid-cols-7 divide-x divide-y divide-gray-100 dark:divide-gray-700">
                    <div
                        v-for="(cell, index) in calendarCells"
                        :key="index"
                        @click="cell.isCurrentMonth && onCellClick(cell.date)"
                        :class="[
                            'min-h-[90px] p-2 relative transition-colors',
                            cell.isCurrentMonth
                                ? 'bg-white dark:bg-gray-800 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-750'
                                : 'bg-gray-50 dark:bg-gray-900/50 cursor-default',
                            selectedDate === cell.date
                                ? 'ring-2 ring-inset ring-blue-500 dark:ring-blue-400'
                                : '',
                        ]"
                    >
                        <!-- Day number -->
                        <span
                            :class="[
                                'inline-flex items-center justify-center w-7 h-7 text-sm rounded-full mb-1',
                                cell.isToday
                                    ? 'bg-blue-600 text-white font-bold'
                                    : cell.isCurrentMonth
                                    ? 'text-gray-900 dark:text-gray-100'
                                    : 'text-gray-400 dark:text-gray-600',
                                selectedDate === cell.date && !cell.isToday
                                    ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 font-semibold'
                                    : '',
                            ]"
                        >
                            {{ cell.day }}
                        </span>

                        <!-- Booking indicators -->
                        <div class="space-y-0.5">
                            <div
                                v-for="booking in cell.bookings.slice(0, 2)"
                                :key="booking.id"
                                :class="[
                                    'text-xs px-1.5 py-0.5 rounded truncate leading-tight',
                                    bookingChipClass(booking),
                                ]"
                            >
                                {{ isCheckInDay(booking, cell.date) ? booking.guestName.split(' ')[0] : '\u00A0' }}
                            </div>
                            <div
                                v-if="cell.bookings.length > 2"
                                class="text-xs text-gray-500 dark:text-gray-400 px-1"
                            >
                                +{{ cell.bookings.length - 2 }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Legend -->
            <div class="flex flex-wrap items-center gap-x-6 gap-y-2 mb-8 px-1">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-green-500 flex-shrink-0"></span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Confirmed</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-yellow-400 flex-shrink-0"></span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Pending</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-gray-400 flex-shrink-0"></span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Completed</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-blue-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                        {{ new Date().getDate() }}
                    </span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Today</span>
                </div>
            </div>

            <!-- Booking list section -->
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                        <template v-if="selectedDate">
                            Bookings for {{ formatDisplayDate(selectedDate) }}
                        </template>
                        <template v-else>
                            Upcoming bookings
                        </template>
                    </h2>
                    <button
                        v-if="selectedDate"
                        @click="clearSelectedDate"
                        class="text-sm text-blue-600 dark:text-blue-400 hover:underline"
                    >
                        Show all upcoming
                    </button>
                </div>

                <div class="space-y-3" v-if="displayedBookings.length > 0">
                    <div
                        v-for="booking in displayedBookings"
                        :key="booking.id"
                        class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4"
                    >
                        <div class="flex items-start gap-4">
                            <!-- Avatar placeholder -->
                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-500 dark:text-gray-400">
                                <icon-loader name="UserIcon" :size="18" />
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                            {{ booking.guestName }}
                                        </p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 truncate">
                                            {{ booking.accommodationTitle }}
                                        </p>
                                    </div>
                                    <fwb-badge :type="badgeType(booking.status)" class="flex-shrink-0 capitalize">
                                        {{ booking.status }}
                                    </fwb-badge>
                                </div>

                                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2 text-xs text-gray-500 dark:text-gray-400">
                                    <span class="flex items-center gap-1">
                                        <icon-loader name="CalendarIcon" :size="13" />
                                        {{ formatDisplayDate(booking.checkIn) }} → {{ formatDisplayDate(booking.checkOut) }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <icon-loader name="UsersIcon" :size="13" />
                                        {{ booking.guests }} guest{{ booking.guests !== 1 ? 's' : '' }}
                                    </span>
                                    <span class="font-medium text-gray-700 dark:text-gray-300">
                                        {{ booking.currency }} {{ booking.totalPrice.toFixed(2) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty state -->
                <div
                    v-else
                    class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-12 text-center"
                >
                    <div class="flex justify-center mb-4 text-gray-300 dark:text-gray-600">
                        <icon-loader name="CalendarIcon" :size="48" />
                    </div>
                    <p class="text-gray-500 dark:text-gray-400">
                        {{ selectedDate ? 'No bookings on this date.' : 'No upcoming bookings.' }}
                    </p>
                </div>
            </div>
        </template>
    </div>
</template>

<script>
import { mapState, mapActions } from "vuex";

export default {
    name: "Calendar",

    data() {
        return {
            weekDays: ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
            monthNames: [
                "January", "February", "March", "April", "May", "June",
                "July", "August", "September", "October", "November", "December",
            ],
        };
    },

    computed: {
        ...mapState("hosting/calendar", [
            "calendarLoading",
            "bookings",
            "selectedDate",
            "currentYear",
            "currentMonth",
        ]),

        monthName() {
            return this.monthNames[this.currentMonth];
        },

        calendarCells() {
            const year = this.currentYear;
            const month = this.currentMonth;
            const firstDay = new Date(year, month, 1);
            const lastDayDate = new Date(year, month + 1, 0);

            // Convert JS Sunday=0 to Monday=0 index
            const startOffset = (firstDay.getDay() + 6) % 7;

            const cells = [];

            // Fill leading days from previous month
            const prevMonthLastDay = new Date(year, month, 0).getDate();
            const prevMonth = month === 0 ? 11 : month - 1;
            const prevYear = month === 0 ? year - 1 : year;
            for (let i = startOffset - 1; i >= 0; i--) {
                const day = prevMonthLastDay - i;
                cells.push({
                    day,
                    date: this.toDateString(prevYear, prevMonth, day),
                    isCurrentMonth: false,
                    isToday: false,
                    bookings: [],
                });
            }

            // Current month days
            const today = new Date();
            for (let day = 1; day <= lastDayDate.getDate(); day++) {
                const dateStr = this.toDateString(year, month, day);
                const isToday =
                    today.getFullYear() === year &&
                    today.getMonth() === month &&
                    today.getDate() === day;
                cells.push({
                    day,
                    date: dateStr,
                    isCurrentMonth: true,
                    isToday,
                    bookings: this.bookingsForDate(dateStr),
                });
            }

            // Fill trailing days from next month to complete the last row
            const remainder = cells.length % 7;
            if (remainder !== 0) {
                const fill = 7 - remainder;
                const nextMonth = month === 11 ? 0 : month + 1;
                const nextYear = month === 11 ? year + 1 : year;
                for (let day = 1; day <= fill; day++) {
                    cells.push({
                        day,
                        date: this.toDateString(nextYear, nextMonth, day),
                        isCurrentMonth: false,
                        isToday: false,
                        bookings: [],
                    });
                }
            }

            return cells;
        },

        displayedBookings() {
            if (this.selectedDate) {
                return this.bookingsForDate(this.selectedDate);
            }
            const today = new Date();
            const todayStr = this.toDateString(
                today.getFullYear(),
                today.getMonth(),
                today.getDate()
            );
            return this.bookings
                .filter((b) => b.checkOut >= todayStr)
                .sort((a, b) => a.checkIn.localeCompare(b.checkIn));
        },
    },

    methods: {
        ...mapActions("hosting/calendar", [
            "loadCalendarData",
            "setSelectedDate",
            "navigateMonth",
        ]),

        toDateString(year, month, day) {
            return `${year}-${String(month + 1).padStart(2, "0")}-${String(day).padStart(2, "0")}`;
        },

        bookingsForDate(dateStr) {
            return this.bookings.filter(
                (b) => b.checkIn <= dateStr && b.checkOut >= dateStr
            );
        },

        isCheckInDay(booking, dateStr) {
            return booking.checkIn === dateStr;
        },

        onCellClick(date) {
            this.setSelectedDate(this.selectedDate === date ? null : date);
        },

        clearSelectedDate() {
            this.setSelectedDate(null);
        },

        prevMonth() {
            this.navigateMonth(-1);
        },

        nextMonth() {
            this.navigateMonth(1);
        },

        formatDisplayDate(dateStr) {
            if (!dateStr) return "";
            const [year, month, day] = dateStr.split("-");
            return new Date(year, month - 1, day).toLocaleDateString("en-US", {
                day: "numeric",
                month: "short",
                year: "numeric",
            });
        },

        bookingChipClass(booking) {
            switch (booking.status) {
                case "confirmed":
                    return "bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-300";
                case "pending":
                    return "bg-yellow-100 dark:bg-yellow-900/40 text-yellow-800 dark:text-yellow-300";
                case "cancelled":
                    return "bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-300";
                default:
                    return "bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400";
            }
        },

        badgeType(status) {
            switch (status) {
                case "confirmed":
                    return "green";
                case "pending":
                    return "yellow";
                case "cancelled":
                    return "red";
                default:
                    return "default";
            }
        },
    },

    created() {
        this.loadCalendarData();
    },
};
</script>
