import EnhancedFluentApiClient from "./enhancedFluentApi";
import store from "@/store";

const apiClient = new EnhancedFluentApiClient(window.location.origin, {
    initCsrf: true,
    publicEndpoints: new Set([
        "login",
        "logIn",
        "signUp",
        "sign-up",
        "register",
        "forgot-password",
        "reset-password",
        "verify-email",
        "auth",
        "public",
        "health",
        "csrf-cookie",
    ]),

    defaultAuthRequired: true,

    onUnauthorized: (error) => {
        // Clear authentication state in Vuex
        store.commit("auth/CLEAR_AUTH");

        // Optional: Emit event for components to listen to
        if (typeof window !== "undefined" && window.Vue) {
            window.Vue.prototype.$bus &&
                window.Vue.prototype.$bus.$emit("auth:unauthorized");
        }

        // Optional: Redirect to login page (uncomment if needed)
        // if (typeof window !== 'undefined' && window.location.pathname !== '/login') {
        //   window.location.href = '/login'
        // }
    },

    onForbidden: (error) => {
        console.warn("Access forbidden:", error.response?.data?.message);

        // Emit event for UI feedback
        if (typeof window !== "undefined" && window.Vue) {
            window.Vue.prototype.$bus &&
                window.Vue.prototype.$bus.$emit("auth:forbidden", {
                    message: error.response?.data?.message || "Access denied",
                });
        }
    },

    // CSRF cookie endpoint (default is '/sanctum/csrf-cookie')
    csrfCookie: "/sanctum/csrf-cookie",

    // Optional: Disable Swagger loading if not needed
    loadSwagger: false,
});

export default apiClient;

// Export additional utility functions
export const configureApiClient = (config) => {
    if (config.publicEndpoints) {
        apiClient.setPublicEndpoints(config.publicEndpoints);
    }

    if (config.onUnauthorized) {
        apiClient.setOnUnauthorized(config.onUnauthorized);
    }

    if (config.onForbidden) {
        apiClient.setOnForbidden(config.onForbidden);
    }

    return apiClient;
};

// Create API call wrapper with enhanced error handling
export const createApiCall = (apiCallPromise) => {
    return async (...args) => {
        try {
            // If apiCallPromise is a function, call it with args
            const response =
                typeof apiCallPromise === "function"
                    ? await apiCallPromise(...args)
                    : await apiCallPromise;

            if (response.success) {
                return response;
            } else {
                const error = new Error(
                    response.error?.message || "API call failed"
                );
                error.status = response.status;
                error.data = response.error;
                error.response = response;
                throw error;
            }
        } catch (error) {
            console.error("API call failed:", error);

            // Enhanced error logging for development
            if (process.env.NODE_ENV === "development") {
                console.group("API Error Details:");
                console.log("Status:", error.status);
                console.log("Data:", error.data);
                console.log("Response:", error.response);
                console.groupEnd();
            }

            throw error;
        }
    };
};
