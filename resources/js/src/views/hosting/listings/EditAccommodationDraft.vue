<template>
    <div class="max-w-4xl mx-auto px-4 pt-6 md:py-12">
        <template v-if="createAccommodationLoading">
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

            <!-- Rejection Banner + Review Comments -->
            <div
                v-if="isRejected"
                class="mb-6 p-5 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl"
            >
                <h3
                    class="text-base font-semibold text-red-800 dark:text-red-300 mb-1"
                >
                    {{ $t('rejected_title') }}
                </h3>
                <p class="text-sm text-red-700 dark:text-red-400">
                    {{ $t('rejected_desc') }}
                </p>

                <div
                    v-if="accommodationDraftReviewComments.length"
                    class="mt-4 space-y-3"
                >
                    <p
                        class="text-sm font-semibold text-red-700 dark:text-red-400"
                    >
                        {{ $t('reviewer_comments') }}
                    </p>
                    <div
                        v-for="comment in accommodationDraftReviewComments"
                        :key="comment.id"
                        class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-red-100 dark:border-red-900"
                    >
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            {{ comment.body }}
                        </p>
                        <p
                            class="text-xs text-gray-500 dark:text-gray-500 mt-1"
                        >
                            {{ formatDate(comment.created_at) }}
                        </p>
                    </div>
                </div>
            </div>

            <hr class="mb-6" />

            <!-- Preview Card -->
            <div
                class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden mb-6"
            >
                <div v-if="currentEditSection === 'photos'" class="p-6">
                    <step6-photos
                        :form-data="formData"
                        :errors="errors"
                        @update:form-data="updateFormData"
                    />
                    <div class="flex gap-3 mt-6">
                        <BaseButton variant="secondary" @click="cancelEdit">
                            {{ $t('btn_done') }}
                        </BaseButton>
                    </div>
                </div>
                <div v-else class="relative aspect-video bg-gray-100 dark:bg-gray-900 group cursor-pointer" @click="startEdit('photos')">
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
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors flex items-center justify-center">
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity bg-white dark:bg-gray-900 text-gray-900 dark:text-white text-sm font-medium px-4 py-2 rounded-lg shadow">
                            {{ $t('edit_photos') }}
                        </span>
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
                            @click="saveSection"
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
                            @click="saveSection"
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
                            @click="saveSection"
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
                            @click="saveSection"
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
                            @click="saveSection"
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
                            @click="saveSection"
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
                            @click="saveSection"
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
                            @click="saveSection"
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

            <!-- Submit for Approval -->
            <div
                class="mt-8 p-6 bg-primary-50 dark:bg-primary-900/20 rounded-xl border border-primary-200 dark:border-primary-800"
            >
                <h4
                    class="text-sm font-semibold text-gray-900 dark:text-white mb-2"
                >
                    {{ $t('submit_heading') }}
                </h4>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    {{ $t('submit_desc') }}
                </p>
                <BaseButton
                    :disabled="isSaving"
                    @click="submitForApproval"
                >
                    {{ $t('submit_btn') }}
                </BaseButton>
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
import Step6Photos from "@/src/views/hosting/createAccommodation/steps/Step6Photos.vue";
import Step7Title from "@/src/views/hosting/createAccommodation/steps/Step7Title.vue";
import Step8Description from "@/src/views/hosting/createAccommodation/steps/Step8Description.vue";
import Step9Pricing from "@/src/views/hosting/createAccommodation/steps/Step9Pricing.vue";
import Step10HouseRules from "@/src/views/hosting/createAccommodation/steps/Step10HouseRules.vue";

export default {
    name: "EditAccommodationDraft",
    components: {
        EditSection,
        Step1AccommodationType,
        Step2OccupationType,
        Step3Address,
        Step4FloorPlan,
        Step5Amenities,
        Step6Photos,
        Step7Title,
        Step8Description,
        Step9Pricing,
        Step10HouseRules,
    },
    data() {
        return {
            currentEditSection: null,
            isSaving: false,
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
                    basePrice: 5000,
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
        ...mapState("hosting/createAccommodation", [
            "accommodationDraft",
            "accommodationDraftId",
            "accommodationDraftStatus",
            "accommodationDraftReviewComments",
            "accommodationTypes",
            "bedTypes",
            "createAccommodationLoading",
        ]),
        draftId() {
            return this.$route.params.draftId;
        },
        isRejected() {
            return this.accommodationDraftStatus === "rejected";
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
        accommodationDraft: {
            immediate: true,
            deep: true,
            handler(newDraft) {
                if (newDraft && this.bedTypes.length) {
                    this.loadDraftData(newDraft);
                }
            },
        },
        bedTypes(newBedTypes) {
            if (newBedTypes.length && this.accommodationDraft) {
                this.loadDraftData(this.accommodationDraft);
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
        ...mapActions("hosting/createAccommodation", [
            "loadInitialEditDraftData",
            "updateAccommodationDraft",
            "fetchPhotos",
        ]),

        startEdit(section) {
            this.currentEditSection = section;
            this.errors = {};
        },

        cancelEdit() {
            this.currentEditSection = null;
            this.errors = {};
        },

        updateFormData(updates) {
            this.formData = { ...this.formData, ...updates };
        },

        async saveSection() {
            this.isSaving = true;
            try {
                const draftData = this.prepareDraftData();
                await this.updateAccommodationDraft({
                    draftId: this.accommodationDraftId,
                    draftData,
                    status: "draft",
                    currentStep: 12,
                });
                this.currentEditSection = null;
            } catch (error) {
                if (error.response?.data?.errors) {
                    this.errors = error.response.data.errors;
                }
            } finally {
                this.isSaving = false;
            }
        },

        async submitForApproval() {
            this.isSaving = true;
            try {
                const draftData = this.prepareDraftData();
                await this.updateAccommodationDraft({
                    draftId: this.accommodationDraftId,
                    draftData,
                    status: "waiting_for_approval",
                    currentStep: 12,
                });
                this.$router.push({ name: "page-listings" });
            } catch (error) {
                if (error.response?.data?.errors) {
                    this.errors = error.response.data.errors;
                }
            } finally {
                this.isSaving = false;
            }
        },

        loadDraftData(draft) {
            this.formData = {
                accommodationType: draft.accommodation_type || null,
                accommodationOccupation:
                    draft.accommodation_occupation || null,
                address: {
                    country: draft.address?.country || "",
                    street: draft.address?.street || "",
                    city: draft.address?.city || "",
                    state: draft.address?.state || "",
                    zipCode: draft.address?.zip_code || "",
                },
                coordinates: {
                    latitude: draft.coordinates?.latitude || null,
                    longitude: draft.coordinates?.longitude || null,
                },
                floorPlan: {
                    guests: draft.floor_plan?.guests || 1,
                    bedrooms: draft.floor_plan?.bedrooms || 1,
                    bathrooms: draft.floor_plan?.bathrooms || 1,
                    bedTypes: this.bedTypes.map((bedType) => ({
                        bed_type: bedType.value,
                        name: bedType.name,
                        description: bedType.description,
                        quantity:
                            draft.floor_plan?.bed_types?.find(
                                (bt) => bt.bed_type === bedType.value
                            )?.quantity ?? 0,
                    })),
                },
                amenities: draft.amenities || [],
                photos: draft.photos || [],
                title: draft.title || "",
                description: draft.description || "",
                pricing: draft.pricing || {
                    basePrice: 5000,
                    bookingType: "instant_booking",
                    minStay: 1,
                },
                houseRules: draft.house_rules || {
                    checkInFrom: "15:00",
                    checkInUntil: "20:00",
                    checkOutUntil: "11:00",
                    hasQuietHours: false,
                    quietHoursFrom: "22:00",
                    quietHoursUntil: "08:00",
                    cancellationPolicy: "moderate",
                },
            };
        },

        prepareDraftData() {
            return {
                accommodation_type: this.formData.accommodationType,
                accommodation_occupation: this.formData.accommodationOccupation,
                address: {
                    country: this.formData.address.country,
                    street: this.formData.address.street,
                    city: this.formData.address.city,
                    state: this.formData.address.state,
                    zip_code: this.formData.address.zipCode,
                },
                coordinates: {
                    latitude: this.formData.coordinates.latitude,
                    longitude: this.formData.coordinates.longitude,
                },
                floor_plan: {
                    guests: this.formData.floorPlan.guests,
                    bedrooms: this.formData.floorPlan.bedrooms,
                    bathrooms: this.formData.floorPlan.bathrooms,
                    bed_types: this.formData.floorPlan.bedTypes.filter(
                        (bt) => bt.quantity > 0
                    ).map((bt) => ({
                        bed_type: bt.bed_type,
                        quantity: bt.quantity,
                    })),
                },
                amenities: this.formData.amenities,
                title: this.formData.title,
                description: this.formData.description,
                pricing: this.formData.pricing,
                house_rules: this.formData.houseRules,
            };
        },

        formatDate(dateStr) {
            if (!dateStr) return "";
            return new Date(dateStr).toLocaleDateString("en-US", {
                year: "numeric",
                month: "long",
                day: "numeric",
            });
        },
    },
    async created() {
        await this.loadInitialEditDraftData(this.draftId);
        if (this.accommodationDraftId) {
            await this.fetchPhotos(this.accommodationDraftId);
        }
    },
};
</script>

<i18n lang="yaml">
en:
  heading: "Edit listing"
  subtitle: "Make changes to your listing below."
  no_photos: "No photos uploaded"
  edit_photos: "Edit photos"
  photos: "{count} photos"
  untitled: "Untitled listing"
  guests: "{count} guests"
  bedrooms: "{count} bedrooms"
  bathrooms: "{count} bathrooms"
  per_night: "/ night"
  rejected_title: "This listing was rejected"
  rejected_desc: "Please review the feedback below, make the necessary changes, and resubmit for approval."
  reviewer_comments: "Reviewer comments:"
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
  btn_done: "Done"
  submit_heading: "Ready to submit?"
  submit_desc: "Once submitted, our team will review your listing within 24 hours."
  submit_btn: "Submit for approval"
sr:
  heading: "Uredi oglas"
  subtitle: "Unesite izmene u oglas ispod."
  no_photos: "Nema otpremljenih fotografija"
  edit_photos: "Uredi fotografije"
  photos: "{count} fotografija"
  untitled: "Oglas bez naslova"
  guests: "{count} gostiju"
  bedrooms: "{count} spavaćih soba"
  bathrooms: "{count} kupatila"
  per_night: "/ noć"
  rejected_title: "Ovaj oglas je odbijen"
  rejected_desc: "Pregledajte povratne informacije u nastavku, napravite neophodne izmene i ponovo podnesite na odobrenje."
  reviewer_comments: "Komentari recenzenta:"
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
  btn_done: "Gotovo"
  submit_heading: "Spremni za slanje?"
  submit_desc: "Nakon slanja, naš tim će pregledati vaš oglas u roku od 24 sata."
  submit_btn: "Pošalji na odobrenje"
hr:
  heading: "Uredi oglas"
  subtitle: "Unesite izmjene u oglas ispod."
  no_photos: "Nema učitanih fotografija"
  edit_photos: "Uredi fotografije"
  photos: "{count} fotografija"
  untitled: "Oglas bez naslova"
  guests: "{count} gostiju"
  bedrooms: "{count} spavaćih soba"
  bathrooms: "{count} kupaonica"
  per_night: "/ noć"
  rejected_title: "Ovaj oglas je odbijen"
  rejected_desc: "Pregledajte povratne informacije u nastavku, napravite potrebne izmjene i ponovo podnesite na odobrenje."
  reviewer_comments: "Komentari recenzenta:"
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
  btn_done: "Gotovo"
  submit_heading: "Spremni za slanje?"
  submit_desc: "Nakon slanja, naš tim će pregledati vaš oglas u roku od 24 sata."
  submit_btn: "Pošalji na odobrenje"
mk:
  heading: "Уреди оглас"
  subtitle: "Направете промени на вашиот оглас подолу."
  no_photos: "Нема прикачени фотографии"
  edit_photos: "Уреди фотографии"
  photos: "{count} фотографии"
  untitled: "Оглас без наслов"
  guests: "{count} гости"
  bedrooms: "{count} спални"
  bathrooms: "{count} бањи"
  per_night: "/ ноќ"
  rejected_title: "Овој оглас е одбиен"
  rejected_desc: "Прегледајте ги повратните информации подолу, направете ги потребните промени и повторно поднесете за одобрување."
  reviewer_comments: "Коментари на рецензентот:"
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
  btn_done: "Готово"
  submit_heading: "Готови за поднесување?"
  submit_desc: "По поднесувањето, нашиот тим ќе го прегледа вашиот оглас во рок од 24 часа."
  submit_btn: "Поднеси за одобрување"
sl:
  heading: "Uredi oglas"
  subtitle: "Spodaj vnesite spremembe v oglas."
  no_photos: "Ni naloženih fotografij"
  edit_photos: "Uredi fotografije"
  photos: "{count} fotografij"
  untitled: "Oglas brez naslova"
  guests: "{count} gostov"
  bedrooms: "{count} spalnic"
  bathrooms: "{count} kopalnic"
  per_night: "/ noč"
  rejected_title: "Ta oglas je bil zavrnjen"
  rejected_desc: "Preglejte spodnje povratne informacije, naredite potrebne spremembe in ponovno oddajte v odobritev."
  reviewer_comments: "Komentarji recenzenta:"
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
  btn_done: "Končano"
  submit_heading: "Pripravljeni za oddajo?"
  submit_desc: "Po oddaji bo naša ekipa pregledala vaš oglas v 24 urah."
  submit_btn: "Oddaj v odobritev"
</i18n>
