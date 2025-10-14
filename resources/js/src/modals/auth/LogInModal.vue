<template>
    <fwb-modal v-if="show" @close="close" size="md">
        <template #header>
            <div class="flex items-center font-semibold text-gray-900">
                Log in
            </div>
        </template>
        <template #body>
            <p class="text-gray-600 mb-6">Please log in to your account</p>

            <form @submit.prevent="handleLogIn">
                <!-- Validation Alert Box -->
                <validation-alert-box
                    v-if="Object.keys(logInErrors).length > 0"
                    :errors="logInErrors"
                    class="mb-4"
                ></validation-alert-box>

                <!-- Email Field -->
                <div class="mb-4">
                    <label
                        for="email"
                        class="block mb-2 text-sm font-medium text-gray-900"
                    >
                        Email address
                    </label>
                    <fwb-input
                        v-model="formData.email"
                        type="email"
                        placeholder="john@example.com"
                        :validation-status="
                            logInErrors.email ? 'error' : undefined
                        "
                    />
                    <p
                        v-if="logInErrors.email"
                        class="mt-1 text-sm text-red-600"
                    >
                        {{ logInErrors.email[0] }}
                    </p>
                </div>

                <!-- Password Field -->
                <div class="mb-4">
                    <label
                        for="password"
                        class="block mb-2 text-sm font-medium text-gray-900"
                    >
                        Password
                    </label>
                    <fwb-input
                        v-model="formData.password"
                        type="password"
                        placeholder="Enter your password"
                        :validation-status="
                            logInErrors.password ? 'error' : undefined
                        "
                    />
                    <p
                        v-if="logInErrors.password"
                        class="mt-1 text-sm text-red-600"
                    >
                        {{ logInErrors.password[0] }}
                    </p>
                </div>

                <!-- Remember Me Checkbox -->
                <div class="flex items-center mb-4">
                    <fwb-checkbox
                        v-model="formData.rememberMe"
                        label="Remember me"
                    />
                </div>

                <!-- Forgot Password Link -->
                <div class="mb-6">
                    <a
                        href="#"
                        @click.prevent="openForgotPasswordModal"
                        class="text-sm text-blue-600 hover:underline"
                    >
                        Forgot your password?
                    </a>
                </div>

                <!-- Login Button -->
                <fwb-button
                    type="submit"
                    color="blue"
                    class="w-full mb-4"
                    :loading="isLoading"
                    :disabled="isLoading"
                >
                    {{ isLoading ? "Logging in..." : "Log in" }}
                </fwb-button>
            </form>

            <!-- Divider -->
            <div class="flex items-center my-6">
                <div class="flex-1 border-t border-gray-300"></div>
                <span class="px-4 text-sm text-gray-500">or</span>
                <div class="flex-1 border-t border-gray-300"></div>
            </div>

            <!-- Social Login Buttons -->
            <div class="space-y-3">
                <fwb-button
                    color="alternative"
                    class="w-full"
                    @click="handleGoogleLogin"
                    :loading="isGoogleLoading"
                    :disabled="isGoogleLoading"
                >
                    <template #prefix>
                        <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24">
                            <path
                                fill="#4285F4"
                                d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                            />
                            <path
                                fill="#34A853"
                                d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                            />
                            <path
                                fill="#FBBC05"
                                d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"
                            />
                            <path
                                fill="#EA4335"
                                d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                            />
                        </svg>
                    </template>
                    Continue with Google
                </fwb-button>

                <fwb-button
                    color="alternative"
                    class="w-full"
                    @click="handleFacebookLogin"
                    :loading="isFacebookLoading"
                    :disabled="isFacebookLoading"
                >
                    <template #prefix>
                        <svg
                            class="w-5 h-5 mr-2"
                            viewBox="0 0 24 24"
                            fill="#1877F2"
                        >
                            <path
                                d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"
                            />
                        </svg>
                    </template>
                    Continue with Facebook
                </fwb-button>
            </div>

            <!-- Sign Up Link -->
            <div class="mt-6 text-center text-sm text-gray-600">
                Don't have an account?
                <a
                    href="#"
                    @click.prevent="openSignUpModal"
                    class="text-blue-600 hover:underline font-medium"
                >
                    Sign up
                </a>
            </div>
        </template>
    </fwb-modal>
</template>

<script>
import config from "@/config";
import { toCamelCase } from "@/utils/helpers";
import { mapState, mapActions } from "vuex";

const modalName = config.modals.logInModal;
const modalNameCamelCase = toCamelCase(modalName);

export default {
    name: "LogInModal",
    computed: {
        ...mapState({
            show: (state) =>
                state.modals[modalNameCamelCase]
                    ? state.modals[modalNameCamelCase].shown
                    : false,
            promise: (state) =>
                state.modals[modalNameCamelCase]
                    ? state.modals[modalNameCamelCase].promise
                    : null,
            resolve: (state) =>
                state.modals[modalNameCamelCase]
                    ? state.modals[modalNameCamelCase].resolve
                    : null,
            reject: (state) =>
                state.modals[modalNameCamelCase]
                    ? state.modals[modalNameCamelCase].reject
                    : null,
            options: (state) =>
                state.modals[modalNameCamelCase]
                    ? state.modals[modalNameCamelCase].options
                    : false,
        }),
    },
    data() {
        return {
            modalName,
            formData: {
                email: "",
                password: "",
                rememberMe: false,
            },
            isLoading: false,
            isGoogleLoading: false,
            isFacebookLoading: false,
            logInErrors: {},
        };
    },
    methods: {
        ...mapActions(["openModal", "initModal", "closeModal"]),
        ...mapActions("auth", ["logIn"]),
        async handleLogIn() {
            this.clearMessage();

            // Client-side validation
            const errors = this.validateForm();
            if (Object.keys(errors).length > 0) {
                this.logInErrors = errors;
                return;
            }

            this.isLoading = true;
            await this.logIn(this.formData)
                .then(() => {
                    console.log("Login successful");

                    // Just close modal and resolve promise
                    // Middleware will handle the redirect
                    this.ok();
                })
                .catch((e) => {
                    if (
                        e.response &&
                        e.response.data &&
                        e.response.data.error
                    ) {
                        this.logInErrors =
                            e.response.data.error.validation_errors || {};
                    } else {
                        this.logInErrors = {
                            general: ["An error occurred. Please try again."],
                        };
                    }
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },
        validateForm() {
            const errors = {};

            // Email validation
            if (!this.formData.email) {
                errors.email = ["Email address is required"];
            } else if (!this.isValidEmail(this.formData.email)) {
                errors.email = ["Please enter a valid email address"];
            }

            // Password validation
            if (!this.formData.password) {
                errors.password = ["Password is required"];
            } else if (this.formData.password.length < 6) {
                errors.password = ["Password must be at least 6 characters"];
            } else if (
                !/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(this.formData.password)
            ) {
                errors.password = [
                    "Password must contain at least one uppercase letter, one lowercase letter, and one number",
                ];
            }

            return errors;
        },
        isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        },
        clearMessage() {
            this.logInErrors = {};
        },
        openForgotPasswordModal() {
            this.close();
            this.openModal({
                modalName: "forgotPasswordModal",
            });
        },
        openSignUpModal() {
            this.close();
            this.openModal({
                modalName: "signUpModal",
            });
        },
        handleGoogleLogin() {
            this.isGoogleLoading = true;
            // Implement Google login logic
            setTimeout(() => {
                this.isGoogleLoading = false;
            }, 1000);
        },
        handleFacebookLogin() {
            this.isFacebookLoading = true;
            // Implement Facebook login logic
            setTimeout(() => {
                this.isFacebookLoading = false;
            }, 1000);
        },
        ok() {
            if (this.resolve !== null) {
                this.resolve({ success: true });
            }
            this.closeModal({ modalName: this.modalName });
        },
        close() {
            if (this.reject !== null) {
                this.reject(new Error("Login cancelled"));
            }

            Object.assign(this.$data, this.$options.data.call(this));
            this.closeModal({ modalName: this.modalName });
        },
    },
    created() {
        this.initModal({ modalName: this.modalName });
    },
};
</script>
