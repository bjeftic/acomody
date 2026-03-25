<template>
    <div>
        <h1 class="text-3xl font-semibold text-gray-900 dark:text-white mb-4">
            {{ $t('title') }}
        </h1>
        <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">
            <template v-if="isColdStart">
                {{ $t('success_cold_start') }}
            </template>
            <template v-else>
                {{ $t('success_regular') }}
            </template>
        </p>

        <!-- Host profile incomplete prompt -->
        <div
            v-if="!hostProfileComplete"
            class="flex gap-4 p-5 mb-8 bg-amber-50 border border-amber-200 rounded-lg"
        >
            <div class="flex-shrink-0 text-amber-500 mt-0.5">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-base font-semibold text-amber-800 mb-1">
                    {{ $t('profile_warning_title') }}
                </h3>
                <p class="text-sm text-amber-700 mb-3">
                    {{ $t('profile_warning_desc') }}
                </p>
                <router-link
                    :to="{ name: 'page-host-profile' }"
                    class="inline-block px-4 py-2 bg-amber-600 text-white text-sm font-medium rounded hover:bg-amber-700 transition"
                >
                    {{ $t('complete_profile') }}
                </router-link>
            </div>
        </div>

        <div class="mt-4">
            <router-link
                to="/hosting/dashboard"
                class="inline-block px-6 py-3 bg-primary-600 text-white font-medium rounded-xl hover:bg-primary-700 transition"
            >
                {{ $t('go_to_dashboard') }}
            </router-link>
        </div>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import config from "@/config.js";

export default {
    name: "SubmissionSuccess",
    computed: {
        ...mapGetters("user", ["hostProfileComplete"]),
        isColdStart() {
            return config.features.cold_start === true;
        },
    },
};
</script>

<i18n lang="yaml">
en:
  title: Congratulations!
  success_cold_start: Your accommodation listing has been successfully submitted and is now under review. Once approved, your listing will be ready — and when the platform launches, guests will be able to start booking right away.
  success_regular: Your accommodation listing has been successfully submitted and is now under review. We will notify you once it has been approved and is live on our platform.
  profile_warning_title: Complete your host profile to go live
  profile_warning_desc: "Your listing won't be searchable until you fill in your host profile (display name, contact email, and phone). It only takes a minute."
  complete_profile: Complete host profile →
  go_to_dashboard: Go to Hosting Dashboard
sr:
  title: Čestitamo!
  success_cold_start: Vaš oglas za smeštaj je uspešno poslat i sada je na proveri. Nakon odobrenja, vaš oglas će biti spreman — a kada platforma bude pokrenuta, gosti će moći početi s rezervacijama.
  success_regular: Vaš oglas za smeštaj je uspešno poslan i sada je na proveri. Obavestićemo vas čim bude odobren i objavljen na našoj platformi.
  profile_warning_title: Popunite profil domaćina da biste postali vidljivi
  profile_warning_desc: Vaš oglas neće biti pretraživ dok ne popunite profil domaćina (ime za prikaz, kontakt e-mail i telefon). Traje samo minut.
  complete_profile: Popunite profil domaćina →
  go_to_dashboard: Idi na početni ekran
hr:
  title: Čestitamo!
  success_cold_start: Vaš oglas za smještaj je uspješno poslan i sada je na pregledu. Nakon odobrenja, vaš oglas će biti spreman — a kada platforma bude pokrenuta, gosti će moći početi s rezervacijama.
  success_regular: Vaš oglas za smještaj je uspješno poslan i sada je na pregledu. Obavijestit ćemo vas čim bude odobren i objavljen na našoj platformi.
  profile_warning_title: Ispunite profil domaćina kako biste postali vidljivi
  profile_warning_desc: Vaš oglas neće biti pretraživ dok ne ispunite profil domaćina (ime za prikaz, kontakt e-mail i telefon). Traje samo minutu.
  complete_profile: Ispunite profil domaćina →
  go_to_dashboard: Idi na upravljačku ploču
mk:
  title: Честитаме!
  success_cold_start: Вашиот оглас за сместување е успешно испратен и е на преглед. По одобрување, вашиот оглас ќе биде подготвен — а кога платформата ќе биде пуштена, гостите ќе можат да почнат со резервирање.
  success_regular: Вашиот оглас за сместување е успешно испратен и е на преглед. Ќе ве известиме кога ќе биде одобрен и објавен на нашата платформа.
  profile_warning_title: Пополнете го профилот на домаќин за да станете видливи
  profile_warning_desc: Вашиот оглас нема да биде пребарлив додека не го пополните профилот на домаќин (прикажано ime, контакт е-пошта и телефон). Трае само минута.
  complete_profile: Пополнете го профилот на домаќин →
  go_to_dashboard: Оди на контролна табла
sl:
  title: Čestitamo!
  success_cold_start: Vaš oglas za nastanitev je bil uspešno poslan in je v pregledu. Po odobritvi bo vaš oglas pripravljen — ko bo platforma zagnana, bodo gostje lahko začeli z rezervacijami.
  success_regular: Vaš oglas za nastanitev je bil uspešno poslan in je v pregledu. Obvestili vas bomo, ko bo odobren in objavljen na naši platformi.
  profile_warning_title: Izpolnite profil gostitelja za objavo
  profile_warning_desc: "Vaš oglas ne bo viden v iskanju, dokler ne izpolnite profila gostitelja (prikazno ime, kontaktni e-naslov in telefon). Traja le minuto."
  complete_profile: Izpolni profil gostitelja →
  go_to_dashboard: Pojdi na nadzorno ploščo
</i18n>
