<template>
    <div
        class="flex justify-center pb-12 px-4 sm:px-6 lg:px-8"
    >
        <div
            class="max-w-md w-full space-y-8 bg-white p-8 rounded-lg shadow-md"
        >
            <div>
                <h2 class="text-center text-3xl font-bold text-gray-900">
                    {{ $t('title') }}
                </h2>
            </div>

            <div
                v-if="!restarted || Object.keys(resetPasswordErrors).length > 0"
            >
                <p class="text-gray-600 mb-6">{{ $t('subtitle') }}</p>

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
                            {{ $t('auth.password') }}
                        </label>
                        <fwb-input
                            v-model="formData.password"
                            type="password"
                            :placeholder="$t('password_placeholder')"
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
                            {{ $t('confirm_password') }}
                        </label>
                        <fwb-input
                            v-model="formData.confirmPassword"
                            type="password"
                            :placeholder="$t('confirm_placeholder')"
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
                        {{ isLoading ? $t('resetting') : $t('reset_button') }}
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
                    {{ $t('success_message') }}
                </p>
                <fwb-button color="blue" class="w-full" @click="openLogInModal">
                    {{ $t('go_to_login') }}
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
                            general: [this.$t('common.error')],
                        };
                    }
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },
        validateForm() {
            const errors = {};

            if (!this.formData.password) {
                errors.password = [this.$t('validation.password_required')];
            } else if (this.formData.password.length < 6) {
                errors.password = [this.$t('validation.password_min')];
            } else if (
                !/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(this.formData.password)
            ) {
                errors.password = [this.$t('validation.password_strength')];
            }

            if (!this.formData.confirmPassword) {
                errors.confirmPassword = [this.$t('validation.confirm_required')];
            } else if (
                this.formData.password !== this.formData.confirmPassword
            ) {
                errors.confirmPassword = [this.$t('validation.confirm_mismatch')];
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

<i18n lang="yml">
en:
  title: Reset Password
  subtitle: Enter your new password
  password_placeholder: Enter your password
  confirm_password: Confirm Password
  confirm_placeholder: Confirm your password
  resetting: Resetting password...
  reset_button: Reset password
  success_message: Password reset successfully! Please log in with your new password.
  go_to_login: Go to log in
  validation:
    password_required: Password is required
    password_min: Password must be at least 6 characters
    password_strength: Password must contain at least one uppercase letter, one lowercase letter, and one number
    confirm_required: Please confirm your password
    confirm_mismatch: Passwords do not match
sr:
  title: Resetuj lozinku
  subtitle: Unesite novu lozinku
  password_placeholder: Unesite lozinku
  confirm_password: Potvrdite lozinku
  confirm_placeholder: Potvrdite lozinku
  resetting: Resetovanje lozinke...
  reset_button: Resetuj lozinku
  success_message: Lozinka je uspešno resetovana! Prijavite se sa novom lozinkom.
  go_to_login: Idi na prijavu
  validation:
    password_required: Lozinka je obavezna
    password_min: Lozinka mora imati najmanje 6 karaktera
    password_strength: Lozinka mora sadržavati bar jedno veliko slovo, malo slovo i broj
    confirm_required: Potvrdite lozinku
    confirm_mismatch: Lozinke se ne podudaraju
hr:
  title: Resetiraj lozinku
  subtitle: Unesite novu lozinku
  password_placeholder: Unesite lozinku
  confirm_password: Potvrdite lozinku
  confirm_placeholder: Potvrdite lozinku
  resetting: Resetiranje lozinke...
  reset_button: Resetiraj lozinku
  success_message: Lozinka je uspješno resetirana! Prijavite se s novom lozinkom.
  go_to_login: Idi na prijavu
  validation:
    password_required: Lozinka je obavezna
    password_min: Lozinka mora imati najmanje 6 znakova
    password_strength: Lozinka mora sadržavati bar jedno veliko slovo, malo slovo i broj
    confirm_required: Potvrdite lozinku
    confirm_mismatch: Lozinke se ne podudaraju
mk:
  title: Ресетирај лозинка
  subtitle: Внесете нова лозинка
  password_placeholder: Внесете лозинка
  confirm_password: Потврдете ја лозинката
  confirm_placeholder: Потврдете ја лозинката
  resetting: Ресетирање на лозинка...
  reset_button: Ресетирај лозинка
  success_message: Лозинката е успешно ресетирана! Најавете се со новата лозинка.
  go_to_login: Оди на најава
  validation:
    password_required: Лозинката е задолжителна
    password_min: Лозинката мора да има најмалку 6 знаци
    password_strength: Лозинката мора да содржи барем едно големо, мало слово и број
    confirm_required: Потврдете ја лозинката
    confirm_mismatch: Лозинките не се совпаѓаат
sl:
  title: Ponastavi geslo
  subtitle: Vnesite novo geslo
  password_placeholder: Vnesite geslo
  confirm_password: Potrdi geslo
  confirm_placeholder: Potrdite geslo
  resetting: Ponastavljanje gesla...
  reset_button: Ponastavi geslo
  success_message: Geslo je bilo uspešno ponastavljeno! Prijavite se z novim geslom.
  go_to_login: Pojdi na prijavo
  validation:
    password_required: Geslo je obvezno
    password_min: Geslo mora imeti vsaj 6 znakov
    password_strength: Geslo mora vsebovati vsaj eno veliko, malo črko in številko
    confirm_required: Potrdite geslo
    confirm_mismatch: Gesli se ne ujemata
</i18n>
