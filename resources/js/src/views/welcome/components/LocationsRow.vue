<template>
    <div class="max-w-7xl mx-auto px-4 pt-8 pb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">{{ title }}</h2>

        <div class="flex gap-3 overflow-x-auto pb-2 scrollbar-hide">
            <button
                v-for="location in locations"
                :key="location.id"
                class="flex-shrink-0 w-28 sm:w-32 text-center group focus:outline-none"
                @click="navigateToLocation(location)"
            >
                <div class="relative aspect-square rounded-2xl overflow-hidden bg-gray-100 dark:bg-gray-800 mb-2 ring-2 ring-transparent">
                    <img
                        v-if="location.photo_url"
                        :src="location.photo_url"
                        :alt="localeName(location.name)"
                        class="w-full h-full object-cover"
                        loading="lazy"
                    />
                    <div
                        v-else
                        class="w-full h-full flex items-center justify-center"
                    >
                        <svg class="w-10 h-10 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <span
                        v-if="location.country_code"
                        class="absolute bottom-1.5 right-1.5 text-lg leading-none drop-shadow"
                    >{{ countryFlag(location.country_code) }}</span>
                </div>
                <span class="text-xs sm:text-sm font-medium text-gray-800 dark:text-gray-200 line-clamp-2 leading-tight">
                    {{ localeName(location.name) }}
                </span>
            </button>
        </div>
    </div>
</template>

<script>
export default {
    name: 'LocationsRow',
    props: {
        title: {
            type: String,
            required: true,
        },
        locations: {
            type: Array,
            required: true,
        },
    },
    methods: {
        localeName(nameObj) {
            if (typeof nameObj === 'string') {
                return nameObj;
            }
            const locale = this.$i18n.locale;
            return nameObj?.[locale] || nameObj?.en || Object.values(nameObj ?? {})[0] || '';
        },
        countryFlag(isoCode) {
            return isoCode
                .toUpperCase()
                .split('')
                .map(char => String.fromCodePoint(0x1F1E6 - 65 + char.charCodeAt(0)))
                .join('');
        },
        navigateToLocation(location) {
            this.$router.push({
                name: 'page-search',
                query: {
                    locationId: location.id,
                    locationName: this.localeName(location.name),
                },
            });
        },
    },
};
</script>

<style scoped>
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
</style>
