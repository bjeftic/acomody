<template>
    <div class="max-w-4xl mx-auto pb-12">
        <!-- Main Content -->
        <div class="mx-auto">
            <template v-if="createAccommodationLoading">
                <form-skeleton />
            </template>
            <template v-else>
                <!-- Step Content -->
                <transition v-if="!submitted" name="fade" mode="out-in">
                    <component
                        :is="currentStepComponent"
                        :key="currentStep"
                        :form-data="formData"
                        :errors="createAccommodationErrors"
                        @update:form-data="updateFormData"
                        @next="nextStep"
                        @previous="previousStep"
                    />
                </transition>

                <!-- Submission Success -->
                <submission-success v-else />
            </template>
        </div>

        <!-- Footer Navigation -->
        <wizard-navigation
            v-if="!loading && !submitted"
            :current-step="currentStep"
            :total-steps="totalSteps"
            :can-proceed="canProceed"
            @previous="previousStep"
            @next="nextStep"
        />
    </div>
</template>

<script>
import { mapState, mapActions } from "vuex";
import WizardNavigation from "@/src/views/hosting/createAccommodation/components/WizardNavigation.vue";
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
import Step11Review from "@/src/views/hosting/createAccommodation/steps/Step11Review.vue";
import SubmissionSuccess from "@/src/views/hosting/createAccommodation/SubmissionSuccess.vue";

export default {
    name: "CreateAccommodation",
    components: {
        WizardNavigation,
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
        Step11Review,
        SubmissionSuccess,
    },
    data() {
        return {
            totalSteps: 11,
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
                    beds: 1,
                    bathrooms: 1,
                },
                amenities: [],
                photos: [],
                title: "",
                description: "",
                pricing: {
                    basePrice: 5000,
                    // hasWeekendPrice: false,
                    // weekendPrice: 0,
                    // weeklyDiscount: 0,
                    // monthlyDiscount: 0,
                    bookingType: "instant-booking",
                    minStay: 1,
                    // hasDaySpecificMinStay: false,
                    // daySpecificMinStay: {
                    //     monday: { enabled: false, nights: 1 },
                    //     tuesday: { enabled: false, nights: 1 },
                    //     wednesday: { enabled: false, nights: 1 },
                    //     thursday: { enabled: false, nights: 1 },
                    //     friday: { enabled: false, nights: 3 },
                    //     saturday: { enabled: false, nights: 2 },
                    //     sunday: { enabled: false, nights: 1 },
                    // },
                },
                houseRules: {
                    checkInFrom: "15:00",
                    checkInUntil: "20:00",
                    checkOutUntil: "11:00",
                    hasQuietHours: false,
                    quietHoursFrom: "22:00",
                    quietHoursUntil: "08:00",
                    additionalRules: "",
                    cancellationPolicy: "moderate",
                },
            },
            submitted: false,
            createAccommodationErrors: {},
        };
    },
    computed: {
        ...mapState("hosting/createAccommodation", [
            "accommodationDraft",
            "accommodationDraftId",
            "currentStep",
            "createAccommodationLoading",
        ]),

        currentStepComponent() {
            return `Step${this.currentStep}${this.getStepName(
                this.currentStep
            )}`;
        },

        canProceed() {
            switch (this.currentStep) {
                case 1:
                    return this.formData.accommodationType !== null;
                case 2:
                    return this.formData.accommodationOccupation !== null;
                case 3:
                    return (
                        this.formData.address.country &&
                        this.formData.address.street &&
                        this.formData.address.city &&
                        this.formData.coordinates.latitude &&
                        this.formData.coordinates.longitude
                    );
                case 4:
                    return (
                        this.formData.floorPlan.guests >= 1 &&
                        this.formData.floorPlan.bedrooms >= 0 &&
                        this.formData.floorPlan.beds >= 1 &&
                        this.formData.floorPlan.bathrooms >= 0.5
                    );
                case 5:
                    return true;
                case 6:
                    return this.formData.photos.length >= 5;
                case 7:
                    return this.formData.title.trim().length >= 10;
                case 8:
                    return this.formData.description.trim().length >= 50;
                case 9:
                    return this.validatePricing();
                case 10:
                    return this.validateHouseRules();
                case 11:
                    return this.formData.agreedToTerms;
                default:
                    return true;
            }
        },
    },
    watch: {
        accommodationDraft: {
            immediate: true,
            deep: true,
            handler(newDraft) {
                if (newDraft) {
                    this.loadDraftData(newDraft);
                }
            },
        },
    },
    methods: {
        ...mapActions("hosting/createAccommodation", [
            "loadInitialCreateAccommodationData",
            "createAccommodationDraft",
            "updateAccommodationDraft",
            "incrementCurrentStep",
            "decrementCurrentStep",
            "restartAccommodationDraftData"
        ]),

        getStepName(step) {
            const names = {
                1: "AccommodationType",
                2: "OccupationType",
                3: "Address",
                4: "FloorPlan",
                5: "Amenities",
                6: "Photos",
                7: "Title",
                8: "Description",
                9: "Pricing",
                10: "HouseRules",
                11: "Review",
            };
            return names[step] || "";
        },

        updateFormData(updates) {
            this.formData = { ...this.formData, ...updates };
        },

        async nextStep() {
            if (this.canProceed) {
                const draftData = this.prepareDraftData();

                if (this.currentStep < this.totalSteps) {
                    if (this.currentStep === 1 && !this.accommodationDraftId) {
                        this.createAccommodationDraft({
                            draftData,
                        });
                    } else {
                        try {
                            await this.updateAccommodationDraft({
                                draftId: this.accommodationDraftId,
                                draftData,
                                status: "draft",
                                currentStep: this.currentStep + 1,
                            });
                        } catch (error) {
                            console.error("Error updating draft:", error);
                            if (error.response?.data?.errors) {
                                this.createAccommodationErrors =
                                    error.response.data.errors;
                            }
                        }
                    }
                } else {
                    await this.updateAccommodationDraft({
                        draftId: this.accommodationDraftId,
                        draftData,
                        status: "waiting_for_approval",
                        currentStep: this.currentStep + 1,
                    })
                        .then(() => {
                            this.submitted = true;
                            this.restartAccommodationDraftData();
                        })
                        .catch((error) => {
                            console.error(
                                "Error submitting accommodation:",
                                error
                            );
                            if (error.response?.data?.errors) {
                                this.createAccommodationErrors =
                                    error.response.data.errors;
                            }
                        });
                }
            }
        },

        previousStep() {
            if (this.currentStep > 1) {
                this.decrementCurrentStep();
            }
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
                    beds: this.formData.floorPlan.beds,
                    bathrooms: this.formData.floorPlan.bathrooms,
                },
                amenities: this.formData.amenities,
                title: this.formData.title,
                description: this.formData.description,
                pricing: this.formData.pricing,
                house_rules: this.formData.houseRules,
            };
        },

        loadDraftData(draft) {
            this.formData = {
                accommodationType: draft.accommodation_type || null,
                accommodationOccupation: draft.accommodation_occupation || null,
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
                    beds: draft.floor_plan?.beds || 1,
                    bathrooms: draft.floor_plan?.bathrooms || 1,
                },
                amenities: draft.amenities || [],
                photos: draft.photos || [],
                title: draft.title || "",
                description: draft.description || "",
                pricing: draft.pricing || {
                    basePrice: 5000,
                    // hasWeekendPrice: false,
                    // weekendPrice: 0,
                    // weeklyDiscount: 0,
                    // monthlyDiscount: 0,
                    bookingType: "instant-booking",
                    minStay: 1,
                    // hasDaySpecificMinStay: false,
                    // daySpecificMinStay: {},
                    // standardFees: [],
                    // amenityFees: [],
                    // customFees: [],
                },
                houseRules: draft.house_rules || {
                    checkInFrom: "15:00",
                    checkInUntil: "20:00",
                    checkOutUntil: "11:00",
                    hasQuietHours: false,
                    quietHoursFrom: "22:00",
                    quietHoursUntil: "08:00",
                    additionalRules: "",
                    cancellationPolicy: "moderate",
                },
            };
        },

        validatePricing() {
            const basePrice = this.formData.pricing.basePrice;
            const validBasePrice = basePrice && basePrice >= 10;

            if (this.formData.pricing.hasWeekendPrice) {
                const weekendPrice = this.formData.pricing.weekendPrice;
                if (!weekendPrice || weekendPrice < 10) {
                    return false;
                }
            }

            const weeklyDiscount = this.formData.pricing.weeklyDiscount;
            const monthlyDiscount = this.formData.pricing.monthlyDiscount;

            if (weeklyDiscount < 0 || weeklyDiscount > 99) return false;
            if (monthlyDiscount < 0 || monthlyDiscount > 99) return false;

            if (
                monthlyDiscount > 0 &&
                weeklyDiscount > 0 &&
                monthlyDiscount <= weeklyDiscount
            ) {
                return false;
            }

            const minStay = this.formData.pricing.minStay;
            if (!minStay || minStay < 1) return false;

            if (!this.formData.pricing.bookingType) return false;

            return validBasePrice;
        },

        validateHouseRules() {
            const hasValidCheckIn =
                this.formData.houseRules.checkInFrom &&
                this.formData.houseRules.checkInUntil;
            const hasValidCheckOut = this.formData.houseRules.checkOutUntil;

            if (this.formData.houseRules.hasQuietHours) {
                const hasValidQuietHours =
                    this.formData.houseRules.quietHoursFrom &&
                    this.formData.houseRules.quietHoursUntil;
                return (
                    hasValidCheckIn && hasValidCheckOut && hasValidQuietHours
                );
            }

            const hasCancellationPolicy =
                this.formData.houseRules.cancellationPolicy !== null;

            return hasValidCheckIn && hasValidCheckOut && hasCancellationPolicy;
        },
    },
    async created() {
        await this.loadInitialCreateAccommodationData();
    },
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
