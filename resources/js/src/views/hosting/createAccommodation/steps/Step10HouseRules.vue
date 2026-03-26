<template>
    <div>
        <h1 class="text-3xl font-semibold text-gray-900 dark:text-white mb-2">
            {{ $t('heading') }}
        </h1>
        <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">
            {{ $t('subtitle') }}
        </p>

        <hr />

        <div class="mx-auto py-4 pr-4 overflow-auto h-[60vh] space-y-8">
            <!-- Check-in/Check-out Times -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    {{ $t('checkin_section') }}
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Check-in Time -->
                    <time-range-selector
                        :label="$t('checkin_label')"
                        :time-slots="config.timeSlots"
                        :show-from="true"
                        :show-until="true"
                        :from-value="localHouseRules.checkInFrom"
                        :until-value="localHouseRules.checkInUntil"
                        @update:from="updateCheckInFrom"
                        @update:until="updateCheckInUntil"
                    />

                    <!-- Check-out Time -->
                    <time-range-selector
                        :label="$t('checkout_label')"
                        :time-slots="config.timeSlots"
                        :single-value="localHouseRules.checkOutUntil"
                        @update:value="updateCheckOutUntil"
                    />
                </div>
            </div>

            <!-- Quiet Hours -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    {{ $t('quiet_hours_section') }}
                </h3>
                <div
                    class="p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl"
                >
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h4
                                class="text-base font-medium text-gray-900 dark:text-white mb-1"
                            >
                                {{ $t('quiet_hours_title') }}
                            </h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $t('quiet_hours_desc') }}
                            </p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer ml-4">
                            <input
                                v-model="localHouseRules.hasQuietHours"
                                type="checkbox"
                                class="sr-only peer"
                                @change="emitUpdate"
                            />
                            <div
                                class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-200 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"
                            ></div>
                        </label>
                    </div>

                    <div v-if="localHouseRules.hasQuietHours" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs text-gray-500 dark:text-gray-400 mb-1 block">
                                {{ $t('from') }}
                            </label>
                            <select
                                v-model="localHouseRules.quietHoursFrom"
                                @change="emitUpdate"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-900 dark:text-white text-sm"
                            >
                                <option v-for="time in config.timeSlots" :key="time" :value="time">
                                    {{ time }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 dark:text-gray-400 mb-1 block">
                                {{ $t('until') }}
                            </label>
                            <select
                                v-model="localHouseRules.quietHoursUntil"
                                @change="emitUpdate"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-900 dark:text-white text-sm"
                            >
                                <option v-for="time in config.timeSlots" :key="time" :value="time">
                                    {{ time }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cancellation Policy -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    {{ $t('cancellation_section') }}
                </h3>
                <div class="space-y-3">
                    <cancellation-policy-card
                        v-for="policy in cancellationPolicies"
                        :key="policy.id"
                        :policy="policy"
                        :selected="localHouseRules.cancellationPolicy === policy.id"
                        @select="updateCancellationPolicy"
                    />
                </div>
            </div>

            <!-- Payment Policy -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">
                    {{ $t('payment_policy_section') }}
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    {{ $t('payment_policy_desc') }}
                </p>
                <div class="space-y-3">
                    <div
                        v-for="policy in paymentPolicies"
                        :key="policy.id"
                        @click="updatePaymentPolicy(policy.id)"
                        :class="[
                            'p-4 rounded-xl border cursor-pointer transition-colors',
                            localHouseRules.paymentPolicy === policy.id
                                ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20'
                                : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-gray-300 dark:hover:border-gray-600',
                        ]"
                    >
                        <div class="flex items-start gap-3">
                            <span class="text-2xl">{{ policy.icon }}</span>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ policy.name }}
                                    </h4>
                                    <div
                                        v-if="localHouseRules.paymentPolicy === policy.id"
                                        class="w-5 h-5 rounded-full bg-primary-500 flex items-center justify-center flex-shrink-0"
                                    >
                                        <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </div>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-0.5">
                                    {{ policy.description }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info Box -->
            <div
                class="p-6 bg-primary-50 dark:bg-primary-900/20 rounded-xl border border-primary-200 dark:border-primary-800"
            >
                <h4
                    class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center"
                >
                    <svg
                        class="w-5 h-5 mr-2 text-primary-500"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    {{ $t('good_to_know') }}
                </h4>
                <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                    <li class="flex items-start">
                        <svg
                            class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0 text-primary-500"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        <span>{{ $t('tip1') }}</span>
                    </li>
                    <li class="flex items-start">
                        <svg
                            class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0 text-primary-500"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        <span>{{ $t('tip2') }}</span>
                    </li>
                    <li class="flex items-start">
                        <svg
                            class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0 text-primary-500"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        <span>{{ $t('tip3') }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script>
import { houseRulesConfig } from "./houseRulesConfig";
import RuleToggle from "@/src/views/hosting/createAccommodation/components/RuleToggle.vue";
import TimeRangeSelector from "@/src/views/hosting/createAccommodation/components/TimeRangeSelector.vue";
import CancellationPolicyCard from "@/src/views/hosting/createAccommodation/components/CancellationPolicyCard.vue";

export default {
    name: "Step10HouseRules",
    components: {
        RuleToggle,
        TimeRangeSelector,
        CancellationPolicyCard,
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
            config: houseRulesConfig,
            localHouseRules: { ...this.formData.houseRules },
        };
    },
    computed: {
        cancellationPolicies() {
            return this.config.cancellationPolicies.map((policy) => {
                const key = policy.id.replace('-', '');
                return {
                    ...policy,
                    name: this.$t(`policy_${key}_name`),
                    description: this.$t(`policy_${key}_desc`),
                    details: this.$tm(`policy_${key}_details`).map((d) => this.$rt(d)),
                };
            });
        },
        paymentPolicies() {
            return this.config.paymentPolicies.map((policy) => ({
                ...policy,
                name: this.$t(`payment_${policy.id}_name`),
                description: this.$t(`payment_${policy.id}_desc`),
            }));
        },
    },
    watch: {
        "formData.houseRules": {
            deep: true,
            handler(newRules) {
                this.localHouseRules = { ...newRules };
            },
        },

        "localHouseRules.hasQuietHours"(enabled) {
            if (enabled && !this.localHouseRules.quietHoursFrom) {
                this.localHouseRules.quietHoursFrom = this.config.defaults.quietHoursFrom;
                this.localHouseRules.quietHoursUntil = this.config.defaults.quietHoursUntil;
                this.emitUpdate();
            }
        },
    },
    methods: {
        updateRule(ruleId, value) {
            this.localHouseRules[ruleId] = value;
            this.emitUpdate();
        },

        updateCheckInFrom(value) {
            this.localHouseRules.checkInFrom = value;

            // Ensure check-in 'until' is after 'from'
            const fromIndex = this.config.timeSlots.indexOf(value);
            const untilIndex = this.config.timeSlots.indexOf(this.localHouseRules.checkInUntil);

            if (untilIndex <= fromIndex) {
                const newUntilIndex = Math.min(fromIndex + 2, this.config.timeSlots.length - 1);
                this.localHouseRules.checkInUntil = this.config.timeSlots[newUntilIndex];
            }

            this.emitUpdate();
        },

        updateCheckInUntil(value) {
            this.localHouseRules.checkInUntil = value;
            this.emitUpdate();
        },

        updateCheckOutUntil(value) {
            this.localHouseRules.checkOutUntil = value;
            this.emitUpdate();
        },

        updateCancellationPolicy(policyId) {
            this.localHouseRules.cancellationPolicy = policyId;
            this.emitUpdate();
        },

        updatePaymentPolicy(policyId) {
            this.localHouseRules.paymentPolicy = policyId;
            this.emitUpdate();
        },

        emitUpdate() {
            this.$emit("update:form-data", {
                ...this.formData,
                houseRules: { ...this.localHouseRules },
            });
        },
    },
};
</script>

<i18n lang="yaml">
en:
  heading: Set house rules for your guests
  subtitle: Guests must agree to your house rules before they book.
  payment_policy_section: Payment policy
  payment_policy_desc: Choose how and when guests will pay for their stay.
  payment_on_site_name: Pay on arrival
  payment_on_site_desc: Guest pays at the property on arrival (cash or card).
  payment_immediate_name: Pay now
  payment_immediate_desc: Guest pays the full amount immediately upon booking.
  payment_ten_days_before_name: Pay 10 days before
  payment_ten_days_before_desc: Guest pays the full amount 10 days before check-in.
  payment_split_name: Split payment
  payment_split_desc: Guest pays 50% now and 50% on arrival.
  checkin_section: Check-in and check-out
  checkin_label: Check-in time
  checkout_label: Check-out time
  quiet_hours_section: Quiet hours (optional)
  quiet_hours_title: Set quiet hours
  quiet_hours_desc: Specify when guests should keep noise to a minimum
  from: From
  until: Until
  cancellation_section: Cancellation policy
  good_to_know: Good to know
  tip1: Clear house rules help set expectations and reduce misunderstandings
  tip2: Flexible cancellation policies often attract more bookings
  tip3: You can update your rules anytime after publishing
  policy_flexible_name: Flexible
  policy_flexible_desc: Full refund 1 day prior to arrival.
  policy_flexible_details:
    - Guests can cancel up to 24 hours before check-in for a full refund
    - If they cancel less than 24 hours before check-in, the first night is non-refundable
    - Service fees are refunded when cancellation happens before check-in
  policy_moderate_name: Moderate
  policy_moderate_desc: Full refund 5 days prior to arrival.
  policy_moderate_details:
    - Guests can cancel up to 5 days before check-in for a full refund
    - If they cancel less than 5 days before check-in, the first night is non-refundable
    - "50% refund for cancellations made 5\u201330 days before check-in"
  policy_firm_name: Firm
  policy_firm_desc: "50% refund up until 30 days prior to arrival. No refund after that."
  policy_firm_details:
    - Guests can cancel up to 30 days before check-in for a 50% refund
    - No refund for cancellations made less than 30 days before check-in
  policy_strict_name: Strict
  policy_strict_desc: ""
  policy_strict_details:
    - Guests can cancel up to 7 days before check-in for a 50% refund
    - No refund for cancellations made less than 7 days before check-in
  policy_nonrefundable_name: Non-Refundable
  policy_nonrefundable_desc: No refunds for any cancellations.
  policy_nonrefundable_details:
    - No refunds for cancellations at any time
sr:
  heading: Postavite kućni red za vaše goste
  subtitle: Gosti moraju prihvatiti vaš kućni red pre rezervacije.
  payment_policy_section: Politika plaćanja
  payment_policy_desc: Odaberite kako i kada će gosti platiti za boravak.
  payment_on_site_name: Plati pri dolasku
  payment_on_site_desc: Gost plaća u objektu pri dolasku (gotovinom ili karticom).
  payment_immediate_name: Plati odmah
  payment_immediate_desc: Gost plaća celokupan iznos odmah pri rezervaciji.
  payment_ten_days_before_name: Plati 10 dana pre dolaska
  payment_ten_days_before_desc: Gost plaća celokupan iznos 10 dana pre dolaska.
  payment_split_name: Podeljeno plaćanje
  payment_split_desc: Gost plaća 50% odmah i 50% pri dolasku.
  checkin_section: Prijava i odjava
  checkin_label: Vreme prijave
  checkout_label: Vreme odjave
  quiet_hours_section: Sati tišine (opciono)
  quiet_hours_title: Postavite sate tišine
  quiet_hours_desc: Navedite kada gosti treba da smanje buku na minimum
  from: Od
  until: Do
  cancellation_section: Politika otkazivanja
  good_to_know: Dobro je znati
  tip1: Jasna pravila pomažu postavljanju očekivanja i smanjuju nesporazume
  tip2: Fleksibilne politike otkazivanja često privlače više rezervacija
  tip3: Možete ažurirati svoja pravila u bilo koje vreme nakon objavljivanja
  policy_flexible_name: Fleksibilna
  policy_flexible_desc: Puni povrat 1 dan pre dolaska.
  policy_flexible_details:
    - Gosti mogu otkazati do 24 sata pre prijave za puni povrat
    - Ako otkazuju manje od 24 sata pre prijave, prva noć nije refundabilna
    - Naknade za usluge se vraćaju kada otkazivanje nastane pre prijave
  policy_moderate_name: Umerena
  policy_moderate_desc: Puni povrat 5 dana pre dolaska.
  policy_moderate_details:
    - Gosti mogu otkazati do 5 dana pre prijave za puni povrat
    - Ako otkazuju manje od 5 dana pre prijave, prva noć nije refundabilna
    - "50% povrat za otkazivanja 5\u201330 dana pre prijave"
  policy_firm_name: Čvrsta
  policy_firm_desc: "50% povrat do 30 dana pre dolaska. Bez povrata nakon toga."
  policy_firm_details:
    - Gosti mogu otkazati do 30 dana pre prijave za 50% povrat
    - Bez povrata za otkazivanja manje od 30 dana pre prijave
  policy_strict_name: Stroga
  policy_strict_desc: ""
  policy_strict_details:
    - Gosti mogu otkazati do 7 dana pre prijave za 50% povrat
    - Bez povrata za otkazivanja manje od 7 dana pre prijave
  policy_nonrefundable_name: Bez povrata
  policy_nonrefundable_desc: Nema povrata za otkazivanja.
  policy_nonrefundable_details:
    - Nema povrata za otkazivanja u bilo koje vreme
hr:
  heading: Postavite kućni red za vaše goste
  subtitle: Gosti moraju prihvatiti vaš kućni red prije rezervacije.
  payment_policy_section: Politika plaćanja
  payment_policy_desc: Odaberite kako i kada će gosti platiti za boravak.
  payment_on_site_name: Plati pri dolasku
  payment_on_site_desc: Gost plaća u objektu pri dolasku (gotovinom ili karticom).
  payment_immediate_name: Plati odmah
  payment_immediate_desc: Gost plaća cjelokupni iznos odmah pri rezervaciji.
  payment_ten_days_before_name: Plati 10 dana prije dolaska
  payment_ten_days_before_desc: Gost plaća cjelokupni iznos 10 dana prije dolaska.
  payment_split_name: Podijeljeno plaćanje
  payment_split_desc: Gost plaća 50% odmah i 50% pri dolasku.
  checkin_section: Prijava i odjava
  checkin_label: Vrijeme prijave
  checkout_label: Vrijeme odjave
  quiet_hours_section: Sati tišine (opcionalno)
  quiet_hours_title: Postavite sate tišine
  quiet_hours_desc: Navedite kada gosti trebaju smanjiti buku na minimum
  from: Od
  until: Do
  cancellation_section: Politika otkazivanja
  good_to_know: Dobro je znati
  tip1: Jasna pravila pomažu postavljanju očekivanja i smanjuju nesporazume
  tip2: Fleksibilne politike otkazivanja često privlače više rezervacija
  tip3: Možete ažurirati svoja pravila u bilo koje vrijeme nakon objavljivanja
  policy_flexible_name: Fleksibilna
  policy_flexible_desc: Puni povrat 1 dan prije dolaska.
  policy_flexible_details:
    - Gosti mogu otkazati do 24 sata prije prijave za puni povrat
    - Ako otkazuju manje od 24 sata prije prijave, prva noć nije refundabilna
    - Naknade za usluge vraćaju se kada otkazivanje nastane prije prijave
  policy_moderate_name: Umjerena
  policy_moderate_desc: Puni povrat 5 dana prije dolaska.
  policy_moderate_details:
    - Gosti mogu otkazati do 5 dana prije prijave za puni povrat
    - Ako otkazuju manje od 5 dana prije prijave, prva noć nije refundabilna
    - "50% povrat za otkazivanja 5\u201330 dana prije prijave"
  policy_firm_name: Čvrsta
  policy_firm_desc: "50% povrat do 30 dana prije dolaska. Bez povrata nakon toga."
  policy_firm_details:
    - Gosti mogu otkazati do 30 dana prije prijave za 50% povrat
    - Bez povrata za otkazivanja manje od 30 dana prije prijave
  policy_strict_name: Stroga
  policy_strict_desc: ""
  policy_strict_details:
    - Gosti mogu otkazati do 7 dana prije prijave za 50% povrat
    - Bez povrata za otkazivanja manje od 7 dana prije prijave
  policy_nonrefundable_name: Bez povrata
  policy_nonrefundable_desc: Nema povrata za otkazivanja.
  policy_nonrefundable_details:
    - Nema povrata za otkazivanja u bilo koje vrijeme
mk:
  heading: Поставете правила за куќата за вашите гости
  subtitle: Гостите мора да се согласат со вашите правила пред резервацијата.
  payment_policy_section: Политика на плаќање
  payment_policy_desc: Одберете како и кога гостите ќе платат за престојот.
  payment_on_site_name: Плати при доаѓање
  payment_on_site_desc: Гостинот плаќа во објектот при доаѓање (готовина или картица).
  payment_immediate_name: Плати сега
  payment_immediate_desc: Гостинот го плаќа целиот износ веднаш при резервација.
  payment_ten_days_before_name: Плати 10 дена пред доаѓање
  payment_ten_days_before_desc: Гостинот го плаќа целиот износ 10 дена пред пристигнување.
  payment_split_name: Поделено плаќање
  payment_split_desc: Гостинот плаќа 50% сега и 50% при доаѓање.
  checkin_section: Пријавување и одјавување
  checkin_label: Време на пријавување
  checkout_label: Време на одјавување
  quiet_hours_section: Часови на тишина (опционално)
  quiet_hours_title: Поставете часови на тишина
  quiet_hours_desc: Наведете кога гостите треба да ја намалат бучавата на минимум
  from: Од
  until: До
  cancellation_section: Политика за откажување
  good_to_know: Добро е да знаете
  tip1: Јасните правила помагаат да се постават очекувања и да се намалат недоразбирањата
  tip2: Флексибилните политики за откажување честопати привлекуваат повеќе резервации
  tip3: Можете да ги ажурирате правилата во секое време по објавувањето
  policy_flexible_name: Флексибилна
  policy_flexible_desc: Целосен поврат 1 ден пред пристигнување.
  policy_flexible_details:
    - Гостите можат да откажат до 24 часа пред пријавување за целосен поврат
    - Ако откажуваат помалку од 24 часа пред пријавување, првата ноќ не се враќа
    - Надоместоците за услуги се враќаат кога откажувањето настанува пред пријавувањето
  policy_moderate_name: Умерена
  policy_moderate_desc: Целосен поврат 5 дена пред пристигнување.
  policy_moderate_details:
    - Гостите можат да откажат до 5 дена пред пријавување за целосен поврат
    - Ако откажуваат помалку од 5 дена пред пријавување, првата ноќ не се враќа
    - "50% поврат за откажувања 5\u201330 дена пред пријавување"
  policy_firm_name: Цврста
  policy_firm_desc: "50% поврат до 30 дена пред пристигнување. Без поврат после тоа."
  policy_firm_details:
    - Гостите можат да откажат до 30 дена пред пријавување за 50% поврат
    - Без поврат за откажувања помалку од 30 дена пред пријавување
  policy_strict_name: Строга
  policy_strict_desc: ""
  policy_strict_details:
    - Гостите можат да откажат до 7 дена пред пријавување за 50% поврат
    - Без поврат за откажувања помалку од 7 дена пред пријавување
  policy_nonrefundable_name: Без поврат
  policy_nonrefundable_desc: Нема поврат за откажувања.
  policy_nonrefundable_details:
    - Нема поврат за откажувања во било кое време
sl:
  heading: Nastavite hišna pravila za vaše goste
  subtitle: Gostje morajo pred rezervacijo sprejeti vaša hišna pravila.
  payment_policy_section: Politika plačila
  payment_policy_desc: Izberite, kako in kdaj bodo gostje plačali za bivanje.
  payment_on_site_name: Plačaj ob prihodu
  payment_on_site_desc: Gost plača v objektu ob prihodu (gotovina ali kartica).
  payment_immediate_name: Plačaj zdaj
  payment_immediate_desc: Gost plača celoten znesek takoj ob rezervaciji.
  payment_ten_days_before_name: Plačaj 10 dni pred prihodom
  payment_ten_days_before_desc: Gost plača celoten znesek 10 dni pred prihodom.
  payment_split_name: Razdeljeno plačilo
  payment_split_desc: Gost plača 50% zdaj in 50% ob prihodu.
  checkin_section: Prijava in odjava
  checkin_label: Čas prijave
  checkout_label: Čas odjave
  quiet_hours_section: Ure tišine (neobvezno)
  quiet_hours_title: Nastavite ure tišine
  quiet_hours_desc: Določite, kdaj naj gostje zmanjšajo hrup na minimum
  from: Od
  until: Do
  cancellation_section: Politika odpovedi
  good_to_know: Dobro je vedeti
  tip1: Jasna hišna pravila pomagajo postaviti pričakovanja in zmanjšati nesporazume
  tip2: Fleksibilne politike odpovedi pogosto privabijo več rezervacij
  tip3: Svoja pravila lahko kadar koli posodobite po objavi
  policy_flexible_name: Prožna
  policy_flexible_desc: Celotno vračilo 1 dan pred prihodom.
  policy_flexible_details:
    - Gostje lahko odpovejo do 24 ur pred prijavo za celotno vračilo
    - Če odpovejo manj kot 24 ur pred prijavo, prva noč ni vračljiva
    - Storitvene pristojbine se vrnejo, ko pride do odpovedi pred prijavo
  policy_moderate_name: Zmerna
  policy_moderate_desc: Celotno vračilo 5 dni pred prihodom.
  policy_moderate_details:
    - Gostje lahko odpovejo do 5 dni pred prijavo za celotno vračilo
    - Če odpovejo manj kot 5 dni pred prijavo, prva noč ni vračljiva
    - "50% vračilo za odpovedi 5\u201330 dni pred prijavo"
  policy_firm_name: Trdna
  policy_firm_desc: "50% vračilo do 30 dni pred prihodom. Brez vračila po tem."
  policy_firm_details:
    - Gostje lahko odpovejo do 30 dni pred prijavo za 50% vračilo
    - Brez vračila za odpovedi manj kot 30 dni pred prijavo
  policy_strict_name: Stroga
  policy_strict_desc: ""
  policy_strict_details:
    - Gostje lahko odpovejo do 7 dni pred prijavo za 50% vračilo
    - Brez vračila za odpovedi manj kot 7 dni pred prijavo
  policy_nonrefundable_name: Brez vračila
  policy_nonrefundable_desc: Brez vračila za odpovedi.
  policy_nonrefundable_details:
    - Brez vračila za odpovedi kadar koli
</i18n>
