import * as types from "./mutation-types";

export default {
    [types.SET_SECTIONS](state, sections) {
        state.sections = sections;
    },

    [types.SET_LOADING](state, loading) {
        state.loading = loading;
    },
};
