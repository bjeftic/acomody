import * as actions from "@/store/modules/notifications/actions";
import * as getters from "@/store/modules/notifications/getters";
import mutations from "@/store/modules/notifications/mutations";
import state from "@/store/modules/notifications/state";

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
};
