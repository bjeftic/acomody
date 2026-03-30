<template>
    <BaseModal v-if="show" @close="close" size="md">
        <template #header>{{ $t('title') }}</template>
        <template #body>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                {{ $t('subtitle') }}
            </p>

            <form @submit.prevent="handleLogIn" class="flex flex-col gap-4">
                <validation-alert-box
                    v-if="Object.keys(logInErrors).length > 0"
                    :errors="logInErrors"
                />

                <BaseInput
                    v-model="formData.email"
                    type="email"
                    :label="$t('auth.email')"
                    placeholder="john@example.com"
                    :error="logInErrors.email ? logInErrors.email[0] : null"
                />

                <BaseInput
                    v-model="formData.password"
                    type="password"
                    :label="$t('auth.password')"
                    :placeholder="$t('password_placeholder')"
                    :error="logInErrors.password ? logInErrors.password[0] : null"
                />

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input
                            type="checkbox"
                            v-model="formData.rememberMe"
                            class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500 dark:bg-gray-700"
                        />
                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ $t('auth.remember_me') }}</span>
                    </label>
                    <a
                        href="#"
                        @click.prevent="openForgotPasswordModal"
                        class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400"
                    >
                        {{ $t('auth.forgot_password') }}
                    </a>
                </div>

                <BaseButton type="submit" :loading="isLoading" :disabled="isLoading" full>
                    {{ isLoading ? $t('logging_in') : $t('auth.login_title') }}
                </BaseButton>
            </form>

            <div class="flex items-center gap-3 my-5">
                <div class="flex-1 border-t border-gray-200 dark:border-gray-700"></div>
                <span class="text-xs text-gray-400">{{ $t('common.or') }}</span>
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
                    {{ $t('google') }}
                </BaseButton>
            </div>

            <p class="mt-5 text-center text-sm text-gray-500 dark:text-gray-400">
                {{ $t('auth.no_account') }}
                <a href="#" @click.prevent="openSignUpModal" class="text-primary-600 hover:text-primary-700 dark:text-primary-400 font-medium">
                    {{ $t('auth.signup_title') }}
                </a>
            </p>
        </template>
    </BaseModal>
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
    },
    data() {
        return {
            modalName,
            formData: {
                email: "",
                password: "",
                rememberMe: false,
            },
            isLoading: false,
            isGoogleLoading: false,
            logInErrors: {},
        };
    },
    methods: {
        ...mapActions(["openModal", "initModal", "closeModal"]),
        ...mapActions("auth", ["logIn"]),
        async handleLogIn() {
            this.clearMessage();

            // Client-side validation
            const errors = this.validateForm();
            if (Object.keys(errors).length > 0) {
                this.logInErrors = errors;
                return;
            }

            this.isLoading = true;
            await this.logIn(this.formData)
                .then((res) => {
                    const redirectTo = this.options?.redirectTo;
                    if (redirectTo) {
                        this.$router.push(redirectTo);
                    }
                    this.ok();
                    if (res.data.meta.refresh_page) {
                        window.location.reload();
                    }
                })
                .catch((e) => {
                    if (e.status === 422) {
                        this.logInErrors = [];
                        if (Array.isArray(e.error.validation_errors)) {
                            e.error.validation_errors.forEach((validation) => {
                                this.logInErrors.push(validation.message);
                            });
                        }
                    } else if (e.error.message) {
                        this.logInErrors = [e.error.message];
                    } else {
                        this.logInErrors = [this.$t('common.error')];
                    }
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },
        validateForm() {
            const errors = {};

            if (!this.formData.email) {
                errors.email = [this.$t('validation.email_required')];
            } else if (!this.isValidEmail(this.formData.email)) {
                errors.email = [this.$t('validation.email_invalid')];
            }

            if (!this.formData.password) {
                errors.password = [this.$t('validation.password_required')];
            } else if (this.formData.password.length < 6) {
                errors.password = [this.$t('validation.password_min')];
            } else if (!/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(this.formData.password)) {
                errors.password = [this.$t('validation.password_strength')];
            }

            return errors;
        },
        isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        },
        clearMessage() {
            this.logInErrors = {};
        },
        openForgotPasswordModal() {
            this.close();
            this.openModal({ modalName: "forgotPasswordModal" });
        },
        openSignUpModal() {
            this.close();
            this.openModal({ modalName: "signUpModal" });
        },
        handleGoogleLogin() {
            this.isGoogleLoading = true;
            window.location.href = '/auth/google';
        },
        ok() {
            if (this.resolve !== null) {
                this.resolve({ success: true });
            }
            this.closeModal({ modalName: this.modalName });
        },
        close() {
            if (this.reject !== null) {
                this.reject(new Error("Login cancelled"));
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

<i18n lang="yml">
en:
  title: Log in
  subtitle: Please log in to your account
  password_placeholder: Enter your password
  logging_in: Logging in...
  google: Continue with Google
  validation:
    email_required: Email address is required
    email_invalid: Please enter a valid email address
    password_required: Password is required
    password_min: Password must be at least 6 characters
    password_strength: Password must contain at least one uppercase letter, one lowercase letter, and one number
sr:
  title: Prijavite se
  subtitle: Prijavite se na vaš nalog
  password_placeholder: Unesite lozinku
  logging_in: Prijavljivanje...
  google: Nastavite sa Google
  validation:
    email_required: Email adresa je obavezna
    email_invalid: Unesite ispravnu email adresu
    password_required: Lozinka je obavezna
    password_min: Lozinka mora imati najmanje 6 karaktera
    password_strength: Lozinka mora sadržavati bar jedno veliko slovo, malo slovo i broj
hr:
  title: Prijavite se
  subtitle: Prijavite se na vaš račun
  password_placeholder: Unesite lozinku
  logging_in: Prijava u tijeku...
  google: Nastavite s Googleom
  validation:
    email_required: Email adresa je obavezna
    email_invalid: Unesite ispravnu email adresu
    password_required: Lozinka je obavezna
    password_min: Lozinka mora imati najmanje 6 znakova
    password_strength: Lozinka mora sadržavati bar jedno veliko slovo, malo slovo i broj
mk:
  title: Најавете се
  subtitle: Најавете се на вашата сметка
  password_placeholder: Внесете лозинка
  logging_in: Најавување...
  google: Продолжете со Google
  validation:
    email_required: Е-поштата е задолжителна
    email_invalid: Внесете важечка е-пошта
    password_required: Лозинката е задолжителна
    password_min: Лозинката мора да има најмалку 6 знаци
    password_strength: Лозинката мора да содржи барем едно големо, мало слово и број
sl:
  title: Prijavite se
  subtitle: Prijavite se v vaš račun
  password_placeholder: Vnesite geslo
  logging_in: Prijava v teku...
  google: Nadaljujte z Googlom
  validation:
    email_required: E-poštni naslov je obvezen
    email_invalid: Vnesite veljavni e-poštni naslov
    password_required: Geslo je obvezno
    password_min: Geslo mora imeti vsaj 6 znakov
    password_strength: Geslo mora vsebovati vsaj eno veliko, malo črko in številko
</i18n>
