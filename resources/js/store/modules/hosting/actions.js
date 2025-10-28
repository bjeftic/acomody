import apiClient from "@/services/apiClient";

export const fetchAccommodationDraft = async ({ commit }) => {
    try {
        const response = await apiClient.accommodationDrafts.get();

        commit("SET_ACCOMMODATION_DRAFT", response.data);
        commit("createAccommodation/SET_CREATE_ACCOMMODATION_STEP", response.data.current_step);

        return response;
    } catch (error) {
        throw error;
    }
};
