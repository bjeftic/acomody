import * as types from "./mutation-types";
import state from "./state";

export default {
    [types.SET_ACCOMMODATION_LOADING](state, data) {
        state.loading = data;
    },
    [types.SET_ACCOMMODATION](state, accommodation) {
        if (accommodation) {
            state.accommodation = accommodation.data
        } else {
            state.accommodation = null;
        }
    },
};
