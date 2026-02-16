<template>
    <div class="space-y-6">
        <!-- Drag & Drop Zone -->
        <div
            v-if="localPhotos.length < maxPhotos"
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
                    accept="image/jpeg,image/png,image/jpg,image/webp"
                    @change="handleFileSelect"
                    class="hidden"
                />

                <!-- Info Text -->
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Maximum {{ maxPhotos }} photos • JPG, PNG or WebP • Max {{ formatFileSize(maxFileSize) }} each
                </p>
            </div>
        </div>

        <!-- Photo Grid -->
        <div v-if="localPhotos.length > 0" class="space-y-4">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ localPhotos.length }} / {{ maxPhotos }} photos
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
                    @end="handleReorder"
                >
                    <template #item="{ element, index }">
                        <div
                            class="relative group aspect-square rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-800 cursor-move"
                        >
                            <!-- Photo -->
                            <img
                                v-if="element.urls?.thumbnail || element.preview"
                                :src="element.urls?.thumbnail || element.preview"
                                :alt="`Photo ${index + 1}`"
                                class="w-full h-full object-cover"
                                @error="handleImageError"
                            />

                            <!-- Fallback for broken images -->
                            <div
                                v-else
                                class="w-full h-full flex items-center justify-center bg-gray-200 dark:bg-gray-700"
                            >
                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>

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
                                        @click="handleDelete(index)"
                                        :disabled="element.uploading"
                                        class="p-2 bg-white dark:bg-gray-900 rounded-full hover:bg-red-500 hover:text-white transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
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

        <!-- Empty State -->
        <div
            v-if="localPhotos.length === 0"
            class="text-center py-8 text-gray-500 dark:text-gray-400"
        >
            <p>No photos uploaded yet. Add at least 5 photos to continue.</p>
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
    data() {
        return {
            isDragging: false,
            localPhotos: [],
            isLoading: false,
        };
    },
    computed: {
        ...mapState("hosting/createAccommodation", ["accommodationDraftId"]),
    },
    watch: {
        photos: {
            immediate: true,
            deep: true,
            handler(newPhotos) {
                // Only update if different to avoid infinite loops
                if (JSON.stringify(newPhotos) !== JSON.stringify(this.localPhotos)) {
                    this.localPhotos = [...newPhotos];
                }
            },
        },
    },
    created() {
        // Fetch existing photos when component is created
        this.loadPhotos();
    },
    methods: {
        ...mapActions("hosting/createAccommodation", [
            "uploadPhoto",
            "deletePhoto",
            "reorderPhotos",
            "fetchPhotos",
        ]),

        async loadPhotos() {
            if (!this.accommodationDraftId) {
                console.warn('No accommodationDraftId provided');
                return;
            }

            this.isLoading = true;
            try {
                await this.fetchPhotos(this.accommodationDraftId);
            } catch (error) {
                console.error('Failed to load photos:', error);
                this.$emit('error', 'Failed to load existing photos.');
            } finally {
                this.isLoading = false;
            }
        },

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
            if (this.localPhotos.length + files.length > this.maxPhotos) {
                this.$emit(
                    "error",
                    `You can only upload a maximum of ${this.maxPhotos} photos.`
                );
                return;
            }

            files.forEach((file) => {
                if (!file.type.match("image/(jpeg|jpg|png|webp)")) {
                    this.$emit(
                        "error",
                        `${file.name} is not a valid image format. Please use JPG, PNG or WebP.`
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
                    urls: null,
                    id: null,
                };

                // Add to local photos with uploading state
                this.localPhotos.push(photo);
                this.$emit("update:photos", [...this.localPhotos]);

                // Upload to server
                this.uploadPhoto({ draftId: this.accommodationDraftId, file })
                    .then((response) => {
                        // Find the photo by tmpId and update it
                        const photoIndex = this.localPhotos.findIndex(p => p.tmpId === photo.tmpId);
                        if (photoIndex !== -1) {
                            this.localPhotos[photoIndex] = {
                                ...this.localPhotos[photoIndex],
                                uploading: false,
                                uploaded: true,
                                url: response.data[0].url,
                                urls: response.data[0].urls,
                                id: response.data[0].id,
                                order: response.data[0].order,
                                status: response.data[0].status,
                            };
                            this.$emit("update:photos", [...this.localPhotos]);
                        }
                    })
                    .catch((error) => {
                        console.error('Upload failed:', error);

                        // Remove failed photo from list
                        const photoIndex = this.localPhotos.findIndex(p => p.tmpId === photo.tmpId);
                        if (photoIndex !== -1) {
                            this.localPhotos.splice(photoIndex, 1);
                            this.$emit("update:photos", [...this.localPhotos]);
                        }

                        // Show error
                        const errorMessage = error?.response?.data?.message
                            || error?.message
                            || `Failed to upload ${file.name}`;
                        this.$emit("error", errorMessage);
                    });
            };

            reader.readAsDataURL(file);
        },

        async handleDelete(index) {
            const photo = this.localPhotos[index];

            if (!photo.id) {
                // Photo not uploaded yet, just remove from list
                this.localPhotos.splice(index, 1);
                this.$emit("update:photos", [...this.localPhotos]);
                return;
            }

            try {
                await this.deletePhoto({
                    draftId: this.accommodationDraftId,
                    photoId: photo.id,
                });

                // Remove from local photos
                this.localPhotos.splice(index, 1);
                this.$emit("update:photos", [...this.localPhotos]);
            } catch (error) {
                console.error('Delete failed:', error);
                this.$emit("error", `Failed to delete photo: ${error.message || 'Unknown error'}`);
            }
        },

        async handleReorder() {
            // Filter out photos that don't have IDs yet (still uploading)
            const photoIds = this.localPhotos
                .filter(photo => photo.id)
                .map(photo => photo.id);

            if (photoIds.length === 0) {
                return;
            }

            try {
                await this.reorderPhotos({
                    draftId: this.accommodationDraftId,
                    photoIds: photoIds,
                });

                // Update parent
                this.$emit("update:photos", [...this.localPhotos]);
            } catch (error) {
                console.error('Reorder failed:', error);
                this.$emit("error", `Failed to reorder photos: ${error.message || 'Unknown error'}`);

                // Reload photos to get correct order
                await this.loadPhotos();
            }
        },

        handleImageError(event) {
            console.error('Image failed to load:', event.target.src);
            // You could set a fallback image here
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
