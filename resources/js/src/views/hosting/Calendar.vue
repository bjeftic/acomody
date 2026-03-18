<template>
    <div class="max-w-4xl mx-auto py-12">
        <template v-if="calendarLoading">
            <form-skeleton />
        </template>
        <template v-else-if="calendarError">
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-6 text-center">
                <p class="text-red-600 dark:text-red-400">{{ calendarError }}</p>
            </div>
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
                                ? 'ring-2 ring-inset ring-primary-500 dark:ring-primary-400'
                                : '',
                        ]"
                    >
                        <!-- Day number -->
                        <span
                            :class="[
                                'inline-flex items-center justify-center w-7 h-7 text-sm rounded-full mb-1',
                                cell.isToday
                                    ? 'bg-primary-600 text-white font-bold'
                                    : cell.isCurrentMonth
                                    ? 'text-gray-900 dark:text-gray-100'
                                    : 'text-gray-400 dark:text-gray-600',
                                selectedDate === cell.date && !cell.isToday
                                    ? 'bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300 font-semibold'
                                    : '',
                            ]"
                        >
                            {{ cell.day }}
                        </span>

                        <!-- Booking and period indicators -->
                        <div class="space-y-0.5">
                            <div
                                v-for="booking in cell.bookings.slice(0, 2)"
                                :key="'b-' + booking.id"
                                :class="[
                                    'text-xs px-1.5 py-0.5 rounded truncate leading-tight',
                                    bookingChipClass(booking),
                                ]"
                            >
                                {{ isCheckInDay(booking, cell.date) ? booking.guestName.split(' ')[0] : '\u00A0' }}
                            </div>
                            <div
                                v-for="period in cell.periods.slice(0, 2 - Math.min(cell.bookings.length, 2))"
                                :key="'p-' + period.id"
                                class="text-xs px-1.5 py-0.5 rounded truncate leading-tight bg-orange-100 dark:bg-orange-900/40 text-orange-800 dark:text-orange-300"
                            >
                                {{ period.isIcalSynced ? (period.icalCalendarName ?? 'iCal') : 'Blocked' }}
                            </div>
                            <div
                                v-if="cell.bookings.length + cell.periods.length > 2"
                                class="text-xs text-gray-500 dark:text-gray-400 px-1"
                            >
                                +{{ cell.bookings.length + cell.periods.length - 2 }}
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
                    <span class="w-3 h-3 rounded-full bg-orange-400 flex-shrink-0"></span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Blocked / iCal</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-primary-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
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
                        class="text-sm text-primary-600 dark:text-primary-400 hover:underline"
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

                                <!-- Confirm / Decline for pending bookings -->
                                <template v-if="booking.status === 'pending'">
                                    <!-- Decline reason form -->
                                    <div
                                        v-if="decliningBookingId === booking.id"
                                        class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700"
                                    >
                                        <textarea
                                            v-model="declineReason"
                                            placeholder="Reason for declining (optional)"
                                            rows="2"
                                            class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-400 resize-none"
                                        ></textarea>
                                        <div class="flex gap-2 mt-2">
                                            <button
                                                @click="onDeclineBooking(booking.id)"
                                                :disabled="pendingAction === booking.id"
                                                class="flex-1 py-1.5 px-3 text-sm font-medium rounded-lg bg-red-600 hover:bg-red-700 text-white transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                            >
                                                {{ pendingAction === booking.id ? 'Declining…' : 'Confirm decline' }}
                                            </button>
                                            <button
                                                @click="decliningBookingId = null; declineReason = ''"
                                                :disabled="pendingAction === booking.id"
                                                class="py-1.5 px-3 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors disabled:opacity-50"
                                            >
                                                Cancel
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Action buttons -->
                                    <div v-else class="flex gap-2 mt-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                                        <button
                                            @click="onConfirmBooking(booking.id)"
                                            :disabled="pendingAction === booking.id"
                                            class="flex-1 py-1.5 px-3 text-sm font-medium rounded-lg bg-green-600 hover:bg-green-700 text-white transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                        >
                                            {{ pendingAction === booking.id ? 'Confirming…' : 'Confirm' }}
                                        </button>
                                        <button
                                            @click="decliningBookingId = booking.id"
                                            :disabled="pendingAction === booking.id"
                                            class="flex-1 py-1.5 px-3 text-sm font-medium rounded-lg border border-red-300 dark:border-red-700 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors disabled:opacity-50"
                                        >
                                            Decline
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty state -->
                <div
                    v-else-if="!selectedDate || periodsForDate(selectedDate).length === 0"
                    class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-12 text-center"
                >
                    <div class="flex justify-center mb-4 text-gray-300 dark:text-gray-600">
                        <icon-loader name="CalendarIcon" :size="48" />
                    </div>
                    <p class="text-gray-500 dark:text-gray-400">
                        {{ selectedDate ? 'No bookings on this date.' : 'No upcoming bookings.' }}
                    </p>
                </div>

                <!-- Blocked periods for selected date -->
                <template v-if="selectedDate && periodsForDate(selectedDate).length > 0">
                    <h3 class="text-base font-semibold text-gray-700 dark:text-gray-300 mt-6 mb-3">
                        Blocked dates
                    </h3>
                    <div class="space-y-3">
                        <div
                            v-for="period in periodsForDate(selectedDate)"
                            :key="period.id"
                            class="bg-orange-50 dark:bg-orange-900/10 border border-orange-200 dark:border-orange-800 rounded-xl p-4"
                        >
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-orange-100 dark:bg-orange-900/40 flex items-center justify-center text-orange-500 dark:text-orange-400">
                                    <icon-loader name="CalendarDaysIcon" :size="18" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white capitalize">
                                            {{ period.status }}
                                        </p>
                                        <span class="text-xs px-2 py-0.5 rounded-full flex-shrink-0" :class="period.isIcalSynced ? 'bg-primary-100 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300' : 'bg-orange-100 dark:bg-orange-900/40 text-orange-700 dark:text-orange-300'">
                                            {{ period.isIcalSynced ? (period.icalCalendarName ?? 'iCal sync') : 'Manual' }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-1 mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        <icon-loader name="CalendarIcon" :size="13" />
                                        {{ formatDisplayDate(period.startDate) }} → {{ formatDisplayDate(period.endDate) }}
                                    </div>
                                    <p v-if="period.notes" class="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate">
                                        {{ period.notes }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
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
            pendingAction: null,
            decliningBookingId: null,
            declineReason: "",
        };
    },

    computed: {
        ...mapState("hosting/calendar", [
            "calendarLoading",
            "calendarError",
            "bookings",
            "blockedPeriods",
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
                    periods: [],
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
                    periods: this.periodsForDate(dateStr),
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
                        periods: [],
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
            "confirmBooking",
            "declineBooking",
        ]),

        toDateString(year, month, day) {
            return `${year}-${String(month + 1).padStart(2, "0")}-${String(day).padStart(2, "0")}`;
        },

        bookingsForDate(dateStr) {
            return this.bookings.filter(
                (b) => b.checkIn <= dateStr && b.checkOut >= dateStr
            );
        },

        periodsForDate(dateStr) {
            return this.blockedPeriods.filter(
                (p) => p.startDate <= dateStr && p.endDate >= dateStr
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

        async onConfirmBooking(bookingId) {
            this.pendingAction = bookingId;
            try {
                await this.confirmBooking(bookingId);
            } catch {
                // silently ignore — booking list retains current state
            } finally {
                this.pendingAction = null;
            }
        },

        async onDeclineBooking(bookingId) {
            this.pendingAction = bookingId;
            try {
                await this.declineBooking({ bookingId, reason: this.declineReason });
                this.decliningBookingId = null;
                this.declineReason = "";
            } catch {
                // silently ignore
            } finally {
                this.pendingAction = null;
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
