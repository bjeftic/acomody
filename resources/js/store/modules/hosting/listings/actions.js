import apiClient from "@/services/apiClient";

export const loadInitialMyListingsData = async ({ dispatch }) => {
    const actions = [
        dispatch("fetchAccommodationDrafts"),
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
