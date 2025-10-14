<template>
    <div
        class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8"
    >
        <div
            class="max-w-md w-full space-y-8 bg-white p-8 rounded-lg shadow-md"
        >
            <div>
                <h2 class="text-center text-3xl font-bold text-gray-900">
                    Reset Password
                </h2>
            </div>

            <div
                v-if="!restarted || Object.keys(resetPasswordErrors).length > 0"
            >
                <p class="text-gray-600 mb-6">Enter your new password</p>

                <form @submit.prevent="handleResetPassword">
                    <!-- Validation Alert Box -->
                    <validation-alert-box
                        v-if="Object.keys(resetPasswordErrors).length > 0"
                        :errors="resetPasswordErrors"
                        class="mb-4"
                    ></validation-alert-box>

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
                                resetPasswordErrors.password
                                    ? 'error'
                                    : undefined
                            "
                        />
                        <p
                            v-if="resetPasswordErrors.password"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ resetPasswordErrors.password[0] }}
                        </p>
                    </div>

                    <!-- Confirm Password Field -->
                    <div class="mb-6">
                        <label
                            for="confirmPassword"
                            class="block mb-2 text-sm font-medium text-gray-900"
                        >
                            Confirm Password
                        </label>
                        <fwb-input
                            v-model="formData.confirmPassword"
                            type="password"
                            placeholder="Confirm your password"
                            :validation-status="
                                resetPasswordErrors.confirmPassword
                                    ? 'error'
                                    : undefined
                            "
                        />
                        <p
                            v-if="resetPasswordErrors.confirmPassword"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ resetPasswordErrors.confirmPassword[0] }}
                        </p>
                    </div>

                    <!-- Reset Password Button -->
                    <fwb-button
                        type="submit"
                        color="blue"
                        class="w-full"
                        :loading="isLoading"
                        :disabled="isLoading"
                    >
                        {{
                            isLoading
                                ? "Resetting password..."
                                : "Reset password"
                        }}
                    </fwb-button>
                </form>
            </div>

            <!-- Success Message -->
            <div v-else class="text-center">
                <div class="mb-6">
                    <svg
                        class="mx-auto h-12 w-12 text-green-500"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                </div>
                <p class="text-gray-700 mb-6 text-lg">
                    Password reset successfully! Please log in with your new
                    password.
                </p>
                <fwb-button color="blue" class="w-full" @click="openLogInModal">
                    Go to log in
                </fwb-button>
            </div>
        </div>
    </div>
</template>

<script>
import { mapActions } from "vuex";

export default {
    name: "ResetPassword",
    computed: {
        resetToken() {
            return this.$route.query.token ?? null;
        },
        resetEmail() {
            return this.$route.query.email ?? null;
        },
    },
    data() {
        return {
            formData: {
                password: "",
                confirmPassword: "",
            },
            isLoading: false,
            resetPasswordErrors: {},
            restarted: false,
        };
    },
    methods: {
        ...mapActions(["openModal"]),
        ...mapActions("auth", ["resetPassword"]),
        async handleResetPassword() {
            this.clearMessage();

            // Client-side validation
            const errors = this.validateForm();
            if (Object.keys(errors).length > 0) {
                this.resetPasswordErrors = errors;
                return;
            }

            this.isLoading = true;
            await this.resetPassword({
                email: this.resetEmail,
                password: this.formData.password,
                confirmPassword: this.formData.confirmPassword,
                token: this.resetToken,
            })
                .then(() => {
                    this.restarted = true;
                })
                .catch((e) => {
                    if (
                        e.response &&
                        e.response.data &&
                        e.response.data.error
                    ) {
                        this.resetPasswordErrors =
                            e.response.data.error.validation_errors || {};
                    } else {
                        this.resetPasswordErrors = {
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

            // Confirm Password validation
            if (!this.formData.confirmPassword) {
                errors.confirmPassword = ["Please confirm your password"];
            } else if (
                this.formData.password !== this.formData.confirmPassword
            ) {
                errors.confirmPassword = ["Passwords do not match"];
            }

            return errors;
        },
        clearMessage() {
            this.resetPasswordErrors = {};
        },
        openLogInModal() {
            this.openModal({
                modalName: "logInModal",
            });
        },
    },
};
</script>
