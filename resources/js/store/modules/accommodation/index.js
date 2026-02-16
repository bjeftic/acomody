import * as actions from "@/store/modules/accommodation/actions";
import mutations from "@/store/modules/accommodation/mutations";
import state from "@/store/modules/accommodation/state";
import * as getters from "@/store/modules/accommodation/getters";

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations,
};
