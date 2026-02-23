import axios from "axios";

class FluentApiBuilder {
    constructor(baseClient, path = "") {
        this.baseClient = baseClient;
        this.path = path;
        this.queryParams = {};
        this.requestConfig = {};
        this._requiresAuth = true;
    }

    _addPath(segment) {
        const builder = new FluentApiBuilder(
            this.baseClient,
            this.path ? `${this.path}/${segment}` : segment
        );
        builder._requiresAuth = this._requiresAuth;
        return builder;
    }

    query(params) {
        this.queryParams = { ...this.queryParams, ...params };
        return this;
    }

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

    async get() {
        return await this.baseClient.request(
            "GET",
            this.path,
            null,
            this.queryParams,
            { ...this.requestConfig, _requiresAuth: this._requiresAuth }
        );
    }

    async post(data = null) {
        return await this.baseClient.request(
            "POST",
            this.path,
            data,
            this.queryParams,
            { ...this.requestConfig, _requiresAuth: this._requiresAuth }
        );
    }

    async put(data = null) {
        return await this.baseClient.request(
            "PUT",
            this.path,
            data,
            this.queryParams,
            { ...this.requestConfig, _requiresAuth: this._requiresAuth }
        );
    }

    async patch(data = null) {
        return await this.baseClient.request(
            "PATCH",
            this.path,
            data,
            this.queryParams,
            { ...this.requestConfig, _requiresAuth: this._requiresAuth }
        );
    }

    async delete() {
        return await this.baseClient.request(
            "DELETE",
            this.path,
            null,
            this.queryParams,
            { ...this.requestConfig, _requiresAuth: this._requiresAuth }
        );
    }

    async upload(files, fieldName = "file", onProgress = null) {
        const formData = new FormData();

        if (Array.isArray(files)) {
            files.forEach((file) => {
                formData.append(`${fieldName}[]`, file);
            });
        } else {
            formData.append(fieldName, files);
        }

        return await this
            .config({
                onUploadProgress: onProgress
                    ? (progressEvent) => {
                          const percentCompleted = Math.round(
                              (progressEvent.loaded * 100) / progressEvent.total
                          );
                          onProgress(percentCompleted);
                      }
                    : undefined,
            })
            .post(formData);
    }
}

class FluentApiClient {
    constructor(baseURL = window.location.origin, options = {}) {
        this.baseURL = baseURL;
        this.csrfInitialized = false;
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

        this.api = axios.create({
            baseURL: `${baseURL}/api`,
            withCredentials: true,
            xsrfCookieName: "XSRF-TOKEN",
            xsrfHeaderName: "X-XSRF-TOKEN",
            headers: {
                Accept: "application/json",
                "X-Requested-With": "XMLHttpRequest",
            },
        });

        // Request interceptor
        this.api.interceptors.request.use(async (config) => {
            // Handle Content-Type
            if (config.data instanceof FormData) {
                delete config.headers["Content-Type"];
            } else if (!config.headers["Content-Type"]) {
                config.headers["Content-Type"] = "application/json";
            }

            // Ensure CSRF cookie is set before state-changing requests
            if (
                ["post", "put", "patch", "delete"].includes(
                    config.method?.toLowerCase()
                ) &&
                !this.csrfInitialized
            ) {
                await this.ensureCsrfCookie();
            }

            delete config._requiresAuth;

            return config;
        });

        // Response interceptor
        this.api.interceptors.response.use(
            (response) => response,
            async (error) => {
                const status = error.response?.status;

                if (status === 401) {
                    if (this.options.onUnauthorized) {
                        this.options.onUnauthorized(error);
                    } else {
                        this.redirectToLogin();
                    }
                } else if (status === 403) {
                    if (this.options.onForbidden) {
                        this.options.onForbidden(error);
                    }
                } else if (status === 419) {
                    // CSRF mismatch — refresh cookie and retry once
                    return this.handleCsrfError(error);
                }

                return Promise.reject(error);
            }
        );

        return new Proxy(this, {
            get(target, prop, receiver) {
                if (prop in target) {
                    return target[prop];
                }

                const builder = new FluentApiBuilder(target, String(prop));

                if (target.options.publicEndpoints.has(String(prop))) {
                    builder._requiresAuth = false;
                } else {
                    builder._requiresAuth = target.options.defaultAuthRequired;
                }

                return builder;
            },
        });
    }

    async ensureCsrfCookie() {
        try {
            await axios.get(`${this.baseURL}${this.options.csrfCookie}`, {
                withCredentials: true,
            });
            this.csrfInitialized = true;
        } catch (error) {
            console.warn("Failed to fetch CSRF cookie:", error.message);
        }
    }

    async handleCsrfError(originalError) {
        try {
            // Reset and re-fetch CSRF cookie
            this.csrfInitialized = false;
            await this.ensureCsrfCookie();

            // Retry original request
            return await this.api.request(originalError.config);
        } catch (error) {
            return Promise.reject(originalError);
        }
    }

    redirectToLogin() {
        if (typeof window !== "undefined") {
            if (window.Vue && window.Vue.prototype.$router) {
                window.Vue.prototype.$router.push("/login");
            } else {
                window.location.href = "/login";
            }
        }
    }

    async request(method, path, data = null, params = {}, config = {}) {
        const isValid = this._validateEndpoint
            ? this._validateEndpoint(method, path)
            : true;

        if (!isValid && process.env.NODE_ENV === "development") {
            console.warn(`Proceeding with unvalidated endpoint: ${method} /${path}`);
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

    async logout() {
        const result = await this.request("POST", "logout");
        this.csrfInitialized = false;
        return result;
    }

    async getUser() {
        return await this.request("GET", "user");
    }

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

    resetCsrf() {
        this.csrfInitialized = false;
        return this;
    }

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
                if (typeof prop === "symbol") {
                    return target[prop];
                }

                if (prop in target && typeof target[prop] === "function") {
                    return target[prop];
                }

                if (prop in target) {
                    return target[prop];
                }

                if (!isNaN(prop) && prop !== "") {
                    return target._addPath(String(prop));
                }

                const kebabProp = target._camelToKebab(String(prop));
                return target._addPath(kebabProp);
            },

            set(target, prop, value) {
                target[prop] = value;
                return true;
            },

            has(target, prop) {
                return true;
            },
        });
    }

    _camelToKebab(str) {
        return str.replace(/([a-z])([A-Z])/g, "$1-$2").toLowerCase();
    }

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

        this.availableEndpoints = new Set();
        this.swaggerLoaded = false;

        if (options.loadSwagger !== false) {
            this.loadSwaggerDocs();
        }

        return new Proxy(this, {
            get(target, prop, receiver) {
                if (typeof prop === "symbol") {
                    return target[prop];
                }

                if (prop in target) {
                    return target[prop];
                }

                const kebabProp = target._camelToKebab(String(prop));
                const builder = new EnhancedFluentApiBuilder(target, kebabProp);

                if (target.options.publicEndpoints.has(kebabProp)) {
                    builder._requiresAuth = false;
                } else {
                    builder._requiresAuth = target.options.defaultAuthRequired;
                }

                return builder;
            },

            has(target, prop) {
                return true;
            },
        });
    }

    async loadSwaggerDocs() {
        try {
            const response = await axios.get(`${this.baseURL}/docs`);
            const swagger = response.data;

            Object.keys(swagger.paths).forEach((path) => {
                const methods = swagger.paths[path];
                Object.keys(methods).forEach((method) => {
                    const normalizedPath = path.replace(/^\/api\//, "");
                    const endpoint = `${method.toUpperCase()} /${normalizedPath}`;
                    this.availableEndpoints.add(endpoint);
                });
            });

            this.swaggerLoaded = true;
        } catch (error) {
            console.warn("Could not load Swagger documentation:", error.message);
        }
    }

    _validateEndpoint(method, path) {
        if (!this.swaggerLoaded || this.availableEndpoints.size === 0) {
            return true;
        }

        const endpoint = `${method.toUpperCase()} /${path}`;
        return this.availableEndpoints.has(endpoint);
    }

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