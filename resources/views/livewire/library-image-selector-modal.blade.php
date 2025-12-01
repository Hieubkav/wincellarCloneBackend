<div class="space-y-4">
    <div class="flex items-center gap-3">
        <input 
            type="text" 
            wire:model.live="search" 
            placeholder="Tìm kiếm ảnh..."
            class="flex-1 rounded border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700"
        >
        <button 
            wire:click="toggleAll" 
            class="rounded bg-blue-500 px-4 py-2 text-sm font-medium text-white hover:bg-blue-600"
        >
            Chọn tất cả trang
        </button>
    </div>

    <div class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-4">
        @forelse($images as $image)
            <div class="relative group cursor-pointer" wire:key="image-{{ $image->id }}">
                <img 
                    src="{{ $image->url ?? '/images/placeholder.png' }}" 
                    alt="{{ $image->alt ?? '' }}"
                    class="h-24 w-full rounded border-2 object-cover transition-all {{ in_array($image->id, $selectedIds) ? 'border-blue-500 ring-2 ring-blue-300' : 'border-gray-200 dark:border-gray-600' }}"
                    wire:click="toggleImage({{ $image->id }})"
                >
                
                <div class="absolute inset-0 flex items-center justify-center rounded bg-black bg-opacity-0 transition-all group-hover:bg-opacity-30">
                    <div class="rounded-full border-2 border-white p-2" 
                        :class="{ 'bg-blue-500': {{ in_array($image->id, $selectedIds) ? 'true' : 'false' }} }">
                        @if(in_array($image->id, $selectedIds))
                            <svg class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        @endif
                    </div>
                </div>

                <div class="absolute bottom-0 left-0 right-0 truncate bg-gradient-to-t from-black to-transparent px-2 py-2 text-xs text-white opacity-0 transition-opacity group-hover:opacity-100">
                    {{ basename($image->file_path ?? '') }}
                </div>
            </div>
        @empty
            <div class="col-span-full rounded bg-gray-100 p-8 text-center text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                Không tìm thấy ảnh
            </div>
        @endforelse
    </div>

    @if($images->hasPages())
        <div class="flex items-center justify-between border-t pt-4 dark:border-gray-700">
            <div class="text-sm text-gray-500 dark:text-gray-400">
                Hiển thị {{ $images->firstItem() }} - {{ $images->lastItem() }} của {{ $images->total() }}
            </div>
            
            <div class="flex gap-2">
                @if($images->onFirstPage())
                    <button class="rounded border border-gray-300 px-3 py-1 text-sm text-gray-400 dark:border-gray-600" disabled>
                        Trang trước
                    </button>
                @else
                    <button wire:click="previousPage" class="rounded border border-gray-300 px-3 py-1 text-sm hover:bg-gray-100 dark:border-gray-600 dark:hover:bg-gray-700">
                        Trang trước
                    </button>
                @endif

                @foreach($images->getUrlRange(1, $images->lastPage()) as $page => $url)
                    @if($page == $images->currentPage())
                        <button class="rounded bg-blue-500 px-3 py-1 text-sm font-medium text-white">
                            {{ $page }}
                        </button>
                    @else
                        <button wire:click="gotoPage({{ $page }})" class="rounded border border-gray-300 px-3 py-1 text-sm hover:bg-gray-100 dark:border-gray-600 dark:hover:bg-gray-700">
                            {{ $page }}
                        </button>
                    @endif
                @endforeach

                @if($images->hasMorePages())
                    <button wire:click="nextPage" class="rounded border border-gray-300 px-3 py-1 text-sm hover:bg-gray-100 dark:border-gray-600 dark:hover:bg-gray-700">
                        Trang sau
                    </button>
                @else
                    <button class="rounded border border-gray-300 px-3 py-1 text-sm text-gray-400 dark:border-gray-600" disabled>
                        Trang sau
                    </button>
                @endif
            </div>
        </div>
    @endif

    <div class="rounded-lg border border-blue-200 bg-blue-50 p-3 text-sm text-blue-700 dark:border-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
        Đã chọn: <strong>{{ count($selectedIds) }}</strong> ảnh
    </div>
</div>
