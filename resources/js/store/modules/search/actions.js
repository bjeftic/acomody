import apiClient from "@/services/apiClient";
import { sortSearchResults } from "@/utils/helpers";

export const searchLocations = async ({ commit }, query) => {
    try {
        const response = await apiClient.search.locations.query({ q: query }).get();
        let results = sortSearchResults(response.data || []);
        commit("SET_SEARCH_BAR_SEARCH_RESULTS", results);
        return results;
    } catch (error) {
        console.error("Failed to search locations:", error);
        throw error;
    }
};

export const searchAccommodations = async ({ commit }, searchParams) => {
    try {
        const response = await apiClient.search.accommodations
            .query(searchParams)
            .get();
        commit("SET_ACCOMMODATIONS_SEARCH_RESULTS", response.data);
        commit("SET_FILTERS", response.data.facet_counts || []);
        commit("SET_TOTAL_ACCOMMODATIONS_FOUND", response.data.found || 0);
        return response.data.hits;
    } catch (error) {
        console.error("Failed to search accommodations:", error);
        throw error;
    }
};
