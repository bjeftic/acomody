export const currentUser = (state) => state.currentUser;
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
export const userDisplayName = (state) => {
  if (!state.currentUser) return null;
  
  const user = state.currentUser;
  return user.full_name || 
         `${user.first_name || ''} ${user.last_name || ''}`.trim() || 
         user.name || 
         user.username || 
         user.email || 
         'User';
};

export const userInitials = (state) => {
  if (!state.currentUser) return '';
  
  const user = state.currentUser;
  const firstName = user.first_name || user.name?.split(' ')[0] || '';
  const lastName = user.last_name || user.name?.split(' ')[1] || '';
  
  return `${firstName.charAt(0)}${lastName.charAt(0)}`.toUpperCase();
};
