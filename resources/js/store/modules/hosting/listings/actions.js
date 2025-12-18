import apiClient from "@/services/apiClient";

export const loadInitialMyListingsData = async ({ dispatch }) => {
    const actions = [
        dispatch("fetchAccommodationDrafts"),
        dispatch("fetchMyListings"),
    ];

    await Promise.all(actions).finally(() => {
        dispatch("setMyListingsLoading", false);
    });
};

export const fetchAccommodationDrafts= async ({ commit }) => {

    // Here we need to fetch only drafts that are in waiting for approval status
    try {
        const drafts = await apiClient.accommodationDrafts
            .query({ status: "waiting_for_approval" })
            .get();

        commit("SET_ACCOMMODATION_DRAFTS", drafts.data);
        return drafts;
    } catch (error) {
        console.error("Failed to fetch accommodation drafts:", error);
        throw error;
    }
};

export const setMyListingsLoading = ({ commit }, isLoading) => {
    commit("SET_MY_LISTINGS_LOADING", isLoading);
};

export const fetchMyListings = async ({ commit }, { page = 1, perPage = 15 } = {}) => {
    try {
        const listings = await apiClient.listings
            .query({
                page: page,
                per_page: perPage
            })
            .get();

        commit("SET_MY_LISTINGS", listings.data);
        return listings;
    } catch (error) {
        console.error("Failed to fetch my listings:", error);
        throw error;
    }
};

export const fetchListing = async ({ commit }, listingId) => {
    try {
        const listing = await apiClient.listings[listingId]
            .get();

        commit("SET_CURRENT_LISTING", listing.data);
        return listing;
    } catch (error) {
        console.error(`Failed to fetch listing with ID ${listingId}:`, error);
        throw error;
    }
};
