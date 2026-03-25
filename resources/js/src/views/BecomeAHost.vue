<template>
    <base-wrapper>
        <template v-slot:content>
            <!-- Hero Section -->
            <div class="bg-gradient-to-r from-primary-600 to-primary-700 text-white rounded-xl p-6 md:p-12 mb-8 md:mb-12">
                <div class="max-w-3xl">
                    <h1 class="text-3xl md:text-5xl font-bold mb-4 md:mb-6">
                        {{ $t('hero_title') }}
                    </h1>
                    <p class="text-base md:text-xl mb-6 md:mb-8 text-primary-100">
                        {{ $t('hero_subtitle') }}
                    </p>
                    <button
                        class="inline-flex items-center px-6 py-3 text-sm font-semibold text-primary-700 bg-white rounded-xl hover:bg-primary-50 transition-colors"
                        @click="handleBecomAHostCta"
                    >
                        {{ heroCta }}
                    </button>
                </div>
            </div>

            <!-- Benefits Section -->
            <div class="mb-10 md:mb-16">
                <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-6 md:mb-8 text-center">
                    {{ $t('benefits_title') }}
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:shadow-card transition-shadow p-5">
                        <div class="bg-primary-100 dark:bg-primary-900/30 p-3 rounded-xl mb-4 inline-flex">
                            <svg class="w-8 h-8 text-primary-600 dark:text-primary-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2">{{ $t('b1_title') }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $t('b1_desc') }}</p>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:shadow-card transition-shadow p-5">
                        <div class="bg-green-100 dark:bg-green-900/30 p-3 rounded-xl mb-4 inline-flex">
                            <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2">{{ $t('b2_title') }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $t('b2_desc') }}</p>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:shadow-card transition-shadow p-5">
                        <div class="bg-purple-100 dark:bg-purple-900/30 p-3 rounded-xl mb-4 inline-flex">
                            <svg class="w-8 h-8 text-purple-600 dark:text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2">{{ $t('b3_title') }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $t('b3_desc') }}</p>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:shadow-card transition-shadow p-5">
                        <div class="bg-orange-100 dark:bg-orange-900/30 p-3 rounded-xl mb-4 inline-flex">
                            <svg class="w-8 h-8 text-orange-600 dark:text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2">{{ $t('b4_title') }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $t('b4_desc') }}</p>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:shadow-card transition-shadow p-5">
                        <div class="bg-red-100 dark:bg-red-900/30 p-3 rounded-xl mb-4 inline-flex">
                            <svg class="w-8 h-8 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2">{{ $t('b5_title') }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $t('b5_desc') }}</p>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:shadow-card transition-shadow p-5">
                        <div class="bg-indigo-100 dark:bg-indigo-900/30 p-3 rounded-xl mb-4 inline-flex">
                            <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
                                <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2">{{ $t('b6_title') }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $t('b6_desc') }}</p>
                    </div>
                </div>
            </div>

            <!-- How It Works Section -->
            <div class="mb-10 md:mb-16">
                <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-6 md:mb-8 text-center">
                    {{ $t('how_title') }}
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="text-center">
                        <div class="bg-primary-600 text-white w-16 h-16 rounded-xl flex items-center justify-center text-2xl font-bold mx-auto mb-4">1</div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2">{{ $t('how_s1_title') }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $t('how_s1_desc') }}</p>
                    </div>
                    <div class="text-center">
                        <div class="bg-primary-600 text-white w-16 h-16 rounded-xl flex items-center justify-center text-2xl font-bold mx-auto mb-4">2</div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2">{{ $t('how_s2_title') }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $t('how_s2_desc') }}</p>
                    </div>
                    <div class="text-center">
                        <div class="bg-primary-600 text-white w-16 h-16 rounded-xl flex items-center justify-center text-2xl font-bold mx-auto mb-4">3</div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2">{{ $t('how_s3_title') }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $t('how_s3_desc') }}</p>
                    </div>
                </div>
            </div>

            <!-- Pricing Section -->
            <div class="bg-gradient-to-r from-gray-900 to-gray-800 text-white rounded-xl p-6 md:p-12 mb-10 md:mb-16">
                <div class="max-w-3xl mx-auto text-center">
                    <h2 class="text-2xl md:text-3xl font-bold mb-4">{{ $t('pricing_title') }}</h2>
                    <p class="text-base md:text-lg mb-6 md:mb-8 text-gray-300">{{ $t('pricing_subtitle') }}</p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-left">
                        <div class="bg-white/10 backdrop-blur rounded-xl p-6">
                            <p class="text-gray-300 mb-2 text-sm">{{ $t('pricing_cities') }}</p>
                            <p class="text-4xl font-bold">12-15%</p>
                        </div>
                        <div class="bg-white/10 backdrop-blur rounded-xl p-6">
                            <p class="text-gray-300 mb-2 text-sm">{{ $t('pricing_mountains') }}</p>
                            <p class="text-4xl font-bold">10-12%</p>
                        </div>
                        <div class="bg-white/10 backdrop-blur rounded-xl p-6">
                            <p class="text-gray-300 mb-2 text-sm">{{ $t('pricing_sea') }}</p>
                            <p class="text-4xl font-bold">8-10%</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FAQ Section -->
            <div class="mb-10 md:mb-16">
                <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-6 md:mb-8 text-center">
                    {{ $t('faq_title') }}
                </h2>
                <div class="max-w-3xl mx-auto space-y-2">
                    <div
                        v-for="(faq, index) in faqs"
                        :key="index"
                        class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden"
                    >
                        <button
                            @click="openFaq = openFaq === index ? null : index"
                            class="w-full flex items-center justify-between px-5 py-4 text-left bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors"
                        >
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ faq.q }}</span>
                            <svg
                                class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform flex-shrink-0 ml-4"
                                :class="openFaq === index ? 'rotate-180' : ''"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div v-if="openFaq === index" class="px-5 py-4 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ faq.a }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Final CTA -->
            <div class="bg-primary-600 text-white rounded-xl p-6 md:p-12 text-center mb-8">
                <h2 class="text-2xl md:text-4xl font-bold mb-4">{{ $t('final_title') }}</h2>
                <p class="text-base md:text-lg mb-6 md:mb-8 text-primary-100">{{ $t('final_subtitle') }}</p>
                <button
                    class="inline-flex items-center px-6 py-3 text-sm font-semibold text-primary-700 bg-white rounded-xl hover:bg-primary-50 transition-colors"
                    @click="handleBecomAHostCta"
                >
                    {{ heroCta }}
                </button>
            </div>
        </template>
    </base-wrapper>
</template>

<script>
import { mapActions, mapGetters } from "vuex";

export default {
    name: "BecomeAHost",

    data() {
        return {
            openFaq: null,
        };
    },

    computed: {
        ...mapGetters("auth", ["isLoggedIn"]),
        ...mapGetters("user", ["hostingCtaStatus"]),

        heroCta() {
            if (!this.isLoggedIn) return this.$t('cta_start');
            if (this.hostingCtaStatus === "not_host") return this.$t('cta_create_profile');
            if (this.hostingCtaStatus === "continue_listing") return this.$t('cta_continue');
            return this.$t('cta_dashboard');
        },

        faqs() {
            return [
                { q: this.$t('faq1_q'), a: this.$t('faq1_a') },
                { q: this.$t('faq2_q'), a: this.$t('faq2_a') },
                { q: this.$t('faq3_q'), a: this.$t('faq3_a') },
                { q: this.$t('faq4_q'), a: this.$t('faq4_a') },
                { q: this.$t('faq5_q'), a: this.$t('faq5_a') },
                { q: this.$t('faq6_q'), a: this.$t('faq6_a') },
            ];
        },
    },

    mounted() {
        if (this.isLoggedIn && this.hostingCtaStatus !== "not_host") {
            this.$router.replace({ name: "page-hosting-home" });
        }
    },

    methods: {
        ...mapActions(["openModal"]),

        handleBecomAHostCta() {
            if (!this.isLoggedIn) {
                this.openModal({
                    modalName: "logInModal",
                    options: { redirectTo: "/hosting/profile?next=listing-create" },
                });
                return;
            }
            if (this.hostingCtaStatus === "not_host") {
                this.$router.push({ name: "page-host-profile", query: { next: "listing-create" } });
            } else if (this.hostingCtaStatus === "continue_listing") {
                this.$router.push({ name: "page-listing-create" });
            } else {
                this.$router.push({ name: "page-hosting-home" });
            }
        },
    },
};
</script>

<i18n lang="yaml">
en:
  hero_title: "Become a Host on Acomody"
  hero_subtitle: "List your property on Acomody and open your doors to millions of travelers worldwide. Start earning today!"
  cta_start: "Start now — it's free"
  cta_create_profile: "Create host profile"
  cta_continue: "Continue listing registration"
  cta_dashboard: "Go to hosting dashboard"
  benefits_title: "Why host with Acomody?"
  b1_title: "Free listing"
  b1_desc: "List any type of accommodation completely free. You only pay when guests book your property."
  b2_title: "Verified bookings"
  b2_desc: "All bookings go through our verification, and you can cancel any that are inappropriate at any time."
  b3_title: "You set the prices"
  b3_desc: "You determine the price at which guests can book, and offer discounts for longer stays."
  b4_title: "Payment guarantee"
  b4_desc: "The option to require a 30-50% deposit significantly reduces cancellations and no-shows."
  b5_title: "You choose the guests"
  b5_desc: "Set your own rental rules and decline guests who don't meet your criteria."
  b6_title: "Flexible payments"
  b6_desc: "Guests can pay directly, by card, company invoice, or online at the time of booking."
  how_title: "How does it work?"
  how_s1_title: "Register"
  how_s1_desc: "Create a free account and add basic information about your property."
  how_s2_title: "Create your listing"
  how_s2_desc: "Add photos, set prices and define rental rules to your liking."
  how_s3_title: "Start hosting"
  how_s3_desc: "Accept bookings, welcome travelers and earn safely."
  pricing_title: "Commission only on bookings"
  pricing_subtitle: "Acomody charges a commission only when bookings occur, depending on the destination."
  pricing_cities: "Cities"
  pricing_mountains: "Mountains"
  pricing_sea: "Sea"
  faq_title: "Frequently asked questions"
  final_title: "Ready to get started?"
  final_subtitle: "Join thousands of happy hosts earning with Acomody"
  faq1_q: "Who can list accommodation on Acomody?"
  faq1_a: "Anyone who owns an apartment, villa, house, cabin or any other type of accommodation can list it on our platform. You just need ownership or the right to rent the property."
  faq2_q: "What guarantee do I have in case of cancellation?"
  faq2_a: "We enable mandatory deposit payments (30-50%) or full payment at booking, significantly reducing cancellations. In case of cancellation, your earnings are protected according to the terms you set."
  faq3_q: "What information do guests provide at booking?"
  faq3_a: "Guests provide all necessary contact details including name, surname, phone number, email address and number of guests. All information is verified and available to you before guest arrival."
  faq4_q: "How do I set the price for my accommodation?"
  faq4_a: "You completely determine the price of your accommodation. Our team provides recommendations to help you set competitive prices in line with your property's structure and location. You can easily update prices and discounts at any time."
  faq5_q: "How much does listing cost?"
  faq5_a: "Listing is completely free! You only pay a commission when a guest books your accommodation. No hidden costs or monthly fees."
  faq6_q: "Do I have to accept every guest?"
  faq6_a: "No! You set the rules and conditions of stay. You can decline a booking if a guest doesn't meet your criteria or if you feel the booking is inappropriate. You have full control over who stays at your property."

sr:
  hero_title: "Postanite domaćin na Acomody"
  hero_subtitle: "Oglašujte svoju nekretninu na Acomody i otvorite vrata milionima putnika širom sveta. Počnite da zarađujete danas!"
  cta_start: "Počnite sada — besplatno je"
  cta_create_profile: "Kreirajte profil domaćina"
  cta_continue: "Nastavite registraciju oglasa"
  cta_dashboard: "Idite na kontrolnu tablu"
  benefits_title: "Zašto biti domaćin sa Acomody?"
  b1_title: "Besplatno oglašavanje"
  b1_desc: "Postavite bilo koji tip smeštaja na sajt potpuno besplatno. Plaćate tek kada gosti rezervišu vaš smeštaj."
  b2_title: "Proverene rezervacije"
  b2_desc: "Sve rezervacije prolaze našu proveru, a vi možete u svakom trenutku otkazati rezervacije koje nisu primerene."
  b3_title: "Vi određujete cene"
  b3_desc: "Sami formirate cenu po kojoj gosti mogu da zakupe smeštaj i odobrite popuste na duže boravke."
  b4_title: "Garancija plaćanja"
  b4_desc: "Mogućnost obavezne uplate avansa 30-50% značajno smanjuje otkaze rezervacija i nepojavljivanje gostiju."
  b5_title: "Vi birate goste"
  b5_desc: "Omogućili smo da sami postavite pravila zakupa i da goste koji ih ne ispunjavaju ne primite u vaš smeštaj."
  b6_title: "Fleksibilno plaćanje"
  b6_desc: "Gosti mogu platiti direktno, putem platnih kartica, računa firme ili onlajn prilikom rezervacije."
  how_title: "Kako funkcioniše?"
  how_s1_title: "Registrujte se"
  how_s1_desc: "Kreirajte nalog potpuno besplatno i dodajte osnovne informacije o vašem smeštaju"
  how_s2_title: "Postavite oglas"
  how_s2_desc: "Dodajte fotografije, postavite cene i odredite pravila zakupa po vašoj želji"
  how_s3_title: "Primajte goste"
  how_s3_desc: "Prihvatite rezervacije, ugostite putnika i ostvarite sigurnu zaradu"
  pricing_title: "Provizija samo po rezervaciji"
  pricing_subtitle: "Acomody naplaćuje proviziju tek kada dođe do rezervacija, u zavisnosti od destinacije."
  pricing_cities: "Gradovi"
  pricing_mountains: "Planine"
  pricing_sea: "More"
  faq_title: "Često postavljana pitanja"
  final_title: "Spremni da započnete?"
  final_subtitle: "Pridružite se hiljadama zadovoljnih domaćina koji zarađuju sa Acomody"
  faq1_q: "Ko može da oglasi smeštaj na Acomody?"
  faq1_a: "Svako ko poseduje stan, vilu, vikendicu, brvnaru ili bilo koji drugi tip smeštaja može da ga oglasi na našoj platformi. Potrebno je samo da imate vlasništvo ili pravo izdavanja objekta."
  faq2_q: "Kakvu garanciju imam u slučaju otkazivanja rezervacije?"
  faq2_a: "Omogućili smo obaveznu uplatu avansa (30-50%) ili punog iznosa prilikom rezervacije, što značajno smanjuje mogućnost otkazivanja. U slučaju otkazivanja, vaša zarada je zaštićena prema uslovima koje ste postavili."
  faq3_q: "Koje podatke gosti ostavljaju prilikom rezervacije?"
  faq3_a: "Gosti ostavljaju sve neophodne kontakt podatke uključujući ime, prezime, broj telefona, email adresu i podatke o broju osoba. Sve informacije su proverene i dostupne vama pre dolaska gosta."
  faq4_q: "Kako da odredim cenu za svoj smeštaj?"
  faq4_a: "Vi u potpunosti određujete cenu svog smeštaja. Naš tim je tu da vam pruži preporuke kako bi postavili konkurentne cene koje su u skladu sa strukturom i lokacijom vašeg smeštaja. Možete lako ažurirati cene i popuste u bilo kom trenutku."
  faq5_q: "Koliko košta oglašavanje smeštaja?"
  faq5_a: "Oglašavanje je potpuno besplatno! Plaćate samo proviziju kada gost rezerviše vaš smeštaj. Nema skrivenih troškova ili mesečnih naknada."
  faq6_q: "Da li moram da primim svakog gosta?"
  faq6_a: "Ne! Vi postavljate pravila i uslove boravka. Možete odbiti rezervaciju ukoliko gost ne ispunjava vaše uslove ili ako smatrate da rezervacija nije primerena. Imate punu kontrolu nad tim ko boravi u vašem smeštaju."

hr:
  hero_title: "Postanite domaćin na Acomody"
  hero_subtitle: "Oglašajte svoju nekretninu na Acomody i otvorite vrata milijunima putnika diljem svijeta. Počnite zarađivati danas!"
  cta_start: "Počnite sada — besplatno je"
  cta_create_profile: "Kreirajte profil domaćina"
  cta_continue: "Nastavite registraciju oglasa"
  cta_dashboard: "Idite na upravljačku ploču"
  benefits_title: "Zašto biti domaćin s Acomody?"
  b1_title: "Besplatno oglašavanje"
  b1_desc: "Postavite bilo koji tip smještaja na stranicu potpuno besplatno. Plaćate tek kada gosti rezerviraju vaš smještaj."
  b2_title: "Provjerene rezervacije"
  b2_desc: "Sve rezervacije prolaze našu provjeru, a vi možete u svakom trenutku otkazati rezervacije koje nisu prikladne."
  b3_title: "Vi određujete cijene"
  b3_desc: "Sami formirate cijenu po kojoj gosti mogu zakupiti smještaj i odobrite popuste za dulje boravke."
  b4_title: "Jamstvo plaćanja"
  b4_desc: "Mogućnost obvezne uplate predujma 30-50% značajno smanjuje otkazivanja i nepojavljivanje gostiju."
  b5_title: "Vi birate goste"
  b5_desc: "Omogućili smo da sami postavite pravila zakupa i goste koji ih ne ispunjavaju ne primite u vaš smještaj."
  b6_title: "Fleksibilno plaćanje"
  b6_desc: "Gosti mogu platiti izravno, putem platnih kartica, računa tvrtke ili online pri rezervaciji."
  how_title: "Kako funkcionira?"
  how_s1_title: "Registrirajte se"
  how_s1_desc: "Kreirajte račun potpuno besplatno i dodajte osnovne informacije o vašem smještaju"
  how_s2_title: "Postavite oglas"
  how_s2_desc: "Dodajte fotografije, postavite cijene i odredite pravila zakupa po vašoj želji"
  how_s3_title: "Primajte goste"
  how_s3_desc: "Prihvatite rezervacije, ugostite putnike i ostvarite sigurnu zaradu"
  pricing_title: "Provizija samo po rezervaciji"
  pricing_subtitle: "Acomody naplaćuje proviziju tek kada dođe do rezervacija, ovisno o destinaciji."
  pricing_cities: "Gradovi"
  pricing_mountains: "Planine"
  pricing_sea: "More"
  faq_title: "Često postavljana pitanja"
  final_title: "Spremni za početak?"
  final_subtitle: "Pridružite se tisućama zadovoljnih domaćina koji zarađuju s Acomody"
  faq1_q: "Tko može oglasiti smještaj na Acomody?"
  faq1_a: "Svako tko posjeduje stan, vilu, kuću, brvnaru ili bilo koji drugi tip smještaja može ga oglasiti na našoj platformi. Potrebno je samo da imate vlasništvo ili pravo najma objekta."
  faq2_q: "Kakvo jamstvo imam u slučaju otkazivanja rezervacije?"
  faq2_a: "Omogućili smo obveznu uplatu predujma (30-50%) ili punog iznosa pri rezervaciji, što značajno smanjuje mogućnost otkazivanja. U slučaju otkazivanja, vaša zarada je zaštićena prema uvjetima koje ste postavili."
  faq3_q: "Koje podatke gosti ostavljaju pri rezervaciji?"
  faq3_a: "Gosti ostavljaju sve potrebne kontakt podatke uključujući ime, prezime, broj telefona, e-mail adresu i podatke o broju osoba. Sve informacije su provjerene i dostupne vama prije dolaska gosta."
  faq4_q: "Kako odrediti cijenu za svoj smještaj?"
  faq4_a: "Vi u potpunosti određujete cijenu svog smještaja. Naš tim pruža preporuke za postavljanje konkurentnih cijena u skladu sa strukturom i lokacijom vašeg smještaja. Možete lako ažurirati cijene i popuste u bilo koje vrijeme."
  faq5_q: "Koliko košta oglašavanje smještaja?"
  faq5_a: "Oglašavanje je potpuno besplatno! Plaćate samo proviziju kada gost rezervira vaš smještaj. Nema skrivenih troškova ili mjesečnih naknada."
  faq6_q: "Moram li prihvatiti svakog gosta?"
  faq6_a: "Ne! Vi postavljate pravila i uvjete boravka. Možete odbiti rezervaciju ako gost ne ispunjava vaše uvjete ili ako smatrate da rezervacija nije prikladna. Imate punu kontrolu nad tim tko boravi u vašem smještaju."

mk:
  hero_title: "Станете домаќин на Acomody"
  hero_subtitle: "Огласете го вашиот имот на Acomody и отворете ги вратите на милиони патници ширум светот. Почнете да заработувате денес!"
  cta_start: "Почнете сега — бесплатно е"
  cta_create_profile: "Создадете профил на домаќин"
  cta_continue: "Продолжете со регистрацијата на огласот"
  cta_dashboard: "Одете на контролната табла"
  benefits_title: "Зошто да бидете домаќин со Acomody?"
  b1_title: "Бесплатно огласување"
  b1_desc: "Поставете кој било тип на сместување целосно бесплатно. Плаќате само кога гостите ќе резервираат."
  b2_title: "Проверени резервации"
  b2_desc: "Сите резервации поминуваат низ наша проверка, а вие можете во секое време да откажете несоодветни резервации."
  b3_title: "Вие ги одредувате цените"
  b3_desc: "Сами ја формирате цената и одобрувате попусти за подолги престои."
  b4_title: "Гаранција за плаќање"
  b4_desc: "Задолжителниот аванс 30-50% значително ги намалува откажувањата и непојавувањата."
  b5_title: "Вие ги бирате гостите"
  b5_desc: "Поставете правила за закуп и одбијте ги гостите кои не ги исполнуваат вашите услови."
  b6_title: "Флексибилно плаќање"
  b6_desc: "Гостите можат да платат директно, со картичка, фактура или онлајн при резервацијата."
  how_title: "Како функционира?"
  how_s1_title: "Регистрирајте се"
  how_s1_desc: "Создадете сметка целосно бесплатно и додадете основни информации за вашиот имот"
  how_s2_title: "Поставете оглас"
  how_s2_desc: "Додадете фотографии, поставете цени и одредете правила за закуп по ваша желба"
  how_s3_title: "Примајте гости"
  how_s3_desc: "Прифатете резервации, пречекајте патници и остварете сигурна заработка"
  pricing_title: "Провизија само по резервација"
  pricing_subtitle: "Acomody наплатува провизија само кога ќе дојде до резервации, во зависност од дестинацијата."
  pricing_cities: "Градови"
  pricing_mountains: "Планини"
  pricing_sea: "Море"
  faq_title: "Често поставувани прашања"
  final_title: "Подготвени да почнете?"
  final_subtitle: "Придружете се на илјадниците задоволни домаќини кои заработуваат со Acomody"
  faq1_q: "Кој може да огласи сместување на Acomody?"
  faq1_a: "Секој кој поседува стан, вила, куќа, брвнара или кој било друг тип на сместување може да го огласи на нашата платформа. Потребно е само да имате сопственост или право на издавање."
  faq2_q: "Каква гаранција имам при откажување на резервацијата?"
  faq2_a: "Овозможивме задолжителна уплата на аванс (30-50%) или целосниот износ при резервацијата, što значително ги намалува откажувањата. При откажување, вашата заработка е заштитена според условите кои ги поставивте."
  faq3_q: "Какви податоци оставаат гостите при резервацијата?"
  faq3_a: "Гостите ги оставаат сите потребни контакт податоци вклучувајќи ime, презиме, телефон, е-пошта и број на лица. Сите информации се проверени и достапни пред пристигнувањето на гостинот."
  faq4_q: "Како да ја одредам цената за моето сместување?"
  faq4_a: "Вие целосно ја одредувате цената. Нашиот тим дава препораки за конкурентни цени во согласност со вашиот имот. Цените и попустите можете лесно да ги ажурирате во секое време."
  faq5_q: "Колку чини огласувањето?"
  faq5_a: "Огласувањето е целосно бесплатно! Плаќате само провизија кога гостинот ќе резервира. Нема скриени трошоци или месечни надоместоци."
  faq6_q: "Дали мора да прифатам секој гостин?"
  faq6_a: "Не! Вие ги поставувате правилата и условите. Можете да одбиете резервација ако гостинот не ги исполнува вашите услови. Имате целосна контрола над тоа кој престојува кај вас."

sl:
  hero_title: "Postanite gostitelj pri Acomody"
  hero_subtitle: "Oglasite svojo nepremičnino na Acomody in odprite vrata milijonom popotnikov po vsem svetu. Začnite zaslužiti danes!"
  cta_start: "Začnite zdaj — brezplačno je"
  cta_create_profile: "Ustvarite profil gostitelja"
  cta_continue: "Nadaljujte registracijo oglasa"
  cta_dashboard: "Pojdite na nadzorno ploščo"
  benefits_title: "Zakaj gostiti z Acomody?"
  b1_title: "Brezplačno oglaševanje"
  b1_desc: "Objavite katero koli vrsto nastanitve popolnoma brezplačno. Plačate le, ko gostje rezervirajo."
  b2_title: "Preverjene rezervacije"
  b2_desc: "Vse rezervacije gredo skozi naše preverjanje, vi pa lahko kadar koli prekličete neprimerne rezervacije."
  b3_title: "Vi določate cene"
  b3_desc: "Sami določite ceno, po kateri gostje lahko rezervirajo, in odobrite popuste za daljše bivanje."
  b4_title: "Jamstvo plačila"
  b4_desc: "Obvezna akontacija 30-50% bistveno zmanjša odpovedi in nepojavljanja gostov."
  b5_title: "Vi izberete goste"
  b5_desc: "Sami postavite pravila najema in zavrnete goste, ki jih ne izpolnjujejo."
  b6_title: "Fleksibilno plačilo"
  b6_desc: "Gostje lahko plačajo neposredno, s kartico, računom podjetja ali spletno ob rezervaciji."
  how_title: "Kako deluje?"
  how_s1_title: "Registrirajte se"
  how_s1_desc: "Ustvarite račun popolnoma brezplačno in dodajte osnovne informacije o svoji nastanit"
  how_s2_title: "Objavite oglas"
  how_s2_desc: "Dodajte fotografije, nastavite cene in določite pravila najema po svojih željah"
  how_s3_title: "Sprejemajte goste"
  how_s3_desc: "Sprejmite rezervacije, gostite potnike in zaslužite varno"
  pricing_title: "Provizija le ob rezervaciji"
  pricing_subtitle: "Acomody zaračuna provizijo le ob rezervacijah, odvisno od destinacije."
  pricing_cities: "Mesta"
  pricing_mountains: "Gore"
  pricing_sea: "Morje"
  faq_title: "Pogosta vprašanja"
  final_title: "Pripravljeni za začetek?"
  final_subtitle: "Pridružite se tisočim zadovoljnim gostiteljem, ki zaslužijo z Acomody"
  faq1_q: "Kdo lahko oglašuje nastanitev na Acomody?"
  faq1_a: "Vsakdo, ki ima stanovanje, vilo, hišo, kočo ali kakršno koli nastanitev, jo lahko objavi na naši platformi. Potrebujete le lastništvo ali pravico do oddajanja."
  faq2_q: "Kakšno jamstvo imam v primeru odpovedi rezervacije?"
  faq2_a: "Omogočili smo obvezno predplačilo (30-50%) ali celoten znesek ob rezervaciji, kar bistveno zmanjša odpovedi. Ob odpovedi so vaši zaslužki zaščiteni po vaših pogojih."
  faq3_q: "Kakšne podatke gostje pustijo ob rezervaciji?"
  faq3_a: "Gostje pustijo vse potrebne kontaktne podatke vključno z imenom, priimkom, telefonom, e-pošto in številom oseb. Vse informacije so preverjene in na voljo pred prihodom gosta."
  faq4_q: "Kako določiti ceno nastanitve?"
  faq4_a: "Vi v celoti določate ceno. Naša ekipa nudi priporočila za konkurenčne cene glede na vašo nastanitev. Cene in popuste lahko enostavno posodabljate kadar koli."
  faq5_q: "Koliko stane oglaševanje nastanitve?"
  faq5_a: "Oglaševanje je popolnoma brezplačno! Plačate le provizijo, ko gost rezervira. Ni skritih stroškov ali mesečnih nadomestil."
  faq6_q: "Ali moram sprejeti vsakega gosta?"
  faq6_a: "Ne! Vi postavljate pravila in pogoje bivanja. Rezervacijo lahko zavrnete, če gost ne izpolnjuje vaših pogojev. Imate popoln nadzor nad tem, kdo biva pri vas."
</i18n>
