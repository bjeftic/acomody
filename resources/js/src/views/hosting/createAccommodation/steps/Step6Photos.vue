<template>
    <div>
        <h1 class="text-3xl font-semibold text-gray-900 dark:text-white mb-2">
            {{ $t('heading') }}
        </h1>
        <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">
            {{ $t('subtitle') }}
        </p>

        <hr />
        <div class="overflow-auto py-4 pr-4 h-[60vh]">
        <photo-uploader
            :photos="formData.photos"
            :upload-errors="uploadErrors"
            :max-photos="20"
            :max-file-size="10485760"
            @update:photos="updatePhotos"
            @error="handleUploadError"
        />
        </div>
    </div>
</template>

<script>
import PhotoUploader from "@/src/views/hosting/createAccommodation/components/PhotoUploader.vue";
import { mapState, mapActions } from "vuex";

export default {
    name: "Step6Photos",
    components: {
        PhotoUploader,
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
    emits: ["update:form-data"],
    computed: {
        ...mapState("hosting/createAccommodation", [
            "accommodationDraftId",
        ]),
    },
    data() {
        return {
            uploadErrors: [],
        };
    },
    methods: {
        ...mapActions("hosting/createAccommodation", ["fetchPhotos"]),
        updatePhotos(photos) {
            this.$emit("update:form-data", {
                ...this.formData,
                photos,
            });
        },
        handleUploadError(error) {
            this.uploadErrors.push(error);
        },
    },
    created() {
        this.fetchPhotos(this.accommodationDraftId);
    },
};
</script>

<i18n lang="yaml">
en:
  heading: Add some photos of your place
  subtitle: "You'll need at least 5 photos to get started. You can add more or make changes later."
sr:
  heading: Dodajte fotografije vašeg smeštaja
  subtitle: Treba će vam najmanje 5 fotografija za početak. Možete dodati više ili napraviti izmene kasnije.
hr:
  heading: Dodajte fotografije vašeg smještaja
  subtitle: Trebate najmanje 5 fotografija za početak. Možete dodati više ili napraviti izmjene kasnije.
mk:
  heading: Додајте фотографии на вашиот простор
  subtitle: Ќе ви требаат најмалку 5 фотографии за почеток. Можете да додадете повеќе или да направите измени подоцна.
sl:
  heading: Dodajte fotografije vašega prostora
  subtitle: Za začetek boste potrebovali vsaj 5 fotografij. Kasneje lahko dodate več ali naredite spremembe.
</i18n>
