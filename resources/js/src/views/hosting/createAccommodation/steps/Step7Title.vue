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
            <!-- Language tabs -->
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
                            v-if="getTitleForLocale(locale.code)"
                            class="w-1.5 h-1.5 rounded-full flex-shrink-0"
                            :class="activeLocale === locale.code ? 'bg-green-400' : 'bg-green-500'"
                        ></span>
                    </button>
                </div>

                <!-- Active locale textarea -->
                <BaseTextarea
                    :key="activeLocale"
                    :model-value="getTitleForLocale(activeLocale)"
                    :label="activeLabelText"
                    :rows="3"
                    :maxlength="50"
                    :placeholder="$t('placeholder')"
                    @update:model-value="updateLocaleTitle(activeLocale, $event)"
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
                        :disabled="isTranslating || !primaryTitle"
                        class="flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        @click="autoTranslateMissing"
                    >
                        <svg
                            class="w-4 h-4 flex-shrink-0"
                            :class="{ 'animate-spin': isTranslating }"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        {{ isTranslating ? $t('translating') : $t('auto_translate_missing') }}
                    </button>
                    <p v-if="remainingToday === 0" class="text-xs text-amber-600 dark:text-amber-400">
                        {{ $t('limit_reached') }}
                    </p>
                </div>
                <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">{{ $t('translations_info_2') }}</p>
            </div>

            <!-- Quick Suggestions -->
            <div v-if="titleSuggestions.length > 0" class="space-y-4">
                <h3 class="text-base font-medium text-gray-900 dark:text-white">
                    {{ $t('inspiration_heading') }}
                </h3>
                <div class="space-y-3">
                    <action-card
                        v-for="(suggestion, index) in titleSuggestions"
                        :key="index"
                        :title="suggestion"
                        @click="selectTitleSuggestion(suggestion)"
                    />
                </div>
            </div>

            <!-- Tips -->
            <div class="p-6 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-primary-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                    {{ $t('tips_heading') }}
                </h4>
                <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                    <li v-for="tip in ['tip1','tip2','tip3','tip4']" :key="tip" class="flex items-start">
                        <svg
                            class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0"
                            :class="tip === 'tip4' ? 'text-red-500' : 'text-green-500'"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path v-if="tip !== 'tip4'" fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            <path v-else fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ $t(tip) }}</span>
                    </li>
                </ul>
            </div>

            <!-- Examples -->
            <div class="p-6 bg-primary-50 dark:bg-primary-900/20 rounded-xl border border-primary-200 dark:border-primary-800">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">
                    {{ $t('examples_heading', { type: accommodationTypeName }) }}
                </h4>
                <div class="space-y-2">
                    <p
                        v-for="(example, index) in titleExamples"
                        :key="index"
                        class="text-sm text-gray-700 dark:text-gray-300 flex items-center"
                    >
                        <svg class="w-4 h-4 mr-2 text-primary-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                        "{{ example }}"
                    </p>
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
    data() {
        return {
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
            return `${this.$t('label_title')} · ${this.activeLocaleName}`;
        },

        otherLocales() {
            return SUPPORTED_LOCALES.filter((l) => l.code !== this.primaryLocale);
        },

        primaryTitle() {
            const t = this.formData.title;
            if (typeof t === "object" && t !== null) {
                return t[this.primaryLocale] || "";
            }
            return typeof t === "string" ? t : "";
        },

        accommodationTypeName() {
            if (!this.formData.accommodationType) return "place";
            const type = this.accommodationTypes.find(
                (t) => t.id === this.formData.accommodationType
            );
            return type ? type.name.toLowerCase() : "place";
        },

        titleSuggestions() {
            const city = this.formData.address.city || this.$t('default_city');
            const guests = this.formData.floorPlan.guests;
            const bedrooms = this.formData.floorPlan.bedrooms;
            const type = this.accommodationTypeName;
            const suggestions = [
                this.$t('suggestion1', { type, city }),
                this.$t('suggestion2', { bedrooms, type, guests }),
                this.$t('suggestion3', { type }),
                this.$t('suggestion4', { type, city }),
            ];
            if (this.formData.amenities.includes("wifi")) {
                suggestions.push(this.$t('suggestion_wifi', { type, city }));
            }
            if (this.formData.amenities.includes("pool")) {
                suggestions.push(this.$t('suggestion_pool', { type }));
            }
            return suggestions.slice(0, 5);
        },

        titleExamples() {
            const key = `examples_${this.formData.accommodationType}`;
            const examples = this.$tm(key);
            if (!examples || !examples.length) {
                return this.$tm('examples_default').map((d) => this.$rt(d));
            }
            return examples.map((d) => this.$rt(d));
        },
    },
    methods: {
        ...mapActions("hosting/createAccommodation", ["translateText"]),

        getTitleForLocale(locale) {
            const t = this.formData.title;
            if (typeof t === "object" && t !== null) {
                return t[locale] || "";
            }
            if (locale === this.primaryLocale) {
                return typeof t === "string" ? t : "";
            }
            return "";
        },

        ensureTitleObject() {
            const t = this.formData.title;
            if (typeof t === "object" && t !== null) {
                return { ...t };
            }
            // Migrate from legacy string
            const obj = {};
            SUPPORTED_LOCALES.forEach((l) => (obj[l.code] = ""));
            if (typeof t === "string" && t) {
                obj[this.primaryLocale] = t;
            }
            return obj;
        },

        updatePrimaryTitle(value) {
            const titleObj = this.ensureTitleObject();
            titleObj[this.primaryLocale] = value;
            this.$emit("update:form-data", { ...this.formData, title: titleObj });
        },

        updateLocaleTitle(locale, value) {
            const titleObj = this.ensureTitleObject();
            titleObj[locale] = value;
            this.$emit("update:form-data", { ...this.formData, title: titleObj });
        },

        selectTitleSuggestion(suggestion) {
            this.updatePrimaryTitle(suggestion);
            this.$nextTick(() => {
                const textarea = document.querySelector("textarea");
                if (textarea) {
                    textarea.focus();
                    textarea.scrollIntoView({ behavior: "smooth", block: "center" });
                }
            });
        },

        async autoTranslateMissing() {
            if (!this.primaryTitle || this.isTranslating) return;

            this.isTranslating = true;
            const titleObj = this.ensureTitleObject();

            for (const locale of this.otherLocales) {
                if (titleObj[locale.code]) continue; // skip non-empty

                try {
                    const result = await this.translateText({
                        text: this.primaryTitle,
                        targetLocale: locale.code,
                    });
                    titleObj[locale.code] = result.translated_text;
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

            this.$emit("update:form-data", { ...this.formData, title: titleObj });
            this.isTranslating = false;
        },
    },
};
</script>

<i18n lang="yaml">
en:
  heading: Give your accommodation a title
  subtitle: Short titles work best. Have fun with it — you can always change it later.
  label_title: Title
  placeholder: e.g. Cozy apartment in the city center
  primary_locale_hint: "your default title · {locale}"
  translation_hint: "translation · {locale}"
  translations_info_2: if a translation is missing, guests will see your default title.
  limit_reached: You've used all auto-translations for today. Try again tomorrow.
  auto_translate_missing: auto-translate missing
  translating: translating…
  inspiration_heading: Need some inspiration?
  tips_heading: Tips for a great title
  tip1: Highlight what makes your place unique
  tip2: Mention the neighborhood or nearby attractions
  tip3: Keep it short and memorable (under 50 characters)
  tip4: Avoid generic phrases like "nice place" or "great location"
  examples_heading: Popular {type} titles
  default_city: the city
  suggestion1: "Charming {type} in {city}"
  suggestion2: "{bedrooms}BR {type} perfect for {guests} guests"
  suggestion3: "Cozy {type} with modern amenities"
  suggestion4: "Beautiful {type} near downtown {city}"
  suggestion_wifi: "{type} with high-speed WiFi in {city}"
  suggestion_pool: "Stunning {type} with pool access"
  examples_house:
    - Charming 3BR house with garden views
    - Modern family home near downtown
    - Cozy cottage with mountain backdrop
  examples_apartment:
    - Stylish loft in the heart of the city
    - Bright 2BR apartment with balcony
    - Downtown studio with city views
  examples_villa:
    - Luxury villa with private pool
    - Stunning beachfront villa retreat
    - Elegant villa in peaceful surroundings
  examples_cabin:
    - Rustic cabin nestled in the woods
    - Mountain cabin with fireplace
    - Secluded lakeside cabin getaway
  examples_default:
    - Beautiful place in prime location
    - Comfortable stay with great amenities
    - Perfect spot for your next trip
sr:
  heading: Dajte svom smestaju naslov
  subtitle: Kratki naslovi rade najbolje. Zabavite se — uvek ga možete promeniti kasnije.
  label_title: Naslov
  placeholder: npr. Udoban apartman u centru grada
  primary_locale_hint: "vaš podrazumevani naslov · {locale}"
  translation_hint: "prevod · {locale}"
  translations_info_2: ako prevod nedostaje, gosti će videti vaš podrazumevani naslov.
  limit_reached: Iskoristili ste sve automatske prevode za danas. Pokušajte ponovo sutra.
  auto_translate_missing: automatski prevedi prazna polja
  translating: prevođenje…
  inspiration_heading: Treba vam inspiracija?
  tips_heading: Saveti za odličan naslov
  tip1: Istaknite šta vaš smeštaj čini jedinstvenim
  tip2: Pomenite kvart ili obližnje atrakcije
  tip3: Neka bude kratak i pamtljiv (ispod 50 znakova)
  tip4: Izbegavajte generičke fraze poput "lepo mesto" ili "odlična lokacija"
  examples_heading: Popularni naslovi za {type}
  default_city: grad
  suggestion1: "Šarmantan {type} u {city}"
  suggestion2: "{bedrooms}-sobni {type} savršen za {guests} gostiju"
  suggestion3: "Udoban {type} sa modernim sadržajima"
  suggestion4: "Lep {type} blizu centra {city}"
  suggestion_wifi: "{type} sa brzim WiFi-jem u {city}"
  suggestion_pool: "Predivan {type} sa bazenom"
  examples_house:
    - Šarmantna kuća sa 3 spavaće sobe i pogledom na baštu
    - Moderna porodična kuća blizu centra
    - Udobna kućica sa pogledom na planinu
  examples_apartment:
    - Stilski loft u srcu grada
    - Svetao stan sa 2 spavaće sobe i balkonom
    - Studio u centru sa pogledom na grad
  examples_villa:
    - Luksuzna vila sa privatnim bazenom
    - Predivna vila uz plažu
    - Elegantna vila u mirnom okruženju
  examples_cabin:
    - Rustikalna koliba usred šume
    - Planinska koliba sa kaminom
    - Usamljena koliba kraj jezera
  examples_default:
    - Lep smeštaj na odličnoj lokaciji
    - Udoban boravak sa sjajnim sadržajima
    - Savršeno mesto za vaš sledeći put
hr:
  heading: Dodijelite naslov svom smještaju
  subtitle: Kratki naslovi su najbolji. Uživajte — uvijek ga možete promijeniti kasnije.
  label_title: Naslov
  placeholder: npr. Ugodan apartman u centru grada
  primary_locale_hint: "vaš zadani naslov · {locale}"
  translation_hint: "prijevod · {locale}"
  translations_info_2: ako prijevod nedostaje, gosti će vidjeti vaš zadani naslov.
  limit_reached: Iskoristili ste sve automatske prijevode za danas. Pokušajte opet sutra.
  auto_translate_missing: automatski prevedi prazna polja
  translating: prevođenje…
  inspiration_heading: Trebate inspiraciju?
  tips_heading: Savjeti za odličan naslov
  tip1: Istaknite što vaš smještaj čini jedinstvenim
  tip2: Spominjite četvrt ili obližnje atrakcije
  tip3: Neka bude kratak i pamtljiv (ispod 50 znakova)
  tip4: Izbjegavajte generičke fraze poput "lijepo mjesto" ili "odlična lokacija"
  examples_heading: Popularni naslovi za {type}
  default_city: grad
  suggestion1: "Šarmantan {type} u {city}"
  suggestion2: "{bedrooms}-sobni {type} savršen za {guests} gostiju"
  suggestion3: "Ugodan {type} s modernim sadržajima"
  suggestion4: "Lijep {type} blizu središta {city}"
  suggestion_wifi: "{type} s brzim WiFi-jem u {city}"
  suggestion_pool: "Predivan {type} s bazenom"
  examples_house:
    - Šarmantna kuća s 3 spavaće sobe i pogledom na vrt
    - Moderna obiteljska kuća blizu središta
    - Udobna kućica s pogledom na planinu
  examples_apartment:
    - Stilski loft u srcu grada
    - Svijetao stan s 2 spavaće sobe i balkonom
    - Studio u centru s pogledom na grad
  examples_villa:
    - Luksuzna vila s privatnim bazenom
    - Predivna vila uz plažu
    - Elegantna vila u mirnom okruženju
  examples_cabin:
    - Rustikalna koliba usred šume
    - Planinska koliba s kaminom
    - Usamljena koliba kraj jezera
  examples_default:
    - Lijepo mjesto na odličnoj lokaciji
    - Ugodan boravak s odličnim sadržajima
    - Savršeno mjesto za vaš sljedeći put
mk:
  heading: Дајте му наслов на вашето сместување
  subtitle: Кратките наслови се најдобри. Забавете се — секогаш можете да го промените подоцна.
  label_title: Наслов
  placeholder: пр. Удобен стан во центарот на градот
  primary_locale_hint: "вашиот стандарден наслов · {locale}"
  translation_hint: "превод · {locale}"
  translations_info_2: ако преводот недостасува, гостите ќе го видат вашиот стандарден наслов.
  limit_reached: Ги искористивте сите автоматски преводи за денес. Обидете се повторно утре.
  auto_translate_missing: автоматски преведи празни полиња
  translating: превод во тек…
  inspiration_heading: Ви треба инспирација?
  tips_heading: Совети за одличен наслов
  tip1: Истакнете го она што го прави вашиот простор единствен
  tip2: Споменете го кварталот или блиските атракции
  tip3: Нека биде краток и паметлив (под 50 знаци)
  tip4: Избегнувајте генерички фрази како "убаво место" или "одлична локација"
  examples_heading: Популарни наслови за {type}
  default_city: градот
  suggestion1: "Шармантен {type} во {city}"
  suggestion2: "{bedrooms}-собен {type} совршен за {guests} гости"
  suggestion3: "Удобен {type} со модерни содржини"
  suggestion4: "Убав {type} блиску до центарот на {city}"
  suggestion_wifi: "{type} со брз WiFi во {city}"
  suggestion_pool: "Прекрасен {type} со базен"
  examples_house:
    - Шармантна куќа со 3 спални и поглед на градина
    - Модерна семејна куќа блиску до центарот
    - Удобна викендичка со поглед на планината
  examples_apartment:
    - Стилски лофт во срцето на градот
    - Светол стан со 2 спални и балкон
    - Студио во центарот со поглед на градот
  examples_villa:
    - Луксузна вила со приватен базен
    - Предивна вила на плажа
    - Елегантна вила во мирна средина
  examples_cabin:
    - Рустична колиба среде шума
    - Планинска колиба со камин
    - Осамена колиба крај езеро
  examples_default:
    - Убав простор на одлична локација
    - Удобен престој со одлични содржини
    - Совршено место за вашето следно патување
sl:
  heading: Poimenujte svojo namestitev
  subtitle: Kratki naslovi so najboljši. Zabavite se — vedno ga lahko pozneje spremenite.
  label_title: Naslov
  placeholder: npr. Udobno stanovanje v središču mesta
  primary_locale_hint: "vaš privzeti naslov · {locale}"
  translation_hint: "prevod · {locale}"
  translations_info_2: če prevod manjka, bodo gostje videli vaš privzeti naslov.
  limit_reached: Porabili ste vse samodejne prevode za danes. Poskusite znova jutri.
  auto_translate_missing: samodejno prevedi prazna polja
  translating: prevajanje…
  inspiration_heading: Potrebujete navdih?
  tips_heading: Nasveti za odličen naslov
  tip1: Poudarite, kaj naredi vaš prostor edinstvenega
  tip2: Omenite sosesko ali bližnje znamenitosti
  tip3: Naj bo kratek in pamtljiv (manj kot 50 znakov)
  tip4: Izogibajte se splošnim frazam, kot so "lepo mesto" ali "odlična lokacija"
  examples_heading: Priljubljeni naslovi za {type}
  default_city: mesta
  suggestion1: "Čudovit {type} v {city}"
  suggestion2: "{bedrooms}-sobni {type} za {guests} gostov"
  suggestion3: "Udoben {type} z modernimi ugodnostmi"
  suggestion4: "Lep {type} v bližini središča {city}"
  suggestion_wifi: "{type} s hitrim WiFi v {city}"
  suggestion_pool: "Čudovit {type} z bazenom"
  examples_house:
    - Čudovita hiša s 3 spalnicami in pogledom na vrt
    - Moderna družinska hiša v bližini središča
    - Udobna koča s pogledom na gore
  examples_apartment:
    - Stilsko podstrešno stanovanje v središču mesta
    - Svetlo stanovanje z 2 spalnicama in balkonom
    - Studio v centru s pogledom na mesto
  examples_villa:
    - Luksuzna vila z zasebnim bazenom
    - Čudovita vila ob plaži
    - Elegantna vila v mirnem okolju
  examples_cabin:
    - Rustikalna koča sredi gozda
    - Gorska koča s kaminom
    - Osamljena koča ob jezeru
  examples_default:
    - Lepo bivališče na odlični lokaciji
    - Udobno bivanje z odličnimi ugodnostmi
    - Idealno mesto za vaše naslednje potovanje
</i18n>
