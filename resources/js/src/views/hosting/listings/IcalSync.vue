<template>
    <div class="space-y-6">
        <!-- iCal Export -->
        <div
            class="p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl"
        >
            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-1">
                {{ $t('export_title') }}
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                {{ $t('export_desc') }}
            </p>

            <div class="space-y-3">
                <label class="flex items-center gap-3 cursor-pointer select-none">
                    <input
                        type="checkbox"
                        :checked="exportActive"
                        :disabled="togglingExport"
                        class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-900 cursor-pointer"
                        @change="toggleExport"
                    />
                    <span class="text-sm text-gray-700 dark:text-gray-300">
                        {{ $t('active_label') }}
                    </span>
                </label>

                <div v-if="exportActive" class="flex items-center gap-2">
                    <input
                        ref="exportUrlInput"
                        :value="exportUrl"
                        readonly
                        class="flex-1 text-sm bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl px-3 py-2 text-gray-700 dark:text-gray-300 font-mono truncate"
                    />
                    <BaseButton variant="secondary" size="sm" @click="copyUrl">
                        {{ copied ? $t('copied') : $t('copy') }}
                    </BaseButton>
                </div>
                <div v-if="exportActive" class="flex items-center gap-3">
                    <BaseButton
                        variant="secondary"
                        size="sm"
                        :disabled="regenerating"
                        @click="regenerateToken"
                    >
                        {{ regenerating ? $t('regenerating') : $t('regenerate_url') }}
                    </BaseButton>
                    <span class="text-xs text-gray-400 dark:text-gray-500">
                        {{ $t('regenerate_warning') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- External Calendars -->
        <div
            class="p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl"
        >
            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-1">
                {{ $t('import_title') }}
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                {{ $t('import_desc') }}
            </p>

            <!-- Existing calendars -->
            <div v-if="calendars.length" class="space-y-2 mb-4">
                <div
                    v-for="cal in calendars"
                    :key="cal.id"
                    class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl"
                >
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 mb-0.5">
                            <span class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ cal.name || cal.source_label }}
                            </span>
                            <span
                                class="text-xs px-2 py-0.5 rounded-full font-medium"
                                :class="cal.is_active
                                    ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                    : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400'"
                            >
                                {{ cal.is_active ? $t('status_active') : $t('status_paused') }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-400 dark:text-gray-500 truncate">
                            {{ cal.ical_url }}
                        </p>
                        <p v-if="cal.last_synced_at" class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                            {{ $t('last_synced', { date: formatDate(cal.last_synced_at) }) }}
                        </p>
                    </div>
                    <button
                        class="ml-3 text-sm text-red-500 hover:text-red-600 dark:text-red-400 dark:hover:text-red-300 font-medium flex-shrink-0"
                        @click="removeCalendar(cal)"
                    >
                        {{ $t('btn_remove') }}
                    </button>
                </div>
            </div>

            <!-- Add form -->
            <div v-if="showAddForm" class="space-y-3 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <h4 class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ $t('add_form_title') }}
                </h4>

                <div>
                    <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">{{ $t('label_platform') }}</label>
                    <select
                        v-model="newCalendar.source"
                        class="w-full text-sm bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl px-3 py-2 text-gray-700 dark:text-gray-300"
                    >
                        <option value="airbnb">Airbnb</option>
                        <option value="booking">Booking.com</option>
                        <option value="expedia">Expedia</option>
                        <option value="other">Other</option>
                    </select>
                    <p v-if="formErrors.source" class="text-xs text-red-500 mt-1">
                        {{ formErrors.source[0] }}
                    </p>
                </div>

                <div>
                    <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">{{ $t('label_name') }}</label>
                    <input
                        v-model="newCalendar.name"
                        type="text"
                        :placeholder="$t('placeholder_name')"
                        class="w-full text-sm bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl px-3 py-2 text-gray-700 dark:text-gray-300"
                    />
                </div>

                <div>
                    <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">{{ $t('label_ical_url') }}</label>
                    <input
                        v-model="newCalendar.ical_url"
                        type="url"
                        :placeholder="$t('placeholder_ical_url')"
                        class="w-full text-sm bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl px-3 py-2 text-gray-700 dark:text-gray-300"
                    />
                    <p v-if="formErrors.ical_url" class="text-xs text-red-500 mt-1">
                        {{ formErrors.ical_url[0] }}
                    </p>
                </div>

                <div class="flex gap-2">
                    <BaseButton :disabled="saving" @click="addCalendar">
                        {{ saving ? $t('saving') : $t('add_calendar') }}
                    </BaseButton>
                    <BaseButton variant="secondary" @click="cancelAdd">
                        {{ $t('btn_cancel') }}
                    </BaseButton>
                </div>
            </div>

            <BaseButton
                v-else
                variant="secondary"
                @click="showAddForm = true"
            >
                {{ $t('btn_add_calendar') }}
            </BaseButton>
        </div>
    </div>
</template>

<script>
import apiClient from "@/services/apiClient";
import config from "@/config";
import { mapActions } from "vuex";


export default {
    name: "IcalSync",

    props: {
        accommodationId: {
            type: String,
            required: true,
        },
        icalToken: {
            type: String,
            default: null,
        },
        icalExportActive: {
            type: Boolean,
            default: false,
        },
    },

    emits: ["token-updated", "export-toggled"],

    data() {
        return {
            currentToken: this.icalToken,
            exportActive: this.icalExportActive,
            togglingExport: false,
            calendars: [],
            loading: false,
            regenerating: false,
            copied: false,
            showAddForm: false,
            saving: false,
            formErrors: {},
            newCalendar: {
                source: "airbnb",
                name: "",
                ical_url: "",
            },
        };
    },

    computed: {
        exportUrl() {
            return `${window.location.origin}/api/${this.accommodationId}/ical/${this.currentToken}`;
        },
    },

    watch: {
        icalToken(newToken) {
            if (newToken) {
                this.currentToken = newToken;
            }
        },
        icalExportActive(val) {
            this.exportActive = val;
        },
    },

    async created() {
        await this.fetchCalendars();
    },

    methods: {
        ...mapActions(["openModal"]),
        ...mapActions("hosting/listings", ["updateAccommodation"]),

        async fetchCalendars() {
            try {
                const response = await apiClient.host.accommodations[this.accommodationId]["ical-calendars"].get();
                this.calendars = response.data.data ?? [];
            } catch (error) {
                console.error("Failed to fetch iCal calendars:", error);
            }
        },

        async toggleExport() {
            this.togglingExport = true;
            try {
                const newValue = !this.exportActive;
                await this.updateAccommodation({
                    accommodationId: this.accommodationId,
                    data: { ical_export_active: newValue },
                });
                this.exportActive = newValue;
                this.$emit("export-toggled", newValue);
            } catch (error) {
                console.error("Failed to toggle iCal export:", error);
            } finally {
                this.togglingExport = false;
            }
        },

        async regenerateToken() {
            const confirmed = await this.openModal({
                modalName: config.modals.confirmModal,
                options: {
                    title: this.$t('regen_modal_title'),
                    message: this.$t('regen_modal_message'),
                    confirmText: this.$t('regen_modal_confirm'),
                    cancelText: this.$t('btn_cancel'),
                    confirmColor: "red",
                },
            });
            if (!confirmed) {
                return;
            }
            this.regenerating = true;
            try {
                const response = await apiClient.host.accommodations[this.accommodationId]["ical-token"].regenerate.post();
                this.currentToken = response.data.meta?.ical_token;
                this.$emit("token-updated", this.currentToken);
            } catch (error) {
                console.error("Failed to regenerate iCal token:", error);
            } finally {
                this.regenerating = false;
            }
        },

        async addCalendar() {
            this.saving = true;
            this.formErrors = {};
            try {
                const response = await apiClient.host.accommodations[this.accommodationId]["ical-calendars"].post({
                    source: this.newCalendar.source,
                    name: this.newCalendar.name || null,
                    ical_url: this.newCalendar.ical_url,
                });
                this.calendars.push(response.data.data);
                this.cancelAdd();
            } catch (error) {
                const validationErrors = error.error?.validation_errors;
                if (validationErrors) {
                    this.formErrors = validationErrors.reduce((acc, e) => {
                        acc[e.field] = [e.message];
                        return acc;
                    }, {});
                }
            } finally {
                this.saving = false;
            }
        },

        async removeCalendar(calendar) {
            const confirmed = await this.openModal({
                modalName: config.modals.confirmModal,
                options: {
                    title: this.$t('remove_modal_title', { name: calendar.name || calendar.source_label }),
                    message: this.$t('remove_modal_message'),
                    confirmText: this.$t('remove_modal_confirm'),
                    cancelText: this.$t('btn_cancel'),
                    confirmColor: "red",
                },
            });
            if (!confirmed) {
                return;
            }
            try {
                await apiClient.host.accommodations[this.accommodationId]["ical-calendars"][calendar.id].delete();
                this.calendars = this.calendars.filter((c) => c.id !== calendar.id);
            } catch (error) {
                console.error("Failed to remove iCal calendar:", error);
            }
        },

        cancelAdd() {
            this.showAddForm = false;
            this.formErrors = {};
            this.newCalendar = { source: "airbnb", name: "", ical_url: "" };
        },

        async copyUrl() {
            try {
                await navigator.clipboard.writeText(this.exportUrl);
            } catch {
                const input = this.$refs.exportUrlInput;
                input.select();
                document.execCommand("copy");
            }
            this.copied = true;
            setTimeout(() => (this.copied = false), 2000);
        },

        formatDate(isoString) {
            if (!isoString) return "";
            return new Date(isoString).toLocaleString();
        },
    },
};
</script>

<i18n lang="yaml">
en:
  export_title: "iCal export"
  export_desc: "Share this URL with Airbnb, Booking.com, or any platform to export your calendar."
  active_label: "Active — allow external platforms to fetch this calendar"
  copied: "Copied!"
  copy: "Copy"
  regenerating: "Regenerating…"
  regenerate_url: "Regenerate URL"
  regenerate_warning: "Regenerating will break any existing subscriptions."
  import_title: "Import external calendars"
  import_desc: "Paste an iCal URL from Airbnb, Booking.com, or Expedia to block dates automatically."
  status_active: "Active"
  status_paused: "Paused"
  last_synced: "Last synced {date}"
  btn_remove: "Remove"
  add_form_title: "Add external calendar"
  label_platform: "Platform"
  label_name: "Name (optional)"
  label_ical_url: "iCal URL"
  placeholder_name: "e.g. My Airbnb listing"
  placeholder_ical_url: "https://www.airbnb.com/calendar/ical/..."
  saving: "Saving…"
  add_calendar: "Add calendar"
  btn_cancel: "Cancel"
  btn_add_calendar: "+ Add calendar"
  regen_modal_title: "Regenerate iCal URL?"
  regen_modal_message: "This will invalidate your current URL. Any platforms already subscribed (Airbnb, Booking.com, etc.) will stop receiving updates until you paste the new URL there."
  regen_modal_confirm: "Regenerate"
  remove_modal_title: "Remove \"{name}\"?"
  remove_modal_message: "Imported blocked dates from this calendar will also be removed."
  remove_modal_confirm: "Remove"
sr:
  export_title: "iCal izvoz"
  export_desc: "Podelite ovaj URL sa Airbnb-om, Booking.com-om ili bilo kojom platformom da biste izvezli vaš kalendar."
  active_label: "Aktivno — dozvoli eksternim platformama da preuzmu ovaj kalendar"
  copied: "Kopirano!"
  copy: "Kopiraj"
  regenerating: "Generisanje…"
  regenerate_url: "Regeneriši URL"
  regenerate_warning: "Regenerisanje će prekinuti sve postojeće pretplate."
  import_title: "Uvoz eksternih kalendara"
  import_desc: "Nalepite iCal URL sa Airbnb-a, Booking.com-a ili Expedia-e da biste automatski blokirali datume."
  status_active: "Aktivno"
  status_paused: "Pauzirano"
  last_synced: "Poslednja sinhronizacija {date}"
  btn_remove: "Ukloni"
  add_form_title: "Dodaj eksterni kalendar"
  label_platform: "Platforma"
  label_name: "Naziv (opciono)"
  label_ical_url: "iCal URL"
  placeholder_name: "npr. Moj Airbnb oglas"
  placeholder_ical_url: "https://www.airbnb.com/calendar/ical/..."
  saving: "Čuvanje…"
  add_calendar: "Dodaj kalendar"
  btn_cancel: "Otkaži"
  btn_add_calendar: "+ Dodaj kalendar"
  regen_modal_title: "Regenerisati iCal URL?"
  regen_modal_message: "Ovo će poništiti vaš trenutni URL. Sve platforme koje su već pretplaćene (Airbnb, Booking.com, itd.) prestaće da primaju ažuriranja dok tamo ne nalepite novi URL."
  regen_modal_confirm: "Regeneriši"
  remove_modal_title: "Ukloniti \"{name}\"?"
  remove_modal_message: "Uvezeni blokirani datumi iz ovog kalendara biće takođe uklonjeni."
  remove_modal_confirm: "Ukloni"
hr:
  export_title: "iCal izvoz"
  export_desc: "Podijelite ovaj URL s Airbnbom, Booking.com-om ili bilo kojom platformom za izvoz vašeg kalendara."
  active_label: "Aktivno — dopusti vanjskim platformama preuzimanje ovog kalendara"
  copied: "Kopirano!"
  copy: "Kopiraj"
  regenerating: "Generiranje…"
  regenerate_url: "Regeneriraj URL"
  regenerate_warning: "Regeneriranje će prekinuti sve postojeće pretplate."
  import_title: "Uvoz vanjskih kalendara"
  import_desc: "Zalijepite iCal URL s Airbnba, Booking.com-a ili Expedie za automatsko blokiranje datuma."
  status_active: "Aktivno"
  status_paused: "Pauzirano"
  last_synced: "Zadnja sinkronizacija {date}"
  btn_remove: "Ukloni"
  add_form_title: "Dodaj vanjski kalendar"
  label_platform: "Platforma"
  label_name: "Naziv (nije obavezno)"
  label_ical_url: "iCal URL"
  placeholder_name: "npr. Moj Airbnb oglas"
  placeholder_ical_url: "https://www.airbnb.com/calendar/ical/..."
  saving: "Spremanje…"
  add_calendar: "Dodaj kalendar"
  btn_cancel: "Odustani"
  btn_add_calendar: "+ Dodaj kalendar"
  regen_modal_title: "Regenerirati iCal URL?"
  regen_modal_message: "Ovo će poništiti vaš trenutni URL. Sve platforme koje su već pretplaćene (Airbnb, Booking.com, itd.) prestat će primati ažuriranja dok tamo ne zalijepite novi URL."
  regen_modal_confirm: "Regeneriraj"
  remove_modal_title: "Ukloniti \"{name}\"?"
  remove_modal_message: "Uvezeni blokirani datumi iz ovog kalendara bit će također uklonjeni."
  remove_modal_confirm: "Ukloni"
mk:
  export_title: "iCal извоз"
  export_desc: "Споделете го овој URL со Airbnb, Booking.com или која било платформа за извоз на вашиот календар."
  active_label: "Активно — дозволи на надворешни платформи да го преземат овој календар"
  copied: "Копирано!"
  copy: "Копирај"
  regenerating: "Генерирање…"
  regenerate_url: "Регенерирај URL"
  regenerate_warning: "Регенерирањето ќе ги прекине сите постоечки претплати."
  import_title: "Увоз на надворешни календари"
  import_desc: "Залепете iCal URL од Airbnb, Booking.com или Expedia за автоматско блокирање датуми."
  status_active: "Активно"
  status_paused: "Паузирано"
  last_synced: "Последна синхронизација {date}"
  btn_remove: "Отстрани"
  add_form_title: "Додај надворешен календар"
  label_platform: "Платформа"
  label_name: "Назив (опционо)"
  label_ical_url: "iCal URL"
  placeholder_name: "пр. Мој Airbnb оглас"
  placeholder_ical_url: "https://www.airbnb.com/calendar/ical/..."
  saving: "Зачувување…"
  add_calendar: "Додај календар"
  btn_cancel: "Откажи"
  btn_add_calendar: "+ Додај календар"
  regen_modal_title: "Регенерирај iCal URL?"
  regen_modal_message: "Ова ќе го поништи вашиот тековен URL. Сите платформи кои веќе се претплатени (Airbnb, Booking.com, итн.) ќе престанат да примаат ажурирања додека не го залепите новиот URL таму."
  regen_modal_confirm: "Регенерирај"
  remove_modal_title: "Отстрани \"{name}\"?"
  remove_modal_message: "Увезените блокирани датуми од овој календар исто така ќе бидат отстранети."
  remove_modal_confirm: "Отстрани"
sl:
  export_title: "iCal izvoz"
  export_desc: "Delite ta URL z Airbnbom, Booking.com ali katero koli platformo za izvoz vašega koledarja."
  active_label: "Aktivno — dovoli zunanjim platformam, da prenesejo ta koledar"
  copied: "Kopirano!"
  copy: "Kopiraj"
  regenerating: "Ustvarjanje…"
  regenerate_url: "Obnovi URL"
  regenerate_warning: "Obnova bo prekinila vse obstoječe naročnine."
  import_title: "Uvoz zunanjih koledarjev"
  import_desc: "Prilepite iCal URL iz Airbnba, Booking.com ali Expedie za samodejno blokiranje datumov."
  status_active: "Aktivno"
  status_paused: "Zaustavljeno"
  last_synced: "Zadnja sinhronizacija {date}"
  btn_remove: "Odstrani"
  add_form_title: "Dodaj zunanji koledar"
  label_platform: "Platforma"
  label_name: "Ime (neobvezno)"
  label_ical_url: "iCal URL"
  placeholder_name: "npr. Moj oglas na Airbnbu"
  placeholder_ical_url: "https://www.airbnb.com/calendar/ical/..."
  saving: "Shranjevanje…"
  add_calendar: "Dodaj koledar"
  btn_cancel: "Prekliči"
  btn_add_calendar: "+ Dodaj koledar"
  regen_modal_title: "Obnovi iCal URL?"
  regen_modal_message: "S tem boste razveljavili vaš trenutni URL. Vse platforme, ki so že naročene (Airbnb, Booking.com itd.), bodo prenehale prejemati posodobitve, dokler tja ne prilepite novega URL-ja."
  regen_modal_confirm: "Obnovi"
  remove_modal_title: "Odstraniti \"{name}\"?"
  remove_modal_message: "Uvoženi blokirani datumi iz tega koledarja bodo prav tako odstranjeni."
  remove_modal_confirm: "Odstrani"
</i18n>
