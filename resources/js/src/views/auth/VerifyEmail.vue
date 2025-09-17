<template>
    <form-wrapper>
        <template #body>
            <div v-if="currentUser && currentUser.email_verified_at">
                <n-p>Your email address is verified!</n-p>
                <n-button
                    type="primary"
                    size="large"
                    @click="$router.push({ name: 'page-welcome' })"
                >
                    Go to home
                </n-button>
            </div>
            <div v-else>
                <n-p>
                    Your email verification link is not valid. Please
                    request a new verification email.
                </n-p>

                <n-button
                    type="primary"
                    size="large"
                    :loading="isLoading"
                    @click="resendVerificationEmail"
                >
                    {{
                        isLoading
                            ? "Sending verification email..."
                            : "Resend verification email"
                    }}
                </n-button>
            </div>
        </template>
    </form-wrapper>
</template>

<script>
import { mapActions, mapGetters } from "vuex";
export default {
    data() {
        return {
            isLoading: false,
        };
    },
    computed: {
        ...mapGetters(["isLoggedIn", "currentUser"]),
    },
    methods: {
        ...mapActions(["openModal", "resendVerificationEmail"]),
        openLogInModal() {
            this.openModal({
                modalName: "logInModal",
            });
        },
    },
};
</script>
