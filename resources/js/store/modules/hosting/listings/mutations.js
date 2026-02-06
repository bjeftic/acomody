import * as types from "./mutation-types";

export default {
    [types.SET_MY_LISTINGS_LOADING](state, isLoading) {
        state.myListingsLoading = isLoading;
    },
    [types.SET_ACCOMMODATION_DRAFTS](state, drafts) {
        state.myAccommodationDrafts = drafts.data;
    },
    [types.SET_ACCOMMODATIONS](state, accommodations) {
        state.accommodations = accommodations.data;
    },
    [types.SET_ACCOMMODATION](state, accommodation) {
        state.accommodation = accommodation.data;
    },
};
