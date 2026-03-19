import * as actions from "@/store/modules/home/actions";
import * as getters from "@/store/modules/home/getters";
import mutations from "@/store/modules/home/mutations";
import state from "@/store/modules/home/state";

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
};
