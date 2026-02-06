<template>
    <div class="pb-6 border-b border-gray-200 dark:border-gray-800">
        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">
            Amenities
        </h3>

        <!-- Popular Amenities -->
        <div class="space-y-3 mb-4">
            <label
                v-for="amenity in popularAmenities"
                :key="amenity.id"
                class="flex items-center cursor-pointer group"
            >
                <input
                    type="checkbox"
                    :checked="isSelected(amenity.id)"
                    @change="toggleAmenity(amenity.id)"
                    class="w-5 h-5 text-blue-600 border-gray-300 dark:border-gray-700 rounded focus:ring-blue-500 dark:focus:ring-blue-600"
                />
                <div class="ml-3 flex items-center space-x-2">
                    <span class="text-lg">{{ amenity.icon }}</span>
                    <span class="text-sm text-gray-900 dark:text-white group-hover:text-gray-700 dark:group-hover:text-gray-300">
                        {{ amenity.name }}
                    </span>
                </div>
            </label>
        </div>

        <!-- Show All Button -->
        <button
            @click="showAllAmenities = !showAllAmenities"
            class="text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white underline"
        >
            {{ showAllAmenities ? 'Show less' : `Show all ${totalAmenitiesCount} amenities` }}
        </button>

        <!-- All Amenities Modal/Expanded -->
        <div v-if="showAllAmenities" class="mt-4 space-y-4">
            <div
                v-for="(amenities, category) in allAmenities"
                :key="category"
                class="pb-4 border-b border-gray-200 dark:border-gray-700 last:border-0"
            >
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 capitalize">
                    {{ category.replace('_', ' ') }}
                </h4>
                <div class="space-y-2">
                    <label
                        v-for="amenity in amenities"
                        :key="amenity.id"
                        class="flex items-center cursor-pointer group"
                    >
                        <input
                            type="checkbox"
                            :checked="isSelected(amenity.id)"
                            @change="toggleAmenity(amenity.id)"
                            class="w-5 h-5 text-blue-600 border-gray-300 dark:border-gray-700 rounded focus:ring-blue-500"
                        />
                        <div class="ml-3 flex items-center space-x-2">
                            <span class="text-lg">{{ amenity.icon }}</span>
                            <span class="text-sm text-gray-900 dark:text-white">
                                {{ amenity.name }}
                            </span>
                        </div>
                    </label>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { filtersConfig } from '../../config/filtersConfig';

export default {
    name: 'AmenitiesFilter',
    props: {
        selectedAmenities: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            showAllAmenities: false,
            allAmenities: filtersConfig.amenities,
        };
    },
    computed: {
        popularAmenities() {
            const popular = [];
            Object.values(this.allAmenities).forEach(category => {
                popular.push(...category.filter(a => a.popular));
            });
            return popular;
        },
        totalAmenitiesCount() {
            let count = 0;
            Object.values(this.allAmenities).forEach(category => {
                count += category.length;
            });
            return count;
        },
    },
    methods: {
        isSelected(amenityId) {
            return this.selectedAmenities.includes(amenityId);
        },
        toggleAmenity(amenityId) {
            const amenities = [...this.selectedAmenities];
            const index = amenities.indexOf(amenityId);

            if (index > -1) {
                amenities.splice(index, 1);
            } else {
                amenities.push(amenityId);
            }

            this.$emit('update:amenities', amenities);
        },
    },
};
</script>
