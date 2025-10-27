import * as types from "./mutation-types";

export default {
    [types.SET_ACCOMMODATION_TYPES](state, accommodationTypes) {
        state.accommodationTypes = accommodationTypes.data;
    },
};
