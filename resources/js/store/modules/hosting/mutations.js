import * as types from "./mutation-types";

export default {
    [types.SET_ACCOMMODATION_DRAFT_EXISTS](state, exists) {
        state.accommodationDraftExists = exists;
    },
    [types.SET_HOSTING_LOADING](state, isLoading) {
        state.hostingLoading = isLoading;
    },
};
