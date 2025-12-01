@php
    $fieldName = $getName();
    $state = $getState();
    $isDisabled = $isDisabled();
    $statePath = $getStatePath();
    $perPage = $getPerPage();
    $stateArray = is_array($state) ? $state : [];
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
    class="col-span-full"
>
    <div 
        x-data="imageLibrarySelector({
            fieldName: @js($fieldName),
            initialIds: @js($stateArray),
            perPage: @js($perPage),
            state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$statePath}')") }},
        })"
        x-init="initComponent()"
        @keydown.escape.window="closeModal()"
        class="space-y-3"
    >
        <!-- Button trigger -->
        <button 
            type="button"
            @click="openModal()"
            {{ $isDisabled ? 'disabled' : '' }}
            class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
        >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Chọn từ thư viện
        </button>

        <!-- Selected count -->
        <div class="text-sm text-gray-600 dark:text-gray-400">
            <span x-text="selectedIds.length"></span> ảnh được chọn
        </div>

        <!-- Hidden input -->
        <input 
            type="hidden"
            name="{{ $fieldName }}"
            x-bind:value="JSON.stringify(selectedIds)"
        />

        <!-- Modal Backdrop + Popup -->
        <div 
            x-show="isOpen"
            x-transition
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-50 p-4"
            style="display: none;"
        >
            <!-- Modal Container -->
            <div 
                @click.stop
                class="relative w-full max-w-5xl bg-white rounded-lg shadow-2xl max-h-[90vh] overflow-hidden flex flex-col dark:bg-gray-800"
            >
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Chọn ảnh từ thư viện
                    </h2>
                    <button 
                        @click="closeModal()"
                        type="button"
                        class="rounded-md text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                    >
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Body -->
                <div class="flex-1 overflow-y-auto p-6 space-y-4">
                    <!-- Search -->
                    <div>
                        <input 
                            type="text"
                            x-model.debounce.500ms="search"
                            @input="currentPage = 1; loadImages()"
                            placeholder="Tìm kiếm ảnh..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        />
                    </div>

                    <!-- Loading -->
                    <div x-show="loading" class="flex justify-center py-12">
                        <div class="animate-spin">
                            <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                        </div>
                    </div>

                    <!-- Images Grid -->
                    <template x-if="!loading && images.length > 0">
                        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
                            <template x-for="image in images" :key="image.id">
                                <div 
                                    @click="toggleImage(image.id)"
                                    class="group relative cursor-pointer rounded-lg overflow-hidden border-2 transition-all"
                                    :class="selectedIds.includes(image.id) 
                                        ? 'border-blue-500 ring-2 ring-blue-300 dark:ring-blue-700' 
                                        : 'border-gray-300 dark:border-gray-600 hover:border-gray-400'"
                                >
                                    <img 
                                        :src="image.url"
                                        :alt="image.alt"
                                        class="h-32 w-full object-cover"
                                    />
                                    
                                    <!-- Overlay -->
                                    <div class="absolute inset-0 bg-black opacity-0 transition-opacity group-hover:opacity-20"></div>
                                    
                                    <!-- Checkbox -->
                                    <div class="absolute inset-0 flex items-center justify-center opacity-0 transition-opacity group-hover:opacity-100">
                                        <div 
                                            class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white"
                                            :class="selectedIds.includes(image.id) ? 'bg-blue-500' : 'bg-transparent'"
                                        >
                                            <template x-if="selectedIds.includes(image.id)">
                                                <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Image name -->
                                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black to-transparent px-2 py-2 text-xs text-white opacity-0 transition-opacity group-hover:opacity-100">
                                        <span x-text="image.name" class="line-clamp-1"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    <!-- Empty -->
                    <template x-if="!loading && images.length === 0">
                        <div class="rounded-lg bg-gray-100 p-8 text-center text-gray-500 dark:bg-gray-700 dark:text-gray-400">
                            Không tìm thấy ảnh
                        </div>
                    </template>

                    <!-- Pagination -->
                    <template x-if="!loading && totalPages > 1">
                        <div class="flex items-center justify-between border-t border-gray-200 pt-4 dark:border-gray-700">
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                Hiển thị <span x-text="(currentPage - 1) * perPage + 1"></span> - <span x-text="Math.min(currentPage * perPage, total)"></span> / <span x-text="total"></span>
                            </div>
                            
                            <div class="flex gap-1">
                                <button 
                                    @click="previousPage()"
                                    :disabled="currentPage === 1"
                                    type="button"
                                    class="rounded border border-gray-300 px-2 py-1 text-sm font-medium transition-colors hover:bg-gray-100 disabled:cursor-not-allowed disabled:text-gray-400 dark:border-gray-600 dark:hover:bg-gray-700"
                                >
                                    ←
                                </button>

                                <template x-for="page in pageNumbers" :key="page">
                                    <button 
                                        @click="goToPage(page)"
                                        type="button"
                                        :class="page === currentPage 
                                            ? 'bg-blue-600 text-white' 
                                            : 'border border-gray-300 text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700'"
                                        class="rounded px-2 py-1 text-sm font-medium transition-colors"
                                    >
                                        <span x-text="page"></span>
                                    </button>
                                </template>

                                <button 
                                    @click="nextPage()"
                                    :disabled="currentPage === totalPages"
                                    type="button"
                                    class="rounded border border-gray-300 px-2 py-1 text-sm font-medium transition-colors hover:bg-gray-100 disabled:cursor-not-allowed disabled:text-gray-400 dark:border-gray-600 dark:hover:bg-gray-700"
                                >
                                    →
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-between border-t border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-900">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        Đã chọn: <strong x-text="selectedIds.length"></strong> ảnh
                    </div>
                    
                    <div class="flex gap-2">
                        <button 
                            @click="closeModal()"
                            type="button"
                            class="rounded-lg border border-gray-300 bg-white px-4 py-2 font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            Hủy
                        </button>
                        <button 
                            @click="confirmSelection()"
                            type="button"
                            class="rounded-lg bg-blue-600 px-4 py-2 font-medium text-white transition-colors hover:bg-blue-700"
                        >
                            Xác nhận
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dynamic-component>

<script>
function imageLibrarySelector(config) {
    return {
        fieldName: config.fieldName,
        state: config.state,
        isOpen: false,
        selectedIds: [],
        images: [],
        search: '',
        loading: false,
        currentPage: 1,
        totalPages: 1,
        total: 0,
        perPage: config.perPage ?? 12,

        initComponent() {
            this.selectedIds = Array.isArray(config.initialIds) ? [...config.initialIds] : [];

            // Dong bo voi Livewire state
            this.$watch('state', (value) => {
                this.selectedIds = Array.isArray(value) ? [...value] : [];
            });
        },

        get pageNumbers() {
            const start = Math.max(1, this.currentPage - 2);
            const end = Math.min(this.totalPages, start + 4);
            return Array.from({ length: Math.max(1, end - start + 1) }, (_, i) => start + i);
        },

        openModal() {
            this.isOpen = true;
            this.loadImages();
        },

        closeModal() {
            this.isOpen = false;
        },

        async loadImages() {
            this.loading = true;
            try {
                const params = new URLSearchParams({
                    page: this.currentPage,
                    per_page: this.perPage,
                    search: this.search,
                });

                const response = await fetch(`/admin/api/library-images?${params}`);
                if (!response.ok) throw new Error('Failed to fetch');

                const data = await response.json();
                this.images = data.data || [];
                this.currentPage = data.current_page || 1;
                this.totalPages = data.last_page || 1;
                this.total = data.total || 0;
            } catch (error) {
                console.error('Error:', error);
            } finally {
                this.loading = false;
            }
        },

        toggleImage(imageId) {
            const idx = this.selectedIds.indexOf(imageId);
            if (idx > -1) {
                this.selectedIds.splice(idx, 1);
            } else {
                this.selectedIds.push(imageId);
            }
        },

        confirmSelection() {
            this.updateFormState();
            this.closeModal();
        },

        updateFormState() {
            // Loai bo trung lap va day state vao Livewire
            this.selectedIds = Array.from(new Set(this.selectedIds));
            this.state = [...this.selectedIds];
        },

        goToPage(page) {
            this.currentPage = page;
            this.loadImages();
        },

        previousPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.loadImages();
            }
        },

        nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
                this.loadImages();
            }
        },
    };
}
</script>
