import * as actions from "@/store/modules/auth/actions";
import mutations from "@/store/modules/auth/mutations";
import state from "@/store/modules/auth/state";
import * as getters from "@/store/modules/auth/getters";

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations,
};
