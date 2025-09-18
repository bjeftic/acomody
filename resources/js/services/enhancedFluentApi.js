import axios from "axios";

class FluentApiBuilder {
    constructor(baseClient, path = "") {
        this.baseClient = baseClient;
        this.path = path;
        this.queryParams = {};
        this.requestConfig = {};
        this._requiresAuth = true; // default: all requests require auth
    }

    // Path segment
    _addPath(segment) {
        const builder = new FluentApiBuilder(
            this.baseClient,
            this.path ? `${this.path}/${segment}` : segment
        );
        builder._requiresAuth = this._requiresAuth;
        return builder;
    }

    // Query params
    query(params) {
        this.queryParams = { ...this.queryParams, ...params };
        return this;
    }

    // Request config (headers, timeout, etc.)
    config(config) {
        this.requestConfig = { ...this.requestConfig, ...config };
        return this;
    }

    noAuth() {
        this._requiresAuth = false;
        return this;
    }

    withAuth() {
        this._requiresAuth = true;
        return this;
    }

    // HTTP Methods
    async get() {
        return await this.baseClient.request(
            "GET",
            this.path,
            null,
            this.queryParams,
            {
                ...this.requestConfig,
                _requiresAuth: this._requiresAuth,
            }
        );
    }

    async post(data = null) {
        return await this.baseClient.request(
            "POST",
            this.path,
            data,
            this.queryParams,
            {
                ...this.requestConfig,
                _requiresAuth: this._requiresAuth,
            }
        );
    }

    async put(data = null) {
        return await this.baseClient.request(
            "PUT",
            this.path,
            data,
            this.queryParams,
            {
                ...this.requestConfig,
                _requiresAuth: this._requiresAuth,
            }
        );
    }

    async patch(data = null) {
        return await this.baseClient.request(
            "PATCH",
            this.path,
            data,
            this.queryParams,
            {
                ...this.requestConfig,
                _requiresAuth: this._requiresAuth,
            }
        );
    }

    async delete() {
        return await this.baseClient.request(
            "DELETE",
            this.path,
            null,
            this.queryParams,
            {
                ...this.requestConfig,
                _requiresAuth: this._requiresAuth,
            }
        );
    }
}

class FluentApiClient {
    constructor(baseURL = window.location.origin, options = {}) {
        this.baseURL = baseURL;
        this.options = {
            publicEndpoints: new Set([
                "login",
                "register",
                "forgot-password",
                "reset-password",
            ]),
            defaultAuthRequired: true,
            onUnauthorized: null,
            onForbidden: null,
            csrfCookie: "/sanctum/csrf-cookie",
            ...options,
        };

        // Initialize axios with Sanctum SPA configuration
        this.api = axios.create({
            baseURL: `${baseURL}/api`,
            withCredentials: true, // Essential for cookie-based auth
            headers: {
                Accept: "application/json",
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest", // Required for Laravel to recognize as AJAX
            },
        });

        // Request interceptor for CSRF protection and auth
        this.api.interceptors.request.use(async (config) => {
            // For state-changing methods, ensure CSRF cookie is set
            if (
                ["post", "put", "patch", "delete"].includes(
                    config.method?.toLowerCase()
                )
            ) {
                await this.ensureCsrfCookie();
            }

            // Remove custom properties before sending
            delete config._requiresAuth;

            return config;
        });

        // Response interceptor for error handling
        this.api.interceptors.response.use(
            (response) => response,
            (error) => {
                const status = error.response?.status;

                if (status === 401) {
                    // Unauthorized - user not authenticated
                    if (this.options.onUnauthorized) {
                        this.options.onUnauthorized(error);
                    } else {
                        // Default behavior for SPA
                        this.redirectToLogin();
                    }
                } else if (status === 403) {
                    // Forbidden - user authenticated but not authorized
                    if (this.options.onForbidden) {
                        this.options.onForbidden(error);
                    } else {
                        console.warn(
                            "Access forbidden:",
                            error.response?.data?.message
                        );
                    }
                } else if (status === 419) {
                    // CSRF token mismatch - refresh and retry
                    return this.handleCsrfError(error);
                }

                return Promise.reject(error);
            }
        );

        // Create Proxy for dynamic properties
        return new Proxy(this, {
            get(target, prop, receiver) {
                // If there is a method on the class, use it
                if (prop in target) {
                    return target[prop];
                }

                // Otherwise create a new FluentApiBuilder with prop as path
                const builder = new FluentApiBuilder(target, String(prop));

                // Check if endpoint is public
                if (target.options.publicEndpoints.has(String(prop))) {
                    builder._requiresAuth = false;
                } else {
                    builder._requiresAuth = target.options.defaultAuthRequired;
                }

                return builder;
            },
        });
    }

    // Ensure CSRF cookie is set before making state-changing requests
    async ensureCsrfCookie() {
        try {
            await axios.get(`${this.baseURL}${this.options.csrfCookie}`, {
                withCredentials: true,
            });
        } catch (error) {
            console.warn("Failed to fetch CSRF cookie:", error.message);
        }
    }

    // Handle CSRF token mismatch
    async handleCsrfError(originalError) {
        try {
            // Refresh CSRF token
            await this.ensureCsrfCookie();

            // Retry the original request
            const config = originalError.config;
            return await this.api.request(config);
        } catch (error) {
            return Promise.reject(originalError);
        }
    }

    // Redirect to login (SPA-friendly)
    redirectToLogin() {
        if (typeof window !== "undefined") {
            // For SPA, you might want to emit an event or update global state
            // instead of hard redirect
            if (window.Vue && window.Vue.prototype.$router) {
                window.Vue.prototype.$router.push("/login");
            } else {
                window.location.href = "/login";
            }
        }
    }

    async request(method, path, data = null, params = {}, config = {}) {
        // Validate endpoint if Swagger docs are loaded
        const isValid = this._validateEndpoint
            ? this._validateEndpoint(method, path)
            : true;

        if (!isValid && process.env.NODE_ENV === "development") {
            console.warn(
                `Proceeding with unvalidated endpoint: ${method} /${path}`
            );
        } else if (!isValid && process.env.NODE_ENV === "production") {
            return Promise.reject({
                success: false,
                error: {
                    message: `Endpoint not found in API documentation: ${method} /${path}`,
                    type: "ENDPOINT_NOT_FOUND",
                },
                status: 404,
                headers: {},
            });
        }

        try {
            const requestConfig = {
                method,
                url: `/${path}`,
                ...config,
            };

            if (data) requestConfig.data = data;
            if (Object.keys(params).length > 0) requestConfig.params = params;

            const response = await this.api(requestConfig);

            return {
                success: true,
                data: response.data,
                status: response.status,
                headers: response.headers,
            };
        } catch (error) {
            return Promise.reject({
                success: false,
                error: error.response?.data || error.message,
                status: error.response?.status,
                headers: error.response?.headers,
            });
        }
    }

    // Authentication methods for Sanctum SPA
    async login(credentials) {
        // First, get CSRF cookie
        await this.ensureCsrfCookie();

        // Then attempt login
        return await this.request(
            "POST",
            "login",
            credentials,
            {},
            { _requiresAuth: false }
        );
    }

    async logout() {
        const result = await this.request("POST", "logout");
        // After logout, you might want to redirect or clear app state
        return result;
    }

    async register(userData) {
        await this.ensureCsrfCookie();
        return await this.request(
            "POST",
            "register",
            userData,
            {},
            { _requiresAuth: false }
        );
    }

    async getUser() {
        return await this.request("GET", "user");
    }

    // Helper methods
    setPublicEndpoints(endpoints) {
        this.options.publicEndpoints = new Set(endpoints);
        return this;
    }

    addPublicEndpoint(endpoint) {
        this.options.publicEndpoints.add(endpoint);
        return this;
    }

    setDefaultAuthRequired(required) {
        this.options.defaultAuthRequired = required;
        return this;
    }

    setOnUnauthorized(callback) {
        this.options.onUnauthorized = callback;
        return this;
    }

    setOnForbidden(callback) {
        this.options.onForbidden = callback;
        return this;
    }

    // Check authentication status
    async checkAuth() {
        try {
            const result = await this.getUser();
            return result.success && result.data;
        } catch (error) {
            return false;
        }
    }
}

// Enhanced FluentApiBuilder with bracket notation and camelCase conversion
class EnhancedFluentApiBuilder extends FluentApiBuilder {
    constructor(baseClient, path = "") {
        super(baseClient, path);

        return new Proxy(this, {
            get(target, prop, receiver) {
                // Handle Symbol properties (like Symbol.iterator)
                if (typeof prop === 'symbol') {
                    return target[prop];
                }

                // If it's a method on the object, return it
                if (prop in target && typeof target[prop] === 'function') {
                    return target[prop];
                }

                // If it's another property on the object
                if (prop in target) {
                    return target[prop];
                }

                // If it's a number or a string that looks like a number
                if (!isNaN(prop) && prop !== "") {
                    return target._addPath(String(prop));
                }

                // Convert camelCase to kebab-case and add as path segment
                const kebabProp = target._camelToKebab(String(prop));
                return target._addPath(kebabProp);
            },
            
            // Add support for bracket notation
            set(target, prop, value, receiver) {
                // This allows for custom property setting if needed
                target[prop] = value;
                return true;
            },

            // Handle property access via brackets
            has(target, prop) {
                // Return true for any property to enable bracket access
                return true;
            }
        });
    }

    // Convert camelCase to kebab-case
    _camelToKebab(str) {
        return str.replace(/([a-z])([A-Z])/g, "$1-$2").toLowerCase();
    }

    // Override _addPath to return a new proxied builder
    _addPath(segment) {
        const builder = new EnhancedFluentApiBuilder(
            this.baseClient,
            this.path ? `${this.path}/${segment}` : segment
        );
        builder._requiresAuth = this._requiresAuth;
        builder.queryParams = { ...this.queryParams };
        builder.requestConfig = { ...this.requestConfig };
        return builder;
    }
}

// Enhanced FluentApiClient for Laravel Sanctum SPA
class EnhancedFluentApiClient extends FluentApiClient {
    constructor(baseURL = window.location.origin, options = {}) {
        super(baseURL, options);

        // Available endpoints from Swagger documentation
        this.availableEndpoints = new Set();
        this.swaggerLoaded = false;

        // Load Swagger docs if not explicitly disabled
        if (options.loadSwagger !== false) {
            this.loadSwaggerDocs();
        }

        return new Proxy(this, {
            get(target, prop, receiver) {
                // Handle Symbol properties
                if (typeof prop === 'symbol') {
                    return target[prop];
                }

                // If it's a method or property on the class, use it
                if (prop in target) {
                    return target[prop];
                }

                // Convert camelCase to kebab-case
                const kebabProp = target._camelToKebab(String(prop));
                const builder = new EnhancedFluentApiBuilder(target, kebabProp);

                // Check if the endpoint is public
                if (target.options.publicEndpoints.has(kebabProp)) {
                    builder._requiresAuth = false;
                } else {
                    builder._requiresAuth = target.options.defaultAuthRequired;
                }

                return builder;
            },

            // Support for bracket notation on the main client
            has(target, prop) {
                return true;
            }
        });
    }

    // Load Swagger documentation
    async loadSwaggerDocs() {
        try {
            const response = await axios.get(`${this.baseURL}/docs`);
            const swagger = response.data;

            // Extract all endpoints from Swagger
            Object.keys(swagger.paths).forEach((path) => {
                const methods = swagger.paths[path];
                Object.keys(methods).forEach((method) => {
                    const normalizedPath = path.replace(/^\/api\//, "");
                    const endpoint = `${method.toUpperCase()} /${normalizedPath}`;
                    this.availableEndpoints.add(endpoint);
                });
            });

            this.swaggerLoaded = true;
            console.log(
                `Loaded ${this.availableEndpoints.size} API endpoints from Swagger`
            );

            if (process.env.NODE_ENV === "development") {
                console.log(
                    "Available endpoints:",
                    Array.from(this.availableEndpoints).sort()
                );
            }
        } catch (error) {
            console.warn(
                "Could not load Swagger documentation:",
                error.message
            );
            console.log(
                "Endpoint validation disabled - all requests will be allowed"
            );
        }
    }

    // Check if endpoint exists in Swagger docs
    _validateEndpoint(method, path) {
        if (!this.swaggerLoaded || this.availableEndpoints.size === 0) {
            return true;
        }

        const endpoint = `${method.toUpperCase()} /${path}`;
        const exists = this.availableEndpoints.has(endpoint);

        if (!exists && process.env.NODE_ENV === "development") {
            console.error(`API Endpoint not found: ${endpoint}`);
            console.trace("Stack trace for debugging:");
        }

        return exists;
    }

    // Convert camelCase to kebab-case
    _camelToKebab(str) {
        return str.replace(/([a-z])([A-Z])/g, "$1-$2").toLowerCase();
    }
}

export default EnhancedFluentApiClient;
export {
    FluentApiClient,
    EnhancedFluentApiClient,
    FluentApiBuilder,
    EnhancedFluentApiBuilder,
};