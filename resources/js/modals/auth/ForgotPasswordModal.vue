<template>
    <n-modal
        v-model:show="show"
        preset="card"
        :style="bodyStyle"
        title="Forgot password"
        size="huge"
        @close="close"
    >
        <n-p>Please enter your email address to reset your password</n-p>
        <n-form
            ref="formRef"
            :model="formData"
            :rules="forgotPasswordRules"
            size="large"
        >
            <n-alert
                v-if="Object.keys(forgotPasswordErrors).length > 0"
                type="error"
                :show-icon="false"
                style="margin-bottom: 16px"
            >
                <span v-for="(field, fieldKey) in forgotPasswordErrors" :key="fieldKey">
                    <span
                        v-for="(error, errorIndex) in field"
                        :key="errorIndex"
                    >
                        - {{ error }} </span
                    ><span v-if="fieldKey < Object.keys(forgotPasswordErrors).length - 1"
                        >, <br
                    /></span>
                </span>
            </n-alert>

            <n-form-item path="email" label="Email address">
                <n-input
                    v-model:value="formData.email"
                    placeholder="john@example.com"
                    :input-props="{ autocomplete: 'email' }"
                    @keypress.enter="handleForgotPassword"
                />
            </n-form-item>

            <n-button
                type="primary"
                size="large"
                block
                :loading="isLoading"
                @click="handleForgotPassword"
            >
                {{ isLoading ? "Sending..." : "Send reset link" }}
            </n-button>
        </n-form>
    </n-modal>
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
        forgotPasswordRules() {
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
            },
            isLoading: false,
            forgotPasswordErrors: {},
        };
    },
    methods: {
        ...mapActions(["initModal", "closeModal", "forgotPassword"]),
        async handleForgotPassword() {
            this.clearMessage();
            await this.$refs.formRef.validate();
            this.isLoading = true;
            await this.forgotPassword(this.formData)
                .then(() => {
                    this.close();
                })
                .catch((error) => {
                    if(error.status === 422) {
                        this.logInErrors = error.error.errors;
                    }
                    if(error.status === 401) {
                        this.logInErrors = { authentication: [error.error.message] };
                    }
                    return;
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },
        clearMessage() {
            this.forgotPasswordErrors = {};
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
