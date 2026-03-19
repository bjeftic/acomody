import apiClient from "@/services/apiClient";
import * as types from "./mutation-types";

export const fetchHomeSections = async ({ commit }) => {
    commit(types.SET_LOADING, true);

    try {
        const response = await apiClient.public["home-sections"].get();
        commit(types.SET_SECTIONS, response.data ?? []);
    } catch (error) {
        console.error("Failed to fetch home sections:", error);
    } finally {
        commit(types.SET_LOADING, false);
    }
};
