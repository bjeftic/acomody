import * as types from "./mutation-types";

export default {
    [types.SET_MY_LISTINGS_LOADING](state, isLoading) {
        state.myListingsLoading = isLoading;
    },
    [types.SET_ACCOMMODATION_DRAFTS](state, drafts) {
        state.myAccommodationDrafts = drafts.data;
    },
    [types.SET_MY_LISTINGS](state, listings) {
        state.myListings = listings.data;
    },
    [types.SET_CURRENT_LISTING](state, listing) {
        state.currentListing = listing.data;
    },
};
