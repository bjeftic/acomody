<template>
    <div class="max-w-4xl mx-auto px-4 pt-6 md:py-12">
        <template v-if="myListingsLoading">
            <form-skeleton />
        </template>

        <template v-else>
            <!-- Page Header -->
            <div class="mb-6">
                <h1
                    class="text-3xl font-semibold text-gray-900 dark:text-white mb-2"
                >
                    {{ $t('heading') }}
                </h1>
                <p class="text-lg text-gray-600 dark:text-gray-400">
                    {{ $t('subtitle') }}
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
                        <span>{{ $t('no_photos') }}</span>
                    </div>
                    <div
                        class="absolute bottom-4 right-4 px-3 py-1 bg-white dark:bg-gray-900 rounded-lg shadow-lg"
                    >
                        <span
                            class="text-sm font-medium text-gray-900 dark:text-white"
                        >
                            {{ $t('photos', { count: formData.photos.length }) }}
                        </span>
                    </div>
                </div>

                <div class="p-6">
                    <h2
                        class="text-2xl font-semibold text-gray-900 dark:text-white mb-2"
                    >
                        {{ formData.title || $t('untitled') }}
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
                            {{ $t('guests', { count: formData.floorPlan.guests }) }}
                        </span>
                        <span class="text-sm text-gray-700 dark:text-gray-300">
                            {{ $t('bedrooms', { count: formData.floorPlan.bedrooms }) }}
                        </span>
                        <span class="text-sm text-gray-700 dark:text-gray-300">
                            {{ $t('bathrooms', { count: formData.floorPlan.bathrooms }) }}
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
                            {{ $t('per_night') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Saved Banner -->
            <div
                v-if="savedSection"
                class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl text-sm text-green-800 dark:text-green-300"
            >
                {{ $t('saved') }}
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
                        {{ $t('section_property') }}
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
                        <BaseButton
                            :disabled="isSaving"
                            @click="saveSection('property')"
                        >
                            {{ $t('btn_save') }}
                        </BaseButton>
                        <BaseButton variant="secondary" @click="cancelEdit">
                            {{ $t('btn_cancel') }}
                        </BaseButton>
                    </div>
                </div>
                <edit-section
                    v-else
                    :title="$t('section_property')"
                    @edit="startEdit('property')"
                >
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">{{ $t('label_type') }}</span>
                            <span
                                class="text-gray-900 dark:text-white font-medium"
                            >
                                {{ accommodationTypeName }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">{{ $t('label_occupation') }}</span>
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
                        {{ $t('section_location') }}
                    </h3>
                    <step3-address
                        :form-data="formData"
                        :errors="errors"
                        @update:form-data="updateFormData"
                    />
                    <div class="flex gap-3 mt-6">
                        <BaseButton
                            :disabled="isSaving"
                            @click="saveSection('location')"
                        >
                            {{ $t('btn_save') }}
                        </BaseButton>
                        <BaseButton variant="secondary" @click="cancelEdit">
                            {{ $t('btn_cancel') }}
                        </BaseButton>
                    </div>
                </div>
                <edit-section
                    v-else
                    :title="$t('section_location')"
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
                        {{ $t('section_floor_plan') }}
                    </h3>
                    <step4-floor-plan
                        :form-data="formData"
                        :errors="errors"
                        @update:form-data="updateFormData"
                    />
                    <div class="flex gap-3 mt-6">
                        <BaseButton
                            :disabled="isSaving"
                            @click="saveSection('floorPlan')"
                        >
                            {{ $t('btn_save') }}
                        </BaseButton>
                        <BaseButton variant="secondary" @click="cancelEdit">
                            {{ $t('btn_cancel') }}
                        </BaseButton>
                    </div>
                </div>
                <edit-section
                    v-else
                    :title="$t('section_floor_plan')"
                    @edit="startEdit('floorPlan')"
                >
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">{{ $t('label_guests') }}</span>
                            <span
                                class="text-gray-900 dark:text-white font-medium"
                            >
                                {{ formData.floorPlan.guests }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">{{ $t('label_bedrooms') }}</span>
                            <span
                                class="text-gray-900 dark:text-white font-medium"
                            >
                                {{ formData.floorPlan.bedrooms }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">{{ $t('label_bathrooms') }}</span>
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
                        {{ $t('section_amenities') }}
                    </h3>
                    <step5-amenities
                        :form-data="formData"
                        :errors="errors"
                        @update:form-data="updateFormData"
                    />
                    <div class="flex gap-3 mt-6">
                        <BaseButton
                            :disabled="isSaving"
                            @click="saveSection('amenities')"
                        >
                            {{ $t('btn_save') }}
                        </BaseButton>
                        <BaseButton variant="secondary" @click="cancelEdit">
                            {{ $t('btn_cancel') }}
                        </BaseButton>
                    </div>
                </div>
                <edit-section
                    v-else
                    :title="$t('section_amenities')"
                    @edit="startEdit('amenities')"
                >
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        {{ $t('amenities_count', { count: formData.amenities.length }) }}
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
                        {{ $t('section_title') }}
                    </h3>
                    <step7-title
                        :form-data="formData"
                        :errors="errors"
                        @update:form-data="updateFormData"
                    />
                    <div class="flex gap-3 mt-6">
                        <BaseButton
                            :disabled="isSaving"
                            @click="saveSection('title')"
                        >
                            {{ $t('btn_save') }}
                        </BaseButton>
                        <BaseButton variant="secondary" @click="cancelEdit">
                            {{ $t('btn_cancel') }}
                        </BaseButton>
                    </div>
                </div>
                <edit-section
                    v-else
                    :title="$t('section_title')"
                    @edit="startEdit('title')"
                >
                    <p
                        class="text-sm text-gray-700 dark:text-gray-300 font-medium"
                    >
                        {{ formData.title || $t('not_set') }}
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
                        {{ $t('section_description') }}
                    </h3>
                    <step8-description
                        :form-data="formData"
                        :errors="errors"
                        @update:form-data="updateFormData"
                    />
                    <div class="flex gap-3 mt-6">
                        <BaseButton
                            :disabled="isSaving"
                            @click="saveSection('description')"
                        >
                            {{ $t('btn_save') }}
                        </BaseButton>
                        <BaseButton variant="secondary" @click="cancelEdit">
                            {{ $t('btn_cancel') }}
                        </BaseButton>
                    </div>
                </div>
                <edit-section
                    v-else
                    :title="$t('section_description')"
                    @edit="startEdit('description')"
                >
                    <p
                        class="text-sm text-gray-700 dark:text-gray-300 line-clamp-2"
                    >
                        {{ formData.description || $t('not_set') }}
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
                        {{ $t('section_pricing') }}
                    </h3>
                    <step9-pricing
                        :form-data="formData"
                        :errors="errors"
                        @update:form-data="updateFormData"
                    />
                    <div class="flex gap-3 mt-6">
                        <BaseButton
                            :disabled="isSaving"
                            @click="saveSection('pricing')"
                        >
                            {{ $t('btn_save') }}
                        </BaseButton>
                        <BaseButton variant="secondary" @click="cancelEdit">
                            {{ $t('btn_cancel') }}
                        </BaseButton>
                    </div>
                </div>
                <edit-section
                    v-else
                    :title="$t('section_pricing')"
                    @edit="startEdit('pricing')"
                >
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">{{ $t('label_base_price') }}</span>
                            <span
                                class="text-gray-900 dark:text-white font-medium"
                            >
                                ${{ formData.pricing.basePrice }} {{ $t('per_night') }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">{{ $t('label_booking_type') }}</span>
                            <span
                                class="text-gray-900 dark:text-white font-medium capitalize"
                            >
                                {{ formData.pricing.bookingType }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">{{ $t('label_min_stay') }}</span>
                            <span
                                class="text-gray-900 dark:text-white font-medium"
                            >
                                {{ $t('nights', { count: formData.pricing.minStay }) }}
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
                        {{ $t('section_house_rules') }}
                    </h3>
                    <step10-house-rules
                        :form-data="formData"
                        :errors="errors"
                        @update:form-data="updateFormData"
                    />
                    <div class="flex gap-3 mt-6">
                        <BaseButton
                            :disabled="isSaving"
                            @click="saveSection('houseRules')"
                        >
                            {{ $t('btn_save') }}
                        </BaseButton>
                        <BaseButton variant="secondary" @click="cancelEdit">
                            {{ $t('btn_cancel') }}
                        </BaseButton>
                    </div>
                </div>
                <edit-section
                    v-else
                    :title="$t('section_house_rules')"
                    @edit="startEdit('houseRules')"
                >
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">{{ $t('label_checkin') }}</span>
                            <span
                                class="text-gray-900 dark:text-white font-medium"
                            >
                                {{ formData.houseRules.checkInFrom }} -
                                {{ formData.houseRules.checkInUntil }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">{{ $t('label_checkout') }}</span>
                            <span
                                class="text-gray-900 dark:text-white font-medium"
                            >
                                {{ formData.houseRules.checkOutUntil }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">{{ $t('label_cancellation') }}</span>
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

<i18n lang="yaml">
en:
  heading: "Edit listing"
  subtitle: "Changes are applied immediately to your live listing."
  no_photos: "No photos uploaded"
  photos: "{count} photos"
  untitled: "Untitled listing"
  guests: "{count} guests"
  bedrooms: "{count} bedrooms"
  bathrooms: "{count} bathrooms"
  per_night: "/ night"
  saved: "Changes saved successfully."
  section_property: "Property info"
  section_location: "Location"
  section_floor_plan: "Floor plan"
  section_amenities: "Amenities"
  section_title: "Title"
  section_description: "Description"
  section_pricing: "Pricing"
  section_house_rules: "House rules"
  label_type: "Type:"
  label_occupation: "Occupation:"
  label_guests: "Guests:"
  label_bedrooms: "Bedrooms:"
  label_bathrooms: "Bathrooms:"
  label_base_price: "Base price:"
  label_booking_type: "Booking type:"
  label_min_stay: "Min. stay:"
  label_checkin: "Check-in:"
  label_checkout: "Check-out:"
  label_cancellation: "Cancellation:"
  not_set: "Not set"
  amenities_count: "{count} amenities selected"
  nights: "{count} nights"
  btn_save: "Save"
  btn_cancel: "Cancel"
sr:
  heading: "Uredi oglas"
  subtitle: "Promene se odmah primenjuju na vaš aktivan oglas."
  no_photos: "Nema otpremljenih fotografija"
  photos: "{count} fotografija"
  untitled: "Oglas bez naslova"
  guests: "{count} gostiju"
  bedrooms: "{count} spavaćih soba"
  bathrooms: "{count} kupatila"
  per_night: "/ noć"
  saved: "Izmene su uspešno sačuvane."
  section_property: "Informacije o nekretnini"
  section_location: "Lokacija"
  section_floor_plan: "Raspored soba"
  section_amenities: "Sadržaji"
  section_title: "Naslov"
  section_description: "Opis"
  section_pricing: "Cene"
  section_house_rules: "Kućni red"
  label_type: "Vrsta:"
  label_occupation: "Tip korišćenja:"
  label_guests: "Gosti:"
  label_bedrooms: "Spavaće sobe:"
  label_bathrooms: "Kupatila:"
  label_base_price: "Osnovna cena:"
  label_booking_type: "Tip rezervacije:"
  label_min_stay: "Min. boravak:"
  label_checkin: "Prijava:"
  label_checkout: "Odjava:"
  label_cancellation: "Otkazivanje:"
  not_set: "Nije postavljeno"
  amenities_count: "{count} sadržaja odabrano"
  nights: "{count} noći"
  btn_save: "Sačuvaj"
  btn_cancel: "Otkaži"
hr:
  heading: "Uredi oglas"
  subtitle: "Promjene se odmah primjenjuju na vaš aktivni oglas."
  no_photos: "Nema učitanih fotografija"
  photos: "{count} fotografija"
  untitled: "Oglas bez naslova"
  guests: "{count} gostiju"
  bedrooms: "{count} spavaćih soba"
  bathrooms: "{count} kupaonica"
  per_night: "/ noć"
  saved: "Promjene su uspješno spremljene."
  section_property: "Informacije o nekretnini"
  section_location: "Lokacija"
  section_floor_plan: "Tlocrt"
  section_amenities: "Sadržaji"
  section_title: "Naslov"
  section_description: "Opis"
  section_pricing: "Cijene"
  section_house_rules: "Kućna pravila"
  label_type: "Vrsta:"
  label_occupation: "Vrsta korištenja:"
  label_guests: "Gosti:"
  label_bedrooms: "Spavaće sobe:"
  label_bathrooms: "Kupaonica:"
  label_base_price: "Osnovna cijena:"
  label_booking_type: "Vrsta rezervacije:"
  label_min_stay: "Min. boravak:"
  label_checkin: "Prijava:"
  label_checkout: "Odjava:"
  label_cancellation: "Otkazivanje:"
  not_set: "Nije postavljeno"
  amenities_count: "{count} sadržaja odabrano"
  nights: "{count} noći"
  btn_save: "Spremi"
  btn_cancel: "Odustani"
mk:
  heading: "Уреди оглас"
  subtitle: "Промените се применуваат веднаш на вашиот активен оглас."
  no_photos: "Нема прикачени фотографии"
  photos: "{count} фотографии"
  untitled: "Оглас без наслов"
  guests: "{count} гости"
  bedrooms: "{count} спални"
  bathrooms: "{count} бањи"
  per_night: "/ ноќ"
  saved: "Промените се успешно зачувани."
  section_property: "Информации за имотот"
  section_location: "Локација"
  section_floor_plan: "План на катот"
  section_amenities: "Погодности"
  section_title: "Наслов"
  section_description: "Опис"
  section_pricing: "Цени"
  section_house_rules: "Правила на домот"
  label_type: "Вид:"
  label_occupation: "Тип на користење:"
  label_guests: "Гости:"
  label_bedrooms: "Спални:"
  label_bathrooms: "Бањи:"
  label_base_price: "Основна цена:"
  label_booking_type: "Тип на резервација:"
  label_min_stay: "Мин. престој:"
  label_checkin: "Пријавување:"
  label_checkout: "Одјавување:"
  label_cancellation: "Откажување:"
  not_set: "Не е поставено"
  amenities_count: "{count} погодности избрани"
  nights: "{count} ноќи"
  btn_save: "Зачувај"
  btn_cancel: "Откажи"
sl:
  heading: "Uredi oglas"
  subtitle: "Spremembe se takoj uveljavijo na vašem aktivnem oglasu."
  no_photos: "Ni naloženih fotografij"
  photos: "{count} fotografij"
  untitled: "Oglas brez naslova"
  guests: "{count} gostov"
  bedrooms: "{count} spalnic"
  bathrooms: "{count} kopalnic"
  per_night: "/ noč"
  saved: "Spremembe so bile uspešno shranjene."
  section_property: "Informacije o nepremičnini"
  section_location: "Lokacija"
  section_floor_plan: "Tloris"
  section_amenities: "Ugodnosti"
  section_title: "Naslov"
  section_description: "Opis"
  section_pricing: "Cene"
  section_house_rules: "Hišni red"
  label_type: "Vrsta:"
  label_occupation: "Vrsta rabe:"
  label_guests: "Gosti:"
  label_bedrooms: "Spalnice:"
  label_bathrooms: "Kopalnice:"
  label_base_price: "Osnovna cena:"
  label_booking_type: "Vrsta rezervacije:"
  label_min_stay: "Min. bivanje:"
  label_checkin: "Prijava:"
  label_checkout: "Odjava:"
  label_cancellation: "Odpoved:"
  not_set: "Ni nastavljeno"
  amenities_count: "{count} ugodnosti izbranih"
  nights: "{count} noči"
  btn_save: "Shrani"
  btn_cancel: "Prekliči"
</i18n>
