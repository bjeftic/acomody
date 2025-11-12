import * as types from "./mutation-types";

export default {
    [types.SET_CURRENT_USER](state, { data }) {
            state.currentUser = data;
        },
};
