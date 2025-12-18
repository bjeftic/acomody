import * as types from "./mutation-types";

export default {
    [types.SET_ACCOMMODATION_DRAFT_STATS](state, stats) {
        state.accommodationDraftStats = stats.data;
    },
    [types.SET_HOSTING_LOADING](state, isLoading) {
        state.hostingLoading = isLoading;
    },

    //Fees
    [types.SET_FEE_TYPES](state, feeTypes) {
        state.feeTypes = feeTypes.data.reduce((acc, fee) => {
            const listing = fee.listing;
            if (!acc[listing]) {
                acc[listing] = [];
            }
            acc[listing].push(fee);
            return acc;
        }, {});
    },

    [types.SET_FEE_CHARGE_TYPES](state, chargeTypes) {
        state.feeChargeTypes = chargeTypes.data;
    }
};
