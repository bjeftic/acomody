import apiClient from "@/services/apiClient";

export const loadInitialDashboardData = async ({ dispatch }) => {
    const actions = [
        dispatch("checkAccommodationDraft"),
    ];

    await Promise.all(actions).finally(() => {
        dispatch("setHostingLoading", false);
    });
};

export const checkAccommodationDraft = async ({ commit }) => {
    await apiClient.accommodationDrafts.get()
        .then(({ data }) => {
            commit("SET_ACCOMMODATION_DRAFT_EXISTS", !!data.id);
        });
};

export const setHostingLoading = ({ commit }, isLoading) => {
    commit("SET_HOSTING_LOADING", isLoading);
};
