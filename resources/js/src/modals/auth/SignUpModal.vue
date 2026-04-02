<template>
    <BaseModal v-if="show" @close="close" size="md">
        <template #header>{{ $t('title') }}</template>
        <template #body>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                {{ $t('subtitle') }}
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
                    :label="$t('auth.email')"
                    placeholder="john@example.com"
                    :error="signUpErrors.email ? signUpErrors.email[0] : null"
                />

                <BaseInput
                    v-model="formData.password"
                    type="password"
                    :label="$t('auth.password')"
                    :placeholder="$t('password_placeholder')"
                    :error="signUpErrors.password ? signUpErrors.password[0] : null"
                    :show-password-toggle="true"
                />

                <BaseInput
                    v-model="formData.confirmPassword"
                    type="password"
                    :label="$t('auth.confirm_password')"
                    :placeholder="$t('confirm_placeholder')"
                    :error="signUpErrors.confirmPassword ? signUpErrors.confirmPassword[0] : null"
                    :show-password-toggle="true"
                />

                <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ $t('terms_prefix') }}
                    <a href="#" class="text-primary-600 hover:text-primary-700 dark:text-primary-400">{{ $t('terms_link') }}</a>
                    {{ $t('terms_and') }}
                    <a href="#" class="text-primary-600 hover:text-primary-700 dark:text-primary-400">{{ $t('privacy_link') }}</a>
                </p>

                <BaseButton
                    type="submit"
                    :loading="isLoading"
                    :disabled="isLoading || isProcessing"
                    full
                >
                    {{ isLoading ? $t('signing_up') : $t('auth.signup_title') }}
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
                    {{ $t('facebook') }}
                </BaseButton>
            </div>

            <p class="mt-5 text-center text-sm text-gray-500 dark:text-gray-400">
                {{ $t('auth.have_account') }}
                <a href="#" @click.prevent="openLogInModal" class="text-primary-600 hover:text-primary-700 dark:text-primary-400 font-medium">
                    {{ $t('auth.login_title') }}
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
                    } else if (config.features.cold_start === true) {
                        this.$router.push({ name: "page-host-profile" });
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
                        this.signUpErrors = { general: [this.$t('common.error')] };
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

            if (!this.formData.confirmPassword) {
                errors.confirmPassword = [this.$t('validation.confirm_required')];
            } else if (this.formData.password !== this.formData.confirmPassword) {
                errors.confirmPassword = [this.$t('validation.confirm_mismatch')];
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

<i18n lang="yml">
en:
  title: Sign up
  subtitle: Please fill in the details to sign up
  password_placeholder: Enter your password
  confirm_placeholder: Confirm your password
  signing_up: Signing up...
  google: Continue with Google
  facebook: Continue with Facebook
  terms_prefix: "By creating an account, you're agreeing with our"
  terms_link: Terms of Use
  terms_and: and
  privacy_link: Privacy Policy
  validation:
    email_required: Email address is required
    email_invalid: Please enter a valid email address
    password_required: Password is required
    password_min: Password must be at least 6 characters
    password_strength: Password must contain at least one uppercase letter, one lowercase letter, one symbol and one number
    confirm_required: Please confirm your password
    confirm_mismatch: Passwords do not match
sr:
  title: Registrujte se
  subtitle: Popunite podatke za registraciju
  password_placeholder: Unesite lozinku
  confirm_placeholder: Potvrdite lozinku
  signing_up: Registracija u toku...
  google: Nastavite sa Google
  facebook: Nastavite sa Facebook
  terms_prefix: Kreiranjem naloga prihvatate naše
  terms_link: Uslove korišćenja
  terms_and: i
  privacy_link: Politiku privatnosti
  validation:
    email_required: Email adresa je obavezna
    email_invalid: Unesite ispravnu email adresu
    password_required: Lozinka je obavezna
    password_min: Lozinka mora imati najmanje 6 karaktera
    password_strength: Lozinka mora sadržavati bar jedno veliko slovo, malo slovo, jedan simbol i broj
    confirm_required: Potvrdite lozinku
    confirm_mismatch: Lozinke se ne podudaraju
hr:
  title: Registrirajte se
  subtitle: Ispunite podatke za registraciju
  password_placeholder: Unesite lozinku
  confirm_placeholder: Potvrdite lozinku
  signing_up: Registracija u tijeku...
  google: Nastavite s Googleom
  facebook: Nastavite s Facebookom
  terms_prefix: Kreiranjem računa prihvaćate naše
  terms_link: Uvjete korištenja
  terms_and: i
  privacy_link: Politiku privatnosti
  validation:
    email_required: Email adresa je obavezna
    email_invalid: Unesite ispravnu email adresu
    password_required: Lozinka je obavezna
    password_min: Lozinka mora imati najmanje 6 znakova
    password_strength: Lozinka mora sadržavati bar jedno veliko slovo, malo slovo, jedan simbol i broj
    confirm_required: Potvrdite lozinku
    confirm_mismatch: Lozinke se ne podudaraju
mk:
  title: Регистрирајте се
  subtitle: Пополнете ги деталите за регистрација
  password_placeholder: Внесете лозинка
  confirm_placeholder: Потврдете ја лозинката
  signing_up: Регистрација во тек...
  google: Продолжете со Google
  facebook: Продолжете со Facebook
  terms_prefix: Со креирање сметка се согласувате со нашите
  terms_link: Услови за користење
  terms_and: и
  privacy_link: Политика за приватност
  validation:
    email_required: Е-поштата е задолжителна
    email_invalid: Внесете важечка е-пошта
    password_required: Лозинката е задолжителна
    password_min: Лозинката мора да има најмалку 6 знаци
    password_strength: Лозинката мора да содржи барем едно големо слово, мало слово, симбол и број
    confirm_required: Потврдете ја лозинката
    confirm_mismatch: Лозинките не се совпаѓаат
sl:
  title: Registrirajte se
  subtitle: Izpolnite podatke za registracijo
  password_placeholder: Vnesite geslo
  confirm_placeholder: Potrdite geslo
  signing_up: Registracija v teku...
  google: Nadaljujte z Googlom
  facebook: Nadaljujte s Facebookom
  terms_prefix: Z ustvarjanjem računa se strinjate z našimi
  terms_link: Pogoji uporabe
  terms_and: in
  privacy_link: Politiko zasebnosti
  validation:
    email_required: E-poštni naslov je obvezen
    email_invalid: Vnesite veljavni e-poštni naslov
    password_required: Geslo je obvezno
    password_min: Geslo mora imeti vsaj 6 znakov
    password_strength: Geslo mora vsebovati vsaj eno veliko črko, malo črko, simbol in številko
    confirm_required: Potrdite geslo
    confirm_mismatch: Gesli se ne ujemata
</i18n>
