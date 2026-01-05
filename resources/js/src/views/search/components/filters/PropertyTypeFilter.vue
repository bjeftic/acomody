<template>
    <div class="pb-6 border-b border-gray-200 dark:border-gray-800">
        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">
            Property type
        </h3>
        <div class="grid grid-cols-2 gap-3">
            <button
                v-for="type in propertyTypes"
                :key="type.id"
                @click="toggleType(type.id)"
                :class="[
                    'p-4 border-2 rounded-xl text-left transition-all',
                    isSelected(type.id)
                        ? 'border-gray-900 dark:border-white bg-gray-50 dark:bg-gray-800'
                        : 'border-gray-200 dark:border-gray-700 hover:border-gray-900 dark:hover:border-white'
                ]"
            >
                <div class="text-2xl mb-2">{{ type.icon }}</div>
                <div class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ type.name }}
                </div>
            </button>
        </div>
    </div>
</template>

<script>
import { filtersConfig } from '../../config/filtersConfig';

export default {
    name: 'PropertyTypeFilter',
    props: {
        selectedTypes: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            propertyTypes: filtersConfig.propertyTypes,
        };
    },
    methods: {
        isSelected(typeId) {
            return this.selectedTypes.includes(typeId);
        },
        toggleType(typeId) {
            const types = [...this.selectedTypes];
            const index = types.indexOf(typeId);

            if (index > -1) {
                types.splice(index, 1);
            } else {
                types.push(typeId);
            }

            this.$emit('update:types', types);
        },
    },
};
</script>
