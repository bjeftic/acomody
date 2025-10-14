export default {
    token:
        localStorage.getItem("token") ||
        sessionStorage.getItem("token") ||
        null,
    isAuthenticated: false,
    isInitialized: false,
    tokenExpiration:
        localStorage.getItem("token_expiration") ||
        sessionStorage.getItem("token_expiration") ||
        null,
};
