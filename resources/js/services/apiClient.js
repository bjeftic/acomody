import EnhancedFluentApiClient from "./enhancedFluentApi";
import store from "@/store";

const apiClient = new EnhancedFluentApiClient('http://localhost:8000', {
    initCsrf: true,
    publicEndpoints: new Set([
        "login",
        "logIn",
        "log-in",
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
        store.commit("auth/CLEAR_AUTH");

        if (typeof window !== "undefined" && window.Vue) {
            window.Vue.prototype.$bus &&
                window.Vue.prototype.$bus.$emit("auth:unauthorized");
        }
    },

    onForbidden: (error) => {
        console.warn("Access forbidden:", error.response?.data?.message);

        if (typeof window !== "undefined" && window.Vue) {
            window.Vue.prototype.$bus &&
                window.Vue.prototype.$bus.$emit("auth:forbidden", {
                    message: error.response?.data?.message || "Access denied",
                });
        }
    },

    csrfCookie: "/sanctum/csrf-cookie",
    loadSwagger: false,
});

export default apiClient;

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

export const createApiCall = (apiCallPromise) => {
    return async (...args) => {
        try {
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
