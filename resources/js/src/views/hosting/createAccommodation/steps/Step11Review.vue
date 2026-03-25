<template>
    <div>
        <h1 class="text-3xl font-semibold text-gray-900 dark:text-white mb-2">
            {{ $t('heading') }}
        </h1>
        <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">
            {{ $t('subtitle') }}
        </p>

        <hr />

        <div class="max-w-4xl mx-auto py-4 space-y-6">
            <!-- Listing Preview Card -->
            <div
                class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden"
            >
                <!-- Photos Preview -->
                <div class="relative aspect-video bg-gray-100 dark:bg-gray-900">
                    <img
                        v-if="formData.photos.length > 0"
                        :src="formData.photos[0].urls.large"
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
                            {{ $t('photos_count', { count: formData.photos.length }) }}
                        </span>
                    </div>
                </div>

                <!-- Listing Details -->
                <div class="p-6">
                    <!-- Title & Location -->
                    <div class="mb-4">
                        <h2
                            class="text-2xl font-semibold text-gray-900 dark:text-white mb-2"
                        >
                            {{ formData.title || $t('untitled') }}
                        </h2>
                        <p class="text-base text-gray-600 dark:text-gray-400">
                            {{ formData.address.city }},
                            {{ formData.address.country }}
                        </p>
                    </div>

                    <!-- Quick Stats -->
                    <div
                        class="flex flex-wrap items-center gap-4 mb-4 pb-4 border-b border-gray-200 dark:border-gray-700"
                    >
                        <div class="flex items-center space-x-2">
                            <svg
                                class="w-5 h-5 text-gray-600 dark:text-gray-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                />
                            </svg>
                            <span
                                class="text-sm text-gray-700 dark:text-gray-300"
                            >
                                {{ $t('stat_guests', { count: formData.floorPlan.guests }) }}
                            </span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <svg
                                class="w-5 h-5 text-gray-600 dark:text-gray-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                                />
                            </svg>
                            <span
                                class="text-sm text-gray-700 dark:text-gray-300"
                            >
                                {{ $t('stat_bedrooms', { count: formData.floorPlan.bedrooms }) }}
                            </span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <svg
                                class="w-5 h-5 text-gray-600 dark:text-gray-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                                />
                            </svg>
                            <span
                                class="text-sm text-gray-700 dark:text-gray-300"
                            >
                                {{ $t('stat_beds', { count: totalBeds }) }}
                            </span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <svg
                                class="w-5 h-5 text-gray-600 dark:text-gray-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"
                                />
                            </svg>
                            <span
                                class="text-sm text-gray-700 dark:text-gray-300"
                            >
                                {{ $t('stat_bathrooms', { count: formData.floorPlan.bathrooms }) }}
                            </span>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <p
                            class="text-sm text-gray-700 dark:text-gray-300 line-clamp-3"
                        >
                            {{ formData.description || $t('no_description') }}
                        </p>
                    </div>

                    <!-- Price -->
                    <div
                        class="pt-4 border-t border-gray-200 dark:border-gray-700"
                    >
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
                        <p
                            v-if="formData.pricing.hasWeekendPrice"
                            class="text-sm text-gray-600 dark:text-gray-400 mt-1"
                        >
                            {{ $t('weekend_prefix') }} ${{ formData.pricing.weekendPrice }} {{ $t('per_night') }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Editable Sections -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Property Info -->
                <edit-section :title="$t('section_property')" @edit="goToStep(1)">
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">{{ $t('field_type') }}</span>
                            <span class="text-gray-900 dark:text-white font-medium">
                                {{ accommodationTypeName }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">{{ $t('field_occupation') }}</span>
                            <span class="text-gray-900 dark:text-white font-medium">
                                {{ occupationTypeName }}
                            </span>
                        </div>
                    </div>
                </edit-section>

                <!-- Location -->
                <edit-section :title="$t('section_location')" @edit="goToStep(3)">
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

                <!-- Amenities -->
                <edit-section :title="$t('section_amenities')" @edit="goToStep(5)">
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        {{ $t('amenities_count', { count: formData.amenities.length }) }}
                    </p>
                </edit-section>

                <!-- House Rules -->
                <edit-section :title="$t('section_house_rules')" @edit="goToStep(10)">
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">{{ $t('field_checkin') }}</span>
                            <span class="text-gray-900 dark:text-white font-medium">
                                {{ formData.houseRules.checkInFrom }} -
                                {{ formData.houseRules.checkInUntil }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">{{ $t('field_checkout') }}</span>
                            <span class="text-gray-900 dark:text-white font-medium">
                                {{ formData.houseRules.checkOutUntil }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">{{ $t('field_cancellation') }}</span>
                            <span class="text-gray-900 dark:text-white font-medium capitalize">
                                {{ formData.houseRules.cancellationPolicy }}
                            </span>
                        </div>
                    </div>
                </edit-section>
            </div>

            <!-- Legal Agreement -->
            <div
                class="p-6 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl"
            >
                <div class="flex items-start space-x-3">
                    <input
                        v-model="agreedToTerms"
                        type="checkbox"
                        id="terms-checkbox"
                        class="mt-1 w-4 h-4 text-primary-600 bg-gray-100 border-gray-300 rounded focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                    />
                    <label
                        for="terms-checkbox"
                        class="text-sm text-gray-700 dark:text-gray-300"
                    >
                        {{ $t('legal_agree') }}
                        <a href="#" class="text-primary-600 hover:text-primary-700 hover:underline dark:text-primary-400">
                            {{ $t('legal_terms') }}</a>,
                        <a href="#" class="text-primary-600 hover:text-primary-700 hover:underline dark:text-primary-400">
                            {{ $t('legal_cancellation') }}</a>, {{ $t('legal_and') }}
                        <a href="#" class="text-primary-600 hover:text-primary-700 hover:underline dark:text-primary-400">
                            {{ $t('legal_rules') }}</a>. {{ $t('legal_acknowledge') }}
                    </label>
                </div>
            </div>

            <!-- Publish Notice -->
            <div
                class="p-6 bg-primary-50 dark:bg-primary-900/20 rounded-xl border border-primary-200 dark:border-primary-800"
            >
                <h4
                    class="text-sm font-semibold text-gray-900 dark:text-white mb-2 flex items-center"
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
                    {{ $t('whats_next') }}
                </h4>
                <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                    <li class="flex items-start">
                        <span class="mr-2">1.</span>
                        <span>{{ $t('next1') }}</span>
                    </li>
                    <li class="flex items-start">
                        <span class="mr-2">2.</span>
                        <span>{{ $t('next2') }}</span>
                    </li>
                    <li class="flex items-start">
                        <span class="mr-2">3.</span>
                        <span>{{ $t('next3') }}</span>
                    </li>
                    <li class="flex items-start">
                        <span class="mr-2">4.</span>
                        <span>{{ $t('next4') }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script>
import { mapState } from "vuex";
import EditSection from "@/src/views/hosting/createAccommodation/components/EditSection.vue";
import { mapActions } from "vuex/dist/vuex.cjs.js";

export default {
    name: "Step11Review",
    components: {
        EditSection,
    },
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
    emits: ["update:form-data", "go-to-step"],
    data() {
        return {
            agreedToTerms: false,
        };
    },
    computed: {
        ...mapState("hosting/createAccommodation", [
            "accommodationDraftId",
            "accommodationTypes",
        ]),

        accommodationTypeName() {
            if (!this.formData.accommodationType) return "";
            const type = this.accommodationTypes.find(
                (t) => t.id === this.formData.accommodationType
            );
            return type ? type.name : "";
        },

        totalBeds() {
            return (this.formData.floorPlan.bedTypes ?? []).reduce(
                (sum, bt) => sum + (bt.quantity ?? 0),
                0
            );
        },

        occupationTypeName() {
            if (!this.formData.accommodationOccupation) return "";
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
        agreedToTerms(value) {
            this.$emit("update:form-data", {
                ...this.formData,
                agreedToTerms: value,
            });
        },
    },
    methods: {
        ...mapActions("hosting/createAccommodation", [
            "fetchPhotos",
            "goToStep",
        ]),
    },
    created() {
        this.fetchPhotos(this.accommodationDraftId);
    },
};
</script>

<i18n lang="yaml">
en:
  heading: Review your listing
  subtitle: "Here's what we'll show to guests. Make sure everything looks good!"
  no_photos: No photos uploaded
  photos_count: "{count} photos"
  untitled: Untitled listing
  no_description: No description provided
  stat_guests: "{count} guests"
  stat_bedrooms: "{count} bedrooms"
  stat_beds: "{count} beds"
  stat_bathrooms: "{count} bathrooms"
  per_night: "/ night"
  weekend_prefix: "Weekend:"
  section_property: Property info
  section_location: Location
  section_amenities: Amenities
  section_house_rules: House rules
  field_type: "Type:"
  field_occupation: "Occupation:"
  field_checkin: "Check-in:"
  field_checkout: "Check-out:"
  field_cancellation: "Cancellation:"
  amenities_count: "{count} amenities selected"
  legal_agree: I agree to the
  legal_terms: Host Terms of Service
  legal_cancellation: Cancellation Policy
  legal_and: and
  legal_rules: House Rules
  legal_acknowledge: I also acknowledge that I will comply with all applicable laws and regulations.
  whats_next: What happens next?
  next1: Your listing will be reviewed by our team (usually within 24 hours)
  next2: Once approved, it will be visible to guests
  next3: You'll receive notifications when guests inquire or book
  next4: You can edit your listing anytime from your dashboard
sr:
  heading: Pregledajte vaš oglas
  subtitle: Ovo ćemo prikazati gostima. Proverite da li sve izgleda dobro!
  no_photos: Nema učitanih fotografija
  photos_count: "{count} fotografija"
  untitled: Oglas bez naslova
  no_description: Opis nije dodan
  stat_guests: "{count} gostiju"
  stat_bedrooms: "{count} spavaćih soba"
  stat_beds: "{count} kreveta"
  stat_bathrooms: "{count} kupatila"
  per_night: "/ noć"
  weekend_prefix: "Vikend:"
  section_property: Informacije o smeštaju
  section_location: Lokacija
  section_amenities: Sadržaji
  section_house_rules: Kućni red
  field_type: "Tip:"
  field_occupation: "Tip korišćenja:"
  field_checkin: "Prijava:"
  field_checkout: "Odjava:"
  field_cancellation: "Otkazivanje:"
  amenities_count: "{count} sadržaja odabrano"
  legal_agree: Slažem se s
  legal_terms: Uslovima korišćenja za domaćine
  legal_cancellation: Politikom otkazivanja
  legal_and: i
  legal_rules: Kućnim redom
  legal_acknowledge: Takođe potvrđujem da ću se pridržavati svih primenljivih zakona i propisa.
  whats_next: Šta se dešava dalje?
  next1: Vaš oglas će pregledati naš tim (obično u roku od 24 sata)
  next2: Nakon odobravanja, biće vidljiv gostima
  next3: Primićete obaveštenja kada gosti upite ili rezervišu
  next4: Oglas možete uređivati u bilo koje vreme s vaše kontrolne table
hr:
  heading: Pregledajte vaš oglas
  subtitle: Ovo ćemo prikazati gostima. Provjerite izgleda li sve dobro!
  no_photos: Nema učitanih fotografija
  photos_count: "{count} fotografija"
  untitled: Oglas bez naslova
  no_description: Opis nije dodan
  stat_guests: "{count} gostiju"
  stat_bedrooms: "{count} spavaćih soba"
  stat_beds: "{count} kreveta"
  stat_bathrooms: "{count} kupaonica"
  per_night: "/ noć"
  weekend_prefix: "Vikend:"
  section_property: Informacije o smještaju
  section_location: Lokacija
  section_amenities: Sadržaji
  section_house_rules: Kućni red
  field_type: "Tip:"
  field_occupation: "Tip korišćenja:"
  field_checkin: "Prijava:"
  field_checkout: "Odjava:"
  field_cancellation: "Otkazivanje:"
  amenities_count: "{count} sadržaja odabrano"
  legal_agree: Slažem se s
  legal_terms: Uvjetima usluge za domaćine
  legal_cancellation: Politikom otkazivanja
  legal_and: i
  legal_rules: Kućnim redom
  legal_acknowledge: Također potvrđujem da ću se pridržavati svih primjenjivih zakona i propisa.
  whats_next: Što se događa dalje?
  next1: Vaš oglas pregledat će naš tim (obično u roku od 24 sata)
  next2: Nakon odobravanja bit će vidljiv gostima
  next3: Primit ćete obavijesti kada gosti upitaju ili rezerviraju
  next4: Oglas možete uređivati u bilo koje vrijeme s vaše nadzorne ploče
mk:
  heading: Прегледајте го вашиот оглас
  subtitle: Ова ќе им го прикажеме на гостите. Проверете дали сè изгледа добро!
  no_photos: Нема прикачени фотографии
  photos_count: "{count} фотографии"
  untitled: Оглас без наслов
  no_description: Описот не е додаден
  stat_guests: "{count} гости"
  stat_bedrooms: "{count} спални соби"
  stat_beds: "{count} кревети"
  stat_bathrooms: "{count} бањи"
  per_night: "/ ноќ"
  weekend_prefix: "Викенд:"
  section_property: Информации за сместувањето
  section_location: Локација
  section_amenities: Содржини
  section_house_rules: Правила за куќата
  field_type: "Тип:"
  field_occupation: "Тип на користење:"
  field_checkin: "Пријавување:"
  field_checkout: "Одјавување:"
  field_cancellation: "Откажување:"
  amenities_count: "{count} содржини одбрани"
  legal_agree: Се согласувам со
  legal_terms: Услови за услуга за домаќини
  legal_cancellation: Политиката за откажување
  legal_and: и
  legal_rules: Правилата за куќата
  legal_acknowledge: Исто така потврдувам дека ќе се придржувам до сите применливи закони и прописи.
  whats_next: Што се случува следно?
  next1: Вашиот оглас ќе го прегледа нашиот тим (обично во рок од 24 часа)
  next2: По одобрувањето ќе биде видлив за гостите
  next3: Ќе добивате известувања кога гостите ќе прашуваат или резервираат
  next4: Огласот можете да го уредувате во секое време од вашата контролна табла
sl:
  heading: Preglejte vaš oglas
  subtitle: To bomo pokazali gostom. Preverite, ali je vse videti dobro!
  no_photos: Ni naloženih fotografij
  photos_count: "{count} fotografij"
  untitled: Oglas brez naslova
  no_description: Opis ni dodan
  stat_guests: "{count} gostov"
  stat_bedrooms: "{count} spalnic"
  stat_beds: "{count} postelj"
  stat_bathrooms: "{count} kopalnic"
  per_night: "/ noč"
  weekend_prefix: "Vikend:"
  section_property: Informacije o nastanišču
  section_location: Lokacija
  section_amenities: Amenitete
  section_house_rules: Hišna pravila
  field_type: "Tip:"
  field_occupation: "Tip zasedenosti:"
  field_checkin: "Prijava:"
  field_checkout: "Odjava:"
  field_cancellation: "Odpoved:"
  amenities_count: "{count} amenitet izbranih"
  legal_agree: Strinjam se z
  legal_terms: Pogoji storitve za gostitelje
  legal_cancellation: Politiko odpovedi
  legal_and: in
  legal_rules: Hišnimi pravili
  legal_acknowledge: Prav tako potrjujem, da bom upošteval vse veljavne zakone in predpise.
  whats_next: Kaj se zgodi naprej?
  next1: Naša ekipa bo pregledala vaš oglas (običajno v 24 urah)
  next2: Ko bo odobren, bo viden gostom
  next3: Prejeli boste obvestila, ko bodo gostje povprašali ali rezervirali
  next4: Oglas lahko kadar koli urejate na svoji nadzorni plošči
</i18n>
