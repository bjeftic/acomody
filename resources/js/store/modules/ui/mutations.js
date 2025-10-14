import * as types from "./mutation-types";

export default {
    [types.SET_CURRENCIES](state, { response }) {
        state.currencies = response.data;
    },
};
