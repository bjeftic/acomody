<template>
    <n-modal
        v-model:show="show"
        preset="card"
        :style="bodyStyle"
        title="Log in"
        size="huge"
        @close="close"
    >
        <n-p>Please log in to your account</n-p>
        <n-form
            ref="formRef"
            :model="formData"
            :rules="logInRules"
            size="large"
        >
            <validation-alert-box :errors="logInErrors"></validation-alert-box>

            <n-form-item path="email" label="Email address">
                <n-input
                    v-model:value="formData.email"
                    placeholder="john@example.com"
                    :input-props="{ autocomplete: 'email' }"
                    @keypress.enter="handleLogIn"
                />
            </n-form-item>

            <n-form-item path="password" label="Password">
                <n-input
                    v-model:value="formData.password"
                    type="password"
                    placeholder="Enter your password"
                    show-password-on="click"
                    :input-props="{ autocomplete: 'current-password' }"
                    @keypress.enter="handleLogIn"
                />
            </n-form-item>

            <n-form-item path="rememberMe">
                <n-checkbox v-model:checked="formData.rememberMe">
                    Remember me
                </n-checkbox>
            </n-form-item>

            <a href="#" @click="openForgotPasswordModal">
                Forgot your password?
            </a>

            <n-button
                type="primary"
                size="large"
                block
                :loading="isLoading"
                @click="handleLogIn"
            >
                {{ isLoading ? "Logging in..." : "Log in" }}
            </n-button>
        </n-form>

        <div class="divider">
            <span>or</span>
        </div>

        <div class="social-login">
            <n-button
                secondary
                size="large"
                block
                @click="handleGoogleLogin"
                :loading="isGoogleLoading"
                class="social-button google"
            >
                <template #icon>
                    <svg width="18" height="18" viewBox="0 0 24 24">
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
            </n-button>

            <n-button
                secondary
                size="large"
                block
                @click="handleFacebookLogin"
                :loading="isFacebookLoading"
                class="social-button facebook"
            >
                <template #icon>
                    <svg
                        width="18"
                        height="18"
                        viewBox="0 0 24 24"
                        fill="#1877F2"
                    >
                        <path
                            d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"
                        />
                    </svg>
                </template>
                Continue with Facebook
            </n-button>
        </div>

        <div>
            Don't have an account?
            <a> Sign up </a>
        </div>
    </n-modal>
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
        logInRules() {
            return {
                email: [
                    {
                        required: true,
                        message: "Email address is required",
                        trigger: "blur",
                    },
                    {
                        type: "email",
                        message: "Please enter valid email address",
                        trigger: "blur",
                    },
                ],
                password: [
                    {
                        required: true,
                        message: "Password is required",
                        trigger: "blur",
                    },
                    {
                        min: 6,
                        message: "Password must be at least 6 characters",
                        trigger: "blur",
                    },
                    {
                        pattern: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/,
                        message:
                            "Password must contain at least one uppercase letter, one lowercase letter, and one number",
                        trigger: "blur",
                    },
                ],
                rememberMe: [],
            };
        },
    },
    data() {
        return {
            bodyStyle: {
                width: "400px",
                maxWidth: "100%",
            },
            modalName,
            formData: {
                email: "",
                password: "",
                rememberMe: false,
            },
            isLoading: false,
            logInErrors: {},
        };
    },
    methods: {
        ...mapActions(["openModal", "initModal", "closeModal", "logIn"]),
        async handleLogIn() {
            this.clearMessage();
            await this.$refs.formRef.validate();
            this.isLoading = true;
            await this.logIn(this.formData)
                .then(() => {
                    this.$router.push({ name: "page-welcome" });
                    this.close();
                })
                .catch((e) => {
                    this.logInErrors = e.response.data.error.validation_errors;
                    return;
                })
                .finally(() => {
                    this.isLoading = false;
                });
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
        handleGoogleLogin() {
            //
        },
        handleFacebookLogin() {
            //
        },
        ok() {
            this.resolve !== null && this.resolve({ formData: this.formData });
            this.close();
        },
        close() {
            if (
                this.$refs.formRef &&
                typeof this.$refs.formRef.restoreValidation === "function"
            ) {
                this.$refs.formRef.restoreValidation();
            }
            if (
                this.$refs.formRef &&
                typeof this.$refs.formRef.resetFields === "function"
            ) {
                this.$refs.formRef.resetFields();
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

<style></style>
