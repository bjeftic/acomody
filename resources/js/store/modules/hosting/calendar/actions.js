import apiClient from "@/services/apiClient";

export const loadCalendarData = async ({ commit, dispatch }) => {
    commit("SET_CALENDAR_ERROR", null);
    try {
        await Promise.all([
            dispatch("fetchBookings"),
            dispatch("fetchBlockedPeriods"),
        ]);
    } catch (error) {
        commit("SET_CALENDAR_ERROR", "Failed to load calendar data. Please try again.");
    } finally {
        commit("SET_CALENDAR_LOADING", false);
    }
};

export const fetchBlockedPeriods = async ({ commit }) => {
    const response = await apiClient.host["blocked-periods"].get();

    const periods = (response.data?.data ?? []).map((item) => ({
        id: item.id,
        accommodationId: item.accommodation_id,
        startDate: item.start_date,
        endDate: item.end_date,
        status: item.status,
        notes: item.notes,
        isIcalSynced: item.is_ical_synced,
        icalCalendarName: item.ical_calendar_name ?? null,
    }));

    commit("SET_BLOCKED_PERIODS", periods);
};

export const fetchBookings = async ({ commit }) => {
    const response = await apiClient.host.bookings
        .query({ per_page: 100 })
        .get();

    const bookings = (response.data?.data ?? []).map((item) => ({
        id: item.id,
        guestName: item.guest?.name ?? "Unknown Guest",
        accommodationId: item.accommodation?.id ?? null,
        accommodationTitle: item.accommodation?.title ?? "",
        checkIn: item.check_in,
        checkOut: item.check_out,
        guests: item.guests,
        status: item.status,
        totalPrice: item.total_price,
        currency: item.currency,
    }));

    commit("SET_BOOKINGS", bookings);
};

export const setSelectedDate = ({ commit }, date) => {
    commit("SET_SELECTED_DATE", date);
};

export const confirmBooking = async ({ commit, state }, bookingId) => {
    const response = await apiClient.host.bookings[bookingId].confirm.post();
    const item = response.data?.data;
    const existing = state.bookings.find((b) => b.id === bookingId);
    if (existing && item) {
        commit("UPDATE_BOOKING", { ...existing, status: item.status });
    }
};

export const declineBooking = async ({ commit, state }, { bookingId, reason }) => {
    const payload = reason ? { reason } : {};
    const response = await apiClient.host.bookings[bookingId].decline.post(payload);
    const item = response.data?.data;
    const existing = state.bookings.find((b) => b.id === bookingId);
    if (existing && item) {
        commit("UPDATE_BOOKING", { ...existing, status: item.status });
    }
};

export const navigateMonth = ({ commit, state }, direction) => {
    let month = state.currentMonth + direction;
    let year = state.currentYear;

    if (month > 11) {
        month = 0;
        year++;
    }

    if (month < 0) {
        month = 11;
        year--;
    }

    commit("SET_CURRENT_MONTH", month);
    commit("SET_CURRENT_YEAR", year);
};
