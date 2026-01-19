 <x-filament-panels::page>
     <div class="space-y-6">
         {{-- Menu Selector --}}
         <div class="flex flex-wrap items-center gap-3">
             @foreach($menus as $menu)
                 <button
                     wire:click="selectMenu({{ $menu->id }})"
                     class="px-4 py-2 rounded-lg font-medium transition-all {{ $selectedMenuId === $menu->id ? 'bg-primary-600 text-white shadow-lg' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:border-primary-500' }}"
                 >
                     {{ $menu->title }}
                     @if($menu->type === 'mega')
                         <span class="ml-1 text-xs opacity-75">(mega)</span>
                     @endif
                 </button>
             @endforeach
         </div>
 
         @if($selectedMenu)
             {{-- Menu Info --}}
             <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                 <div class="flex items-center justify-between">
                     <div>
                         <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ $selectedMenu->title }}</h2>
                         <p class="text-sm text-gray-500 dark:text-gray-400">
                             Loại: <span class="font-medium">{{ $selectedMenu->type === 'mega' ? 'Mega Menu' : 'Link đơn' }}</span>
                         </p>
                     </div>
                     <div class="flex items-center gap-2">
                         <span class="text-sm text-gray-500 dark:text-gray-400">Link "Xem tất cả":</span>
                         <input 
                             type="text" 
                             value="{{ $selectedMenu->href }}"
                             wire:change="updateMenuHref($event.target.value)"
                             class="px-3 py-1.5 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                             placeholder="/filter?type=..."
                         />
                     </div>
                 </div>
             </div>
 
             @if($selectedMenu->type === 'mega')
                 {{-- Mega Menu Builder --}}
                 <div 
                     class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4"
                     x-data="{
                         draggedBlock: null,
                         draggedItem: null,
                         draggedFromBlock: null
                     }"
                 >
                     @foreach($blocks as $blockIndex => $block)
                         <div 
                             class="bg-white dark:bg-gray-800 rounded-xl border-2 transition-all {{ $block['active'] ? 'border-gray-200 dark:border-gray-700' : 'border-dashed border-gray-300 dark:border-gray-600 opacity-60' }}"
                             draggable="true"
                             x-on:dragstart="draggedBlock = {{ $block['id'] }}"
                             x-on:dragend="draggedBlock = null"
                             x-on:dragover.prevent="if(draggedBlock && draggedBlock !== {{ $block['id'] }}) $el.classList.add('ring-2', 'ring-primary-500')"
                             x-on:dragleave="$el.classList.remove('ring-2', 'ring-primary-500')"
                             x-on:drop="
                                 $el.classList.remove('ring-2', 'ring-primary-500');
                                 if(draggedBlock && draggedBlock !== {{ $block['id'] }}) {
                                     let blocks = [...document.querySelectorAll('[data-block-id]')].map(el => parseInt(el.dataset.blockId));
                                     let fromIdx = blocks.indexOf(draggedBlock);
                                     let toIdx = blocks.indexOf({{ $block['id'] }});
                                     blocks.splice(fromIdx, 1);
                                     blocks.splice(toIdx, 0, draggedBlock);
                                     $wire.call('reorderBlocks', blocks);
                                 }
                             "
                             data-block-id="{{ $block['id'] }}"
                         >
                             {{-- Block Header --}}
                             <div class="p-3 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-t-xl cursor-move">
                                 <div class="flex items-center justify-between">
                                     @if($editingBlockId === $block['id'])
                                         <div class="flex-1 flex items-center gap-2">
                                             <input 
                                                 type="text" 
                                                 wire:model="editLabel"
                                                 class="flex-1 px-2 py-1 text-sm rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700"
                                                 wire:keydown.enter="saveBlock"
                                                 wire:keydown.escape="resetEditing"
                                                 autofocus
                                             />
                                             <button wire:click="saveBlock" class="p-1 text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 rounded">
                                                 <x-heroicon-s-check class="w-4 h-4"/>
                                             </button>
                                             <button wire:click="resetEditing" class="p-1 text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded">
                                                 <x-heroicon-s-x-mark class="w-4 h-4"/>
                                             </button>
                                         </div>
                                     @else
                                         <div class="flex items-center gap-2">
                                             <x-heroicon-o-bars-3 class="w-4 h-4 text-gray-400"/>
                                             <h3 
                                                 class="font-semibold text-gray-900 dark:text-white cursor-pointer hover:text-primary-600"
                                                 wire:click="startEditBlock({{ $block['id'] }})"
                                             >
                                                 {{ $block['title'] }}
                                             </h3>
                                         </div>
                                         <div class="flex items-center gap-1">
                                             <button 
                                                 wire:click="toggleBlockActive({{ $block['id'] }})"
                                                 class="p-1 rounded hover:bg-gray-200 dark:hover:bg-gray-700 {{ $block['active'] ? 'text-green-600' : 'text-gray-400' }}"
                                                 title="{{ $block['active'] ? 'Đang hiện' : 'Đang ẩn' }}"
                                             >
                                                 @if($block['active'])
                                                     <x-heroicon-s-eye class="w-4 h-4"/>
                                                 @else
                                                     <x-heroicon-s-eye-slash class="w-4 h-4"/>
                                                 @endif
                                             </button>
                                             <button 
                                                 wire:click="deleteBlock({{ $block['id'] }})"
                                                 wire:confirm="Xóa cột này và tất cả items trong đó?"
                                                 class="p-1 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded"
                                             >
                                                 <x-heroicon-s-trash class="w-4 h-4"/>
                                             </button>
                                         </div>
                                     @endif
                                 </div>
                             </div>
 
                             {{-- Items List --}}
                             <div 
                                 class="p-2 space-y-1 min-h-[100px]"
                                 x-on:dragover.prevent="if(draggedItem) $el.classList.add('bg-primary-50', 'dark:bg-primary-900/20')"
                                 x-on:dragleave="$el.classList.remove('bg-primary-50', 'dark:bg-primary-900/20')"
                                 x-on:drop="
                                     $el.classList.remove('bg-primary-50', 'dark:bg-primary-900/20');
                                     if(draggedItem) {
                                         let items = [...$el.querySelectorAll('[data-item-id]')].map(el => parseInt(el.dataset.itemId));
                                         if(!items.includes(draggedItem)) items.push(draggedItem);
                                         $wire.call('reorderItems', {{ $block['id'] }}, items);
                                     }
                                 "
                                 data-block-id="{{ $block['id'] }}"
                             >
                                 @foreach($block['items'] as $item)
                                     <div 
                                         class="group flex items-center gap-2 p-2 rounded-lg transition-all {{ $item['active'] ? 'bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700' : 'bg-gray-100 dark:bg-gray-800 opacity-50' }}"
                                         draggable="true"
                                         x-on:dragstart="draggedItem = {{ $item['id'] }}; draggedFromBlock = {{ $block['id'] }}"
                                         x-on:dragend="draggedItem = null; draggedFromBlock = null"
                                         data-item-id="{{ $item['id'] }}"
                                     >
                                         @if($editingItemId === $item['id'])
                                             <div class="flex-1 space-y-2">
                                                 <input 
                                                     type="text" 
                                                     wire:model="editLabel"
                                                     placeholder="Tiêu đề"
                                                     class="w-full px-2 py-1 text-sm rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700"
                                                     wire:keydown.enter="saveItem"
                                                 />
                                                 <input 
                                                     type="text" 
                                                     wire:model="editHref"
                                                     placeholder="URL (VD: /filter?type=1)"
                                                     class="w-full px-2 py-1 text-sm font-mono rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700"
                                                 />
                                                 <input 
                                                     type="text" 
                                                     wire:model="editBadge"
                                                     placeholder="Badge (HOT, NEW...)"
                                                     class="w-full px-2 py-1 text-sm rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700"
                                                 />
                                                 <div class="flex gap-2">
                                                     <button wire:click="saveItem" class="px-2 py-1 text-xs bg-green-600 text-white rounded hover:bg-green-700">Lưu</button>
                                                     <button wire:click="resetEditing" class="px-2 py-1 text-xs bg-gray-200 dark:bg-gray-600 rounded hover:bg-gray-300">Hủy</button>
                                                 </div>
                                             </div>
                                         @else
                                             <x-heroicon-o-bars-2 class="w-3 h-3 text-gray-400 cursor-move"/>
                                             <div 
                                                 class="flex-1 cursor-pointer"
                                                 wire:click="startEditItem({{ $item['id'] }})"
                                             >
                                                 <div class="flex items-center gap-2">
                                                     <span class="text-sm text-gray-700 dark:text-gray-300">{{ $item['label'] }}</span>
                                                     @if($item['badge'])
                                                         <span class="px-1.5 py-0.5 text-[10px] font-bold uppercase rounded {{ strtoupper($item['badge']) === 'HOT' ? 'bg-red-500 text-white' : 'bg-yellow-400 text-gray-900' }}">
                                                             {{ $item['badge'] }}
                                                         </span>
                                                     @endif
                                                 </div>
                                                 <div class="text-xs text-gray-400 font-mono truncate">{{ $item['href'] }}</div>
                                             </div>
                                             <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                 <button 
                                                     wire:click="toggleItemActive({{ $item['id'] }})"
                                                     class="p-1 rounded {{ $item['active'] ? 'text-green-600' : 'text-gray-400' }} hover:bg-gray-200 dark:hover:bg-gray-600"
                                                 >
                                                     @if($item['active'])
                                                         <x-heroicon-s-eye class="w-3 h-3"/>
                                                     @else
                                                         <x-heroicon-s-eye-slash class="w-3 h-3"/>
                                                     @endif
                                                 </button>
                                                 <button 
                                                     wire:click="deleteItem({{ $item['id'] }})"
                                                     wire:confirm="Xóa item này?"
                                                     class="p-1 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded"
                                                 >
                                                     <x-heroicon-s-trash class="w-3 h-3"/>
                                                 </button>
                                             </div>
                                         @endif
                                     </div>
                                 @endforeach
 
                                 {{-- Add Item Form --}}
                                 <div 
                                     class="mt-2 p-2 border-2 border-dashed border-gray-200 dark:border-gray-600 rounded-lg"
                                     x-data="{ showForm: false }"
                                 >
                                     <button 
                                         x-show="!showForm"
                                         x-on:click="showForm = true"
                                         class="w-full py-1 text-sm text-gray-500 hover:text-primary-600 flex items-center justify-center gap-1"
                                     >
                                         <x-heroicon-o-plus class="w-4 h-4"/>
                                         Thêm item
                                     </button>
                                     <div x-show="showForm" x-cloak class="space-y-2">
                                         <input 
                                             type="text" 
                                             wire:model="newItemLabel"
                                             placeholder="Tiêu đề item"
                                             class="w-full px-2 py-1 text-sm rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700"
                                         />
                                         <input 
                                             type="text" 
                                             wire:model="newItemHref"
                                             placeholder="URL (VD: /filter?type=1)"
                                             class="w-full px-2 py-1 text-sm font-mono rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700"
                                         />
                                         <div class="flex gap-2">
                                             <button 
                                                 wire:click="addItem({{ $block['id'] }})"
                                                 class="px-2 py-1 text-xs bg-primary-600 text-white rounded hover:bg-primary-700"
                                             >
                                                 Thêm
                                             </button>
                                             <button 
                                                 x-on:click="showForm = false"
                                                 class="px-2 py-1 text-xs bg-gray-200 dark:bg-gray-600 rounded hover:bg-gray-300"
                                             >
                                                 Hủy
                                             </button>
                                         </div>
                                     </div>
                                 </div>
                             </div>
                         </div>
                     @endforeach
 
                     {{-- Add Block Card --}}
                     <div 
                         class="bg-gray-50 dark:bg-gray-800/50 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600 p-4 flex flex-col items-center justify-center min-h-[200px]"
                         x-data="{ showForm: false }"
                     >
                         <div x-show="!showForm" class="text-center">
                             <x-heroicon-o-plus-circle class="w-12 h-12 mx-auto text-gray-400 mb-2"/>
                             <button 
                                 x-on:click="showForm = true"
                                 class="text-sm text-gray-600 dark:text-gray-400 hover:text-primary-600 font-medium"
                             >
                                 Thêm cột mới
                             </button>
                         </div>
                         <div x-show="showForm" x-cloak class="w-full space-y-3">
                             <input 
                                 type="text" 
                                 wire:model="newBlockTitle"
                                 placeholder="Tiêu đề cột (VD: Theo loại)"
                                 class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700"
                             />
                             <div class="flex gap-2">
                                 <button 
                                     wire:click="addBlock"
                                     class="flex-1 px-3 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 text-sm font-medium"
                                 >
                                     Thêm cột
                                 </button>
                                 <button 
                                     x-on:click="showForm = false"
                                     class="px-3 py-2 bg-gray-200 dark:bg-gray-600 rounded-lg hover:bg-gray-300 text-sm"
                                 >
                                     Hủy
                                 </button>
                             </div>
                         </div>
                     </div>
                 </div>
             @else
                 {{-- Standard Menu (no mega) --}}
                 <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-6 text-center">
                     <x-heroicon-o-information-circle class="w-12 h-12 mx-auto text-yellow-500 mb-3"/>
                     <p class="text-yellow-800 dark:text-yellow-200">
                         Menu này là loại <strong>Link đơn</strong>, không có mega menu.<br>
                         Đổi sang loại <strong>Mega menu</strong> trong trang chỉnh sửa để thêm các cột và items.
                     </p>
                     <a 
                         href="{{ \App\Filament\Resources\Menus\MenuResource::getUrl('edit', ['record' => $selectedMenu]) }}"
                         class="inline-flex items-center gap-2 mt-4 px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700"
                     >
                         <x-heroicon-o-pencil class="w-4 h-4"/>
                         Chỉnh sửa menu
                     </a>
                 </div>
             @endif
         @else
             <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-12 text-center">
                 <x-heroicon-o-rectangle-stack class="w-16 h-16 mx-auto text-gray-400 mb-4"/>
                 <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Chưa có menu nào</h3>
                 <p class="text-gray-500 mb-4">Tạo menu mới để bắt đầu</p>
                 <a 
                     href="{{ \App\Filament\Resources\Menus\MenuResource::getUrl('create') }}"
                     class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700"
                 >
                     <x-heroicon-o-plus class="w-4 h-4"/>
                     Tạo menu mới
                 </a>
             </div>
         @endif
     </div>
 </x-filament-panels::page>
