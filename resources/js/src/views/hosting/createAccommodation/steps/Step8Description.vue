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
            <!-- Language tabs + textarea -->
            <div>
                <!-- Tab bar -->
                <div class="flex flex-wrap gap-2 mb-4">
                    <button
                        v-for="locale in allLocales"
                        :key="locale.code"
                        type="button"
                        class="relative flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-lg border transition-colors"
                        :class="activeLocale === locale.code
                            ? 'bg-gray-900 dark:bg-white text-white dark:text-gray-900 border-gray-900 dark:border-white'
                            : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-400'"
                        @click="activeLocale = locale.code"
                    >
                        {{ locale.code.toUpperCase() }}
                        <span
                            v-if="locale.code === primaryLocale"
                            class="text-xs opacity-60"
                        >★</span>
                        <span
                            v-if="getDescriptionForLocale(locale.code)"
                            class="w-1.5 h-1.5 rounded-full flex-shrink-0"
                            :class="activeLocale === locale.code ? 'bg-green-400' : 'bg-green-500'"
                        ></span>
                    </button>
                </div>

                <!-- Active locale textarea -->
                <BaseTextarea
                    :key="activeLocale"
                    :model-value="getDescriptionForLocale(activeLocale)"
                    :label="activeLabelText"
                    :rows="10"
                    :maxlength="500"
                    :placeholder="$t('placeholder')"
                    @update:model-value="updateLocaleDescription(activeLocale, $event)"
                />
                <p class="mt-1.5 text-xs text-gray-400 dark:text-gray-500">
                    <template v-if="activeLocale === primaryLocale">
                        {{ $t('primary_locale_hint', { locale: activeLocaleName }) }}
                    </template>
                    <template v-else>
                        {{ $t('translation_hint', { locale: activeLocaleName }) }}
                    </template>
                </p>

                <!-- Auto-translate bar -->
                <div class="mt-4 flex flex-col sm:flex-row sm:items-center gap-3">
                    <button
                        type="button"
                        :disabled="isTranslating || !primaryDescription"
                        class="flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        @click="translateNow"
                    >
                        <svg
                            class="w-4 h-4 flex-shrink-0"
                            :class="{ 'animate-spin': isTranslating }"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                            />
                        </svg>
                        {{ isTranslating ? $t('translating') : $t('translate_now') }}
                    </button>
                    <p v-if="remainingToday === 0" class="text-xs text-amber-600 dark:text-amber-400">
                        {{ $t('limit_reached') }}
                    </p>
                </div>
                <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">{{ $t('auto_translate_info_2') }}</p>
            </div>

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

                <div v-if="showTemplates" class="space-y-3">
                    <button
                        v-for="(template, index) in descriptionTemplates"
                        :key="index"
                        @click="selectDescriptionTemplate(template)"
                        class="w-full p-4 text-left bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:border-gray-900 dark:hover:border-white hover:bg-gray-50 dark:hover:bg-gray-700 transition-all group"
                    >
                        <div class="flex items-start justify-between mb-2">
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white">
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
                        <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-3">
                            {{ template.content }}
                        </p>
                    </button>
                </div>
            </div>

            <!-- Writing Tips -->
            <div class="p-6 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
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
                    <li
                        v-for="tip in ['tip1','tip2','tip3','tip4','tip5']"
                        :key="tip"
                        class="flex items-start"
                    >
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
                        <span>{{ $t(tip) }}</span>
                    </li>
                </ul>
            </div>

            <!-- Structure Guide -->
            <div class="p-6 bg-primary-50 dark:bg-primary-900/20 rounded-xl border border-primary-200 dark:border-primary-800">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">
                    {{ $t('structure_heading') }}
                </h4>
                <div class="space-y-4">
                    <div v-for="n in [1,2,3,4]" :key="n">
                        <h5 class="text-sm font-medium text-gray-900 dark:text-white mb-1">
                            {{ $t('section' + n + '_title') }}
                        </h5>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ $t('section' + n + '_desc') }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Word Count -->
            <div class="flex items-center justify-between p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg">
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
                        <strong class="text-gray-900 dark:text-white">{{ wordCount }}</strong>
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
import { mapActions, mapState } from "vuex";
import runtimeConstants from "@/runtime-constants";

const SUPPORTED_LOCALES = runtimeConstants.supportedLocales || [];

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
            activeLocale: this.$i18n.locale || "en",
            isTranslating: false,
            remainingToday: null,
        };
    },
    computed: {
        ...mapState("hosting/createAccommodation", ["accommodationTypes"]),

        primaryLocale() {
            return this.$i18n.locale || "en";
        },

        primaryLocaleName() {
            return SUPPORTED_LOCALES.find((l) => l.code === this.primaryLocale)?.name || this.primaryLocale;
        },

        allLocales() {
            const primary = SUPPORTED_LOCALES.find((l) => l.code === this.primaryLocale);
            const others = SUPPORTED_LOCALES.filter((l) => l.code !== this.primaryLocale);
            return primary ? [primary, ...others] : SUPPORTED_LOCALES;
        },

        activeLocaleName() {
            return SUPPORTED_LOCALES.find((l) => l.code === this.activeLocale)?.name || this.activeLocale;
        },

        activeLabelText() {
            return `${this.$t('label_description')} · ${this.activeLocaleName}`;
        },

        otherLocales() {
            return SUPPORTED_LOCALES.filter((l) => l.code !== this.primaryLocale);
        },

        primaryDescription() {
            const d = this.formData.description;
            if (typeof d === "object" && d !== null) {
                return d[this.primaryLocale] || "";
            }
            return typeof d === "string" ? d : "";
        },

        accommodationTypeName() {
            if (!this.formData.accommodationType) return "place";
            const type = this.accommodationTypes.find(
                (t) => t.id === this.formData.accommodationType
            );
            return type ? type.name.toLowerCase() : "place";
        },

        wordCount() {
            if (!this.primaryDescription) return 0;
            return this.primaryDescription
                .trim()
                .split(/\s+/)
                .filter((word) => word.length > 0).length;
        },

        descriptionTemplates() {
            const city = this.formData.address.city || this.$t('default_city');
            const type = this.accommodationTypeName;
            const guests = this.formData.floorPlan.guests;
            const bedrooms = this.formData.floorPlan.bedrooms;
            const beds = this.formData.floorPlan.beds;

            const amenitiesArr = [];
            if (this.formData.amenities.includes("wifi")) amenitiesArr.push(this.$t('amenity_wifi'));
            if (this.formData.amenities.includes("kitchen")) amenitiesArr.push(this.$t('amenity_kitchen'));
            if (this.formData.amenities.includes("free-parking") || this.formData.amenities.includes("paid-parking")) amenitiesArr.push(this.$t('amenity_parking'));
            if (this.formData.amenities.includes("air-conditioning")) amenitiesArr.push(this.$t('amenity_ac'));
            const amenities = amenitiesArr.length > 0 ? amenitiesArr.join(', ') : this.$t('amenity_default');

            const bedroomsLabel = bedrooms === 1 ? this.$t('bedroom_single') : this.$t('bedrooms_many', { n: bedrooms });
            const bedsLabel = beds === 1 ? this.$t('bed_single') : this.$t('beds_many', { n: beds });
            const guestsLabel = guests === 1 ? this.$t('guest_single') : this.$t('guests_many', { n: guests });

            return [
                {
                    title: this.$t('template1_title'),
                    content: this.$t('template1_content', { type, city, guests, bedroomsLabel, bedsLabel, amenities }),
                },
                {
                    title: this.$t('template2_title'),
                    content: this.$t('template2_content', { type, city, guests, amenities }),
                },
                {
                    title: this.$t('template3_title'),
                    content: this.$t('template3_content', { type, city, guests, amenities }),
                },
                {
                    title: this.$t('template4_title'),
                    content: this.$t('template4_content', { type, city, guests, bedroomsLabel }),
                },
                {
                    title: this.$t('template5_title'),
                    content: this.$t('template5_content', { type, city, guestsLabel, amenities }),
                },
            ];
        },
    },
    methods: {
        ...mapActions("hosting/createAccommodation", ["translateText"]),

        getDescriptionForLocale(locale) {
            const d = this.formData.description;
            if (typeof d === "object" && d !== null) {
                return d[locale] || "";
            }
            if (locale === this.primaryLocale) {
                return typeof d === "string" ? d : "";
            }
            return "";
        },

        ensureDescriptionObject() {
            const d = this.formData.description;
            if (typeof d === "object" && d !== null) {
                return { ...d };
            }
            const obj = {};
            SUPPORTED_LOCALES.forEach((l) => (obj[l.code] = ""));
            if (typeof d === "string" && d) {
                obj[this.primaryLocale] = d;
            }
            return obj;
        },

        updatePrimaryDescription(value) {
            const descObj = this.ensureDescriptionObject();
            descObj[this.primaryLocale] = value;
            this.$emit("update:form-data", { ...this.formData, description: descObj });
        },

        updateLocaleDescription(locale, value) {
            const descObj = this.ensureDescriptionObject();
            descObj[locale] = value;
            this.$emit("update:form-data", { ...this.formData, description: descObj });
        },

        selectDescriptionTemplate(template) {
            this.updatePrimaryDescription(template.content);
            this.showTemplates = false;
            this.$nextTick(() => {
                const textarea = document.querySelector("textarea");
                if (textarea) {
                    textarea.focus();
                    textarea.scrollIntoView({ behavior: "smooth", block: "center" });
                }
            });
        },

        async translateNow() {
            if (!this.primaryDescription || this.isTranslating) return;

            this.isTranslating = true;
            const descObj = this.ensureDescriptionObject();

            for (const locale of this.otherLocales) {
                if (descObj[locale.code]) continue; // never overwrite existing

                try {
                    const result = await this.translateText({
                        text: this.primaryDescription,
                        targetLocale: locale.code,
                    });
                    descObj[locale.code] = result.translated_text;
                    if (result.remaining_today !== undefined) {
                        this.remainingToday = result.remaining_today;
                    }
                } catch (err) {
                    if (err.response?.status === 429) {
                        this.remainingToday = 0;
                        break;
                    }
                }
            }

            this.$emit("update:form-data", { ...this.formData, description: descObj });
            this.isTranslating = false;
        },
    },
};
</script>

<i18n lang="yaml">
en:
  heading: Create your description
  subtitle: Share what makes your place special.
  label_description: Description
  placeholder: Describe your space, the neighborhood, and what guests will love about staying here...
  primary_locale_hint: "your main description · {locale}"
  translation_hint: "translation · {locale}"
  auto_translate_info_2: "only empty translations are filled — any text you've written won't be touched."
  limit_reached: You've used all auto-translations for today. Try again tomorrow.
  translate_now: translate to all languages now
  translating: translating…
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
  default_city: the city
  amenity_wifi: high-speed WiFi
  amenity_kitchen: a fully equipped kitchen
  amenity_parking: free parking
  amenity_ac: air conditioning
  amenity_default: modern amenities
  bedroom_single: "1 bedroom"
  bedrooms_many: "{n} bedrooms"
  bed_single: "1 bed"
  beds_many: "{n} beds"
  guest_single: "1 guest"
  guests_many: "{n} guests"
  template1_content: "Welcome to our beautiful {type} in {city}! This charming space comfortably accommodates {guests} guests with {bedroomsLabel} and {bedsLabel}. Enjoy {amenities} during your stay. The space is perfect for families, couples, or solo travelers looking for a comfortable and convenient base to explore the area."
  template2_content: "Cozy {type} in the heart of {city}. Perfect for {guests} guests with all the essentials for a comfortable stay. Features {amenities}. Great location with easy access to local attractions and public transport."
  template3_content: "Experience comfort and style in this thoughtfully designed {type}. Located in {city}, our space offers {guests} guests a perfect blend of modern amenities and local charm. Featuring {amenities}, every detail has been carefully considered to ensure your stay is exceptional."
  template4_content: "Looking for a family-friendly {type} in {city}? You've found it! Our spacious accommodation sleeps {guests} guests comfortably, with {bedroomsLabel} providing plenty of space for everyone. We're located in a safe, quiet neighborhood with restaurants, parks, and family attractions nearby."
  template5_content: "Ideal {type} for business travelers visiting {city}. This professional space accommodates {guestsLabel} and includes {amenities}, perfect for both work and relaxation. Fast check-in and responsive hosting to support your busy schedule."
sr:
  heading: Napišite opis
  subtitle: Podelite šta vaše mesto čini posebnim.
  label_description: Opis
  placeholder: Opišite vaš prostor, kvart i šta će gosti voleti u boravku kod vas...
  primary_locale_hint: "vaš glavni opis · {locale}"
  translation_hint: "prevod · {locale}"
  auto_translate_info_2: "popunjavaju se samo prazna polja — tekst koji ste već uneli neće biti promenjen."
  limit_reached: Iskoristili ste sve automatske prevode za danas. Pokušajte ponovo sutra.
  translate_now: prevedi na sve jezike odmah
  translating: prevođenje…
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
  default_city: grad
  amenity_wifi: brzi WiFi
  amenity_kitchen: potpuno opremljena kuhinja
  amenity_parking: besplatno parkiranje
  amenity_ac: klima uređaj
  amenity_default: moderni sadržaji
  bedroom_single: "1 spavaća soba"
  bedrooms_many: "{n} spavaće sobe"
  bed_single: "1 krevet"
  beds_many: "{n} kreveta"
  guest_single: "1 gost"
  guests_many: "{n} gostiju"
  template1_content: "Dobrodošli u naš lep {type} u {city}! Ovaj šarmantni prostor udobno prima {guests} gostiju sa {bedroomsLabel} i {bedsLabel}. Uživajte u {amenities} tokom boravka. Prostor je savršen za porodice, parove ili solo putnike koji traže udobnu i praktičnu bazu za istraživanje okoline."
  template2_content: "Udoban {type} u srcu {city}. Savršeno za {guests} gostiju sa svim neophodnim za udoban boravak. Sadrži {amenities}. Odlična lokacija sa lakim pristupom lokalnim atrakcijama i javnom prevozu."
  template3_content: "Doživite udobnost i stil u ovom pažljivo osmišljenom {type}. Smešten u {city}, naš prostor pruža {guests} gostiju savršen spoj modernih sadržaja i lokalnog šarma. Sa {amenities}, svaki detalj je pažljivo osmišljen kako bi vaš boravak bio izvanredan."
  template4_content: "Tražite {type} pogodan za porodicu u {city}? Pronašli ste ga! Naš prostrani smeštaj prima {guests} gostiju udobno, sa {bedroomsLabel} koji pružaju dovoljno prostora za sve. Nalazimo se u bezbednom, mirnom kvartu sa restoranima, parkovima i porodičnim atrakcijama u blizini."
  template5_content: "Idealan {type} za poslovne putnike koji posećuju {city}. Ovaj profesionalni prostor prima {guestsLabel} i uključuje {amenities}, savršeno za rad i opuštanje. Brz čekin i ažuran domaćin za vaš zauzet raspored."
hr:
  heading: Napišite opis
  subtitle: Podijelite što vaše mjesto čini posebnim.
  label_description: Opis
  placeholder: Opišite vaš prostor, kvart i što će gosti voljeti u boravku kod vas...
  primary_locale_hint: "vaš glavni opis · {locale}"
  translation_hint: "prijevod · {locale}"
  auto_translate_info_2: "popunjavaju se samo prazna polja — tekst koji ste već unijeli neće biti promijenjen."
  limit_reached: Iskoristili ste sve automatske prijevode za danas. Pokušajte opet sutra.
  translate_now: prevedi na sve jezike odmah
  translating: prevođenje…
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
  default_city: grad
  amenity_wifi: brzi WiFi
  amenity_kitchen: potpuno opremljena kuhinja
  amenity_parking: besplatno parkiranje
  amenity_ac: klima uređaj
  amenity_default: moderni sadržaji
  bedroom_single: "1 spavaća soba"
  bedrooms_many: "{n} spavaće sobe"
  bed_single: "1 krevet"
  beds_many: "{n} kreveta"
  guest_single: "1 gost"
  guests_many: "{n} gostiju"
  template1_content: "Dobrodošli u naš lijepi {type} u {city}! Ovaj šarmantni prostor ugodno prima {guests} gostiju s {bedroomsLabel} i {bedsLabel}. Uživajte u {amenities} tijekom boravka. Prostor je savršen za obitelji, parove ili solo putnike koji traže ugodnu i praktičnu bazu za istraživanje okolice."
  template2_content: "Ugodan {type} u srcu {city}. Savršeno za {guests} gostiju sa svim potrebnim za ugodan boravak. Uključuje {amenities}. Odlična lokacija s lakim pristupom lokalnim atrakcijama i javnom prijevozu."
  template3_content: "Doživite ugodu i stil u ovom promišljeno osmišljenom {type}. Smješten u {city}, naš prostor pruža {guests} gostiju savršenu kombinaciju modernih sadržaja i lokalnog šarma. S {amenities}, svaki detalj pažljivo je osmišljen kako bi vaš boravak bio izniman."
  template4_content: "Tražite {type} pogodan za obitelj u {city}? Pronašli ste ga! Naš prostrani smještaj prima {guests} gostiju ugodno, s {bedroomsLabel} koji pružaju dovoljno prostora za sve. Nalazimo se u sigurnoj, tihoj četvrti s restoranima, parkovima i obiteljskim atrakcijama u blizini."
  template5_content: "Idealan {type} za poslovne putnike koji posjećuju {city}. Ovaj profesionalni prostor prima {guestsLabel} i uključuje {amenities}, savršeno za rad i opuštanje. Brzi check-in i ažurni domaćin za vaš zauzet raspored."
mk:
  heading: Напишете опис
  subtitle: Споделете го она што го прави вашето место посебно.
  label_description: Опис
  placeholder: Опишете го вашиот простор, кварталот и она што гостите ќе го сакаат во престојот кај вас...
  primary_locale_hint: "вашиот главен опис · {locale}"
  translation_hint: "превод · {locale}"
  auto_translate_info_2: "се пополнуваат само празните полиња — текстот што веќе сте го внеле нема да биде сменет."
  limit_reached: Ги искористивте сите автоматски преводи за денес. Обидете се повторно утре.
  translate_now: преведи на сите јазици сега
  translating: превод во тек…
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
  default_city: градот
  amenity_wifi: брз WiFi
  amenity_kitchen: целосно опремена кујна
  amenity_parking: бесплатно паркирање
  amenity_ac: клима уред
  amenity_default: модерни содржини
  bedroom_single: "1 спална соба"
  bedrooms_many: "{n} спални соби"
  bed_single: "1 кревет"
  beds_many: "{n} кревети"
  guest_single: "1 гостин"
  guests_many: "{n} гости"
  template1_content: "Добредојдовте во нашиот убав {type} во {city}! Овој шармантен простор удобно прима {guests} гости со {bedroomsLabel} и {bedsLabel}. Уживајте во {amenities} за време на престојот. Просторот е совршен за семејства, парови или соло патници кои бараат удобна и практична база за истражување на околината."
  template2_content: "Удобен {type} во срцето на {city}. Совршено за {guests} гости со сè што е потребно за удобен престој. Вклучува {amenities}. Одлична локација со лесен пристап до локалните атракции и јавниот превоз."
  template3_content: "Доживејте удобност и стил во овој внимателно дизајниран {type}. Сместен во {city}, нашиот простор им нуди на {guests} гости совршена мешавина на модерни содржини и локален шарм. Со {amenities}, секој детал е внимателно осмислен за да биде вашиот престој исклучителен."
  template4_content: "Барате {type} погоден за семејство во {city}? Го најдовте! Нашиот просторен сместувачки простор прима {guests} гости удобно, со {bedroomsLabel} кои обезбедуваат доволно простор за сите. Се наоѓаме во безбеден, тивок кварт со ресторани, паркови и семејни атракции во близина."
  template5_content: "Идеален {type} за деловни патници кои ја посетуваат {city}. Овој професионален простор прима {guestsLabel} и вклучува {amenities}, совршено за работа и одмор. Брза пријава и одговорен домаќин за вашиот зафатен распоред."
sl:
  heading: Napišite opis
  subtitle: Delite, kaj naredi vaše mesto posebno.
  label_description: Opis
  placeholder: Opišite vaš prostor, sosesko in kaj bodo gostje imeli radi pri bivanju pri vas...
  primary_locale_hint: "vaš glavni opis · {locale}"
  translation_hint: "prevod · {locale}"
  auto_translate_info_2: "izpolnijo se samo prazna polja — besedilo, ki ste ga že vnesli, ne bo spremenjeno."
  limit_reached: Porabili ste vse samodejne prevode za danes. Poskusite znova jutri.
  translate_now: prevedi v vse jezike zdaj
  translating: prevajanje…
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
  default_city: mesta
  amenity_wifi: hiter WiFi
  amenity_kitchen: popolnoma opremljena kuhinja
  amenity_parking: brezplačno parkiranje
  amenity_ac: klimatska naprava
  amenity_default: moderne ugodnosti
  bedroom_single: "1 spalnica"
  bedrooms_many: "{n} spalnice"
  bed_single: "1 postelja"
  beds_many: "{n} postelje"
  guest_single: "1 gost"
  guests_many: "{n} gostov"
  template1_content: "Dobrodošli v naš lep {type} v {city}! Ta šarmantni prostor udobno sprejme {guests} gostov z {bedroomsLabel} in {bedsLabel}. Uživajte v {amenities} med bivanjem. Prostor je popoln za družine, pare ali posamezne popotnike, ki iščejo udobno in priročno izhodišče za odkrivanje okolice."
  template2_content: "Udoben {type} v srcu {city}. Popolno za {guests} gostov z vsem potrebnim za udobno bivanje. Vključuje {amenities}. Odlična lokacija z enostavnim dostopom do lokalnih znamenitosti in javnega prevoza."
  template3_content: "Doživite udobnost in slog v tem premišljeno zasnovanem {type}. Nahaja se v {city}, naš prostor ponuja {guests} gostom popolno kombinacijo modernih ugodnosti in lokalnega šarma. Z {amenities} je bila vsaka podrobnost skrbno premišljena, da bo vaše bivanje izjemno."
  template4_content: "Iščete {type}, primeren za družine v {city}? Našli ste ga! Naše prostorno nastanitev udobno sprejme {guests} gostov, z {bedroomsLabel}, ki zagotavlja dovolj prostora za vse. Nahajamo se v varni, mirni soseski z restavracijami, parki in družinskimi atrakcijami v bližini."
  template5_content: "Idealen {type} za poslovne potnike, ki obiskujejo {city}. Ta profesionalni prostor sprejme {guestsLabel} in vključuje {amenities}, popolno za delo in sprostitev. Hiter odhod in odziven gostitelj za vaš zaseden urnik."
</i18n>
