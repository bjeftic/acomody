export const isLoggedIn = (state) => {
  return state.isAuthenticated &&
         state.currentUser !== null &&
         state.currentUser !== undefined;
};
export const isAuthInitialized = (state) => state.isInitialized;
export const isSessionValid = (state) => {
  return state.isInitialized &&
         state.isAuthenticated &&
         state.currentUser !== null;
};
