<template>
    <base-wrapper>
        <template v-slot:content>
            <!-- Hero Section -->
            <div class="bg-gradient-to-r from-primary-600 to-primary-700 text-white rounded-xl p-6 md:p-12 mb-8 md:mb-12">
                <div class="max-w-3xl">
                    <h1 class="text-3xl md:text-5xl font-bold mb-4 md:mb-6">
                        Become a Host on Acomody
                    </h1>
                    <p class="text-base md:text-xl mb-6 md:mb-8 text-primary-100">
                        List your property on Acomody and open your doors to millions of travelers worldwide. Start earning today!
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
                    Why host with Acomody?
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:shadow-card transition-shadow p-5">
                        <div class="bg-primary-100 dark:bg-primary-900/30 p-3 rounded-xl mb-4 inline-flex">
                            <svg class="w-8 h-8 text-primary-600 dark:text-primary-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2">Besplatno oglašavanje</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Postavite bilo koji tip smeštaja na sajt potpuno besplatno.
                            Plaćate tek kada gosti rezervišu vaš smeštaj.
                        </p>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:shadow-card transition-shadow p-5">
                        <div class="bg-green-100 dark:bg-green-900/30 p-3 rounded-xl mb-4 inline-flex">
                            <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2">Proverene rezervacije</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Sve rezervacije prolaze našu proveru, a vi možete u svakom trenutku
                            otkazati rezervacije koje nisu primerene.
                        </p>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:shadow-card transition-shadow p-5">
                        <div class="bg-purple-100 dark:bg-purple-900/30 p-3 rounded-xl mb-4 inline-flex">
                            <svg class="w-8 h-8 text-purple-600 dark:text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2">Vi određujete cene</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Sami formirate cenu po kojoj gosti mogu da zakupe smeštaj i
                            odobrite popuste na duže boravke.
                        </p>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:shadow-card transition-shadow p-5">
                        <div class="bg-orange-100 dark:bg-orange-900/30 p-3 rounded-xl mb-4 inline-flex">
                            <svg class="w-8 h-8 text-orange-600 dark:text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2">Garancija plaćanja</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Mogućnost obavezne uplate avansa 30-50% značajno smanjuje
                            otkaze rezervacija i nepojavljivanje gostiju.
                        </p>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:shadow-card transition-shadow p-5">
                        <div class="bg-red-100 dark:bg-red-900/30 p-3 rounded-xl mb-4 inline-flex">
                            <svg class="w-8 h-8 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2">Vi birate goste</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Omogućili smo da sami postavite pravila zakupa i da goste
                            koji ih ne ispunjavaju ne primite u vaš smestaj.
                        </p>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:shadow-card transition-shadow p-5">
                        <div class="bg-indigo-100 dark:bg-indigo-900/30 p-3 rounded-xl mb-4 inline-flex">
                            <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
                                <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2">Fleksibilno plaćanje</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Gosti mogu platiti direktno, putem platnih kartica,
                            računa firme ili onlajn prilikom rezervacije.
                        </p>
                    </div>
                </div>
            </div>

            <!-- How It Works Section -->
            <div class="mb-10 md:mb-16">
                <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-6 md:mb-8 text-center">
                    Kako funkcioniše?
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="text-center">
                        <div class="bg-primary-600 text-white w-16 h-16 rounded-xl flex items-center justify-center text-2xl font-bold mx-auto mb-4">
                            1
                        </div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2">Registrujte se</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Kreirajte nalog potpuno besplatno i dodajte osnovne informacije o vašem smeštaju
                        </p>
                    </div>

                    <div class="text-center">
                        <div class="bg-primary-600 text-white w-16 h-16 rounded-xl flex items-center justify-center text-2xl font-bold mx-auto mb-4">
                            2
                        </div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2">Postavite oglas</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Dodajte fotografije, postavite cene i odredite pravila zakupa po vašoj želji
                        </p>
                    </div>

                    <div class="text-center">
                        <div class="bg-primary-600 text-white w-16 h-16 rounded-xl flex items-center justify-center text-2xl font-bold mx-auto mb-4">
                            3
                        </div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2">Primajte goste</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Prihvatite rezervacije, ugostite putnika i ostvarite sigurnu zaradu
                        </p>
                    </div>
                </div>
            </div>

            <!-- Pricing Section -->
            <div class="bg-gradient-to-r from-gray-900 to-gray-800 text-white rounded-xl p-6 md:p-12 mb-10 md:mb-16">
                <div class="max-w-3xl mx-auto text-center">
                    <h2 class="text-2xl md:text-3xl font-bold mb-4">Provizija samo po rezervaciji</h2>
                    <p class="text-base md:text-lg mb-6 md:mb-8 text-gray-300">
                        Acomody naplaćuje proviziju tek kada dođe do prvih rezervacija.
                        U zavisnosti od destinacije na kojoj se smeštaj nalazi.
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-left">
                        <div class="bg-white/10 backdrop-blur rounded-xl p-6">
                            <p class="text-gray-300 mb-2 text-sm">Gradovi</p>
                            <p class="text-4xl font-bold">12-15%</p>
                        </div>
                        <div class="bg-white/10 backdrop-blur rounded-xl p-6">
                            <p class="text-gray-300 mb-2 text-sm">Planine</p>
                            <p class="text-4xl font-bold">10-12%</p>
                        </div>
                        <div class="bg-white/10 backdrop-blur rounded-xl p-6">
                            <p class="text-gray-300 mb-2 text-sm">More</p>
                            <p class="text-4xl font-bold">8-10%</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FAQ Section -->
            <div class="mb-10 md:mb-16">
                <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-6 md:mb-8 text-center">
                    Često postavljana pitanja
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
                <h2 class="text-2xl md:text-4xl font-bold mb-4">Spremni da započnete?</h2>
                <p class="text-base md:text-lg mb-6 md:mb-8 text-primary-100">
                    Pridružite se hiljadama zadovoljnih domaćina koji zarađuju sa Acomody
                </p>
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
            faqs: [
                {
                    q: "Ko može da oglasi smeštaj na Acomody?",
                    a: "Svako ko poseduje stan, vilu, vikendicu, brvnaru ili bilo koji drugi tip smeštaja može da ga oglasi na našoj platformi. Potrebno je samo da imate vlasništvo ili pravo izdavanja objekta.",
                },
                {
                    q: "Kakvu garanciju imam u slučaju otkazivanja rezervacije?",
                    a: "Omogućili smo obaveznu uplatu avansa (30-50%) ili punog iznosa prilikom rezervacije, što značajno smanjuje mogućnost otkazivanja. U slučaju otkazivanja, vaša zarada je zaštićena prema uslovima koje ste postavili.",
                },
                {
                    q: "Koje podatke gosti ostavljaju prilikom rezervacije?",
                    a: "Gosti ostavljaju sve neophodne kontakt podatke uključujući ime, prezime, broj telefona, email adresu i podatke o broju osoba. Sve informacije su proverene i dostupne vama pre dolaska gosta.",
                },
                {
                    q: "Kako da odredim cenu za svoj smeštaj?",
                    a: "Vi u potpunosti određujete cenu svog smeštaja. Naš tim je tu da vam pruži preporuke kako bi postavili konkurentne cene koje su u skladu sa strukturom i lokacijom vašeg smeštaja. Možete lako ažurirati cene i popuste u bilo kom trenutku.",
                },
                {
                    q: "Koliko košta oglašavanje smeštaja?",
                    a: "Oglašavanje je potpuno besplatno! Plaćate samo proviziju kada gost rezerviše vaš smeštaj. Nema skrivenih troškova ili mesečnih naknada.",
                },
                {
                    q: "Da li moram da primim svakog gosta?",
                    a: "Ne! Vi postavljate pravila i uslove boravka. Možete odbiti rezervaciju ukoliko gost ne ispunjava vaše uslove ili ako smatrate da rezervacija nije primerena. Imate punu kontrolu nad tim ko boravi u vašem smeštaju.",
                },
            ],
        };
    },
    computed: {
        ...mapGetters("auth", ["isLoggedIn"]),
        ...mapGetters("user", ["hostingCtaStatus"]),
        heroCta() {
            if (!this.isLoggedIn) return "Start now — it's free";
            if (this.hostingCtaStatus === "not_host") return "Create host profile";
            if (this.hostingCtaStatus === "continue_listing") return "Continue listing registration";
            return "Go to hosting dashboard";
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
