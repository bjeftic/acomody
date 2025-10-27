import * as actions from "@/store/modules/hosting/actions";
import mutations from "@/store/modules/hosting/mutations";
import state from "@/store/modules/hosting/state";
import createListing from "@/store/modules/hosting/createListing";

export default {
    namespaced: true,
    state,
    // getters,
    actions,
    mutations,
    modules: {
        createListing,
    },
};
