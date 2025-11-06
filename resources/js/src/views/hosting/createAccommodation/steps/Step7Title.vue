<template>
    <div>
        <h1 class="text-3xl font-semibold text-gray-900 dark:text-white mb-2">
            Now, let's give your {{ propertyTypeName }} a title
        </h1>
        <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">
            Short titles work best. Have fun with it—you can always change it later.
        </p>

        <hr />

        <div class="max-w-2xl mx-auto overflow-auto py-4 pr-4 h-[60vh] space-y-6">
            <!-- Title Input -->
            <div class="relative">
                <fwb-textarea
                    :model-value="formData.title"
                    label="Title"
                    @update:model-value="updateTitle"
                    :rows="3"
                    :maxlength="50"
                    placeholder="Example: Cozy studio in the heart of the city"
                    class="text-xl resize-none"
                />

                <!-- Character Counter -->
                <div class="flex items-center justify-between mt-2">
                    <p
                        :class="[
                            'text-sm font-medium',
                            formData.title.length >= 50
                                ? 'text-red-600 dark:text-red-400'
                                : 'text-gray-500 dark:text-gray-400',
                        ]"
                    >
                        {{ formData.title.length }}/50
                    </p>
                </div>
            </div>

            <!-- Quick Suggestions -->
            <div v-if="titleSuggestions.length > 0" class="space-y-4">
                <h3 class="text-base font-medium text-gray-900 dark:text-white">
                    Need inspiration? Try one of these:
                </h3>

                <!-- Suggestion Cards -->
                <div class="space-y-3">
                    <button
                        v-for="(suggestion, index) in titleSuggestions"
                        :key="index"
                        @click="selectTitleSuggestion(suggestion)"
                        class="w-full p-4 text-left bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:border-gray-900 dark:hover:border-white hover:bg-gray-50 dark:hover:bg-gray-700 transition-all group"
                    >
                        <div class="flex items-start justify-between">
                            <p
                                class="text-base text-gray-900 dark:text-white font-medium flex-1"
                            >
                                {{ suggestion }}
                            </p>
                            <svg
                                class="w-5 h-5 text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white flex-shrink-0 ml-3"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 5l7 7-7 7"
                                />
                            </svg>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Tips -->
            <div
                class="p-6 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700"
            >
                <h4
                    class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center"
                >
                    <svg
                        class="w-5 h-5 mr-2 text-blue-500"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    Tips for a great title
                </h4>
                <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                    <li class="flex items-start">
                        <svg
                            class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0 text-green-500"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        <span>Highlight what makes your place special</span>
                    </li>
                    <li class="flex items-start">
                        <svg
                            class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0 text-green-500"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        <span>Include the location or a unique feature</span>
                    </li>
                    <li class="flex items-start">
                        <svg
                            class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0 text-green-500"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        <span>Keep it clear and descriptive</span>
                    </li>
                    <li class="flex items-start">
                        <svg
                            class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0 text-red-500"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        <span>Avoid all caps or excessive punctuation!!!</span>
                    </li>
                </ul>
            </div>

            <!-- Examples based on property type -->
            <div
                class="p-6 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800"
            >
                <h4
                    class="text-sm font-semibold text-gray-900 dark:text-white mb-3"
                >
                    Popular {{ propertyTypeName }} titles
                </h4>
                <div class="space-y-2">
                    <p
                        v-for="(example, index) in titleExamples"
                        :key="index"
                        class="text-sm text-gray-700 dark:text-gray-300 flex items-center"
                    >
                        <svg
                            class="w-4 h-4 mr-2 text-blue-500 flex-shrink-0"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        "{{ example }}"
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { mapState } from "vuex";

export default {
    name: "Step7Title",
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
        ...mapState("hosting/createAccommodation", ["accommodationTypes"]),

        propertyTypeName() {
            if (!this.formData.propertyType) return "place";
            const type = this.accommodationTypes.find(
                (t) => t.id === this.formData.propertyType
            );
            return type ? type.name.toLowerCase() : "place";
        },

        titleSuggestions() {
            const city = this.formData.address.city || "the city";
            const guests = this.formData.floorPlan.guests;
            const bedrooms = this.formData.floorPlan.bedrooms;
            const type = this.propertyTypeName;

            const suggestions = [];

            // Basic suggestions
            suggestions.push(`Charming ${type} in ${city}`);
            suggestions.push(
                `${bedrooms}BR ${type} perfect for ${guests} guests`
            );
            suggestions.push(`Cozy ${type} with modern amenities`);
            suggestions.push(`Beautiful ${type} near downtown ${city}`);

            // Add amenity-based suggestions
            if (this.formData.amenities.includes("wifi")) {
                suggestions.push(`${type} with high-speed WiFi in ${city}`);
            }
            if (this.formData.amenities.includes("pool")) {
                suggestions.push(`Stunning ${type} with pool access`);
            }
            if (
                this.formData.amenities.includes("free-parking") ||
                this.formData.amenities.includes("paid-parking")
            ) {
                suggestions.push(`${type} with free parking in ${city}`);
            }
            if (this.formData.amenities.includes("air-conditioning")) {
                suggestions.push(`Cool and comfortable ${type} in ${city}`);
            }

            // Return top 5 suggestions
            return suggestions.slice(0, 5);
        },

        titleExamples() {
            const examples = {
                house: [
                    "Charming 3BR house with garden views",
                    "Modern family home near downtown",
                    "Cozy cottage with mountain backdrop",
                ],
                apartment: [
                    "Stylish loft in the heart of the city",
                    "Bright 2BR apartment with balcony",
                    "Downtown studio with city views",
                ],
                villa: [
                    "Luxury villa with private pool",
                    "Stunning beachfront villa retreat",
                    "Elegant villa in peaceful surroundings",
                ],
                cabin: [
                    "Rustic cabin nestled in the woods",
                    "Mountain cabin with fireplace",
                    "Secluded lakeside cabin getaway",
                ],
                default: [
                    "Beautiful place in prime location",
                    "Comfortable stay with great amenities",
                    "Perfect spot for your next trip",
                ],
            };

            return (
                examples[this.formData.propertyType] || examples["default"]
            );
        },
    },
    methods: {
        updateTitle(value) {
            this.$emit("update:form-data", {
                ...this.formData,
                title: value,
            });
        },

        selectTitleSuggestion(suggestion) {
            this.updateTitle(suggestion);

            // Scroll to textarea
            this.$nextTick(() => {
                const textarea = document.querySelector("textarea");
                if (textarea) {
                    textarea.focus();
                    textarea.scrollIntoView({
                        behavior: "smooth",
                        block: "center",
                    });
                }
            });
        },
    },
};
</script>
