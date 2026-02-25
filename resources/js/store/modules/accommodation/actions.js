import apiClient from "@/services/apiClient";

export const fetchAccommodation = async ({ commit }, { id, params = {} } = {}) => {
    try {
        const request = apiClient.public.accommodations[id];
        const response = await (Object.keys(params).length
            ? request.query(params).get()
            : request.get());
        commit("SET_ACCOMMODATION", response.data);
        return response.data;
    } catch (error) {
        console.error("Failed to fetch accommodation:", error);
        throw error;
    }
};

export const clearAccommodation = ({ commit }) => {
    commit("SET_ACCOMMODATION", null);
};

export const createBooking = async (_, { accommodationId, checkIn, checkOut, guests, guestNotes }) => {
    const payload = {
        accommodation_id: accommodationId,
        check_in: checkIn,
        check_out: checkOut,
        guests,
    };

    if (guestNotes) {
        payload.guest_notes = guestNotes;
    }

    const response = await apiClient.bookings.post(payload);
    return response.data;
};
