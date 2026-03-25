<template>
    <div class="min-h-[60vh] flex items-center justify-center px-4 py-12">
        <div class="max-w-md w-full bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-8 text-center space-y-6">

            <!-- Success -->
            <div v-if="verificationStatus === 'success'" class="space-y-6">
                <div class="flex justify-center">
                    <div class="w-20 h-20 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                        <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ $t('success_title') }}</h2>
                    <p class="text-gray-500 dark:text-gray-400">{{ $t('success_body') }}</p>
                </div>
                <button
                    class="w-full px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition"
                    @click="$router.push({ name: 'page-welcome' })"
                >
                    {{ $t('go_home') }}
                </button>
            </div>

            <!-- Error -->
            <div v-else-if="verificationStatus === 'error'" class="space-y-6">
                <div class="flex justify-center">
                    <div class="w-20 h-20 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                        <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ $t('error_title') }}</h2>
                    <p class="text-gray-500 dark:text-gray-400">{{ $t('error_body') }}</p>
                </div>

                <div
                    v-if="successMessage"
                    class="flex items-center gap-2 p-4 text-sm text-green-800 bg-green-50 dark:bg-green-900/20 dark:text-green-400 rounded-xl border border-green-200 dark:border-green-800 text-left"
                >
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ successMessage }}</span>
                </div>

                <button
                    class="w-full px-6 py-3 bg-primary-600 hover:bg-primary-700 disabled:opacity-50 text-white font-semibold rounded-xl transition flex items-center justify-center gap-2"
                    :disabled="isLoading"
                    @click="handleResendVerification"
                >
                    <svg v-if="isLoading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                    {{ isLoading ? $t('sending') : $t('resend') }}
                </button>

                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $t('already_verified') }}
                    <a href="#" class="text-primary-600 hover:underline font-medium" @click.prevent="openLogInModal">
                        {{ $t('auth.login_title') }}
                    </a>
                </p>
            </div>

            <!-- Info (already verified) -->
            <div v-else-if="verificationStatus === 'info'" class="space-y-6">
                <div class="flex justify-center">
                    <div class="w-20 h-20 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                        <svg class="w-10 h-10 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ $t('info_title') }}</h2>
                    <p class="text-gray-500 dark:text-gray-400">{{ $t('info_body') }}</p>
                </div>
                <button
                    class="w-full px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition"
                    @click="$router.push({ name: 'page-welcome' })"
                >
                    {{ $t('go_home') }}
                </button>
            </div>

        </div>
    </div>
</template>

<script>
import { mapActions } from "vuex";

export default {
    name: "VerifyEmail",
    data() {
        return {
            isLoading: false,
            successMessage: "",
        };
    },
    computed: {
        verificationStatus() {
            return this.$route.query.status;
        },
    },
    methods: {
        ...mapActions(["openModal"]),
        ...mapActions("auth", ["resendVerificationEmail"]),
        async handleResendVerification() {
            this.successMessage = "";
            this.isLoading = true;
            try {
                await this.resendVerificationEmail();
                this.successMessage = this.$t("sent_success");
            } catch {
                this.successMessage = "";
            } finally {
                this.isLoading = false;
            }
        },
        openLogInModal() {
            this.openModal({ modalName: "logInModal" });
        },
    },
};
</script>

<i18n lang="yaml">
en:
  success_title: Email Verified!
  success_body: Your email address has been successfully verified. You can now use your account.
  error_title: Verification Failed
  error_body: Your email verification link is invalid or has expired. Request a new one below.
  info_title: Already Verified
  info_body: Your email address is already verified. You're all set.
  go_home: Go to home
  sending: Sending...
  resend: Resend verification email
  already_verified: Already verified?
  sent_success: Verification email sent! Please check your inbox.
  send_error: Something went wrong. Please try again.
sr:
  success_title: Email verifikovan!
  success_body: Vaša email adresa je uspešno verifikovana. Sada možete koristiti vaš nalog.
  error_title: Verifikacija neuspešna
  error_body: Vaš link za verifikaciju nije važeći ili je istekao. Zatražite novi ispod.
  info_title: Već verifikovano
  info_body: Vaša email adresa je već verifikovana. Sve je spremno.
  go_home: Idi na početnu
  sending: Slanje...
  resend: Ponovo pošalji verifikacioni email
  already_verified: Već verifikovano?
  sent_success: Verifikacioni email je poslat! Proverite inbox.
  send_error: Nešto je pošlo naopako. Pokušajte ponovo.
hr:
  success_title: Email verificiran!
  success_body: Vaša email adresa je uspješno verificirana. Sada možete koristiti vaš račun.
  error_title: Verifikacija neuspješna
  error_body: Vaša poveznica za verifikaciju nije važeća ili je istekla. Zatražite novu ispod.
  info_title: Već verificirano
  info_body: Vaša email adresa je već verificirana. Sve je spremno.
  go_home: Idi na početnu
  sending: Slanje...
  resend: Ponovo pošalji verifikacijski email
  already_verified: Već verificirano?
  sent_success: Verifikacijski email je poslan! Provjerite inbox.
  send_error: Nešto je pošlo po krivu. Pokušajte ponovo.
mk:
  success_title: Е-маилот е верификуван!
  success_body: Вашата е-маил адреса е успешно верификувана. Сега можете да го користите вашиот профил.
  error_title: Верификацијата не успеа
  error_body: Вашата врска за верификација не е важечка или е истечена. Побарајте нова подолу.
  info_title: Веќе верификувано
  info_body: Вашата е-маил адреса е веќе верификувана. Сè е подготвено.
  go_home: Оди на почетна
  sending: Испраќање...
  resend: Повторно испрати верификациски е-маил
  already_verified: Веќе верификувано?
  sent_success: Верификацискиот е-маил е испратен! Проверете го инбоксот.
  send_error: Нешто тргна наопаку. Обидете се повторно.
sl:
  success_title: E-pošta potrjena!
  success_body: Vaš e-poštni naslov je bil uspešno potrjen. Zdaj lahko uporabljate vaš račun.
  error_title: Potrditev ni uspela
  error_body: Vaša potrditvena povezava ni veljavna ali je potekla. Zahtevajte novo spodaj.
  info_title: Že potrjeno
  info_body: Vaš e-poštni naslov je že potrjen. Vse je pripravljeno.
  go_home: Pojdi na domov
  sending: Pošiljanje...
  resend: Ponovno pošlji potrditveno e-poštno sporočilo
  already_verified: Že potrjeno?
  sent_success: Potrditveno e-poštno sporočilo je poslano! Preverite poštni predal.
  send_error: Prišlo je do napake. Poskusite znova.
</i18n>
