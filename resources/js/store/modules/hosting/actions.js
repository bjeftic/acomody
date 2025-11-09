import apiClient from "@/services/apiClient";

export const loadInitialDashboardData = async ({ dispatch }) => {
    const actions = [
        dispatch("getAccommodationDraftStats"),
    ];

    await Promise.all(actions).finally(() => {
        dispatch("setHostingLoading", false);
    });
};

export const getAccommodationDraftStats = async ({ commit }) => {
    const stats = await apiClient.accommodationDrafts.stats.get()
    commit("SET_ACCOMMODATION_DRAFT_STATS", stats.data);
}

export const setHostingLoading = ({ commit }, isLoading) => {
    commit("SET_HOSTING_LOADING", isLoading);
};
