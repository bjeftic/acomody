<template>
    <div>
        <h1 class="text-3xl font-semibold text-gray-900 dark:text-white mb-2">
            Tell guests what your place has to offer
        </h1>
        <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">
            You can add more amenities after you publish your listing.
        </p>

        <hr />

        <div class="space-y-8 mx-auto overflow-auto h-[60vh] py-4 pr-4">
            <template v-for="amenityCategory in amenityCategories" :key="amenityCategory.key">
                <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">
                    {{ amenityCategory.title }}
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <amenity-checkbox
                        v-for="amenity in amenitiesSorted[amenityCategory.key]"
                        :key="amenity.id"
                        :amenity="amenity"
                        :selected="formData.amenities.includes(amenity.id)"
                        @toggle="toggleAmenity(amenity.id)"
                    />
                </div>
            </template>
        </div>
    </div>
</template>

<script>
import { mapState } from "vuex";
import AmenityCheckbox from "@/src/views/hosting/createAccommodation/components/AmenityCheckbox.vue";

export default {
    name: "Step5Amenities",
    components: {
        AmenityCheckbox,
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
    computed: {
        ...mapState("hosting/createAccommodation", ["amenities"]),
        amenitiesSorted() {
            const sorted = {};

            this.amenities.forEach((amenity) => {
                const categoryKey = amenity.category.replace(/[-_]/g, "");

                if (!sorted[categoryKey]) {
                    sorted[categoryKey] = [];
                }

                sorted[categoryKey].push(amenity);
            });

            return sorted;
        },
        amenityCategories() {
            const categories = new Set();

            this.amenities.forEach((amenity) => {
                categories.add(amenity.category);
            });

            return Array.from(categories).map(category => ({
                title: category
                    .split('-')
                    .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                    .join(' '),
                key: category.replace(/[-_]/g, "")
            }));
        }
    },
    methods: {
        toggleAmenity(amenityId) {
            const amenities = [...this.formData.amenities];
            const index = amenities.indexOf(amenityId);

            if (index > -1) {
                amenities.splice(index, 1);
            } else {
                amenities.push(amenityId);
            }

            this.$emit("update:form-data", {
                ...this.formData,
                amenities,
            });
        },
    },
};
</script>
