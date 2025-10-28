import * as types from "./mutation-types";

export default {
    [types.SET_ACCOMMODATION_DRAFT](state, accommodationDraft) {
        state.accommodationDraft = accommodationDraft.data;
    },
};
