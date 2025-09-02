<template>
    <form-wrapper>
        <template #body>
            <div v-if="!restarted">
                <n-p>Enter your new password</n-p>
                <n-form
                    ref="formRef"
                    :model="formData"
                    :rules="resetPasswordRules"
                    size="large"
                >
                    <n-alert
                        v-if="Object.keys(resetPasswordErrors).length > 0"
                        type="error"
                        :show-icon="false"
                        style="margin-bottom: 16px"
                    >
                        <span
                            v-for="(field, fieldKey) in resetPasswordErrors"
                            :key="fieldKey"
                        >
                            <span
                                v-for="(error, errorIndex) in field"
                                :key="errorIndex"
                            >
                                - {{ error }} </span
                            ><span
                                v-if="
                                    fieldKey <
                                    Object.keys(resetPasswordErrors).length - 1
                                "
                                >, <br
                            /></span>
                        </span>
                    </n-alert>

                    <n-form-item path="password" label="Password">
                        <n-input
                            v-model:value="formData.password"
                            type="password"
                            placeholder="Enter your password"
                            show-password-on="click"
                            :input-props="{ autocomplete: 'current-password' }"
                            :status="
                                resetPasswordErrors &&
                                resetPasswordErrors.password
                                    ? 'error'
                                    : ''
                            "
                            @keypress.enter="handleResetPassword"
                        />
                    </n-form-item>

                    <n-form-item
                        path="confirmPassword"
                        label="Confirm Password"
                    >
                        <n-input
                            v-model:value="formData.confirmPassword"
                            type="password"
                            placeholder="Confirm your password"
                            show-password-on="click"
                            :input-props="{ autocomplete: 'new-password' }"
                            :status="
                                resetPasswordErrors &&
                                resetPasswordErrors.confirmPassword
                                    ? 'error'
                                    : ''
                            "
                            @keypress.enter="handleResetPassword"
                        />
                    </n-form-item>

                    <n-button
                        type="primary"
                        size="large"
                        block
                        :loading="isLoading"
                        @click="handleResetPassword"
                    >
                        {{
                            isLoading
                                ? "Resetting password..."
                                : "Reset password"
                        }}
                    </n-button>
                </n-form>
            </div>
            <div v-else>
                <n-p
                    >Password reset successfully! Please log in with your new
                    password.</n-p
                >
                <n-button
                    type="primary"
                    size="large"
                    block
                    :loading="isLoading"
                    @click="openLogInModal"
                >
                    Go to log in
                </n-button>
            </div>
        </template>
    </form-wrapper>
</template>

<script>
import { mapActions } from "vuex";
export default {
    computed: {
        resetPasswordRules() {
            return {
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
                confirmPassword: [
                    {
                        required: true,
                        message: "Please confirm your password",
                        trigger: "blur",
                    },
                    {
                        validator: (rule, value, callback) => {
                            if (value !== this.formData.password) {
                                callback(new Error("Passwords do not match"));
                            } else {
                                callback();
                            }
                        },
                        trigger: "blur",
                    },
                ],
            };
        },
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
        ...mapActions(["resetPassword", "openModal"]),
        async handleResetPassword() {
            await this.$refs.formRef.validate();
            this.isLoading = true;
            this.resetPassword({
                email: this.resetEmail,
                password: this.formData.password,
                confirmPassword: this.formData.confirmPassword,
                token: this.resetToken,
            })
                .then((response) => {
                    console.log('TEST');
                })
                .catch((error) => {
                    this.resetPasswordErrors = error.response.data.errors;
                })
                .finally(() => {
                    this.isLoading = false;
                    this.restarted = true;
                });
        },
        openLogInModal() {
            this.openModal({
                modalName: "logInModal",
            });
        },
    },
};
</script>
