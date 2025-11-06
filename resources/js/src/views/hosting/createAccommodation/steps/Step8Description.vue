<template>
    <div>
        <h1 class="text-3xl font-semibold text-gray-900 dark:text-white mb-2">
            Create your description
        </h1>
        <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">
            Share what makes your place special.
        </p>

        <hr />

        <div class="max-w-2xl mx-auto py-4 pr-4 overflow-auto h-[60vh] space-y-6">
            <!-- Description Textarea -->
            <div class="relative">
                <fwb-textarea
                    :model-value="formData.description"
                    :label="'Description of your ' + formData.title"
                    @update:model-value="updateDescription"
                    :rows="10"
                    :maxlength="500"
                    placeholder="Describe your space, the neighborhood, and what guests will love about staying here..."
                    class="resize-none"
                />

                <!-- Character Counter -->
                <div class="flex items-center justify-between mt-2">
                    <p
                        :class="[
                            'text-sm font-medium',
                            formData.description.length >= 500
                                ? 'text-red-600 dark:text-red-400'
                                : 'text-gray-500 dark:text-gray-400',
                        ]"
                    >
                        {{ formData.description.length }}/500
                    </p>
                </div>
            </div>

            <!-- Quick Templates -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-medium text-gray-900 dark:text-white">
                        Start with a template
                    </h3>
                    <button
                        @click="showTemplates = !showTemplates"
                        class="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                    >
                        {{ showTemplates ? "Hide templates" : "Show templates" }}
                    </button>
                </div>

                <!-- Template Cards -->
                <div v-if="showTemplates" class="space-y-3">
                    <button
                        v-for="(template, index) in descriptionTemplates"
                        :key="index"
                        @click="selectDescriptionTemplate(template)"
                        class="w-full p-4 text-left bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:border-gray-900 dark:hover:border-white hover:bg-gray-50 dark:hover:bg-gray-700 transition-all group"
                    >
                        <div class="flex items-start justify-between mb-2">
                            <h4
                                class="text-sm font-semibold text-gray-900 dark:text-white"
                            >
                                {{ template.title }}
                            </h4>
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
                        <p
                            class="text-sm text-gray-600 dark:text-gray-400 line-clamp-3"
                        >
                            {{ template.content }}
                        </p>
                    </button>
                </div>
            </div>

            <!-- Writing Tips -->
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
                    Tips for a great description
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
                        <span>Describe the atmosphere and vibe of your space</span>
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
                        <span>Mention what's special about the location</span>
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
                        <span>Highlight unique features or amenities</span>
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
                        <span>Include nearby attractions or transportation</span>
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
                        <span>Be honest and set clear expectations</span>
                    </li>
                </ul>
            </div>

            <!-- Structure Guide -->
            <div
                class="p-6 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800"
            >
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">
                    Suggested structure
                </h4>
                <div class="space-y-4">
                    <div>
                        <h5 class="text-sm font-medium text-gray-900 dark:text-white mb-1">
                            1. The Space
                        </h5>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Describe the rooms, layout, and overall feel of your place.
                        </p>
                    </div>
                    <div>
                        <h5 class="text-sm font-medium text-gray-900 dark:text-white mb-1">
                            2. Guest Access
                        </h5>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Explain which areas guests can use (entire place, private room, etc.).
                        </p>
                    </div>
                    <div>
                        <h5 class="text-sm font-medium text-gray-900 dark:text-white mb-1">
                            3. The Neighborhood
                        </h5>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Share what makes the area special and mention nearby spots.
                        </p>
                    </div>
                    <div>
                        <h5 class="text-sm font-medium text-gray-900 dark:text-white mb-1">
                            4. Getting Around
                        </h5>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Note transportation options and parking information.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Word Count Helper -->
            <div
                class="flex items-center justify-between p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg"
            >
                <div class="flex items-center space-x-2">
                    <svg
                        class="w-5 h-5 text-gray-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                        />
                    </svg>
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        Word count:
                        <strong class="text-gray-900 dark:text-white">{{
                            wordCount
                        }}</strong>
                    </span>
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    Recommended: 50-200 words
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { mapState } from "vuex";

export default {
    name: "Step8Description",
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
    data() {
        return {
            showTemplates: false,
        };
    },
    computed: {
        ...mapState("hosting/createAccommodation", ["accommodationTypes"]),

        propertyTypeName() {
            if (!this.formData.propertyType) return "place";
            const type = this.accommodationTypes.find(
                (t) => t.id === this.formData.propertyType
            );
            return type ? type.name.toLowerCase() : "place";
        },

        wordCount() {
            if (!this.formData.description) return 0;
            return this.formData.description
                .trim()
                .split(/\s+/)
                .filter((word) => word.length > 0).length;
        },

        descriptionTemplates() {
            const city = this.formData.address.city || "the city";
            const type = this.propertyTypeName;
            const guests = this.formData.floorPlan.guests;
            const bedrooms = this.formData.floorPlan.bedrooms;
            const beds = this.formData.floorPlan.beds;

            // Get key amenities
            const hasWifi = this.formData.amenities.includes("wifi");
            const hasKitchen = this.formData.amenities.includes("kitchen");
            const hasParking =
                this.formData.amenities.includes("free-parking") ||
                this.formData.amenities.includes("paid-parking");
            const hasAC = this.formData.amenities.includes("air-conditioning");

            const amenitiesText = [];
            if (hasWifi) amenitiesText.push("high-speed WiFi");
            if (hasKitchen) amenitiesText.push("a fully equipped kitchen");
            if (hasParking) amenitiesText.push("free parking");
            if (hasAC) amenitiesText.push("air conditioning");

            const amenitiesList =
                amenitiesText.length > 0
                    ? amenitiesText.join(", ")
                    : "modern amenities";

            return [
                {
                    title: "Welcoming & Detailed",
                    content: `Welcome to our beautiful ${type} in ${city}! This charming space comfortably accommodates ${guests} guests with ${bedrooms} bedroom${
                        bedrooms !== 1 ? "s" : ""
                    } and ${beds} bed${
                        beds !== 1 ? "s" : ""
                    }. Enjoy ${amenitiesList} during your stay. The space is perfect for families, couples, or solo travelers looking for a comfortable and convenient base to explore the area. The neighborhood is vibrant yet peaceful, with restaurants, cafes, and shops just a short walk away. Public transportation is easily accessible, making it simple to get around the city.`,
                },
                {
                    title: "Short & Sweet",
                    content: `Cozy ${type} in the heart of ${city}. Perfect for ${guests} guests with all the essentials you need for a comfortable stay. Features ${amenitiesList}. Great location with easy access to local attractions and public transport. Looking forward to hosting you!`,
                },
                {
                    title: "Luxury Focused",
                    content: `Experience comfort and style in this thoughtfully designed ${type}. Located in ${city}, our space offers ${guests} guests a perfect blend of modern amenities and local charm. Featuring ${amenitiesList}, every detail has been carefully considered to ensure your stay is exceptional. The space boasts elegant interiors, quality furnishings, and a prime location that puts you close to the best the area has to offer.`,
                },
                {
                    title: "Family Friendly",
                    content: `Looking for a family-friendly ${type} in ${city}? You've found it! Our spacious accommodation sleeps ${guests} guests comfortably, with ${bedrooms} bedroom${
                        bedrooms !== 1 ? "s" : ""
                    } providing plenty of space for everyone. Kids will love the area, and parents will appreciate ${amenitiesList}. We're located in a safe, quiet neighborhood that's still close to all the action. Restaurants, parks, and family attractions are all nearby.`,
                },
                {
                    title: "Business Traveler",
                    content: `Ideal ${type} for business travelers visiting ${city}. This professional space accommodates ${guests} guest${
                        guests !== 1 ? "s" : ""
                    } and includes ${amenitiesList}, perfect for both work and relaxation. The location offers easy access to the business district and major transportation hubs. After a productive day, unwind in your comfortable, quiet retreat. Fast check-in and responsive hosting to support your busy schedule.`,
                },
            ];
        },
    },
    methods: {
        updateDescription(value) {
            this.$emit("update:form-data", {
                ...this.formData,
                description: value,
            });
        },

        selectDescriptionTemplate(template) {
            this.updateDescription(template.content);
            this.showTemplates = false;

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
