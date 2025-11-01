import * as types from "./mutation-types";

export default {
    [types.SET_ACCOMMODATION_DRAFT](state, accommodationDraft) {
        state.accommodationDraft = accommodationDraft.data;
    },
    [types.SET_HOSTING_LOADING](state, isLoading) {
        state.hostingLoading = isLoading;
    },
};
