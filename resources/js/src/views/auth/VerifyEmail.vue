<template>
    <div class="flex justify-center pb-12 px-4 sm:px-6 lg:px-8">
        <div
            class="max-w-md w-full space-y-8 bg-white p-8 rounded-lg shadow-md"
        >
            <div>
                <h2 class="text-center text-3xl font-bold text-gray-900">
                    Email Verification
                </h2>
            </div>

            <!-- Email Already Verified -->
            <div
                v-if="currentUser && currentUser.email_verified_at && verificationStatus === 'success'"
                class="text-center"
            >
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
                    Your email address is verified!
                </p>
                <fwb-button
                    color="blue"
                    class="w-full"
                    @click="$router.push({ name: 'page-welcome' })"
                >
                    Go to home
                </fwb-button>
            </div>

            <!-- Email Not Verified -->
            <div v-if="verificationStatus === 'error'">
                <fwb-alert type="danger" class="mb-4">
                    {{ verificationMessage }}
                </fwb-alert>

                <!-- Success Message -->
                <div
                    v-if="successMessage"
                    class="mb-4 p-4 text-sm text-green-800 bg-green-50 rounded-lg border border-green-200"
                    role="alert"
                >
                    <div class="flex items-center">
                        <svg
                            class="w-5 h-5 mr-2"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        <span>{{ successMessage }}</span>
                    </div>
                </div>

                <div class="text-center mb-6">
                    <svg
                        class="mx-auto h-12 w-12 text-blue-500 mb-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                        />
                    </svg>
                    <p class="text-gray-700 mb-2">
                        Your email verification link is not valid.
                    </p>
                    <p class="text-gray-600 text-sm">
                        Please request a new verification email.
                    </p>
                </div>

                <fwb-button
                    color="blue"
                    class="w-full"
                    :loading="isLoading"
                    :disabled="isLoading"
                    @click="handleResendVerification"
                >
                    {{
                        isLoading
                            ? "Sending verification email..."
                            : "Resend verification email"
                    }}
                </fwb-button>

                <!-- Login Link -->
                <div class="mt-6 text-center text-sm text-gray-600">
                    Already verified?

                    <a
                        href="#"
                        @click.prevent="openLogInModal"
                        class="text-blue-600 hover:underline font-medium"
                    >
                        Log in
                    </a>
                </div>
            </div>
            <div v-if="verificationStatus === 'info'">

                <!-- Success Message -->
                <div
                    v-if="successMessage"
                    class="mb-4 p-4 text-sm text-green-800 bg-green-50 rounded-lg border border-green-200"
                    role="alert"
                >
                    <div class="flex items-center">
                        <svg
                            class="w-5 h-5 mr-2"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        <span>{{ successMessage }}</span>
                    </div>
                </div>

                <div class="text-center mb-6">
                    <svg
                        class="mx-auto h-12 w-12 text-blue-500 mb-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                        />
                    </svg>
                    <p class="text-gray-700 mb-2">
                        Email already verified.
                    </p>
                </div>

                <!-- Login Link -->
                <fwb-button
                    color="blue"
                    class="w-full"
                    @click="$router.push({ name: 'page-welcome' })"
                >
                    Go to home
                </fwb-button>
            </div>
        </div>
    </div>
</template>

<script>
import { mapActions, mapState } from "vuex";

export default {
    name: "VerifyEmail",
    computed: {
        ...mapState('user', ["currentUser"]),
        verificationStatus() {
            return this.$route.query.status;
        },
        verificationMessage() {
            return this.$route.query.message;
        },
    },
    data() {
        return {
            isLoading: false,
            verificationErrors: {},
            successMessage: "",
        };
    },
    methods: {
        ...mapActions(["openModal"]),
        ...mapActions("auth", ["resendVerificationEmail"]),
        async handleResendVerification() {
            this.clearMessages();
            this.isLoading = true;

            await this.resendVerificationEmail()
                .then((response) => {
                    this.successMessage =
                        "Verification email sent successfully! Please check your inbox.";
                })
                .catch((e) => {
                    if (
                        e.response &&
                        e.response.data &&
                        e.response.data.error
                    ) {
                        this.verificationErrors =
                            e.response.data.error.validation_errors || {};
                    } else {
                        this.verificationErrors = {
                            general: [
                                "An error occurred while sending the verification email. Please try again.",
                            ],
                        };
                    }
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },
        clearMessages() {
            this.verificationErrors = {};
            this.successMessage = "";
        },
        openLogInModal() {
            this.openModal({
                modalName: "logInModal",
            });
        },
    },
};
</script>
