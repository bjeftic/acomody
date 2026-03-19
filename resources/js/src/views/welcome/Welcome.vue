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
import { mapGetters, mapActions } from 'vuex';
import HomeSectionComponent from '@/src/views/welcome/components/HomeSectionComponent.vue';

export default {
    components: {
        HomeSectionComponent,
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
