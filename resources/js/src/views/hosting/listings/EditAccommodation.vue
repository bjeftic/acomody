<template>
    <div class="max-w-4xl mx-auto py-12">
        <template v-if="myListingsLoading">
            <form-skeleton />
        </template>

        <template v-else>
            <!-- Page Header -->
            <div class="mb-6">
                <h1
                    class="text-3xl font-semibold text-gray-900 dark:text-white mb-2"
                >
                    Edit listing
                </h1>
                <p class="text-lg text-gray-600 dark:text-gray-400">
                    Changes are applied immediately to your live listing.
                </p>
            </div>

            <hr class="mb-6" />

            <!-- Preview Card -->
            <div
                class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden mb-6"
            >
                <div class="relative aspect-video bg-gray-100 dark:bg-gray-900">
                    <img
                        v-if="formData.photos.length > 0"
                        :src="formData.photos[0].urls?.large"
                        alt="Cover photo"
                        class="w-full h-full object-cover"
                    />
                    <div
                        v-else
                        class="w-full h-full flex items-center justify-center text-gray-400"
                    >
                        <span>No photos uploaded</span>
                    </div>
                    <div
                        class="absolute bottom-4 right-4 px-3 py-1 bg-white dark:bg-gray-900 rounded-lg shadow-lg"
                    >
                        <span
                            class="text-sm font-medium text-gray-900 dark:text-white"
                        >
                            {{ formData.photos.length }} photos
                        </span>
                    </div>
                </div>

                <div class="p-6">
                    <h2
                        class="text-2xl font-semibold text-gray-900 dark:text-white mb-2"
                    >
                        {{ formData.title || "Untitled listing" }}
                    </h2>
                    <p
                        v-if="formData.address.city"
                        class="text-base text-gray-600 dark:text-gray-400 mb-4"
                    >
                        {{ formData.address.city }},
                        {{ formData.address.country }}
                    </p>

                    <div
                        class="flex items-center space-x-6 mb-4 pb-4 border-b border-gray-200 dark:border-gray-700"
                    >
                        <span class="text-sm text-gray-700 dark:text-gray-300">
                            {{ formData.floorPlan.guests }} guests
                        </span>
                        <span class="text-sm text-gray-700 dark:text-gray-300">
                            {{ formData.floorPlan.bedrooms }} bedrooms
                        </span>
                        <span class="text-sm text-gray-700 dark:text-gray-300">
                            {{ formData.floorPlan.bathrooms }} bathrooms
                        </span>
                    </div>

                    <div class="flex items-baseline">
                        <span
                            class="text-2xl font-bold text-gray-900 dark:text-white"
                        >
                            ${{ formData.pricing.basePrice }}
                        </span>
                        <span
                            class="text-base text-gray-600 dark:text-gray-400 ml-1"
                        >
                            / night
                        </span>
                    </div>
                </div>
            </div>

            <!-- Saved Banner -->
            <div
                v-if="savedSection"
                class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl text-sm text-green-800 dark:text-green-300"
            >
                Changes saved successfully.
            </div>

            <!-- Edit Sections -->
            <div class="space-y-4">
                <!-- Property Info (steps 1+2) -->
                <div
                    v-if="currentEditSection === 'property'"
                    class="p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl"
                >
                    <h3
                        class="text-base font-semibold text-gray-900 dark:text-white mb-4"
                    >
                        Property info
                    </h3>
                    <step1-accommodation-type
                        :form-data="formData"
                        :errors="errors"
                        @update:form-data="updateFormData"
                    />
                    <step2-occupation-type
                        :form-data="formData"
                        :errors="errors"
                        @update:form-data="updateFormData"
                    />
                    <div class="flex gap-3 mt-6">
                        <fwb-button
                            :disabled="isSaving"
                            @click="saveSection('property')"
                        >
                            Save
                        </fwb-button>
                        <fwb-button color="light" @click="cancelEdit">
                            Cancel
                        </fwb-button>
                    </div>
                </div>
                <edit-section
                    v-else
                    title="Property info"
                    @edit="startEdit('property')"
                >
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400"
                                >Type:</span
                            >
                            <span
                                class="text-gray-900 dark:text-white font-medium"
                            >
                                {{ accommodationTypeName }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400"
                                >Occupation:</span
                            >
                            <span
                                class="text-gray-900 dark:text-white font-medium"
                            >
                                {{ occupationTypeName }}
                            </span>
                        </div>
                    </div>
                </edit-section>

                <!-- Location (step 3) -->
                <div
                    v-if="currentEditSection === 'location'"
                    class="p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl"
                >
                    <h3
                        class="text-base font-semibold text-gray-900 dark:text-white mb-4"
                    >
                        Location
                    </h3>
                    <step3-address
                        :form-data="formData"
                        :errors="errors"
                        @update:form-data="updateFormData"
                    />
                    <div class="flex gap-3 mt-6">
                        <fwb-button
                            :disabled="isSaving"
                            @click="saveSection('location')"
                        >
                            Save
                        </fwb-button>
                        <fwb-button color="light" @click="cancelEdit">
                            Cancel
                        </fwb-button>
                    </div>
                </div>
                <edit-section
                    v-else
                    title="Location"
                    @edit="startEdit('location')"
                >
                    <div
                        class="space-y-1 text-sm text-gray-700 dark:text-gray-300"
                    >
                        <p>{{ formData.address.street }}</p>
                        <p>
                            {{ formData.address.city }},
                            {{ formData.address.state }}
                            {{ formData.address.zipCode }}
                        </p>
                        <p>{{ formData.address.country }}</p>
                    </div>
                </edit-section>

                <!-- Floor Plan (step 4) -->
                <div
                    v-if="currentEditSection === 'floorPlan'"
                    class="p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl"
                >
                    <h3
                        class="text-base font-semibold text-gray-900 dark:text-white mb-4"
                    >
                        Floor plan
                    </h3>
                    <step4-floor-plan
                        :form-data="formData"
                        :errors="errors"
                        @update:form-data="updateFormData"
                    />
                    <div class="flex gap-3 mt-6">
                        <fwb-button
                            :disabled="isSaving"
                            @click="saveSection('floorPlan')"
                        >
                            Save
                        </fwb-button>
                        <fwb-button color="light" @click="cancelEdit">
                            Cancel
                        </fwb-button>
                    </div>
                </div>
                <edit-section
                    v-else
                    title="Floor plan"
                    @edit="startEdit('floorPlan')"
                >
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400"
                                >Guests:</span
                            >
                            <span
                                class="text-gray-900 dark:text-white font-medium"
                            >
                                {{ formData.floorPlan.guests }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400"
                                >Bedrooms:</span
                            >
                            <span
                                class="text-gray-900 dark:text-white font-medium"
                            >
                                {{ formData.floorPlan.bedrooms }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400"
                                >Bathrooms:</span
                            >
                            <span
                                class="text-gray-900 dark:text-white font-medium"
                            >
                                {{ formData.floorPlan.bathrooms }}
                            </span>
                        </div>
                    </div>
                </edit-section>

                <!-- Amenities (step 5) -->
                <div
                    v-if="currentEditSection === 'amenities'"
                    class="p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl"
                >
                    <h3
                        class="text-base font-semibold text-gray-900 dark:text-white mb-4"
                    >
                        Amenities
                    </h3>
                    <step5-amenities
                        :form-data="formData"
                        :errors="errors"
                        @update:form-data="updateFormData"
                    />
                    <div class="flex gap-3 mt-6">
                        <fwb-button
                            :disabled="isSaving"
                            @click="saveSection('amenities')"
                        >
                            Save
                        </fwb-button>
                        <fwb-button color="light" @click="cancelEdit">
                            Cancel
                        </fwb-button>
                    </div>
                </div>
                <edit-section
                    v-else
                    title="Amenities"
                    @edit="startEdit('amenities')"
                >
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        {{ formData.amenities.length }} amenities selected
                    </p>
                </edit-section>

                <!-- Title (step 7) -->
                <div
                    v-if="currentEditSection === 'title'"
                    class="p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl"
                >
                    <h3
                        class="text-base font-semibold text-gray-900 dark:text-white mb-4"
                    >
                        Title
                    </h3>
                    <step7-title
                        :form-data="formData"
                        :errors="errors"
                        @update:form-data="updateFormData"
                    />
                    <div class="flex gap-3 mt-6">
                        <fwb-button
                            :disabled="isSaving"
                            @click="saveSection('title')"
                        >
                            Save
                        </fwb-button>
                        <fwb-button color="light" @click="cancelEdit">
                            Cancel
                        </fwb-button>
                    </div>
                </div>
                <edit-section
                    v-else
                    title="Title"
                    @edit="startEdit('title')"
                >
                    <p
                        class="text-sm text-gray-700 dark:text-gray-300 font-medium"
                    >
                        {{ formData.title || "Not set" }}
                    </p>
                </edit-section>

                <!-- Description (step 8) -->
                <div
                    v-if="currentEditSection === 'description'"
                    class="p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl"
                >
                    <h3
                        class="text-base font-semibold text-gray-900 dark:text-white mb-4"
                    >
                        Description
                    </h3>
                    <step8-description
                        :form-data="formData"
                        :errors="errors"
                        @update:form-data="updateFormData"
                    />
                    <div class="flex gap-3 mt-6">
                        <fwb-button
                            :disabled="isSaving"
                            @click="saveSection('description')"
                        >
                            Save
                        </fwb-button>
                        <fwb-button color="light" @click="cancelEdit">
                            Cancel
                        </fwb-button>
                    </div>
                </div>
                <edit-section
                    v-else
                    title="Description"
                    @edit="startEdit('description')"
                >
                    <p
                        class="text-sm text-gray-700 dark:text-gray-300 line-clamp-2"
                    >
                        {{ formData.description || "Not set" }}
                    </p>
                </edit-section>

                <!-- Pricing (step 9) -->
                <div
                    v-if="currentEditSection === 'pricing'"
                    class="p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl"
                >
                    <h3
                        class="text-base font-semibold text-gray-900 dark:text-white mb-4"
                    >
                        Pricing
                    </h3>
                    <step9-pricing
                        :form-data="formData"
                        :errors="errors"
                        @update:form-data="updateFormData"
                    />
                    <div class="flex gap-3 mt-6">
                        <fwb-button
                            :disabled="isSaving"
                            @click="saveSection('pricing')"
                        >
                            Save
                        </fwb-button>
                        <fwb-button color="light" @click="cancelEdit">
                            Cancel
                        </fwb-button>
                    </div>
                </div>
                <edit-section
                    v-else
                    title="Pricing"
                    @edit="startEdit('pricing')"
                >
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400"
                                >Base price:</span
                            >
                            <span
                                class="text-gray-900 dark:text-white font-medium"
                            >
                                ${{ formData.pricing.basePrice }} / night
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400"
                                >Booking type:</span
                            >
                            <span
                                class="text-gray-900 dark:text-white font-medium capitalize"
                            >
                                {{ formData.pricing.bookingType }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400"
                                >Min. stay:</span
                            >
                            <span
                                class="text-gray-900 dark:text-white font-medium"
                            >
                                {{ formData.pricing.minStay }} nights
                            </span>
                        </div>
                    </div>
                </edit-section>

                <!-- House Rules (step 10) -->
                <div
                    v-if="currentEditSection === 'houseRules'"
                    class="p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl"
                >
                    <h3
                        class="text-base font-semibold text-gray-900 dark:text-white mb-4"
                    >
                        House rules
                    </h3>
                    <step10-house-rules
                        :form-data="formData"
                        :errors="errors"
                        @update:form-data="updateFormData"
                    />
                    <div class="flex gap-3 mt-6">
                        <fwb-button
                            :disabled="isSaving"
                            @click="saveSection('houseRules')"
                        >
                            Save
                        </fwb-button>
                        <fwb-button color="light" @click="cancelEdit">
                            Cancel
                        </fwb-button>
                    </div>
                </div>
                <edit-section
                    v-else
                    title="House rules"
                    @edit="startEdit('houseRules')"
                >
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400"
                                >Check-in:</span
                            >
                            <span
                                class="text-gray-900 dark:text-white font-medium"
                            >
                                {{ formData.houseRules.checkInFrom }} -
                                {{ formData.houseRules.checkInUntil }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400"
                                >Check-out:</span
                            >
                            <span
                                class="text-gray-900 dark:text-white font-medium"
                            >
                                {{ formData.houseRules.checkOutUntil }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400"
                                >Cancellation:</span
                            >
                            <span
                                class="text-gray-900 dark:text-white font-medium capitalize"
                            >
                                {{ formData.houseRules.cancellationPolicy }}
                            </span>
                        </div>
                    </div>
                </edit-section>
            </div>
        </template>
    </div>
</template>

<script>
import { mapState, mapActions } from "vuex";
import EditSection from "@/src/views/hosting/createAccommodation/components/EditSection.vue";
import Step1AccommodationType from "@/src/views/hosting/createAccommodation/steps/Step1AccommodationType.vue";
import Step2OccupationType from "@/src/views/hosting/createAccommodation/steps/Step2OccupationType.vue";
import Step3Address from "@/src/views/hosting/createAccommodation/steps/Step3Address.vue";
import Step4FloorPlan from "@/src/views/hosting/createAccommodation/steps/Step4FloorPlan.vue";
import Step5Amenities from "@/src/views/hosting/createAccommodation/steps/Step5Amenities.vue";
import Step7Title from "@/src/views/hosting/createAccommodation/steps/Step7Title.vue";
import Step8Description from "@/src/views/hosting/createAccommodation/steps/Step8Description.vue";
import Step9Pricing from "@/src/views/hosting/createAccommodation/steps/Step9Pricing.vue";
import Step10HouseRules from "@/src/views/hosting/createAccommodation/steps/Step10HouseRules.vue";

export default {
    name: "EditAccommodation",
    components: {
        EditSection,
        Step1AccommodationType,
        Step2OccupationType,
        Step3Address,
        Step4FloorPlan,
        Step5Amenities,
        Step7Title,
        Step8Description,
        Step9Pricing,
        Step10HouseRules,
    },
    data() {
        return {
            currentEditSection: null,
            isSaving: false,
            savedSection: null,
            errors: {},
            formData: {
                accommodationType: null,
                accommodationOccupation: null,
                address: {
                    country: "",
                    street: "",
                    city: "",
                    state: "",
                    zipCode: "",
                },
                coordinates: {
                    latitude: null,
                    longitude: null,
                },
                floorPlan: {
                    guests: 1,
                    bedrooms: 1,
                    bathrooms: 1,
                    bedTypes: [],
                },
                amenities: [],
                photos: [],
                title: "",
                description: "",
                pricing: {
                    basePrice: 100,
                    bookingType: "instant_booking",
                    minStay: 1,
                },
                houseRules: {
                    checkInFrom: "15:00",
                    checkInUntil: "20:00",
                    checkOutUntil: "11:00",
                    hasQuietHours: false,
                    quietHoursFrom: "22:00",
                    quietHoursUntil: "08:00",
                    cancellationPolicy: "moderate",
                },
            },
        };
    },
    computed: {
        ...mapState("hosting/listings", ["accommodation", "myListingsLoading"]),
        ...mapState("hosting/createAccommodation", ["accommodationTypes", "bedTypes"]),
        accommodationId() {
            return this.$route.params.accommodationId;
        },
        accommodationTypeName() {
            if (!this.formData.accommodationType) return "";
            const type = this.accommodationTypes.find(
                (t) => t.id === this.formData.accommodationType
            );
            return type ? type.name : "";
        },
        occupationTypeName() {
            if (!this.formData.accommodationType) return "";
            const type = this.accommodationTypes.find(
                (t) => t.id === this.formData.accommodationType
            );
            const occupation = type?.available_occupations?.find(
                (o) => o.id === this.formData.accommodationOccupation
            );
            return occupation ? occupation.name : "";
        },
    },
    watch: {
        accommodation: {
            immediate: true,
            handler(newAccommodation) {
                if (newAccommodation && this.bedTypes.length) {
                    this.loadAccommodationData(newAccommodation);
                }
            },
        },
        bedTypes(newBedTypes) {
            if (newBedTypes.length && this.accommodation) {
                this.loadAccommodationData(this.accommodation);
            } else if (newBedTypes.length) {
                this.formData.floorPlan.bedTypes = newBedTypes.map(
                    (bedType) => ({
                        bed_type: bedType.value,
                        name: bedType.name,
                        description: bedType.description,
                        quantity: 0,
                    })
                );
            }
        },
    },
    methods: {
        ...mapActions("hosting/listings", [
            "loadInitialEditAccommodationData",
            "updateAccommodation",
        ]),

        startEdit(section) {
            this.currentEditSection = section;
            this.savedSection = null;
            this.errors = {};
        },

        cancelEdit() {
            this.currentEditSection = null;
            this.errors = {};
        },

        updateFormData(updates) {
            this.formData = { ...this.formData, ...updates };
        },

        async saveSection(section) {
            this.isSaving = true;
            try {
                await this.updateAccommodation({
                    accommodationId: this.accommodationId,
                    data: this.prepareData(),
                });
                this.currentEditSection = null;
                this.savedSection = section;
                setTimeout(() => {
                    this.savedSection = null;
                }, 3000);
            } catch (error) {
                if (error.response?.data?.errors) {
                    this.errors = error.response.data.errors;
                }
            } finally {
                this.isSaving = false;
            }
        },

        loadAccommodationData(accommodation) {
            this.formData = {
                accommodationType: accommodation.accommodation_type?.value ?? accommodation.accommodation_type ?? null,
                accommodationOccupation: accommodation.accommodation_occupation?.value ?? accommodation.accommodation_occupation ?? null,
                address: {
                    country: accommodation.location?.country_code || "",
                    street: accommodation.address || "",
                    city: accommodation.location?.name || "",
                    state: "",
                    zipCode: "",
                },
                coordinates: {
                    latitude: accommodation.latitude || null,
                    longitude: accommodation.longitude || null,
                },
                floorPlan: {
                    guests: accommodation.max_guests || 1,
                    bedrooms: accommodation.bedrooms || 1,
                    bathrooms: accommodation.bathrooms || 1,
                    bedTypes: this.bedTypes.map((bedType) => ({
                        bed_type: bedType.value,
                        name: bedType.name,
                        description: bedType.description,
                        quantity:
                            accommodation.beds?.find(
                                (bt) => bt.bed_type === bedType.value
                            )?.quantity ?? 0,
                    })),
                },
                amenities: accommodation.amenities?.map((a) => a.id) || [],
                photos: accommodation.photos || [],
                title: accommodation.title || "",
                description: accommodation.description || "",
                pricing: {
                    basePrice: accommodation.pricing?.base_price?.base_price || 100,
                    bookingType: accommodation.booking_type || "instant_booking",
                    minStay: accommodation.pricing?.min_quantity || 1,
                },
                houseRules: {
                    checkInFrom: accommodation.check_in_from || "15:00",
                    checkInUntil: accommodation.check_in_until || "20:00",
                    checkOutUntil: accommodation.check_out_until || "11:00",
                    hasQuietHours: !!(accommodation.quiet_hours_from),
                    quietHoursFrom: accommodation.quiet_hours_from || "22:00",
                    quietHoursUntil: accommodation.quiet_hours_until || "08:00",
                    cancellationPolicy: accommodation.cancellation_policy || "moderate",
                },
            };
        },

        prepareData() {
            return {
                accommodation_type: this.formData.accommodationType,
                accommodation_occupation: this.formData.accommodationOccupation,
                address: {
                    street: this.formData.address.street,
                },
                coordinates: {
                    latitude: this.formData.coordinates.latitude,
                    longitude: this.formData.coordinates.longitude,
                },
                floor_plan: {
                    guests: this.formData.floorPlan.guests,
                    bedrooms: this.formData.floorPlan.bedrooms,
                    bathrooms: this.formData.floorPlan.bathrooms,
                    bed_types: this.formData.floorPlan.bedTypes
                        .filter((bt) => bt.quantity > 0)
                        .map((bt) => ({
                            bed_type: bt.bed_type,
                            quantity: bt.quantity,
                        })),
                },
                amenities: this.formData.amenities,
                title: this.formData.title,
                description: this.formData.description,
                pricing: {
                    basePrice: this.formData.pricing.basePrice,
                    bookingType: this.formData.pricing.bookingType,
                    minStay: this.formData.pricing.minStay,
                },
                house_rules: {
                    checkInFrom: this.formData.houseRules.checkInFrom,
                    checkInUntil: this.formData.houseRules.checkInUntil,
                    checkOutUntil: this.formData.houseRules.checkOutUntil,
                    hasQuietHours: this.formData.houseRules.hasQuietHours,
                    quietHoursFrom: this.formData.houseRules.quietHoursFrom,
                    quietHoursUntil: this.formData.houseRules.quietHoursUntil,
                    cancellationPolicy: this.formData.houseRules.cancellationPolicy,
                },
            };
        },
    },
    async created() {
        await this.loadInitialEditAccommodationData(this.accommodationId);
    },
};
</script>
