import apiClient from "@/services/apiClient";

export const fetchUser = async ({ commit }) => {
    try {
        const response = await apiClient.users.get();

        commit("SET_CURRENT_USER", response.data);
        commit("auth/SET_AUTHENTICATED", true, { root: true });

        return response;
    } catch (error) {
        throw error;
    }
};
