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

export const getFeeTypes = async ({ commit }) => {
    const response = await apiClient.fees.feeTypes.get();
    commit("SET_FEE_TYPES", response.data);
}

export const getFeeChargeTypes = async ({ commit }) => {
    const response = await apiClient.fees.chargeTypes.get();
    commit("SET_FEE_CHARGE_TYPES", response.data);
}
