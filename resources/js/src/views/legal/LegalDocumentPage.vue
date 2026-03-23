<template>
    <base-wrapper>
        <template v-slot:content>
            <div class="max-w-4xl mx-auto py-8 px-4">

                <!-- Loading -->
                <div v-if="loading" class="space-y-4 animate-pulse">
                    <div class="h-8 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/4 mb-8"></div>
                    <div v-for="i in 5" :key="i" class="space-y-2">
                        <div class="h-5 bg-gray-200 dark:bg-gray-700 rounded w-1/3"></div>
                        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded"></div>
                        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-5/6"></div>
                    </div>
                </div>

                <!-- Error -->
                <div v-else-if="error" class="text-center py-24">
                    <p class="text-gray-500 dark:text-gray-400 text-lg">{{ error }}</p>
                </div>

                <!-- Content -->
                <div v-else-if="document">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                        {{ title }}
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-10">
                        Version {{ document.version }} &mdash; Last updated {{ formattedDate }}
                    </p>

                    <div class="prose prose-gray dark:prose-invert max-w-none">
                        <template v-for="(section, index) in document.sections" :key="index">
                            <h2
                                v-if="section.section_type === 'heading'"
                                class="text-xl font-bold text-gray-900 dark:text-white mt-10 mb-4"
                            >
                                {{ sectionContent(section) }}
                            </h2>
                            <h3
                                v-else-if="section.section_type === 'subheading'"
                                class="text-lg font-semibold text-gray-800 dark:text-gray-200 mt-6 mb-3"
                            >
                                {{ sectionContent(section) }}
                            </h3>
                            <p
                                v-else
                                class="text-gray-700 dark:text-gray-300 leading-relaxed mb-4 whitespace-pre-wrap"
                            >
                                {{ sectionContent(section) }}
                            </p>
                        </template>
                    </div>
                </div>

            </div>
        </template>
    </base-wrapper>
</template>

<script>
import apiClient from '@/services/apiClient.js';

export default {
    name: 'LegalDocumentPage',

    props: {
        documentType: {
            type: String,
            required: true,
        },
    },

    data() {
        return {
            document: null,
            loading: true,
            error: null,
        };
    },

    computed: {
        locale() {
            return this.$store?.getters?.locale ?? 'en';
        },

        title() {
            if (!this.document?.title) {
                return '';
            }
            return this.document.title[this.locale]
                ?? this.document.title['en']
                ?? '';
        },

        formattedDate() {
            if (!this.document?.published_at) {
                return '';
            }
            return new Date(this.document.published_at).toLocaleDateString('en-GB', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
            });
        },
    },

    async created() {
        await this.fetchDocument();
    },

    methods: {
        async fetchDocument() {
            this.loading = true;
            this.error = null;

            try {
                const response = await apiClient.public.legal[this.documentType].get();
                this.document = response.data;
            } catch {
                this.error = 'This document is not available yet.';
            } finally {
                this.loading = false;
            }
        },

        sectionContent(section) {
            if (!section?.content) {
                return '';
            }
            return section.content[this.locale]
                ?? section.content['en']
                ?? '';
        },
    },
};
</script>
