import apiClient from "@/services/apiClient";

export const fetchAccommodationTypes = async ({ commit }) => {
    try {
        const response = await apiClient.accommodationTypes.get();

        commit("SET_ACCOMMODATION_TYPES", response.data);

        return response;
    } catch (error) {
        throw error;
    }
};
