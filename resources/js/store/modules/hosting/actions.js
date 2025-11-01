import apiClient from "@/services/apiClient";

export const loadInitialAccommodationData = async ({ dispatch }) => {
    const actions = [
        dispatch("fetchAccommodationDraft"),
    ];

    await Promise.all(actions).then(() => {
        dispatch("setHostingLoading", false);
    });
};

export const fetchAccommodationDraft = async ({ commit }) => {
    const response = await apiClient.accommodationDrafts.get();

    commit("SET_ACCOMMODATION_DRAFT", response.data);
    commit("createAccommodation/SET_CREATE_ACCOMMODATION_STEP", response.data.current_step);

    return response;
};

export const setHostingLoading = ({ commit }, isLoading) => {
    commit("SET_HOSTING_LOADING", isLoading);
};
