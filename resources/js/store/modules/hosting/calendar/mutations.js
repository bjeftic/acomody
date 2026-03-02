import * as types from "./mutation-types";

export default {
    [types.SET_CALENDAR_LOADING](state, isLoading) {
        state.calendarLoading = isLoading;
    },
    [types.SET_CALENDAR_ERROR](state, error) {
        state.calendarError = error;
    },
    [types.SET_BOOKINGS](state, bookings) {
        state.bookings = bookings;
    },
    [types.SET_SELECTED_DATE](state, date) {
        state.selectedDate = date;
    },
    [types.SET_CURRENT_MONTH](state, month) {
        state.currentMonth = month;
    },
    [types.SET_CURRENT_YEAR](state, year) {
        state.currentYear = year;
    },

    [types.UPDATE_BOOKING](state, updatedBooking) {
        const index = state.bookings.findIndex((b) => b.id === updatedBooking.id);
        if (index !== -1) {
            state.bookings.splice(index, 1, updatedBooking);
        }
    },
};
