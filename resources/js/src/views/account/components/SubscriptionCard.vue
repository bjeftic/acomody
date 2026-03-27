<template>
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ $t('title') }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    {{ $t('subtitle') }}
                </p>
            </div>
            <button
                disabled
                class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 cursor-not-allowed"
            >
                {{ $t('upgrade_btn') }}
            </button>
        </div>

        <div v-if="subscription" class="space-y-4">
            <!-- Plan badge -->
            <div class="flex flex-wrap items-center gap-3">
                <span class="px-3 py-1 rounded-full text-sm font-semibold"
                    :class="planBadgeClass">
                    {{ subscription.plan_name || $t('free') }}
                </span>
                <span v-if="subscription.is_early_host"
                    class="px-3 py-1 rounded-full text-sm font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
                    ⭐ {{ $t('early_host_badge') }}
                </span>
                <span v-if="subscription.is_commission_free"
                    class="px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                    {{ $t('no_commission_badge') }}
                </span>
            </div>

            <!-- Details grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ $t('plan_price') }}</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ !subscription.price_eur ? $t('free') : `€${(subscription.price_eur / 100).toFixed(0)}/mo` }}
                    </p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ $t('commission_label') }}</p>
                    <p class="text-lg font-semibold" :class="subscription.commission_rate === 0 ? 'text-green-600 dark:text-green-400' : 'text-gray-900 dark:text-white'">
                        {{ (subscription.commission_rate ?? 10) + '%' }}
                    </p>
                </div>

            </div>

            <!-- Early host expiry -->
            <p v-if="subscription.is_early_host && subscription.early_host_expires_at"
                class="text-sm text-amber-600 dark:text-amber-400">
                {{ $t('early_host_expires', { date: formatDate(subscription.early_host_expires_at) }) }}
            </p>

            <!-- Features -->
            <ul v-if="subscription.features?.length" class="space-y-1 pt-2">
                <li v-for="feature in subscription.features" :key="feature"
                    class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                    <svg class="w-4 h-4 text-green-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    {{ feature }}
                </li>
            </ul>
        </div>

        <div v-else class="text-sm text-gray-500 dark:text-gray-400">
            {{ $t('loading') }}
        </div>
    </div>
</template>

<script>
import { mapGetters } from 'vuex';

export default {
    name: 'SubscriptionCard',
    computed: {
        ...mapGetters('user', ['subscription']),

        planBadgeClass() {
            const code = this.subscription?.plan_code;
            if (code === 'club') return 'bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-400';
            return 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300';
        },
    },
    methods: {
        formatDate(iso) {
            return new Date(iso).toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' });
        },
    },
};
</script>

<i18n lang="yaml">
en:
  title: Subscription & Plan
  subtitle: Your current hosting plan and commission info
  upgrade_btn: Upgrade (coming soon)
  plan_price: Monthly price
  commission_label: Platform commission
  max_listings: Max listings
  unlimited: Unlimited
  free: Free
  no_commission_badge: "0% commission"
  early_host_badge: Early Host
  early_host_expires: "Early host benefits expire on {date}"
  loading: Loading subscription info...
sr:
  title: Pretplata i plan
  subtitle: Vaš trenutni plan i informacije o proviziji
  upgrade_btn: Nadogradnja (uskoro)
  plan_price: Mesečna cena
  commission_label: Provizija platforme
  max_listings: Maks. smeštaja
  unlimited: Neograničeno
  free: Besplatno
  no_commission_badge: "0% provizija"
  early_host_badge: Rani domaćin
  early_host_expires: "Benefiti ranog domaćina ističu {date}"
  loading: Učitavanje informacija o pretplati...
hr:
  title: Pretplata i plan
  subtitle: Vaš trenutni hosting plan i informacije o proviziji
  upgrade_btn: Nadogradnja (uskoro)
  plan_price: Mjesečna cijena
  commission_label: Provizija platforme
  max_listings: Maks. smještaja
  unlimited: Neograničeno
  free: Besplatno
  no_commission_badge: "0% provizija"
  early_host_badge: Rani domaćin
  early_host_expires: "Benefiti ranog domaćina istječu {date}"
  loading: Učitavanje informacija o pretplati...
mk:
  title: Претплата и план
  subtitle: Вашиот тековен план и информации за провизија
  upgrade_btn: Надградба (наскоро)
  plan_price: Месечна цена
  commission_label: Провизија на платформата
  max_listings: Макс. сместувања
  unlimited: Неограничено
  free: Бесплатно
  no_commission_badge: "0% провизија"
  early_host_badge: Ран домаќин
  early_host_expires: "Бенефитите на раниот домаќин истекуваат на {date}"
  loading: Вчитување информации за претплата...
sl:
  title: Naročnina in načrt
  subtitle: Vaš trenutni plan in informacije o proviziji
  upgrade_btn: Nadgradnja (kmalu)
  plan_price: Mesečna cena
  commission_label: Provizija platforme
  max_listings: Maks. nastanitev
  unlimited: Neomejeno
  free: Brezplačno
  no_commission_badge: "0% provizija"
  early_host_badge: Zgodnji gostitelj
  early_host_expires: "Ugodnosti zgodnjega gostitelja potečejo {date}"
  loading: Nalaganje informacij o naročnini...
</i18n>
