<template>
    <div>
        <h1 class="text-3xl font-semibold text-gray-900 dark:text-white mb-2">
            Add some photos of your place
        </h1>
        <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">
            You'll need at least 5 photos to get started. You can add more or make changes later.
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
