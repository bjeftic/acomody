<template>
    <BaseModal v-if="show" @close="close" size="md">
        <template #header>Sign Up</template>
        <template #body>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                Please fill in the details to sign up
            </p>

            <validation-alert-box
                v-if="Object.keys(signUpErrors).length > 0"
                :errors="signUpErrors"
                class="mb-4"
            />

            <form @submit.prevent="handleSignUp" class="flex flex-col gap-4">
                <BaseInput
                    v-model="formData.email"
                    type="email"
                    label="Email address"
                    placeholder="john@example.com"
                    :error="signUpErrors.email ? signUpErrors.email[0] : null"
                />

                <BaseInput
                    v-model="formData.password"
                    type="password"
                    label="Password"
                    placeholder="Enter your password"
                    :error="signUpErrors.password ? signUpErrors.password[0] : null"
                />

                <BaseInput
                    v-model="formData.confirmPassword"
                    type="password"
                    label="Confirm password"
                    placeholder="Confirm your password"
                    :error="signUpErrors.confirmPassword ? signUpErrors.confirmPassword[0] : null"
                />

                <p class="text-xs text-gray-500 dark:text-gray-400">
                    By creating an account, you're agreeing with our
                    <a href="#" class="text-primary-600 hover:text-primary-700 dark:text-primary-400">Terms of Use</a>
                    and
                    <a href="#" class="text-primary-600 hover:text-primary-700 dark:text-primary-400">Privacy Policy</a>
                </p>

                <BaseButton
                    type="submit"
                    :loading="isLoading"
                    :disabled="isLoading || isProcessing"
                    full
                >
                    {{ isLoading ? "Signing up..." : "Sign up" }}
                </BaseButton>
            </form>

            <div class="flex items-center gap-3 my-5">
                <div class="flex-1 border-t border-gray-200 dark:border-gray-700"></div>
                <span class="text-xs text-gray-400">or</span>
                <div class="flex-1 border-t border-gray-200 dark:border-gray-700"></div>
            </div>

            <div class="flex flex-col gap-2">
                <BaseButton
                    variant="secondary"
                    full
                    :loading="isGoogleLoading"
                    :disabled="isGoogleLoading"
                    @click="handleGoogleLogin"
                >
                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    Continue with Google
                </BaseButton>

                <BaseButton
                    variant="secondary"
                    full
                    :loading="isFacebookLoading"
                    :disabled="isFacebookLoading"
                    @click="handleFacebookLogin"
                >
                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="#1877F2">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                    Continue with Facebook
                </BaseButton>
            </div>

            <p class="mt-5 text-center text-sm text-gray-500 dark:text-gray-400">
                Already have an account?
                <a href="#" @click.prevent="openLogInModal" class="text-primary-600 hover:text-primary-700 dark:text-primary-400 font-medium">
                    Log in
                </a>
            </p>
        </template>
    </BaseModal>
</template>

<script>
import config from "@/config";
import { toCamelCase } from "@/utils/helpers";
import { mapState, mapActions } from "vuex";

const modalName = config.modals.signUpModal;
const modalNameCamelCase = toCamelCase(modalName);

export default {
    name: "SignUpModal",
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
                confirmPassword: "",
            },
            isLoading: false,
            isProcessing: false,
            isGoogleLoading: false,
            isFacebookLoading: false,
            signUpErrors: {},
        };
    },
    methods: {
        ...mapActions(["initModal", "closeModal", "openModal"]),
        ...mapActions("auth", ["signUp", "logIn"]),
        async handleSignUp() {
            if (this.isProcessing) return;

            this.clearMessage();

            const errors = this.validateForm();
            if (Object.keys(errors).length > 0) {
                this.signUpErrors = errors;
                return;
            }

            this.isLoading = true;
            this.isProcessing = true;

            await this.signUp(this.formData)
                .then(() => {
                    return this.logIn({
                        email: this.formData.email,
                        password: this.formData.password,
                    });
                })
                .then(() => {
                    const redirectTo = this.options?.redirectTo;
                    if (redirectTo) {
                        this.$router.push(redirectTo);
                    } else {
                        this.$router.push({ name: "page-welcome" });
                    }
                    this.ok();
                })
                .catch((e) => {
                    if (e.status === 422) {
                        this.signUpErrors = [];
                        if (Array.isArray(e.error.validation_errors)) {
                            e.error.validation_errors.forEach((validation) => {
                                this.signUpErrors.push(validation.message);
                            });
                        }
                    } else if (e.error.message) {
                        this.signUpErrors = [e.error.message];
                    } else {
                        this.signUpErrors = { general: ["An error occurred. Please try again."] };
                    }
                })
                .finally(() => {
                    this.isLoading = false;
                    this.isProcessing = false;
                });
        },
        validateForm() {
            const errors = {};

            if (!this.formData.email) {
                errors.email = ["Email address is required"];
            } else if (!this.isValidEmail(this.formData.email)) {
                errors.email = ["Please enter a valid email address"];
            }

            if (!this.formData.password) {
                errors.password = ["Password is required"];
            } else if (this.formData.password.length < 6) {
                errors.password = ["Password must be at least 6 characters"];
            } else if (!/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(this.formData.password)) {
                errors.password = [
                    "Password must contain at least one uppercase letter, one lowercase letter, and one number",
                ];
            }

            if (!this.formData.confirmPassword) {
                errors.confirmPassword = ["Please confirm your password"];
            } else if (this.formData.password !== this.formData.confirmPassword) {
                errors.confirmPassword = ["Passwords do not match"];
            }

            return errors;
        },
        isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        },
        clearMessage() {
            this.signUpErrors = {};
        },
        openLogInModal() {
            this.close();
            this.openModal({ modalName: "logInModal" });
        },
        handleGoogleLogin() {
            this.isGoogleLoading = true;
            setTimeout(() => {
                this.isGoogleLoading = false;
            }, 1000);
        },
        handleFacebookLogin() {
            this.isFacebookLoading = true;
            setTimeout(() => {
                this.isFacebookLoading = false;
            }, 1000);
        },
        ok() {
            this.resolve !== null && this.resolve({ formData: this.formData });
            this.close();
        },
        close() {
            this.clearMessage();
            Object.assign(this.$data, this.$options.data.call(this));
            this.closeModal({ modalName: this.modalName });
        },
    },
    created() {
        this.initModal({ modalName: this.modalName });
    },
};
</script>
