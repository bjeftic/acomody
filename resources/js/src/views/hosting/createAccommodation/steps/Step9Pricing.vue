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
            <!-- Base Price Input Section -->
            <div class="text-center space-y-6">
                <!-- Large Price Display -->
                <div class="relative inline-block">
                    <div class="flex items-baseline justify-center">
                        <span
                            v-if="priceCurrency.symbol_position === 'before'"
                            class="text-6xl font-semibold text-gray-900 dark:text-white mr-2"
                        >
                            {{ priceCurrency.code }}
                        </span>
                        <input
                            :value="localPricing.basePrice"
                            @input="updateBasePrice"
                            @focus="$event.target.select()"
                            type="number"
                            min="10"
                            step="1"
                            class="text-6xl font-semibold text-gray-900 dark:text-white bg-transparent border-0 border-b-4 border-gray-300 dark:border-gray-700 focus:border-gray-900 dark:focus:border-white focus:ring-0 w-48 text-center outline-none"
                            placeholder="00"
                        />
                        <span
                            v-if="priceCurrency.symbol_position === 'after'"
                            class="text-6xl font-semibold text-gray-900 dark:text-white mr-2"
                        >
                            {{ priceCurrency.code }}
                        </span>
                    </div>
                    <p class="text-lg text-gray-600 dark:text-gray-400 mt-2">
                        {{ $t('base_price_per_night') }}
                    </p>
                </div>

                <!-- Guest Price Preview -->
                <div
                    class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg inline-block"
                >
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">
                        {{ $t('guest_price_label') }}
                    </p>
                    <div class="flex items-baseline justify-center">
                        <p
                            class="text-2xl font-semibold text-gray-900 dark:text-white"
                        >
                            {{ formatPrice(guestPriceBeforeTaxes, priceCurrency, true, 'code') }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Weekend Pricing -->
            <!-- <weekend-pricing
                :pricing="localPricing"
                :currency="pricingConfig.currency"
                :guest-price="weekendGuestPrice"
                @update:pricing="updatePricing"
            /> -->

            <!-- Long Stay Discounts -->
            <!-- <long-stay-discounts
                :pricing="localPricing"
                :currency="pricingConfig.currency"
                :base-price="localPricing.basePrice"
                @update:pricing="updatePricing"
            /> -->

            <!-- Additional Fees -->
            <!-- <additional-fees
                :pricing="localPricing"
                :currency="pricingConfig.currency"
                @update:pricing="updatePricing"
            /> -->

            <!-- Booking Type -->
            <div
                class="p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl"
            >
                <div class="mb-4">
                    <h3
                        class="text-base font-semibold text-gray-900 dark:text-white mb-2 flex items-center"
                    >
                        <svg
                            class="w-5 h-5 mr-2 text-primary-500"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z"
                            />
                        </svg>
                        {{ $t('booking_type_heading') }}
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $t('booking_type_desc') }}
                    </p>
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

                <!-- Info Note -->
                <div class="mt-4 p-3 bg-primary-50 dark:bg-primary-900/20 rounded-xl">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $t('booking_type_tip') }}
                    </p>
                </div>
            </div>

            <!-- Minimum Stay Requirements -->
            <minimum-stay-selector
                :pricing="localPricing"
                :days-of-week="config.ui.daysOfWeek"
                @update:pricing="updatePricing"
            />

            <!-- Price Breakdown -->
            <pricing-breakdown
                :base-price="localPricing.basePrice"
                :currency="priceCurrency"
                :guest-service-fee-percentage="
                    pricingConfig.guestServiceFeePercentage
                "
                @toggle-info="showServiceFeeInfo = !showServiceFeeInfo"
            />

            <!-- Service Fee Info -->
            <div
                v-if="showServiceFeeInfo"
                class="p-4 bg-primary-50 dark:bg-primary-900/20 rounded-xl"
            >
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ $t('service_fee_tip') }}
                </p>
            </div>

            <!-- Your Earnings -->
            <div
                class="p-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl"
            >
                <div class="flex items-center justify-between mb-4">
                    <h3
                        class="text-base font-semibold text-gray-900 dark:text-white"
                    >
                        {{ $t('you_earn') }}
                    </h3>
                    <span
                        class="text-2xl font-bold text-green-600 dark:text-green-400"
                    >

                        {{ formatPrice(youEarn, priceCurrency, true, 'code') }}
                    </span>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ $t('after_fee', { percentage: pricingConfig.hostServiceFeePercentage }) }}
                </p>
            </div>

            <!-- Pricing Tips -->
            <!-- <pricing-tips :currency="pricingConfig.currency" /> -->

            <!-- Competitive Analysis -->
            <!-- <competitive-pricing
                :city="formData.address.city"
                :property-type-name="accommodationTypeName"
                :currency="pricingConfig.currency"
                :average-price="pricingConfig.averagePrice"
                :price-range="pricingConfig.priceRange"
                :your-price="localPricing.basePrice"
            /> -->
        </div>
    </div>
</template>

<script>
import { mapState } from "vuex";
import { pricingConfig } from "./pricingConfig";
import { formatPrice } from "@/utils/helpers";
import config from "@/config";
import PricingBreakdown from "@/src/views/hosting/createAccommodation/components/PricingBreakdown.vue";
import BookingTypeCard from "@/src/views/hosting/createAccommodation/components/BookingTypeCard.vue";
import WeekendPricing from "@/src/views/hosting/createAccommodation/components/WeekendPricing.vue";
import LongStayDiscounts from "@/src/views/hosting/createAccommodation/components/LongStayDiscounts.vue";
import MinimumStaySelector from "@/src/views/hosting/createAccommodation/components/MinimumStaySelector.vue";
import PricingTips from "@/src/views/hosting/createAccommodation/components/PricingTips.vue";
import CompetitivePricing from "@/src/views/hosting/createAccommodation/components/CompetitivePricing.vue";
import AdditionalFees from "@/src/views/hosting/createAccommodation/components/AdditionalFees.vue";

export default {
    name: "Step9Pricing",
    components: {
        PricingBreakdown,
        BookingTypeCard,
        WeekendPricing,
        LongStayDiscounts,
        MinimumStaySelector,
        PricingTips,
        CompetitivePricing,
        AdditionalFees,
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
            pricingConfig: pricingConfig,
            localPricing: { ...this.formData.pricing },
            showServiceFeeInfo: false,
        };
    },
    computed: {
        ...mapState("hosting/createAccommodation", ["accommodationTypes"]),
        ...mapState("ui", ["currencies"]),
        accommodationTypeName() {
            if (!this.formData.accommodationType) return "place";
            const type = this.accommodationTypes.find(
                (t) => t.id === this.formData.accommodationType
            );
            return type ? type.name.toLowerCase() : "place";
        },

        guestPriceBeforeTaxes() {
            if (!this.localPricing.basePrice) return 0;
            const serviceFee = Math.round(
                this.localPricing.basePrice *
                    (this.pricingConfig.guestServiceFeePercentage / 100)
            );
            return this.localPricing.basePrice + serviceFee;
        },

        weekendGuestPrice() {
            if (
                !this.localPricing.hasWeekendPrice ||
                !this.localPricing.weekendPrice
            ) {
                return this.guestPriceBeforeTaxes;
            }
            const weekendServiceFee = Math.round(
                this.localPricing.weekendPrice *
                    (this.pricingConfig.guestServiceFeePercentage / 100)
            );
            return this.localPricing.weekendPrice + weekendServiceFee;
        },

        youEarn() {
            if (!this.localPricing.basePrice) return 0;
            const hostFee = Math.round(
                this.localPricing.basePrice *
                    (this.pricingConfig.hostServiceFeePercentage / 100)
            );
            return this.localPricing.basePrice - hostFee;
        },

        priceCurrency() {
            const validCurrency =
                this.config.ui.countryCurrencyMap[
                    this.formData.address.country
                ];
            return this.currencies.filter(
                (currency) => currency.code === validCurrency
            )[0];
        },
    },
    watch: {
        "formData.pricing": {
            deep: true,
            handler(newPricing) {
                this.localPricing = { ...newPricing };
            },
        },

        "localPricing.hasWeekendPrice"(enabled) {
            if (enabled && !this.localPricing.weekendPrice) {
                this.localPricing.weekendPrice = Math.round(
                    this.localPricing.basePrice * 1.25
                );
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
  subtitle: You can change it anytime.
  base_price_per_night: base price per night
  guest_price_label: Guest price before taxes (weekday)
  booking_type_heading: Booking Type
  booking_type_desc: Choose how you want to receive reservations
  booking_type_tip: "Tip: Instant booking typically results in 3x more reservations compared to request-only listings."
  you_earn: You earn (per night)
  after_fee: After the {percentage}% host service fee
  service_fee_tip: This fee helps us run our platform and provide 24/7 customer support for your trip.
sr:
  heading: Sada postavite svoju cenu
  subtitle: Možete je promeniti u bilo koje vreme.
  base_price_per_night: osnovna cena po noći
  guest_price_label: Cena za gosta pre poreza (radni dan)
  booking_type_heading: Vrsta rezervacije
  booking_type_desc: Odaberite kako želite primati rezervacije
  booking_type_tip: "Savet: Direktna rezervacija tipično rezultira 3 puta više rezervacija u poređenju s oglasima koji zahtevaju zahtev."
  you_earn: Vi zarađujete (po noći)
  after_fee: Nakon {percentage}% naknade za domaćina
  service_fee_tip: Ova naknada nam pomaže da upravljamo platformom i pružimo korisničku podršku 24/7 za vaše putovanje.
hr:
  heading: Sada postavite svoju cijenu
  subtitle: Možete je promijeniti u bilo koje vrijeme.
  base_price_per_night: osnovna cijena po noći
  guest_price_label: Cijena za gosta prije poreza (radni dan)
  booking_type_heading: Vrsta rezervacije
  booking_type_desc: Odaberite kako želite primati rezervacije
  booking_type_tip: "Savjet: Direktna rezervacija tipično rezultira 3 puta više rezervacija u usporedbi s oglasima koji zahtijevaju zahtjev."
  you_earn: Vi zarađujete (po noći)
  after_fee: Nakon {percentage}% naknade za domaćina
  service_fee_tip: Ova naknada nam pomaže upravljati platformom i pružiti korisničku podršku 24/7 za vaše putovanje.
mk:
  heading: Сега поставете ја вашата цена
  subtitle: Можете да ја промените во секое време.
  base_price_per_night: основна цена по ноќ
  guest_price_label: Цена за гостин пред даноци (работен ден)
  booking_type_heading: Вид на резервација
  booking_type_desc: Одберете како сакате да примате резервации
  booking_type_tip: "Совет: Директната резервација обично резултира со 3 пати повеќе резервации во споредба со огласите кои бараат барање."
  you_earn: Вие заработувате (по ноќ)
  after_fee: По {percentage}% провизија за домаќин
  service_fee_tip: Оваа провизија ни помага да ја водиме платформата и да обезбедиме поддршка 24/7 за вашето патување.
sl:
  heading: Zdaj nastavite svojo ceno
  subtitle: Spremenite jo lahko kadarkoli.
  base_price_per_night: osnovna cena na noč
  guest_price_label: Cena za gosta pred davki (delovni dan)
  booking_type_heading: Vrsta rezervacije
  booking_type_desc: Izberite, kako želite prejemati rezervacije
  booking_type_tip: "Nasvet: Takojšnja rezervacija tipično prinese 3-krat več rezervacij v primerjavi z oglasi, ki zahtevajo zahtevek."
  you_earn: Vi zaslužite (na noč)
  after_fee: Po {percentage}% proviziji za gostitelja
  service_fee_tip: Ta provizija nam pomaga upravljati platformo in zagotavljati podporo strankam 24/7 za vaše potovanje.
</i18n>
