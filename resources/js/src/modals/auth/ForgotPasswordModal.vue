<template>
    <BaseModal v-if="show" @close="close" size="md">
        <template #header>{{ $t('title') }}</template>
        <template #body>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                {{ $t('subtitle') }}
            </p>

            <form @submit.prevent="handleForgotPassword" class="flex flex-col gap-4">
                <validation-alert-box
                    v-if="Object.keys(forgotPasswordErrors).length > 0"
                    :errors="forgotPasswordErrors"
                />

                <BaseInput
                    v-model="formData.email"
                    type="email"
                    :label="$t('auth.email')"
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
                    {{ isLoading ? $t('sending') : $t('send_link') }}
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
                this.emailError = this.$t('validation.email_required');
                return false;
            }

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(this.formData.email)) {
                this.emailError = this.$t('validation.email_invalid');
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

<i18n lang="yml">
en:
  title: Forgot password
  subtitle: "Please enter your email address and we'll send you a link to reset your password."
  sending: Sending...
  send_link: Send reset link
  validation:
    email_required: Email address is required
    email_invalid: Please enter a valid email address
sr:
  title: Zaboravili ste lozinku
  subtitle: Unesite email adresu i poslaćemo vam link za resetovanje lozinke.
  sending: Slanje...
  send_link: Pošaljite link
  validation:
    email_required: Email adresa je obavezna
    email_invalid: Unesite ispravnu email adresu
hr:
  title: Zaboravili ste lozinku
  subtitle: Unesite email adresu i poslat ćemo vam poveznicu za resetiranje lozinke.
  sending: Slanje...
  send_link: Pošaljite poveznicu
  validation:
    email_required: Email adresa je obavezna
    email_invalid: Unesite ispravnu email adresu
mk:
  title: Заборавена лозинка
  subtitle: Внесете ја вашата е-пошта и ќе ви испратиме линк за ресетирање на лозинката.
  sending: Испраќање...
  send_link: Испратете линк
  validation:
    email_required: Е-поштата е задолжителна
    email_invalid: Внесете важечка е-пошта
sl:
  title: Pozabljeno geslo
  subtitle: Vnesite e-poštni naslov in poslali vam bomo povezavo za ponastavitev gesla.
  sending: Pošiljanje...
  send_link: Pošlji povezavo
  validation:
    email_required: E-poštni naslov je obvezen
    email_invalid: Vnesite veljavni e-poštni naslov
</i18n>
