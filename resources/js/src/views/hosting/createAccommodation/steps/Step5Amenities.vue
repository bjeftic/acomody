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
            <!-- General Amenities -->
            <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">
                General Amenities
            </p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <amenity-checkbox
                    v-for="amenity in amenitiesSorted.general"
                    :key="amenity.id"
                    :amenity="amenity"
                    :selected="formData.amenities.includes(amenity.id)"
                    @toggle="toggleAmenity(amenity.id)"
                />
            </div>

            <!-- Bathroom -->
            <!-- <amenity-checkbox
                title="Bathroom"
                :amenities="amenitiesSorted.bathroom"
                :selected-amenities="formData.amenities"
                @toggle="toggleAmenity"
            /> -->

            <!-- Kitchen & Dining -->
            <!-- <amenity-checkbox
                title="Kitchen and dining"
                :amenities="amenitiesSorted.kitchen"
                :selected-amenities="formData.amenities"
                @toggle="toggleAmenity"
            /> -->

            <!-- Heating & Cooling -->
            <!-- <amenity-checkbox
                title="Heating and cooling"
                :amenities="amenitiesSorted.heatingCooling"
                :selected-amenities="formData.amenities"
                @toggle="toggleAmenity"
            /> -->

            <!-- Entertainment -->
            <!-- <amenity-checkbox
                title="Entertainment"
                :amenities="amenitiesSorted.entertainment"
                :selected-amenities="formData.amenities"
                @toggle="toggleAmenity"
            /> -->

            <!-- Internet & Office -->
            <!-- <amenity-checkbox
                title="Internet and office"
                :amenities="amenitiesSorted.internetOffice"
                :selected-amenities="formData.amenities"
                @toggle="toggleAmenity"
            /> -->

            <!-- Parking & Facilities -->
            <!-- <amenity-checkbox
                title="Parking and facilities"
                :amenities="amenitiesSorted.parkingFacilities"
                :selected-amenities="formData.amenities"
                @toggle="toggleAmenity"
            /> -->

            <!-- Outdoor -->
            <!-- <amenity-checkbox
                title="Outdoor"
                :amenities="amenitiesSorted.outdoor"
                :selected-amenities="formData.amenities"
                @toggle="toggleAmenity"
            /> -->

            <!-- Safety -->
            <!-- <amenity-checkbox
                title="Safety items"
                :amenities="amenitiesSorted.safety"
                :selected-amenities="formData.amenities"
                @toggle="toggleAmenity"
            /> -->
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
                const typeKey = amenity.type.replace(/_/g, "");

                if (!sorted[typeKey]) {
                    sorted[typeKey] = [];
                }

                sorted[typeKey].push(amenity);
            });

            return sorted;
        },
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
