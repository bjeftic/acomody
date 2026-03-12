<template>
    <div class="space-y-6">
        <!-- iCal Export -->
        <div
            class="p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl"
        >
            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-1">
                iCal export
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                Share this URL with Airbnb, Booking.com, or any platform to export your calendar.
            </p>

            <div class="space-y-3">
                <label class="flex items-center gap-3 cursor-pointer select-none">
                    <input
                        type="checkbox"
                        :checked="exportActive"
                        :disabled="togglingExport"
                        class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-900 cursor-pointer"
                        @change="toggleExport"
                    />
                    <span class="text-sm text-gray-700 dark:text-gray-300">
                        Active — allow external platforms to fetch this calendar
                    </span>
                </label>

                <div v-if="exportActive" class="flex items-center gap-2">
                    <input
                        :value="exportUrl"
                        readonly
                        class="flex-1 text-sm bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-gray-700 dark:text-gray-300 font-mono truncate"
                    />
                    <fwb-button color="light" size="sm" @click="copyUrl">
                        {{ copied ? 'Copied!' : 'Copy' }}
                    </fwb-button>
                </div>
                <div v-if="exportActive" class="flex items-center gap-3">
                    <fwb-button
                        color="light"
                        size="sm"
                        :disabled="regenerating"
                        @click="regenerateToken"
                    >
                        {{ regenerating ? 'Regenerating…' : 'Regenerate URL' }}
                    </fwb-button>
                    <span class="text-xs text-gray-400 dark:text-gray-500">
                        Regenerating will break any existing subscriptions.
                    </span>
                </div>
            </div>
        </div>

        <!-- External Calendars -->
        <div
            class="p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl"
        >
            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-1">
                Import external calendars
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                Paste an iCal URL from Airbnb, Booking.com, or Expedia to block dates automatically.
            </p>

            <!-- Existing calendars -->
            <div v-if="calendars.length" class="space-y-2 mb-4">
                <div
                    v-for="cal in calendars"
                    :key="cal.id"
                    class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg"
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
                                {{ cal.is_active ? 'Active' : 'Paused' }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-400 dark:text-gray-500 truncate">
                            {{ cal.ical_url }}
                        </p>
                        <p v-if="cal.last_synced_at" class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                            Last synced {{ formatDate(cal.last_synced_at) }}
                        </p>
                    </div>
                    <button
                        class="ml-3 text-sm text-red-500 hover:text-red-600 dark:text-red-400 dark:hover:text-red-300 font-medium flex-shrink-0"
                        @click="removeCalendar(cal)"
                    >
                        Remove
                    </button>
                </div>
            </div>

            <!-- Add form -->
            <div v-if="showAddForm" class="space-y-3 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <h4 class="text-sm font-medium text-gray-900 dark:text-white">
                    Add external calendar
                </h4>

                <div>
                    <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Platform</label>
                    <select
                        v-model="newCalendar.source"
                        class="w-full text-sm bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-gray-700 dark:text-gray-300"
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
                    <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Name (optional)</label>
                    <input
                        v-model="newCalendar.name"
                        type="text"
                        placeholder="e.g. My Airbnb listing"
                        class="w-full text-sm bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-gray-700 dark:text-gray-300"
                    />
                </div>

                <div>
                    <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">iCal URL</label>
                    <input
                        v-model="newCalendar.ical_url"
                        type="url"
                        placeholder="https://www.airbnb.com/calendar/ical/..."
                        class="w-full text-sm bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-gray-700 dark:text-gray-300"
                    />
                    <p v-if="formErrors.ical_url" class="text-xs text-red-500 mt-1">
                        {{ formErrors.ical_url[0] }}
                    </p>
                </div>

                <div class="flex gap-2">
                    <fwb-button :disabled="saving" @click="addCalendar">
                        {{ saving ? 'Saving…' : 'Add calendar' }}
                    </fwb-button>
                    <fwb-button color="light" @click="cancelAdd">
                        Cancel
                    </fwb-button>
                </div>
            </div>

            <fwb-button
                v-else
                color="light"
                @click="showAddForm = true"
            >
                + Add calendar
            </fwb-button>
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
                    title: "Regenerate iCal URL?",
                    message: "This will invalidate your current URL. Any platforms already subscribed (Airbnb, Booking.com, etc.) will stop receiving updates until you paste the new URL there.",
                    confirmText: "Regenerate",
                    cancelText: "Cancel",
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
                    title: `Remove "${calendar.name || calendar.source_label}"?`,
                    message: "Imported blocked dates from this calendar will also be removed.",
                    confirmText: "Remove",
                    cancelText: "Cancel",
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
                this.copied = true;
                setTimeout(() => (this.copied = false), 2000);
            } catch {
                // fallback: select input manually
            }
        },

        formatDate(isoString) {
            if (!isoString) return "";
            return new Date(isoString).toLocaleString();
        },
    },
};
</script>
