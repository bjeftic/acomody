<template>
    <div class="space-y-6">
        <!-- Drag & Drop Zone -->
        <div
            v-if="photos.length < maxPhotos"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="handleDrop"
            :class="[
                'border-2 border-dashed rounded-xl p-8 text-center transition-all duration-200 h-72',
                isDragging
                    ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
                    : 'border-gray-300 dark:border-gray-700 hover:border-gray-400 dark:hover:border-gray-600',
            ]"
        >
            <div class="flex flex-col items-center space-y-4">
                <!-- Upload Icon -->
                <svg
                    class="w-16 h-10 text-gray-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                    />
                </svg>

                <!-- Text -->
                <div>
                    <p class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                        Drag your photos here
                    </p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Choose at least 5 photos
                    </p>
                </div>

                <!-- Upload Button -->
                <label
                    for="photo-upload"
                    class="px-6 py-3 bg-white dark:bg-gray-800 border-2 border-gray-900 dark:border-white text-gray-900 dark:text-white font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors cursor-pointer"
                >
                    Upload from your device
                </label>
                <input
                    id="photo-upload"
                    type="file"
                    multiple
                    accept="image/jpeg,image/png,image/jpg"
                    @change="handleFileSelect"
                    class="hidden"
                />

                <!-- Info Text -->
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Maximum {{ maxPhotos }} photos • JPG or PNG • Max {{ formatFileSize(maxFileSize) }} each
                </p>
            </div>
        </div>

        <!-- Photo Grid -->
        <div v-if="photos.length > 0" class="space-y-4">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ photos.length }} / {{ maxPhotos }} photos
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Drag photos to reorder
                </p>
            </div>

            <div class="overflow-auto max-h-[105px] pr-2">
            <!-- Draggable Photo Grid -->
            <draggable
                v-model="localPhotos"
                item-key="id"
                class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-8 gap-4"
                :animation="200"
                ghost-class="opacity-50"
                @end="updatePhotosOrder"
            >
                <template #item="{ element, index }">
                    <div
                        class="relative group aspect-square rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-800 cursor-move"
                    >
                        <!-- Photo -->
                        <img
                            :src="element?.urls?.thumbnail || element.preview"
                            :alt="`Photo ${index + 1}`"
                            class="w-full h-full object-cover"
                        />

                        <!-- Cover Photo Badge -->
                        <div
                            v-if="index === 0"
                            class="absolute top-2 left-2 px-3 py-1 bg-white dark:bg-gray-900 text-xs font-semibold rounded-full shadow-lg"
                        >
                            Cover photo
                        </div>

                        <!-- Overlay on Hover -->
                        <div
                            class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-all duration-200"
                        >
                            <div
                                class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
                            >
                                <!-- Delete Button -->
                                <button
                                    @click="removePhoto(index)"
                                    class="p-2 bg-white dark:bg-gray-900 rounded-full hover:bg-red-500 hover:text-white transition-colors"
                                >
                                    <svg
                                        class="w-5 h-5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Loading Indicator -->
                        <div
                            v-if="element.uploading"
                            class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center"
                        >
                            <svg
                                class="animate-spin h-8 w-8 text-white"
                                viewBox="0 0 24 24"
                            >
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"
                                    fill="none"
                                />
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                />
                            </svg>
                        </div>
                    </div>
                </template>
            </draggable>
            </div>
        </div>

        <!-- Error Messages -->
        <div v-if="uploadErrors.length > 0" class="space-y-2">
            <div
                v-for="(error, index) in uploadErrors"
                :key="index"
                class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg flex items-start space-x-3"
            >
                <svg
                    class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                >
                    <path
                        fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                        clip-rule="evenodd"
                    />
                </svg>
                <p class="text-sm text-red-700 dark:text-red-300">
                    {{ error }}
                </p>
            </div>
        </div>
    </div>
</template>

<script>
import { mapState, mapActions } from "vuex";
import draggable from "vuedraggable";

export default {
    name: "PhotoUploader",
    components: {
        draggable,
    },
    props: {
        photos: {
            type: Array,
            default: () => [],
        },
        uploadErrors: {
            type: Array,
            default: () => [],
        },
        maxPhotos: {
            type: Number,
            default: 20,
        },
        maxFileSize: {
            type: Number,
            default: 10485760, // 10MB
        },
    },
    emits: ["update:photos", "error"],
    computed: {
        ...mapState("hosting/createAccommodation", ["accommodationDraftId"]),
    },
    data() {
        return {
            isDragging: false,
            localPhotos: [...this.photos],
        };
    },
    watch: {
        photos: {
            deep: true,
            handler(newPhotos) {
                this.localPhotos = [...newPhotos];
            },
        },
    },
    methods: {
        ...mapActions("hosting/createAccommodation", [
            "uploadPhoto",
            "deletePhoto",
            "reorderPhotos",
        ]),
        handleFileSelect(event) {
            const files = Array.from(event.target.files);
            this.processFiles(files);
            event.target.value = "";
        },

        handleDrop(event) {
            this.isDragging = false;
            const files = Array.from(event.dataTransfer.files);
            this.processFiles(files);
        },

        processFiles(files) {
            if (this.photos.length + files.length > this.maxPhotos) {
                this.$emit(
                    "error",
                    `You can only upload a maximum of ${this.maxPhotos} photos.`
                );
                return;
            }

            files.forEach((file) => {
                if (!file.type.match("image/(jpeg|jpg|png)")) {
                    this.$emit(
                        "error",
                        `${file.name} is not a valid image format. Please use JPG or PNG.`
                    );
                    return;
                }

                if (file.size > this.maxFileSize) {
                    this.$emit(
                        "error",
                        `${file.name} is too large. Maximum size is ${this.formatFileSize(
                            this.maxFileSize
                        )}.`
                    );
                    return;
                }

                this.addPhoto(file);
            });
        },

        addPhoto(file) {
            const reader = new FileReader();

            reader.onload = (e) => {
                const photo = {
                    tmpId: Date.now() + Math.random(),
                    file: file,
                    preview: e.target.result,
                    uploading: true,
                    uploaded: false,
                    url: null,
                };

                const updatedPhotos = [...this.photos, photo];

                this.uploadPhoto({ draftId: this.accommodationDraftId, file })
                    .then((response) => {
                        photo.uploading = false;
                        photo.uploaded = true;
                        photo.url = response.data[0].url;
                        photo.urls = response.data[0].urls;
                        photo.id = response.data[0].id;

                        this.$emit("update:photos", [
                            ...this.photos.filter((p) => p.tmpId !== photo.tmpId),
                            photo,
                        ]);
                    })
                    .catch((error) => {
                        this.$emit(
                            "error",
                            `Failed to upload ${file.name}. ${error.error.error.validation_errors[0].message}`
                        );
                        const filteredPhotos = this.photos.filter(
                            (p) => p.tmpId !== photo.tmpId
                        );
                        this.$emit("update:photos", filteredPhotos);
                    });
                this.$emit("update:photos", updatedPhotos);
            };

            reader.readAsDataURL(file);
        },

        removePhoto(index) {
            this.deletePhoto({
                draftId: this.accommodationDraftId,
                photoId: this.localPhotos[index].id,
            });
        },

        updatePhotosOrder() {
            this.reorderPhotos({
                draftId: this.accommodationDraftId,
                photoIds: this.localPhotos.map(photo => photo.id),
            });
        },

        formatFileSize(bytes) {
            if (bytes === 0) return "0 Bytes";
            const k = 1024;
            const sizes = ["Bytes", "KB", "MB", "GB"];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i)) + " " + sizes[i];
        },
    },
};
</script>
