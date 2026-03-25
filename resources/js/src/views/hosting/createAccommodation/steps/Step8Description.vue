<template>
    <div>
        <h1 class="text-3xl font-semibold text-gray-900 dark:text-white mb-2">
            {{ $t('heading') }}
        </h1>
        <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">
            {{ $t('subtitle') }}
        </p>

        <hr />

        <div class="max-w-2xl mx-auto py-4 pr-4 overflow-auto h-[60vh] space-y-6">
            <!-- Description Textarea -->
            <BaseTextarea
                :model-value="formData.description"
                :label="$t('label_description') + ' ' + formData.title"
                :rows="10"
                :maxlength="500"
                :placeholder="$t('placeholder')"
                @update:model-value="updateDescription"
            />

            <!-- Quick Templates -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-medium text-gray-900 dark:text-white">
                        {{ $t('templates_heading') }}
                    </h3>
                    <button
                        @click="showTemplates = !showTemplates"
                        class="text-sm font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300"
                    >
                        {{ showTemplates ? $t('hide_templates') : $t('show_templates') }}
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
                        <span>{{ $t('tip4') }}</span>
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
                        <span>{{ $t('tip5') }}</span>
                    </li>
                </ul>
            </div>

            <!-- Structure Guide -->
            <div
                class="p-6 bg-primary-50 dark:bg-primary-900/20 rounded-xl border border-primary-200 dark:border-primary-800"
            >
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">
                    {{ $t('structure_heading') }}
                </h4>
                <div class="space-y-4">
                    <div>
                        <h5 class="text-sm font-medium text-gray-900 dark:text-white mb-1">
                            {{ $t('section1_title') }}
                        </h5>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ $t('section1_desc') }}
                        </p>
                    </div>
                    <div>
                        <h5 class="text-sm font-medium text-gray-900 dark:text-white mb-1">
                            {{ $t('section2_title') }}
                        </h5>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ $t('section2_desc') }}
                        </p>
                    </div>
                    <div>
                        <h5 class="text-sm font-medium text-gray-900 dark:text-white mb-1">
                            {{ $t('section3_title') }}
                        </h5>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ $t('section3_desc') }}
                        </p>
                    </div>
                    <div>
                        <h5 class="text-sm font-medium text-gray-900 dark:text-white mb-1">
                            {{ $t('section4_title') }}
                        </h5>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ $t('section4_desc') }}
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
                        {{ $t('word_count') }}
                        <strong class="text-gray-900 dark:text-white">{{
                            wordCount
                        }}</strong>
                    </span>
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $t('recommended') }}
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

        accommodationTypeName() {
            if (!this.formData.accommodationType) return "place";
            const type = this.accommodationTypes.find(
                (t) => t.id === this.formData.accommodationType
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
            const type = this.accommodationTypeName;
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
                    title: this.$t('template1_title'),
                    content: `Welcome to our beautiful ${type} in ${city}! This charming space comfortably accommodates ${guests} guests with ${bedrooms} bedroom${
                        bedrooms !== 1 ? "s" : ""
                    } and ${beds} bed${
                        beds !== 1 ? "s" : ""
                    }. Enjoy ${amenitiesList} during your stay. The space is perfect for families, couples, or solo travelers looking for a comfortable and convenient base to explore the area. The neighborhood is vibrant yet peaceful, with restaurants, cafes, and shops just a short walk away. Public transportation is easily accessible, making it simple to get around the city.`,
                },
                {
                    title: this.$t('template2_title'),
                    content: `Cozy ${type} in the heart of ${city}. Perfect for ${guests} guests with all the essentials you need for a comfortable stay. Features ${amenitiesList}. Great location with easy access to local attractions and public transport. Looking forward to hosting you!`,
                },
                {
                    title: this.$t('template3_title'),
                    content: `Experience comfort and style in this thoughtfully designed ${type}. Located in ${city}, our space offers ${guests} guests a perfect blend of modern amenities and local charm. Featuring ${amenitiesList}, every detail has been carefully considered to ensure your stay is exceptional. The space boasts elegant interiors, quality furnishings, and a prime location that puts you close to the best the area has to offer.`,
                },
                {
                    title: this.$t('template4_title'),
                    content: `Looking for a family-friendly ${type} in ${city}? You've found it! Our spacious accommodation sleeps ${guests} guests comfortably, with ${bedrooms} bedroom${
                        bedrooms !== 1 ? "s" : ""
                    } providing plenty of space for everyone. Kids will love the area, and parents will appreciate ${amenitiesList}. We're located in a safe, quiet neighborhood that's still close to all the action. Restaurants, parks, and family attractions are all nearby.`,
                },
                {
                    title: this.$t('template5_title'),
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

<i18n lang="yaml">
en:
  heading: Create your description
  subtitle: Share what makes your place special.
  label_description: Description of your
  placeholder: Describe your space, the neighborhood, and what guests will love about staying here...
  templates_heading: Start with a template
  show_templates: Show templates
  hide_templates: Hide templates
  tips_heading: Tips for a great description
  tip1: Describe the atmosphere and vibe of your space
  tip2: Mention what's special about the location
  tip3: Highlight unique features or amenities
  tip4: Include nearby attractions or transportation
  tip5: Be honest and set clear expectations
  structure_heading: Suggested structure
  section1_title: "1. The Space"
  section1_desc: Describe the rooms, layout, and overall feel of your place.
  section2_title: "2. Guest Access"
  section2_desc: Explain which areas guests can use (entire place, private room, etc.).
  section3_title: "3. The Neighborhood"
  section3_desc: Share what makes the area special and mention nearby spots.
  section4_title: "4. Getting Around"
  section4_desc: Note transportation options and parking information.
  word_count: "Word count:"
  recommended: "Recommended: 50-200 words"
  template1_title: Welcoming & Detailed
  template2_title: Short & Sweet
  template3_title: Luxury Focused
  template4_title: Family Friendly
  template5_title: Business Traveler
sr:
  heading: Napišite opis
  subtitle: Podelite šta vaše mesto čini posebnim.
  label_description: Opis vašeg
  placeholder: Opišite vaš prostor, kvart i šta će gosti voleti u boravku kod vas...
  templates_heading: Počnite s predlogom
  show_templates: Prikaži predloge
  hide_templates: Sakrij predloge
  tips_heading: Saveti za odličan opis
  tip1: Opišite atmosferu i osećaj vašeg prostora
  tip2: Pomenite šta je posebno u pogledu lokacije
  tip3: Istaknite jedinstvene karakteristike ili sadržaje
  tip4: Uključite obližnje atrakcije ili prevoz
  tip5: Budite iskreni i postavite jasna očekivanja
  structure_heading: Predložena struktura
  section1_title: "1. Prostor"
  section1_desc: Opišite sobe, raspored i opšti izgled vašeg mesta.
  section2_title: "2. Pristup gostima"
  section2_desc: Objasnite koje prostorije gosti mogu koristiti (celo mesto, privatna soba, itd.).
  section3_title: "3. Kvart"
  section3_desc: Podelite šta čini područje posebnim i pomenite obližnja mesta.
  section4_title: "4. Kretanje"
  section4_desc: Navedite mogućnosti prevoza i informacije o parkiranju.
  word_count: "Broj reči:"
  recommended: "Preporučeno: 50-200 reči"
  template1_title: Dobrodošlica i detalji
  template2_title: Kratko i jasno
  template3_title: Luksuzno usmereno
  template4_title: Prilagođeno porodici
  template5_title: Poslovni putnik
hr:
  heading: Napišite opis
  subtitle: Podijelite što vaše mjesto čini posebnim.
  label_description: Opis vašeg
  placeholder: Opišite vaš prostor, kvart i što će gosti voljeti u boravku kod vas...
  templates_heading: Počnite s predloškom
  show_templates: Prikaži predloške
  hide_templates: Sakrij predloške
  tips_heading: Savjeti za odličan opis
  tip1: Opišite atmosferu i ugođaj vašeg prostora
  tip2: Spomenite što je posebno u pogledu lokacije
  tip3: Istaknite jedinstvene karakteristike ili sadržaje
  tip4: Uključite obližnje atrakcije ili prijevoz
  tip5: Budite iskreni i postavite jasna očekivanja
  structure_heading: Predložena struktura
  section1_title: "1. Prostor"
  section1_desc: Opišite sobe, raspored i opći dojam vašeg mjesta.
  section2_title: "2. Pristup gostima"
  section2_desc: Objasnite koje prostorije gosti mogu koristiti (cijelo mjesto, privatna soba, itd.).
  section3_title: "3. Kvart"
  section3_desc: Podijelite što čini područje posebnim i spominjite obližnja mjesta.
  section4_title: "4. Kretanje"
  section4_desc: Navedite mogućnosti prijevoza i informacije o parkiranju.
  word_count: "Broj riječi:"
  recommended: "Preporučeno: 50-200 riječi"
  template1_title: Dobrodošlica i detalji
  template2_title: Kratko i jasno
  template3_title: Luksuzno usmjereno
  template4_title: Prilagođeno obitelji
  template5_title: Poslovni putnik
mk:
  heading: Напишете опис
  subtitle: Споделете го она што го прави вашето место посебно.
  label_description: Опис на вашиот
  placeholder: Опишете го вашиот простор, кварталот и она што гостите ќе го сакаат во престојот кај вас...
  templates_heading: Почнете со предлошка
  show_templates: Прикажи предлошки
  hide_templates: Сокриј предлошки
  tips_heading: Совети за одличен опис
  tip1: Опишете ја атмосферата и угодноста на вашиот простор
  tip2: Споменете го она што е посебно во поглед на локацијата
  tip3: Истакнете ги единствените карактеристики или содржини
  tip4: Вклучете блиски атракции или превоз
  tip5: Бидете искрени и поставете јасни очекувања
  structure_heading: Предложена структура
  section1_title: "1. Просторот"
  section1_desc: Опишете ги собите, распоредот и општиот впечаток на вашето место.
  section2_title: "2. Пристап за гостите"
  section2_desc: Објаснете кои простории гостите можат да ги користат (цело место, приватна соба, итн.).
  section3_title: "3. Кварталот"
  section3_desc: Споделете го она што го прави подрачјето посебно и споменете блиски места.
  section4_title: "4. Движење"
  section4_desc: Наведете опции за превоз и информации за паркирање.
  word_count: "Број зборови:"
  recommended: "Препорачано: 50-200 зборови"
  template1_title: Добредојде и детали
  template2_title: Кратко и јасно
  template3_title: Луксузно насочено
  template4_title: Прилагодено за семејство
  template5_title: Деловен патник
sl:
  heading: Napišite opis
  subtitle: Delite, kaj naredi vaše mesto posebno.
  label_description: Opis vašega
  placeholder: Opišite vaš prostor, sosesko in kaj bodo gostje imeli radi pri bivanju pri vas...
  templates_heading: Začnite s predlogo
  show_templates: Prikaži predloge
  hide_templates: Skrij predloge
  tips_heading: Nasveti za odličen opis
  tip1: Opišite vzdušje in počutje vašega prostora
  tip2: Omenite, kaj je posebnega glede lokacije
  tip3: Poudarite edinstvene značilnosti ali amenitete
  tip4: Vključite bližnje atrakcije ali prevoz
  tip5: Bodite iskreni in postavite jasna pričakovanja
  structure_heading: Predlagana struktura
  section1_title: "1. Prostor"
  section1_desc: Opišite sobe, razpored in splošni vtis vašega mesta.
  section2_title: "2. Dostop za goste"
  section2_desc: Pojasnite, katere prostore lahko gostje uporabljajo (celotno mesto, zasebna soba itd.).
  section3_title: "3. Soseska"
  section3_desc: Delite, kaj naredi območje posebno, in omenite bližnja mesta.
  section4_title: "4. Gibanje"
  section4_desc: Navedite možnosti prevoza in informacije o parkiranju.
  word_count: "Število besed:"
  recommended: "Priporočeno: 50-200 besed"
  template1_title: Dobrodošlica in podrobnosti
  template2_title: Kratko in jasno
  template3_title: Luksuzno usmerjeno
  template4_title: Primerno za družine
  template5_title: Poslovni potnik
</i18n>
