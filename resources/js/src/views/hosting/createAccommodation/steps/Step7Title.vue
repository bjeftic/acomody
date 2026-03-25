<template>
    <div>
        <h1 class="text-3xl font-semibold text-gray-900 dark:text-white mb-2">
            {{ $t('heading', { type: accommodationTypeName }) }}
        </h1>
        <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">
            {{ $t('subtitle') }}
        </p>

        <hr />

        <div class="max-w-2xl mx-auto overflow-auto py-4 pr-4 h-[60vh] space-y-6">
            <!-- Title Input -->
            <BaseTextarea
                :model-value="formData.title"
                :label="$t('label_title')"
                :rows="3"
                :maxlength="50"
                :placeholder="$t('placeholder')"
                @update:model-value="updateTitle"
            />

            <!-- Quick Suggestions -->
            <div v-if="titleSuggestions.length > 0" class="space-y-4">
                <h3 class="text-base font-medium text-gray-900 dark:text-white">
                    {{ $t('inspiration_heading') }}
                </h3>

                <!-- Suggestion Cards -->
                <div class="space-y-3">
                    <action-card v-for="(suggestion, index) in titleSuggestions" :key="index" :title="suggestion" @click="selectTitleSuggestion(suggestion)" />
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
                        class="w-5 h-5 mr-2 text-primary-500"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    {{ $t('tips_heading') }}
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
                        <span>{{ $t('tip1') }}</span>
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
                        <span>{{ $t('tip2') }}</span>
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
                        <span>{{ $t('tip3') }}</span>
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
                        <span>{{ $t('tip4') }}</span>
                    </li>
                </ul>
            </div>

            <!-- Examples based on accommodation type -->
            <div
                class="p-6 bg-primary-50 dark:bg-primary-900/20 rounded-xl border border-primary-200 dark:border-primary-800"
            >
                <h4
                    class="text-sm font-semibold text-gray-900 dark:text-white mb-3"
                >
                    {{ $t('examples_heading', { type: accommodationTypeName }) }}
                </h4>
                <div class="space-y-2">
                    <p
                        v-for="(example, index) in titleExamples"
                        :key="index"
                        class="text-sm text-gray-700 dark:text-gray-300 flex items-center"
                    >
                        <svg
                            class="w-4 h-4 mr-2 text-primary-500 flex-shrink-0"
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

        accommodationTypeName() {
            if (!this.formData.accommodationType) return "place";
            const type = this.accommodationTypes.find(
                (t) => t.id === this.formData.accommodationType
            );
            return type ? type.name.toLowerCase() : "place";
        },

        titleSuggestions() {
            const city = this.formData.address.city || "the city";
            const guests = this.formData.floorPlan.guests;
            const bedrooms = this.formData.floorPlan.bedrooms;
            const type = this.accommodationTypeName;

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
                examples[this.formData.accommodationType] || examples["default"]
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

<i18n lang="yaml">
en:
  heading: Give your {type} a title
  subtitle: Short titles work best. Have fun with it — you can always change it later.
  label_title: Title
  placeholder: e.g. Cozy apartment in the city center
  inspiration_heading: Need some inspiration?
  tips_heading: Tips for a great title
  tip1: Highlight what makes your place unique
  tip2: Mention the neighborhood or nearby attractions
  tip3: Keep it short and memorable (under 50 characters)
  tip4: Avoid generic phrases like "nice place" or "great location"
  examples_heading: Popular {type} titles
sr:
  heading: Dajte svom {type} naslov
  subtitle: Kratki naslovi rade najbolje. Zabavite se — uvek ga možete promeniti kasnije.
  label_title: Naslov
  placeholder: npr. Udoban apartman u centru grada
  inspiration_heading: Treba vam inspiracija?
  tips_heading: Saveti za odličan naslov
  tip1: Istaknite šta vaš smeštaj čini jedinstvenim
  tip2: Pomenite kvart ili obližnje atrakcije
  tip3: Neka bude kratak i pamtljiv (ispod 50 znakova)
  tip4: Izbegavajte generičke fraze poput "lepo mesto" ili "odlična lokacija"
  examples_heading: Popularni naslovi za {type}
hr:
  heading: Dajte svom {type} naslov
  subtitle: Kratki naslovi su najbolji. Uživajte — uvijek ga možete promijeniti kasnije.
  label_title: Naslov
  placeholder: npr. Ugodan apartman u centru grada
  inspiration_heading: Trebate inspiraciju?
  tips_heading: Savjeti za odličan naslov
  tip1: Istaknite što vaš smještaj čini jedinstvenim
  tip2: Spominjite četvrt ili obližnje atrakcije
  tip3: Neka bude kratak i pamtljiv (ispod 50 znakova)
  tip4: Izbjegavajte generičke fraze poput "lijepo mjesto" ili "odlična lokacija"
  examples_heading: Popularni naslovi za {type}
mk:
  heading: Дајте му наслов на вашиот {type}
  subtitle: Кратките наслови се најдобри. Забавете се — секогаш можете да го промените подоцна.
  label_title: Наслов
  placeholder: пр. Удобен стан во центарот на градот
  inspiration_heading: Ви треба инспирација?
  tips_heading: Совети за одличен наслов
  tip1: Истакнете го она што го прави вашиот простор единствен
  tip2: Споменете го кварталот или блиските атракции
  tip3: Нека биде краток и паметлив (под 50 знаци)
  tip4: Избегнувајте генерички фрази како "убаво место" или "одлична локација"
  examples_heading: Популарни наслови за {type}
sl:
  heading: Dajte svojemu {type} naslov
  subtitle: Kratki naslovi so najboljši. Zabavite se — vedno ga lahko pozneje spremenite.
  label_title: Naslov
  placeholder: npr. Udobno stanovanje v središču mesta
  inspiration_heading: Potrebujete navdih?
  tips_heading: Nasveti za odličen naslov
  tip1: Poudarite, kaj naredi vaš prostor edinstvenega
  tip2: Omenite sosesko ali bližnje znamenitosti
  tip3: Naj bo kratek in pamtljiv (manj kot 50 znakov)
  tip4: Izogibajte se splošnim frazam, kot so "lepo mesto" ali "odlična lokacija"
  examples_heading: Priljubljeni naslovi za {type}
</i18n>
