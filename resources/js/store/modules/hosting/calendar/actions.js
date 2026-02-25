export const loadCalendarData = async ({ commit, dispatch }) => {
    await dispatch("fetchBookings");
    commit("SET_CALENDAR_LOADING", false);
};

export const fetchBookings = ({ commit }) => {
    // Mock data — will be replaced with API call once backend is ready
    const mockBookings = [
        {
            id: 1,
            guestName: "Alice Johnson",
            accommodationId: 1,
            accommodationTitle: "Cozy Studio in City Center",
            checkIn: "2026-02-22",
            checkOut: "2026-02-27",
            guests: 2,
            status: "confirmed",
            totalPrice: 350.0,
            currency: "EUR",
        },
        {
            id: 2,
            guestName: "Bob Smith",
            accommodationId: 1,
            accommodationTitle: "Cozy Studio in City Center",
            checkIn: "2026-03-03",
            checkOut: "2026-03-07",
            guests: 1,
            status: "confirmed",
            totalPrice: 240.0,
            currency: "EUR",
        },
        {
            id: 3,
            guestName: "Carol Davis",
            accommodationId: 2,
            accommodationTitle: "Beachfront Villa",
            checkIn: "2026-03-15",
            checkOut: "2026-03-22",
            guests: 4,
            status: "pending",
            totalPrice: 1750.0,
            currency: "EUR",
        },
        {
            id: 4,
            guestName: "David Wilson",
            accommodationId: 1,
            accommodationTitle: "Cozy Studio in City Center",
            checkIn: "2026-03-20",
            checkOut: "2026-03-24",
            guests: 2,
            status: "confirmed",
            totalPrice: 320.0,
            currency: "EUR",
        },
        {
            id: 5,
            guestName: "Emma Brown",
            accommodationId: 2,
            accommodationTitle: "Beachfront Villa",
            checkIn: "2026-04-05",
            checkOut: "2026-04-12",
            guests: 6,
            status: "pending",
            totalPrice: 2800.0,
            currency: "EUR",
        },
    ];

    commit("SET_BOOKINGS", mockBookings);
};

export const setSelectedDate = ({ commit }, date) => {
    commit("SET_SELECTED_DATE", date);
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
