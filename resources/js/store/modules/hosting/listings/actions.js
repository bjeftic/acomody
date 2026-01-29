import apiClient from "@/services/apiClient";

export const loadInitialMyListingsData = async ({ dispatch }) => {
    const actions = [
        dispatch("fetchAccommodationDrafts"),
        dispatch("fetchMyAccommodations"),
    ];

    await Promise.all(actions).finally(() => {
        dispatch("setMyListingsLoading", false);
    });
};

export const fetchAccommodationDrafts= async ({ commit }) => {

    // Here we need to fetch only drafts that are in waiting for approval status
    try {
        const drafts = await apiClient.accommodationDrafts
            .query({ status: "waiting_for_approval" })
            .get();

        commit("SET_ACCOMMODATION_DRAFTS", drafts.data);
        return drafts;
    } catch (error) {
        console.error("Failed to fetch accommodation drafts:", error);
        throw error;
    }
};

export const setMyListingsLoading = ({ commit }, isLoading) => {
    commit("SET_MY_LISTINGS_LOADING", isLoading);
};

export const fetchMyAccommodations = async ({ commit }, { page = 1, perPage = 15 } = {}) => {
    try {
        const accommodations = await apiClient.accommodations
            .query({
                page: page,
                per_page: perPage
            })
            .get();

        commit("SET_ACCOMMODATIONS", accommodations.data);
        return accommodations;
    } catch (error) {
        console.error("Failed to fetch my accommodations:", error);
        throw error;
    }
};

export const fetchAccommodation = async ({ commit }, accommodationId) => {
    try {
        const accommodation = await apiClient.accommodations[accommodationId]
            .get();

        commit("SET_ACCOMMODATION", accommodation.data);
        return accommodation;
    } catch (error) {
        console.error(`Failed to fetch accommodation with ID ${accommodationId}:`, error);
        throw error;
    }
};
