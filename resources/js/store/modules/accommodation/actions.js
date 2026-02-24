import apiClient from "@/services/apiClient";

export const fetchAccommodation = async ({ commit }, { id, params = {} } = {}) => {
    try {
        const request = apiClient.public.accommodation[id];
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
}
