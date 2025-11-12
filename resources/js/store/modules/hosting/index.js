import * as actions from "@/store/modules/hosting/actions";
import mutations from "@/store/modules/hosting/mutations";
import state from "@/store/modules/hosting/state";
import createAccommodation from "@/store/modules/hosting/createAccommodation";
import listings from "@/store/modules/hosting/listings";

export default {
    namespaced: true,
    state,
    // getters,
    actions,
    mutations,
    modules: {
        createAccommodation,
        listings,
    },
};
