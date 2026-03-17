import * as actions from "@/store/modules/user/actions";
import * as getters from "@/store/modules/user/getters";
import mutations from "@/store/modules/user/mutations";
import state from "@/store/modules/user/state";

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations,
};
