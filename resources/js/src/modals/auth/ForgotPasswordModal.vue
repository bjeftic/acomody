<template>
    <BaseModal v-if="show" @close="close" size="md">
        <template #header>Forgot password</template>
        <template #body>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                Please enter your email address to reset your password, and we'll email you a link to reset your password.
            </p>

            <form @submit.prevent="handleForgotPassword" class="flex flex-col gap-4">
                <validation-alert-box
                    v-if="Object.keys(forgotPasswordErrors).length > 0"
                    :errors="forgotPasswordErrors"
                />

                <BaseInput
                    v-model="formData.email"
                    type="email"
                    label="Email address"
                    placeholder="john@example.com"
                    :error="emailError || null"
                    @blur="validateEmail"
                />

                <BaseButton
                    type="submit"
                    size="lg"
                    :loading="isLoading"
                    :disabled="isLoading"
                    full
                >
                    {{ isLoading ? "Sending..." : "Send reset link" }}
                </BaseButton>
            </form>
        </template>
    </BaseModal>
</template>

<script>
import config from "@/config";
import { toCamelCase } from "@/utils/helpers";
import { mapState, mapActions } from "vuex";

const modalName = config.modals.forgotPasswordModal;
const modalNameCamelCase = toCamelCase(modalName);

export default {
    name: "ForgotPasswordModal",
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
            },
            isLoading: false,
            forgotPasswordErrors: {},
            emailError: "",
        };
    },
    methods: {
        ...mapActions(["initModal", "closeModal"]),
        ...mapActions("auth", ["forgotPassword"]),
        validateEmail() {
            this.emailError = "";

            if (!this.formData.email) {
                this.emailError = "Email address is required";
                return false;
            }

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(this.formData.email)) {
                this.emailError = "Please enter valid email address";
                return false;
            }

            return true;
        },
        async handleForgotPassword() {
            this.clearMessage();

            if (!this.validateEmail()) {
                return;
            }

            this.isLoading = true;

            try {
                await this.forgotPassword({ email: this.formData.email });
                this.close();
            } catch (e) {
                if (e.error && e.error.error && e.error.error.validation_errors) {
                    this.forgotPasswordErrors = e.error.error.validation_errors;
                }
            } finally {
                this.isLoading = false;
            }
        },
        clearMessage() {
            this.forgotPasswordErrors = {};
            this.emailError = "";
        },
        ok() {
            if (this.resolve !== null) {
                this.resolve({ formData: this.formData });
            }
            this.close();
        },
        close() {
            Object.assign(this.$data, this.$options.data.call(this));
            this.closeModal({ modalName: this.modalName });
        },
    },
    created() {
        this.initModal({ modalName: this.modalName });
    },
};
</script>
