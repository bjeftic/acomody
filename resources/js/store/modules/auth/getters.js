export const isLoggedIn = (state, getters, rootState) => {
  return state.isAuthenticated &&
         rootState.user.currentUser !== null &&
         rootState.user.currentUser !== undefined;
};
export const isAuthInitialized = (state) => state.isInitialized;
export const isSessionValid = (state, getters, rootState) => {
  return state.isInitialized &&
         state.isAuthenticated &&
         rootState.user.currentUser !== null;
};
