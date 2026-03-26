<template>
    <div>
        <h1 class="text-3xl font-semibold text-gray-900 dark:text-white mb-2">
            {{ $t('heading') }}
        </h1>
        <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">
            {{ $t('subtitle') }}
        </p>

        <hr />

        <div class="py-4 overflow-auto h-[60vh] mx-auto space-y-8 pr-4">

            <!-- BASE PRICE INPUT -->
            <div class="text-center space-y-4">
                <div class="relative inline-block">
                    <div class="flex items-baseline justify-center">
                        <span
                            v-if="priceCurrency && priceCurrency.symbol_position === 'before'"
                            class="text-6xl font-semibold text-gray-900 dark:text-white mr-2"
                        >
                            {{ priceCurrency.code }}
                        </span>
                        <input
                            :value="earnMode ? desiredEarnings : localPricing.basePrice"
                            @input="earnMode ? updateFromEarnings($event) : updateBasePrice($event)"
                            @focus="$event.target.select()"
                            type="number"
                            min="10"
                            step="1"
                            class="text-6xl font-semibold text-gray-900 dark:text-white bg-transparent border-0 border-b-4 border-gray-300 dark:border-gray-700 focus:border-gray-900 dark:focus:border-white focus:ring-0 w-48 text-center outline-none"
                            placeholder="00"
                        />
                        <span
                            v-if="priceCurrency && priceCurrency.symbol_position === 'after'"
                            class="text-6xl font-semibold text-gray-900 dark:text-white ml-2"
                        >
                            {{ priceCurrency.code }}
                        </span>
                    </div>
                    <p class="text-lg text-gray-600 dark:text-gray-400 mt-2">
                        {{ earnMode ? $t('desired_earnings_label') : $t('base_price_per_night') }}
                    </p>
                    <div v-if="earnMode" class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        {{ $t('listing_price_calculated', { price: formatPrice(calculatedListingPrice, priceCurrency, true, 'code') }) }}
                    </div>
                </div>

                <!-- Earn mode toggle -->
                <button
                    type="button"
                    @click="earnMode = !earnMode"
                    class="text-sm text-primary-600 dark:text-primary-400 underline hover:no-underline"
                >
                    {{ earnMode ? $t('enter_listing_price') : $t('enter_desired_earnings') }}
                </button>
            </div>

            <!-- HOST EARNINGS BREAKDOWN (primary, prominent) -->
            <div class="p-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl space-y-3">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                        {{ $t('your_earnings_heading') }}
                    </h3>
                    <span v-if="isCommissionFree"
                        class="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-200 text-green-800 dark:bg-green-800 dark:text-green-100">
                        {{ $t('no_commission_badge') }}
                    </span>
                    <span v-else-if="currentPlan === 'club'"
                        class="px-2 py-0.5 rounded-full text-xs font-semibold bg-primary-100 text-primary-800 dark:bg-primary-900/40 dark:text-primary-300">
                        Club — {{ effectiveCommissionRate }}%
                    </span>
                </div>

                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">{{ $t('base_price_label') }}</span>
                    <span class="font-medium text-gray-900 dark:text-white">
                        {{ formatPrice(localPricing.basePrice || 0, priceCurrency, true, 'code') }}
                    </span>
                </div>

                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">
                        {{ isCommissionFree
                            ? $t('platform_commission_free')
                            : $t('platform_commission', { percentage: effectiveCommissionRate }) }}
                    </span>
                    <span :class="isCommissionFree ? 'font-medium text-green-600 dark:text-green-400' : 'font-medium text-red-600 dark:text-red-400'">
                        {{ isCommissionFree ? $t('free') : `− ${formatPrice(hostCommission, priceCurrency, true, 'code')}` }}
                    </span>
                </div>

                <div class="flex items-center justify-between pt-3 border-t border-green-200 dark:border-green-700">
                    <span class="text-base font-semibold text-gray-900 dark:text-white">
                        {{ $t('you_earn') }}
                    </span>
                    <span class="text-2xl font-bold text-green-600 dark:text-green-400">
                        {{ formatPrice(youEarn, priceCurrency, true, 'code') }}
                    </span>
                </div>

                <!-- Early host note -->
                <p v-if="isCommissionFree" class="text-xs text-green-700 dark:text-green-300">
                    {{ earlyHostNote }}
                </p>
                <p v-else class="text-xs text-gray-500 dark:text-gray-400">
                    {{ $t('commission_note') }}
                </p>
            </div>

            <!-- CLUB UPGRADE CTA (shown for Free plan users who are not early hosts) -->
            <div
                v-if="currentPlan === 'free' && !isCommissionFree"
                class="p-4 bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800 rounded-xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3"
            >
                <div>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">
                        {{ $t('club_cta_title') }}
                    </p>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">
                        {{ $t('club_cta_desc') }}
                    </p>
                </div>
                <router-link
                    :to="{ name: 'page-account', query: { tab: 'subscription' } }"
                    class="shrink-0 text-xs font-semibold text-primary-700 dark:text-primary-300 underline hover:no-underline whitespace-nowrap"
                >
                    {{ $t('club_cta_link') }}
                </router-link>
            </div>

            <!-- GUEST VIEW BREAKDOWN (informational) -->
            <div class="p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl space-y-3">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2">
                    {{ $t('guest_view_heading') }}
                </h3>

                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">{{ $t('accommodation_price') }}</span>
                    <span class="font-medium text-gray-900 dark:text-white">
                        {{ formatPrice(localPricing.basePrice || 0, priceCurrency, true, 'code') }}
                    </span>
                </div>

                <div class="flex items-center justify-between pt-3 border-t border-gray-200 dark:border-gray-700">
                    <span class="text-base font-semibold text-gray-900 dark:text-white">{{ $t('guest_pays') }}</span>
                    <span class="text-base font-semibold text-gray-900 dark:text-white">
                        {{ formatPrice(localPricing.basePrice || 0, priceCurrency, true, 'code') }}
                    </span>
                </div>

                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $t('guest_no_fee_note') }}</p>
            </div>

            <!-- BOOKING TYPE -->
            <div class="p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl">
                <div class="mb-4">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        {{ $t('booking_type_heading') }}
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $t('booking_type_desc') }}</p>
                </div>

                <div class="space-y-3">
                    <booking-type-card
                        v-for="type in pricingConfig.bookingTypes"
                        :key="type.id"
                        :booking-type="type"
                        :selected="localPricing.bookingType === type.id"
                        @select="updateBookingType"
                    />
                </div>

                <div class="mt-4 p-3 bg-primary-50 dark:bg-primary-900/20 rounded-xl">
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $t('booking_type_tip') }}</p>
                </div>
            </div>

            <!-- MINIMUM STAY -->
            <minimum-stay-selector
                :pricing="localPricing"
                :days-of-week="config.ui.daysOfWeek"
                @update:pricing="updatePricing"
            />
        </div>
    </div>
</template>

<script>
import { mapState, mapGetters } from "vuex";
import { pricingConfig } from "./pricingConfig";
import { formatPrice } from "@/utils/helpers";
import config from "@/config";
import BookingTypeCard from "@/src/views/hosting/createAccommodation/components/BookingTypeCard.vue";
import MinimumStaySelector from "@/src/views/hosting/createAccommodation/components/MinimumStaySelector.vue";

export default {
    name: "Step9Pricing",
    components: {
        BookingTypeCard,
        MinimumStaySelector,
    },
    props: {
        formData: {
            type: Object,
            required: true,
        },
        errors: {
            type: Object,
            default: () => ({}),
        },
    },
    emits: ["update:form-data"],
    data() {
        return {
            config,
            pricingConfig,
            localPricing: { ...this.formData.pricing },
            earnMode: false,
            desiredEarnings: 0,
        };
    },
    computed: {
        ...mapState("hosting/createAccommodation", ["accommodationTypes"]),
        ...mapState("ui", ["currencies"]),
        ...mapGetters("user", ["isCommissionFree", "commissionRate", "currentPlan", "subscription"]),

        effectiveCommissionRate() {
            return this.commissionRate ?? pricingConfig.freeCommissionPercentage;
        },

        earlyHostNote() {
            const sub = this.subscription;
            if (!sub || !sub.is_early_host) return '';
            if (!sub.early_host_expires_at) {
                return this.$t('early_host_note_pending');
            }
            const date = new Date(sub.early_host_expires_at).toLocaleDateString();
            return this.$t('early_host_note_expires', { date });
        },

        priceCurrency() {
            const code = this.config.ui.countryCurrencyMap[this.formData.address?.country];
            return this.currencies.find((c) => c.code === code) || null;
        },

        hostCommission() {
            if (!this.localPricing.basePrice) return 0;
            return Math.round(this.localPricing.basePrice * (this.effectiveCommissionRate / 100));
        },

        youEarn() {
            if (!this.localPricing.basePrice) return 0;
            return this.localPricing.basePrice - this.hostCommission;
        },

        calculatedListingPrice() {
            if (!this.desiredEarnings) return 0;
            if (this.effectiveCommissionRate === 0) return this.desiredEarnings;
            return Math.ceil(this.desiredEarnings / (1 - this.effectiveCommissionRate / 100));
        },
    },
    watch: {
        "formData.pricing": {
            deep: true,
            handler(newPricing) {
                this.localPricing = { ...newPricing };
            },
        },
        earnMode(active) {
            if (active) {
                this.desiredEarnings = this.youEarn || 0;
            }
        },
    },
    methods: {
        formatPrice,

        updateBasePrice(event) {
            const value = parseFloat(event.target.value) || 0;
            this.localPricing.basePrice = value;
            this.emitUpdate();
        },

        updateFromEarnings(event) {
            const earnings = parseFloat(event.target.value) || 0;
            this.desiredEarnings = earnings;
            this.localPricing.basePrice = this.calculatedListingPrice;
            this.emitUpdate();
        },

        updateBookingType(typeId) {
            this.localPricing.bookingType = typeId;
            this.emitUpdate();
        },

        updatePricing(updates) {
            this.localPricing = { ...this.localPricing, ...updates };
            this.emitUpdate();
        },

        emitUpdate() {
            this.$emit("update:form-data", {
                ...this.formData,
                pricing: { ...this.localPricing },
            });
        },
    },
};
</script>

<i18n lang="yaml">
en:
  heading: Now, set your price
  subtitle: You control your price. You can change it anytime.
  base_price_per_night: base price per night
  desired_earnings_label: desired earnings per night
  listing_price_calculated: "Listing price will be set to: {price}"
  enter_desired_earnings: I want to earn a specific amount →
  enter_listing_price: ← Enter listing price instead
  your_earnings_heading: Your earnings per night
  base_price_label: Base price (set by you)
  platform_commission: "Platform commission ({percentage}%)"
  platform_commission_free: Platform commission
  free: Free
  no_commission_badge: "0% commission"
  early_host_note_pending: "Early host benefit — 0% commission will apply for 6 months after platform launch."
  early_host_note_expires: "Early host benefit active until {date}."
  you_earn: You receive
  commission_note: The platform acts as an intermediary. Your price is always yours to set.
  club_cta_title: "Reduce your commission to 5% with Club"
  club_cta_desc: "Club plan (€30/mo) cuts your commission in half — unlimited listings, full AI Receptionist."
  club_cta_link: "Upgrade to Club →"
  guest_view_heading: What your guests will see
  accommodation_price: Accommodation price (per night)
  guest_pays: Guest pays
  guest_no_fee_note: Guests pay exactly the price you set — no service fee added. Platform commission is deducted from your earnings only.
  booking_type_heading: Booking Type
  booking_type_desc: Choose how you want to receive reservations
  booking_type_tip: "Tip: Instant booking typically results in 3x more reservations compared to request-only listings."

sr:
  heading: Sada postavite svoju cenu
  subtitle: Vi kontrolišete svoju cenu. Možete je promeniti u bilo koje vreme.
  base_price_per_night: osnovna cena po noći
  desired_earnings_label: željena zarada po noći
  listing_price_calculated: "Cena oglasa biće postavljena na: {price}"
  enter_desired_earnings: Želim da zaradim određeni iznos →
  enter_listing_price: ← Unesi cenu oglasa
  your_earnings_heading: Vaša zarada po noći
  base_price_label: Osnovna cena (Vi je postavljate)
  platform_commission: "Provizija platforme ({percentage}%)"
  platform_commission_free: Provizija platforme
  free: Besplatno
  no_commission_badge: "0% provizija"
  early_host_note_pending: "Benefit ranog domaćina — 0% provizije važi 6 meseci od lansiranja platforme."
  early_host_note_expires: "Benefit ranog domaćina aktivan do {date}."
  you_earn: Vi dobijate
  commission_note: Platforma je posrednik. Vašu cenu uvek Vi postavljate.
  club_cta_title: "Smanjite proviziju na 5% sa Club planom"
  club_cta_desc: "Club plan (30€/mes) prepolovljuje vašu proviziju — neograničeni smeštaji, pun AI recepcionista."
  club_cta_link: "Pređite na Club →"
  guest_view_heading: Šta vaši gosti vide
  accommodation_price: Cena smeštaja (po noći)
  guest_pays: Gost plaća
  guest_no_fee_note: Gosti plaćaju tačno cenu koju ste postavili — bez dodatnih naknada. Provizija platforme se odbija samo od vaše zarade.
  booking_type_heading: Vrsta rezervacije
  booking_type_desc: Odaberite kako želite primati rezervacije
  booking_type_tip: "Savet: Direktna rezervacija tipično rezultira 3 puta više rezervacija u poređenju s oglasima koji zahtevaju zahtev."

hr:
  heading: Sada postavite svoju cijenu
  subtitle: Vi kontrolirate svoju cijenu. Možete je promijeniti u bilo koje vrijeme.
  base_price_per_night: osnovna cijena po noći
  desired_earnings_label: željena zarada po noći
  listing_price_calculated: "Cijena oglasa bit će postavljena na: {price}"
  enter_desired_earnings: Želim zaraditi određeni iznos →
  enter_listing_price: ← Unesi cijenu oglasa
  your_earnings_heading: Vaša zarada po noći
  base_price_label: Osnovna cijena (Vi je postavljate)
  platform_commission: "Provizija platforme ({percentage}%)"
  platform_commission_free: Provizija platforme
  free: Besplatno
  no_commission_badge: "0% provizija"
  early_host_note_pending: "Benefit ranog domaćina — 0% provizije vrijedi 6 mjeseci od lansiranja platforme."
  early_host_note_expires: "Benefit ranog domaćina aktivan do {date}."
  you_earn: Vi primate
  commission_note: Platforma je posrednik. Vaša cijena uvijek pripada Vama.
  club_cta_title: "Smanjite proviziju na 5% s Club planom"
  club_cta_desc: "Club plan (30€/mj) prepolovljuje vašu proviziju — neograničeni smještaji, puni AI recepcionar."
  club_cta_link: "Prijeđite na Club →"
  guest_view_heading: Što vaši gosti vide
  accommodation_price: Cijena smještaja (po noći)
  guest_pays: Gost plaća
  guest_no_fee_note: Gosti plaćaju točno cijenu koju ste postavili — bez dodatnih naknada. Provizija platforme odbija se samo od vaše zarade.
  booking_type_heading: Vrsta rezervacije
  booking_type_desc: Odaberite kako želite primati rezervacije
  booking_type_tip: "Savjet: Direktna rezervacija tipično rezultira 3 puta više rezervacija u usporedbi s oglasima koji zahtijevaju zahtjev."

mk:
  heading: Сега поставете ја вашата цена
  subtitle: Вие ја контролирате вашата цена. Можете да ја промените во секое време.
  base_price_per_night: основна цена по ноќ
  desired_earnings_label: посакувана заработка по ноќ
  listing_price_calculated: "Цената на огласот ќе биде поставена на: {price}"
  enter_desired_earnings: Сакам да заработам одреден износ →
  enter_listing_price: ← Внеси цена на огласот
  your_earnings_heading: Вашата заработка по ноќ
  base_price_label: Основна цена (Вие ја поставувате)
  platform_commission: "Провизија на платформата ({percentage}%)"
  platform_commission_free: Провизија на платформата
  free: Бесплатно
  no_commission_badge: "0% провизија"
  early_host_note_pending: "Придобивка за ран домаќин — 0% провизија важи 6 месеци по лансирањето на платформата."
  early_host_note_expires: "Придобивката за ран домаќин е активна до {date}."
  you_earn: Вие добивате
  commission_note: Платформата е посредник. Вашата цена секогаш е ваша.
  club_cta_title: "Намалете ја провизијата на 5% со Club план"
  club_cta_desc: "Club план (30€/мес) ја преполовува вашата провизија — неограничени сместувања, полн AI рецепционер."
  club_cta_link: "Преминете на Club →"
  guest_view_heading: Што гледаат вашите гости
  accommodation_price: Цена на сместување (по ноќ)
  guest_pays: Гостинот плаќа
  guest_no_fee_note: Гостите плаќаат точно ја цената што сте ја поставиле — без дополнителни надоместоци. Провизијата на платформата се одбива само од вашата заработка.
  booking_type_heading: Вид на резервација
  booking_type_desc: Одберете како сакате да примате резервации
  booking_type_tip: "Совет: Директната резервација обично резултира со 3 пати повеќе резервации во споредба со огласите кои бараат барање."

sl:
  heading: Zdaj nastavite svojo ceno
  subtitle: Vi nadzorujete svojo ceno. Spremenite jo lahko kadarkoli.
  base_price_per_night: osnovna cena na noč
  desired_earnings_label: želen zaslužek na noč
  listing_price_calculated: "Cena oglasa bo nastavljena na: {price}"
  enter_desired_earnings: Želim zaslužiti določen znesek →
  enter_listing_price: ← Vnesi ceno oglasa
  your_earnings_heading: Vaš zaslužek na noč
  base_price_label: Osnovna cena (nastavite jo vi)
  platform_commission: "Provizija platforme ({percentage}%)"
  platform_commission_free: Provizija platforme
  free: Brezplačno
  no_commission_badge: "0% provizija"
  early_host_note_pending: "Ugodnost zgodnjega gostitelja — 0% provizija velja 6 mesecev po zagonu platforme."
  early_host_note_expires: "Ugodnost zgodnjega gostitelja aktivna do {date}."
  you_earn: Prejmete
  commission_note: Platforma je posrednik. Vašo ceno vedno določate vi.
  club_cta_title: "Znižajte provizijo na 5% s Club načrtom"
  club_cta_desc: "Club načrt (30€/mes) razpolovi vašo provizijo — neomejene nastanitve, polni AI recepcionist."
  club_cta_link: "Nadgradite na Club →"
  guest_view_heading: Kaj vidijo vaši gostje
  accommodation_price: Cena nastanitve (na noč)
  guest_pays: Gost plača
  guest_no_fee_note: Gostje plačajo točno ceno, ki ste jo nastavili — brez dodatnih provizij. Provizija platforme se odbije samo od vašega zaslužka.
  booking_type_heading: Vrsta rezervacije
  booking_type_desc: Izberite, kako želite prejemati rezervacije
  booking_type_tip: "Nasvet: Takojšnja rezervacija tipično prinese 3-krat več rezervacij v primerjavi z oglasi, ki zahtevajo zahtevek."
</i18n>
