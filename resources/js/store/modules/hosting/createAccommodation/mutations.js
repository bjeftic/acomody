import * as types from "./mutation-types";

export default {
    [types.SET_ACCOMMODATION_TYPES](state, accommodationTypes) {
        state.accommodationTypes = accommodationTypes.data;
    },
    [types.SET_CREATE_ACCOMMODATION_STEP](state, step) {
        state.currentStep = step;
    },
    [types.INCREMENT_CURRENT_STEP](state) {
        state.currentStep += 1;
    },
    [types.DECREMENT_CURRENT_STEP](state) {
        state.currentStep -= 1;
    },
};
