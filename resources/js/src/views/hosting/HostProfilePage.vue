<template>
    <div class="max-w-4xl mx-auto py-12 px-4">
        <div class="mb-8">
            <h1 class="text-3xl font-semibold text-gray-900 dark:text-white mb-2">
                {{ $t('page_title') }}
            </h1>
            <p class="text-base text-gray-600 dark:text-gray-400">
                {{ $t('page_desc') }}
            </p>
        </div>

        <!-- Avatar -->
        <section class="mb-8">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                {{ $t('profile_photo_title') }}
            </h2>
            <div class="flex items-center gap-6">
                <div class="relative w-24 h-24 rounded-full overflow-hidden bg-gray-200 dark:bg-gray-700 cursor-pointer flex-shrink-0"
                    @click="triggerAvatarUpload">
                    <img v-if="avatarPreview || form.avatar_url" :src="avatarPreview || form.avatar_url"
                        :alt="$t('profile_photo_title')" class="w-full h-full object-cover" />
                    <div v-else class="w-full h-full flex items-center justify-center text-gray-400 dark:text-gray-500">
                        <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z" />
                        </svg>
                    </div>
                    <div
                        class="absolute inset-0 bg-black bg-opacity-30 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $t('photo_hint') }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        {{ $t('photo_format') }}
                    </p>
                    <p v-if="avatarUploading" class="text-xs text-primary-500 mt-1">
                        {{ $t('uploading') }}
                    </p>
                    <p v-if="avatarError" class="text-xs text-red-500 mt-1">
                        {{ avatarError }}
                    </p>
                </div>
                <input ref="avatarInput" type="file" accept="image/jpeg,image/jpg,image/png,image/webp" class="hidden"
                    @change="onAvatarSelected" />
            </div>
        </section>

        <!-- Alert -->
        <div
            v-if="alert.message"
            class="mb-6 px-4 py-3 rounded-xl text-sm border"
            :class="alert.type === 'success'
                ? 'bg-primary-50 dark:bg-primary-900/20 border-primary-200 dark:border-primary-800 text-primary-700 dark:text-primary-300'
                : 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 text-red-700 dark:text-red-400'"
        >
            {{ alert.message }}
        </div>

        <!-- Form -->
        <form @submit.prevent="submit">
            <!-- Required fields -->
            <section class="mb-8">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                    {{ $t('contact_section') }}
                    <span class="text-sm font-normal text-gray-400 ml-1">{{ $t('required_note') }}</span>
                </h2>
                <div class="flex flex-col gap-4">
                    <BaseInput
                        v-model="form.display_name"
                        :label="$t('display_name_label')"
                        :placeholder="$t('display_name_placeholder')"
                        :error="errors.display_name ? errors.display_name[0] : null"
                        required
                    />
                    <div>
                        <BaseInput
                            v-model="form.contact_email"
                            type="email"
                            :label="$t('contact_email_label')"
                            :placeholder="$t('email_placeholder')"
                            :error="errors.contact_email ? errors.contact_email[0] : null"
                            required
                        />
                        <button
                            v-if="!form.contact_email && currentUser?.email"
                            type="button"
                            class="mt-1.5 text-xs text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 transition-colors"
                            @click="form.contact_email = currentUser.email"
                        >
                            {{ $t('apply_login_email') }} — {{ currentUser.email }}
                        </button>
                    </div>
                    <BaseInput
                        v-model="form.phone"
                        type="tel"
                        :label="$t('phone_label')"
                        placeholder="+381 60 000 0000"
                        :error="errors.phone ? errors.phone[0] : null"
                        required
                    />
                </div>
            </section>

            <!-- Optional fields -->
            <section class="mb-8">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                    {{ $t('business_section') }}
                </h2>
                <div class="flex flex-col gap-4">
                    <BaseInput
                        v-model="form.business_name"
                        :label="$t('business_name_label')"
                        :placeholder="$t('business_name_placeholder')"
                    />
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <BaseInput
                            v-model="form.tax_id"
                            :label="$t('tax_id_label')"
                            :placeholder="$t('optional_placeholder')"
                        />
                        <BaseInput
                            v-model="form.vat_number"
                            :label="$t('vat_number_label')"
                            :placeholder="$t('optional_placeholder')"
                        />
                    </div>
                </div>
            </section>

            <section class="mb-8">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                    {{ $t('location_section') }}
                </h2>
                <div class="flex flex-col gap-4">
                    <BaseInput
                        v-model="form.address"
                        :label="$t('address_label')"
                        :placeholder="$t('address_placeholder')"
                    />
                    <BaseInput
                        v-model="form.city"
                        :label="$t('city_label')"
                        :placeholder="$t('city_placeholder')"
                    />
                </div>
            </section>

            <section class="mb-8">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                    {{ $t('about_section') }}
                </h2>
                <BaseTextarea
                    v-model="form.bio"
                    :label="$t('bio_label')"
                    :placeholder="$t('bio_placeholder')"
                    :rows="4"
                    :maxlength="2000"
                />
            </section>

            <div class="flex items-center gap-3">
                <BaseButton type="submit" size="lg" :loading="submitting" :disabled="submitting">
                    {{ submitting ? $t('saving') : $t('save_btn') }}
                </BaseButton>
                <BaseButton
                    v-if="isEditing"
                    variant="secondary"
                    size="lg"
                    @click="$router.push({ name: 'page-dashboard' })"
                >
                    {{ $t('common.cancel') }}
                </BaseButton>
                <BaseButton
                    v-if="$route.query.next === 'listing-create'"
                    variant="link"
                    size="sm"
                    @click="$router.push({ name: 'page-listing-create' })"
                >
                    {{ $t('skip_btn') }}
                </BaseButton>
            </div>
        </form>

        <!-- Danger Zone -->
        <template v-if="isEditing && !$route.query.next">
            <hr class="border-gray-200 dark:border-gray-700 my-10" />

            <section>
                <h2 class="text-xl font-semibold text-red-600 mb-6">
                    {{ $t('danger_zone') }}
                </h2>

                <div
                    v-if="deletionAlert.message"
                    class="mb-6 px-4 py-3 rounded-xl text-sm border"
                    :class="deletionAlert.type === 'success'
                        ? 'bg-primary-50 dark:bg-primary-900/20 border-primary-200 dark:border-primary-800 text-primary-700 dark:text-primary-300'
                        : 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 text-red-700 dark:text-red-400'"
                >
                    {{ deletionAlert.message }}
                </div>

                <div class="flex flex-wrap items-start justify-between gap-4 p-4 border border-rose-200 dark:border-rose-900 rounded-xl">
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $t('delete_account_title') }}
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            {{ $t('delete_account_desc') }}
                        </p>
                    </div>
                    <BaseButton variant="danger" size="sm" @click="deletionModal = true">
                        {{ $t('request_deletion') }}
                    </BaseButton>
                </div>

                <!-- Confirmation Modal -->
                <fwb-modal v-if="deletionModal" @close="closeDeletionModal">
                    <template #header>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $t('delete_account_title') }}
                        </h3>
                    </template>
                    <template #body>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            {{ $t('delete_modal_desc') }}
                        </p>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            <i18n-t keypath="delete_confirm_prompt" tag="span">
                                <template #word>
                                    <span class="font-bold text-rose-600">DELETE</span>
                                </template>
                            </i18n-t>
                        </p>
                        <BaseInput v-model="deletionConfirmWord" :placeholder="$t('delete_confirm_placeholder')" />
                    </template>
                    <template #footer>
                        <div class="flex gap-3">
                            <BaseButton
                                variant="danger"
                                :disabled="deletionConfirmWord !== 'DELETE' || deletionRequesting"
                                :loading="deletionRequesting"
                                @click="submitDeletionRequest"
                            >
                                {{ $t('submit_deletion') }}
                            </BaseButton>
                            <BaseButton variant="secondary" @click="closeDeletionModal">
                                {{ $t('common.cancel') }}
                            </BaseButton>
                        </div>
                    </template>
                </fwb-modal>
            </section>
        </template>
    </div>
</template>

<script>
import { mapState, mapActions, mapGetters } from "vuex";
import apiClient from "@/services/apiClient"; // TODO: move profile submit/avatar calls to actions

export default {
    name: "HostProfilePage",
    data() {
        return {
            form: {
                display_name: "",
                business_name: "",
                contact_email: "",
                phone: "",
                address: "",
                city: "",
                tax_id: "",
                vat_number: "",
                bio: "",
                avatar_url: null,
            },
            errors: {},
            initializing: false,
            submitting: false,
            alert: { message: "", type: "success" },
            avatarUploading: false,
            avatarError: "",
            avatarPreview: null,
            deletionAlert: { message: "", type: "success" },
            deletionModal: false,
            deletionConfirmWord: "",
            deletionRequesting: false,
        };
    },
    computed: {
        ...mapState({ currentUser: (state) => state.user.currentUser }),
        ...mapGetters("user", ["isHost"]),
        isEditing() {
            return this.isHost;
        },
    },
    async mounted() {
        if (!this.isEditing) {
            this.initializing = true;
            try {
                await this.initializeHostProfile();
                await this.fetchUser();
            } finally {
                this.initializing = false;
            }
        }
        this.populateForm();
    },
    methods: {
        ...mapActions("user", ["fetchUser", "initializeHostProfile", "requestHostAccountDeletion"]),
        populateForm() {
            const hp = this.currentUser?.host_profile;
            if (!hp) return;
            this.form.display_name = hp.display_name || "";
            this.form.business_name = hp.business_name || "";
            this.form.contact_email = hp.contact_email || "";
            this.form.phone = hp.phone || "";
            this.form.address = hp.address || "";
            this.form.city = hp.city || "";
            this.form.tax_id = hp.tax_id || "";
            this.form.vat_number = hp.vat_number || "";
            this.form.bio = hp.bio || "";
            this.form.avatar_url = hp.avatar_url || null;
        },
        async submit() {
            this.errors = {};
            this.submitting = true;
            try {
                const payload = {
                    display_name: this.form.display_name,
                    business_name: this.form.business_name || null,
                    contact_email: this.form.contact_email,
                    phone: this.form.phone,
                    address: this.form.address || null,
                    city: this.form.city || null,
                    tax_id: this.form.tax_id || null,
                    vat_number: this.form.vat_number || null,
                    bio: this.form.bio || null,
                };

                await apiClient.host.profile.put(payload);

                await this.fetchUser();

                if (this.$route.query.next === "listing-create") {
                    this.$router.push({ name: "page-listing-create" });
                    return;
                }

                this.alert = { message: this.$t('profile_updated'), type: "success" };
            } catch (error) {
                if (error.status === 422) {
                    this.errors = error.error?.errors || {};
                } else {
                    this.alert = {
                        message: error.error?.message || this.$t('common.error'),
                        type: "danger",
                    };
                }
            } finally {
                this.submitting = false;
            }
        },
        triggerAvatarUpload() {
            if (!this.isEditing) return;
            this.$refs.avatarInput.click();
        },
        async onAvatarSelected(event) {
            const file = event.target.files[0];
            if (!file) return;

            this.avatarError = "";
            this.avatarPreview = URL.createObjectURL(file);
            this.avatarUploading = true;

            try {
                await apiClient.host.profile.avatar.upload(file, "avatar");
                await this.fetchUser();
                this.form.avatar_url = this.currentUser?.host_profile?.avatar_url || null;
            } catch {
                this.avatarError = this.$t('avatar_upload_failed');
                this.avatarPreview = null;
            } finally {
                this.avatarUploading = false;
                event.target.value = "";
            }
        },

        closeDeletionModal() {
            this.deletionModal = false;
            this.deletionConfirmWord = "";
        },

        async submitDeletionRequest() {
            this.deletionRequesting = true;
            this.deletionAlert = { message: "", type: "success" };

            try {
                await this.requestHostAccountDeletion();
                this.closeDeletionModal();
                this.deletionAlert = {
                    type: "success",
                    message: this.$t('deletion_submitted'),
                };
            } catch (error) {
                this.closeDeletionModal();
                this.deletionAlert = {
                    type: "danger",
                    message: error?.error?.message || this.$t('deletion_failed'),
                };
            } finally {
                this.deletionRequesting = false;
            }
        },
    },
};
</script>

<i18n lang="yaml">
en:
  page_title: Host Profile
  page_desc: Update your host profile information visible to guests. Display name, contact email, and phone are required before your listings appear in search.
  profile_photo_title: Profile photo
  photo_hint: Click the avatar to upload a new photo. This photo will appear on your listings.
  photo_format: "JPEG, PNG or WebP — max 5MB"
  uploading: "Uploading..."
  contact_section: Contact information
  required_note: "* required"
  display_name_label: Display name
  display_name_placeholder: Your name or business name shown to guests
  contact_email_label: Contact email
  email_placeholder: Email for guest communication
  apply_login_email: Apply login email
  phone_label: Phone
  business_section: Business details
  business_name_label: Business name
  business_name_placeholder: "Optional — if you operate as a company"
  tax_id_label: Tax ID
  vat_number_label: VAT number
  optional_placeholder: Optional
  location_section: Location
  address_label: Address
  address_placeholder: Street address (optional)
  city_label: City
  city_placeholder: City (optional)
  about_section: About you
  bio_label: Bio
  bio_placeholder: Tell guests a bit about yourself as a host (optional)
  save_btn: Save changes
  saving: "Saving..."
  skip_btn: "Skip — create accommodation first"
  danger_zone: Danger zone
  delete_account_title: Delete hosting account
  delete_account_desc: Removes your host profile and all accommodation listings. Your user account stays active.
  request_deletion: Request deletion
  delete_modal_desc: This will request the deletion of your host profile and all accommodation listings. Your user account will remain active. An admin will review and process your request.
  delete_confirm_prompt: "Type {word} to confirm:"
  delete_confirm_placeholder: Type DELETE
  submit_deletion: Submit deletion request
  profile_updated: Host profile updated successfully.
  deletion_submitted: Your deletion request has been submitted. Our team will review and process it shortly.
  deletion_failed: Failed to submit deletion request. Please try again.
  avatar_upload_failed: Failed to upload avatar. Please try again.

sr:
  page_title: Profil domaćina
  page_desc: Ažurirajte informacije o profilu domaćina vidljive gostima. Ime za prikaz, kontakt email i telefon su obavezni pre nego što vaši oglasi budu prikazani u pretrazi.
  profile_photo_title: Profilna fotografija
  photo_hint: Kliknite na avatar da biste otpremili novu fotografiju. Ova fotografija će se pojaviti na vašim oglasima.
  photo_format: "JPEG, PNG ili WebP — max 5MB"
  uploading: "Otpremanje..."
  contact_section: Kontakt informacije
  required_note: "* obavezno"
  display_name_label: Ime za prikaz
  display_name_placeholder: Vaše ime ili naziv kompanije koji se prikazuje gostima
  contact_email_label: Kontakt email
  email_placeholder: Email za komunikaciju sa gostima
  apply_login_email: Koristite email za prijavu
  phone_label: Telefon
  business_section: Poslovni podaci
  business_name_label: Naziv kompanije
  business_name_placeholder: "Opcionalno — ako poslujete kao kompanija"
  tax_id_label: PIB
  vat_number_label: PDV broj
  optional_placeholder: Opcionalno
  location_section: Lokacija
  address_label: Adresa
  address_placeholder: Ulica i broj (opcionalno)
  city_label: Grad
  city_placeholder: Grad (opcionalno)
  about_section: O vama
  bio_label: Biografija
  bio_placeholder: Recite gostima nešto o sebi kao domaćinu (opcionalno)
  save_btn: Sačuvajte izmene
  saving: "Čuvanje..."
  skip_btn: "Preskočite — prvo kreirajte smeštaj"
  danger_zone: Opasna zona
  delete_account_title: Obrišite nalog domaćina
  delete_account_desc: Uklanja vaš profil domaćina i sve oglase za smeštaj. Vaš korisnički nalog ostaje aktivan.
  request_deletion: Zatražite brisanje
  delete_modal_desc: Ovo će zatražiti brisanje vašeg profila domaćina i svih oglasa za smeštaj. Vaš korisnički nalog će ostati aktivan. Administrator će pregledati i obraditi vaš zahtev.
  delete_confirm_prompt: "Unesite {word} za potvrdu:"
  delete_confirm_placeholder: Unesite DELETE
  submit_deletion: Pošaljite zahtev za brisanje
  profile_updated: Profil domaćina je uspešno ažuriran.
  deletion_submitted: Vaš zahtev za brisanje je podnet. Naš tim će ga pregledati i obraditi uskoro.
  deletion_failed: Greška pri podnošenju zahteva za brisanje. Pokušajte ponovo.
  avatar_upload_failed: Greška pri otpremanju avatara. Pokušajte ponovo.

hr:
  page_title: Profil domaćina
  page_desc: Ažurirajte informacije o profilu domaćina vidljive gostima. Ime za prikaz, kontakt email i telefon su obavezni prije nego što vaši oglasi budu prikazani u pretrazi.
  profile_photo_title: Profilna fotografija
  photo_hint: Kliknite na avatar za učitavanje nove fotografije. Ova fotografija će se pojaviti na vašim oglasima.
  photo_format: "JPEG, PNG ili WebP — max 5MB"
  uploading: "Učitavanje..."
  contact_section: Kontakt informacije
  required_note: "* obavezno"
  display_name_label: Ime za prikaz
  display_name_placeholder: Vaše ime ili naziv tvrtke koji se prikazuje gostima
  contact_email_label: Kontakt email
  email_placeholder: Email za komunikaciju s gostima
  apply_login_email: Koristite email za prijavu
  phone_label: Telefon
  business_section: Poslovni podaci
  business_name_label: Naziv tvrtke
  business_name_placeholder: "Opcionalno — ako poslujete kao tvrtka"
  tax_id_label: OIB
  vat_number_label: PDV broj
  optional_placeholder: Opcionalno
  location_section: Lokacija
  address_label: Adresa
  address_placeholder: Ulica i kućni broj (opcionalno)
  city_label: Grad
  city_placeholder: Grad (opcionalno)
  about_section: O vama
  bio_label: Biografija
  bio_placeholder: Recite gostima nešto o sebi kao domaćinu (opcionalno)
  save_btn: Spremi promjene
  saving: "Spremanje..."
  skip_btn: "Preskočite — prvo kreirajte smještaj"
  danger_zone: Opasna zona
  delete_account_title: Obrišite račun domaćina
  delete_account_desc: Uklanja vaš profil domaćina i sve oglase za smještaj. Vaš korisnički račun ostaje aktivan.
  request_deletion: Zatražite brisanje
  delete_modal_desc: Ovo će zatražiti brisanje vašeg profila domaćina i svih oglasa za smještaj. Vaš korisnički račun će ostati aktivan. Administrator će pregledati i obraditi vaš zahtjev.
  delete_confirm_prompt: "Unesite {word} za potvrdu:"
  delete_confirm_placeholder: Unesite DELETE
  submit_deletion: Pošaljite zahtjev za brisanje
  profile_updated: Profil domaćina je uspješno ažuriran.
  deletion_submitted: Vaš zahtjev za brisanje je podnesen. Naš tim će ga pregledati i obraditi uskoro.
  deletion_failed: Greška pri podnošenju zahtjeva za brisanje. Pokušajte ponovo.
  avatar_upload_failed: Greška pri učitavanju avatara. Pokušajte ponovo.

mk:
  page_title: Профил на домаќин
  page_desc: Ажурирајте ги информациите за профилот на домаќинот видливи за гостите. Прикажаното име, контакт email и телефон се задолжителни пред вашите огласи да се прикажат во пребарувањето.
  profile_photo_title: Профилна фотографија
  photo_hint: Кликнете на аватарот за да отпремите нова фотографија. Оваа фотографија ќе се прикаже на вашите огласи.
  photo_format: "JPEG, PNG или WebP — макс 5MB"
  uploading: "Отпремање..."
  contact_section: Контакт информации
  required_note: "* задолжително"
  display_name_label: Прикажано име
  display_name_placeholder: Вашето име или назив на бизнисот прикажан за гостите
  contact_email_label: Контакт email
  email_placeholder: Email за комуникација со гостите
  apply_login_email: Користете email за најава
  phone_label: Телефон
  business_section: Деловни податоци
  business_name_label: Назив на компанија
  business_name_placeholder: "Опционално — ако работите како компанија"
  tax_id_label: Даночен број
  vat_number_label: ДДВ број
  optional_placeholder: Опционално
  location_section: Локација
  address_label: Адреса
  address_placeholder: Улица и број (опционално)
  city_label: Град
  city_placeholder: Град (опционално)
  about_section: За вас
  bio_label: Биографија
  bio_placeholder: Кажете им на гостите нешто за себе како домаќин (опционално)
  save_btn: Зачувајте ги промените
  saving: "Зачувување..."
  skip_btn: "Прескокни — прво креирај сместување"
  danger_zone: Опасна зона
  delete_account_title: Избришете го налогот на домаќинот
  delete_account_desc: Го отстранува вашиот профил на домаќин и сите огласи за сместување. Вашиот кориснички налог останува активен.
  request_deletion: Побарајте бришење
  delete_modal_desc: Ова ќе побара бришење на вашиот профил на домаќин и сите огласи за сместување. Вашиот кориснички налог ќе остане активен. Администраторот ќе го прегледа и обработи вашето барање.
  delete_confirm_prompt: "Внесете {word} за потврда:"
  delete_confirm_placeholder: Внесете DELETE
  submit_deletion: Испратете барање за бришење
  profile_updated: Профилот на домаќинот е успешно ажуриран.
  deletion_submitted: Вашето барање за бришење е поднесено. Нашиот тим ќе го прегледа и обработи наскоро.
  deletion_failed: Грешка при поднесување барање за бришење. Обидете се повторно.
  avatar_upload_failed: Грешка при отпремање на аватар. Обидете се повторно.

sl:
  page_title: Profil gostitelja
  page_desc: Posodobite informacije o profilu gostitelja, vidne gostom. Prikazno ime, kontaktni e-naslov in telefon so obvezni, preden se vaši oglasi prikažejo v iskanju.
  profile_photo_title: Profilna fotografija
  photo_hint: Kliknite na avatar za nalaganje nove fotografije. Ta fotografija se bo pojavila na vaših oglasih.
  photo_format: "JPEG, PNG ali WebP — največ 5MB"
  uploading: "Nalaganje..."
  contact_section: Kontaktni podatki
  required_note: "* obvezno"
  display_name_label: Prikazno ime
  display_name_placeholder: Vaše ime ali ime podjetja, prikazano gostom
  contact_email_label: Kontaktni e-naslov
  email_placeholder: E-naslov za komunikacijo z gosti
  apply_login_email: Uporabi prijavni e-naslov
  phone_label: Telefon
  business_section: Poslovni podatki
  business_name_label: Ime podjetja
  business_name_placeholder: "Neobvezno — če poslujete kot podjetje"
  tax_id_label: Davčna številka
  vat_number_label: DDV številka
  optional_placeholder: Neobvezno
  location_section: Lokacija
  address_label: Naslov
  address_placeholder: Ulica in hišna številka (neobvezno)
  city_label: Mesto
  city_placeholder: Mesto (neobvezno)
  about_section: O vas
  bio_label: Biografija
  bio_placeholder: Povejte gostom nekaj o sebi kot gostitelju (neobvezno)
  save_btn: Shranite spremembe
  saving: "Shranjevanje..."
  skip_btn: "Preskoči — najprej ustvari nastanitev"
  danger_zone: Nevarna cona
  delete_account_title: Izbrišite račun gostitelja
  delete_account_desc: Odstrani vaš profil gostitelja in vse oglase za nastanitev. Vaš uporabniški račun ostane aktiven.
  request_deletion: Zahtevajte brisanje
  delete_modal_desc: S tem boste zahtevali brisanje vašega profila gostitelja in vseh oglasov za nastanitev. Vaš uporabniški račun bo ostal aktiven. Skrbnik bo pregledal in obdelal vašo zahtevo.
  delete_confirm_prompt: "Vnesite {word} za potrditev:"
  delete_confirm_placeholder: Vnesite DELETE
  submit_deletion: Pošljite zahtevo za brisanje
  profile_updated: Profil gostitelja je bil uspešno posodobljen.
  deletion_submitted: Vaša zahteva za brisanje je bila oddana. Naša ekipa jo bo pregledala in obdelala kmalu.
  deletion_failed: Napaka pri oddaji zahteve za brisanje. Poskusite znova.
  avatar_upload_failed: Napaka pri nalaganju avatarja. Poskusite znova.
</i18n>
