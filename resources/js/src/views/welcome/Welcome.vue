<template>
    <search-wrapper>
        <template v-slot:content>
            <!-- Loading skeleton -->
            <div v-if="loading" class="max-w-7xl mx-auto px-4 pt-8 pb-6">
                <div class="flex gap-3 overflow-x-auto pb-2">
                    <div
                        v-for="n in 6"
                        :key="n"
                        class="flex-shrink-0 w-28 sm:w-32 animate-pulse"
                    >
                        <div class="aspect-square rounded-2xl bg-gray-200 dark:bg-gray-700 mb-2"></div>
                        <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-3/4 mx-auto"></div>
                    </div>
                </div>
            </div>

            <!-- Dynamic sections -->
            <home-section-component
                v-for="section in sections"
                :key="section.id"
                :section="section"
            />
        </template>
    </search-wrapper>
</template>

<script>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { mapGetters, mapActions } from 'vuex';
import HomeSectionComponent from '@/src/views/welcome/components/HomeSectionComponent.vue';
import { useSeoHead } from '@/src/composables/useSeoHead';

export default {
    components: {
        HomeSectionComponent,
    },
    setup() {
        const { t } = useI18n();

        useSeoHead({
            title: computed(() => t('seo.title')),
            description: computed(() => t('seo.description')),
        });
    },
    computed: {
        ...mapGetters('home', ['sections', 'loading']),
    },
    mounted() {
        this.fetchHomeSections();
    },
    methods: {
        ...mapActions('home', ['fetchHomeSections']),
    },
};
</script>

<i18n lang="yaml">
en:
  seo:
    title: Find accommodation in Serbia
    description: Acomody – short-term rentals of apartments and rooms in Belgrade, Novi Sad, Niš and across Serbia. Find the perfect place at the best price.
sr:
  seo:
    title: Pronađite smeštaj u Srbiji
    description: Acomody – platforma za kratkoročni najam apartmana i soba u Beogradu, Novom Sadu, Nišu i celoj Srbiji. Pronađite idealan smeštaj po najboljoj ceni.
hr:
  seo:
    title: Pronađite smještaj u Srbiji
    description: Acomody – platforma za kratkoročni najam apartmana i soba u Beogradu, Novom Sadu, Nišu i cijeloj Srbiji. Pronađite idealan smještaj po najboljoj cijeni.
mk:
  seo:
    title: Најдете сместување во Србија
    description: Acomody – платформа за краткорочен закуп на станови и соби во Белград, Нови Сад, Ниш и низ цела Србија. Најдете идеален сместај по најдобра цена.
sl:
  seo:
    title: Najdite nastanitev v Srbiji
    description: Acomody – platforma za kratkoročni najem apartmajev in sob v Beogradu, Novem Sadu, Nišu in po vsej Srbiji. Najdite idealno nastanitev po najboljši ceni.
</i18n>
