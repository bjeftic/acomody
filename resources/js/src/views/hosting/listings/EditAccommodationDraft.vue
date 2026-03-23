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
                    Edit listing
                </h1>
                <p class="text-lg text-gray-600 dark:text-gray-400">
                    Make changes to your listing below.
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
                    This listing was rejected
                </h3>
                <p class="text-sm text-red-700 dark:text-red-400">
                    Please review the feedback below, make the necessary changes,
                    and resubmit for approval.
                </p>

                <div
                    v-if="accommodationDraftReviewComments.length"
                    class="mt-4 space-y-3"
                >
                    <p
                        class="text-sm font-semibold text-red-700 dark:text-red-400"
                    >
                        Reviewer comments:
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
                            Done
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
                        <span>No photos uploaded</span>
                    </div>
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors flex items-center justify-center">
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity bg-white dark:bg-gray-900 text-gray-900 dark:text-white text-sm font-medium px-4 py-2 rounded-lg shadow">
                            Edit photos
                        </span>
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
                        <BaseButton
                            :disabled="isSaving"
                            @click="saveSection"
                        >
                            Save
                        </BaseButton>
                        <BaseButton variant="secondary" @click="cancelEdit">
                            Cancel
                        </BaseButton>
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
                        <BaseButton
                            :disabled="isSaving"
                            @click="saveSection"
                        >
                            Save
                        </BaseButton>
                        <BaseButton variant="secondary" @click="cancelEdit">
                            Cancel
                        </BaseButton>
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
                        <BaseButton
                            :disabled="isSaving"
                            @click="saveSection"
                        >
                            Save
                        </BaseButton>
                        <BaseButton variant="secondary" @click="cancelEdit">
                            Cancel
                        </BaseButton>
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
                        <BaseButton
                            :disabled="isSaving"
                            @click="saveSection"
                        >
                            Save
                        </BaseButton>
                        <BaseButton variant="secondary" @click="cancelEdit">
                            Cancel
                        </BaseButton>
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
                        <BaseButton
                            :disabled="isSaving"
                            @click="saveSection"
                        >
                            Save
                        </BaseButton>
                        <BaseButton variant="secondary" @click="cancelEdit">
                            Cancel
                        </BaseButton>
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
                        <BaseButton
                            :disabled="isSaving"
                            @click="saveSection"
                        >
                            Save
                        </BaseButton>
                        <BaseButton variant="secondary" @click="cancelEdit">
                            Cancel
                        </BaseButton>
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
                        <BaseButton
                            :disabled="isSaving"
                            @click="saveSection"
                        >
                            Save
                        </BaseButton>
                        <BaseButton variant="secondary" @click="cancelEdit">
                            Cancel
                        </BaseButton>
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
                        <BaseButton
                            :disabled="isSaving"
                            @click="saveSection"
                        >
                            Save
                        </BaseButton>
                        <BaseButton variant="secondary" @click="cancelEdit">
                            Cancel
                        </BaseButton>
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

            <!-- Submit for Approval -->
            <div
                class="mt-8 p-6 bg-primary-50 dark:bg-primary-900/20 rounded-xl border border-primary-200 dark:border-primary-800"
            >
                <h4
                    class="text-sm font-semibold text-gray-900 dark:text-white mb-2"
                >
                    Ready to submit?
                </h4>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    Once submitted, our team will review your listing within 24
                    hours.
                </p>
                <BaseButton
                    :disabled="isSaving"
                    @click="submitForApproval"
                >
                    Submit for approval
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
