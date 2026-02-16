import apiClient from "@/services/apiClient";

export const fetchAccommodation = async ({ commit }, accommodationId) => {
    try {
        const response = await apiClient.public.accommodation[accommodationId]
            .get();
        commit("SET_ACCOMMODATION", response.data);
        return response.data;
    } catch (error) {
        console.error("Failed to fetch accommodation:", error);
        throw error;
    }
};

export const clearAccommodation = ({ commit }) => {
    commit("SET_ACCOMMODATION", null);
}
