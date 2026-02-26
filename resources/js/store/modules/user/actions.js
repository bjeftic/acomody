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

export const updateProfile = async ({ commit }, payload) => {
    const response = await apiClient.users.put(payload);
    commit("SET_CURRENT_USER", response.data);
    return response;
};

export const updatePassword = async ({ commit }, payload) => {
    return await apiClient.users.password.put(payload);
};

export const uploadAvatar = async ({ commit }, file) => {
    const response = await apiClient.users.avatar.upload(file, "avatar");
    commit("SET_CURRENT_USER", response.data);
    return response;
};
