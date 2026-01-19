<?php

namespace App\Filament\Pages;
 
 use App\Models\Menu;
 use App\Models\MenuBlock;
 use App\Models\MenuBlockItem;
 use BackedEnum;
 use Filament\Notifications\Notification;
 use Filament\Pages\Page;
 use Filament\Support\Icons\Heroicon;
 use Illuminate\Support\Collection;
 use Livewire\Attributes\On;
 use UnitEnum;
 
 class MenuBuilderPage extends Page
 {
     protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
 
     protected string $view = 'filament.pages.menu-builder-page';
 
    protected static ?string $slug = 'menu-builder';

     protected static UnitEnum|string|null $navigationGroup = 'Điều hướng';
 
     protected static ?int $navigationSort = 5;
 
     protected static ?string $title = 'Menu Builder';
 
     protected static ?string $navigationLabel = 'Menu Builder';
 
     public ?int $selectedMenuId = null;
 
     public Collection $menus;
 
     public ?Menu $selectedMenu = null;
 
     public array $blocks = [];
 
     public ?int $editingBlockId = null;
 
     public ?int $editingItemId = null;
 
     public string $editLabel = '';
 
     public string $editHref = '';
 
     public string $editBadge = '';
 
     public string $newBlockTitle = '';
 
     public string $newItemLabel = '';
 
     public string $newItemHref = '';
 
     public function mount(): void
     {
         $this->menus = Menu::with(['blocks.items'])->orderBy('order')->get();
 
         if ($this->menus->isNotEmpty()) {
             $this->selectMenu($this->menus->first()->id);
         }
     }
 
     public function selectMenu(int $menuId): void
     {
         $this->selectedMenuId = $menuId;
         $this->selectedMenu = Menu::with(['blocks.items' => fn ($q) => $q->orderBy('order')])
             ->with(['blocks' => fn ($q) => $q->orderBy('order')])
             ->find($menuId);
 
         $this->blocks = $this->selectedMenu?->blocks->map(fn ($block) => [
             'id' => $block->id,
             'title' => $block->title,
             'active' => $block->active,
             'order' => $block->order,
             'items' => $block->items->map(fn ($item) => [
                 'id' => $item->id,
                 'label' => $item->label,
                 'href' => $item->href,
                 'badge' => $item->badge,
                 'active' => $item->active,
                 'order' => $item->order,
             ])->toArray(),
         ])->toArray() ?? [];
 
         $this->resetEditing();
     }
 
     public function refreshData(): void
     {
         if ($this->selectedMenuId) {
             $this->selectMenu($this->selectedMenuId);
         }
         $this->menus = Menu::with(['blocks.items'])->orderBy('order')->get();
     }
 
     public function resetEditing(): void
     {
         $this->editingBlockId = null;
         $this->editingItemId = null;
         $this->editLabel = '';
         $this->editHref = '';
         $this->editBadge = '';
     }
 
     public function startEditBlock(int $blockId): void
     {
         $this->resetEditing();
         $this->editingBlockId = $blockId;
         $block = MenuBlock::find($blockId);
         $this->editLabel = $block?->title ?? '';
     }
 
     public function saveBlock(): void
     {
         if (!$this->editingBlockId) {
             return;
         }
 
         MenuBlock::where('id', $this->editingBlockId)->update([
             'title' => $this->editLabel,
         ]);
 
         Notification::make()
             ->title('Đã lưu')
             ->success()
             ->send();
 
         $this->refreshData();
         $this->resetEditing();
     }
 
     public function startEditItem(int $itemId): void
     {
         $this->resetEditing();
         $this->editingItemId = $itemId;
         $item = MenuBlockItem::find($itemId);
         $this->editLabel = $item?->label ?? '';
         $this->editHref = $item?->href ?? '';
         $this->editBadge = $item?->badge ?? '';
     }
 
     public function saveItem(): void
     {
         if (!$this->editingItemId) {
             return;
         }
 
         MenuBlockItem::where('id', $this->editingItemId)->update([
             'label' => $this->editLabel,
             'href' => $this->editHref,
             'badge' => $this->editBadge ?: null,
         ]);
 
         Notification::make()
             ->title('Đã lưu')
             ->success()
             ->send();
 
         $this->refreshData();
         $this->resetEditing();
     }
 
     public function addBlock(): void
     {
         if (!$this->selectedMenuId || !$this->newBlockTitle) {
             return;
         }
 
         $maxOrder = MenuBlock::where('menu_id', $this->selectedMenuId)->max('order') ?? 0;
 
         MenuBlock::create([
             'menu_id' => $this->selectedMenuId,
             'title' => $this->newBlockTitle,
             'order' => $maxOrder + 1,
             'active' => true,
         ]);
 
         $this->newBlockTitle = '';
 
         Notification::make()
             ->title('Đã thêm cột mới')
             ->success()
             ->send();
 
         $this->refreshData();
     }
 
     public function addItem(int $blockId): void
     {
         if (!$this->newItemLabel) {
             return;
         }
 
         $maxOrder = MenuBlockItem::where('menu_block_id', $blockId)->max('order') ?? 0;
 
         MenuBlockItem::create([
             'menu_block_id' => $blockId,
             'label' => $this->newItemLabel,
             'href' => $this->newItemHref ?: '#',
             'order' => $maxOrder + 1,
             'active' => true,
         ]);
 
         $this->newItemLabel = '';
         $this->newItemHref = '';
 
         Notification::make()
             ->title('Đã thêm item mới')
             ->success()
             ->send();
 
         $this->refreshData();
     }
 
     public function deleteBlock(int $blockId): void
     {
         MenuBlockItem::where('menu_block_id', $blockId)->delete();
         MenuBlock::where('id', $blockId)->delete();
 
         Notification::make()
             ->title('Đã xóa cột')
             ->success()
             ->send();
 
         $this->refreshData();
     }
 
     public function deleteItem(int $itemId): void
     {
         MenuBlockItem::where('id', $itemId)->delete();
 
         Notification::make()
             ->title('Đã xóa item')
             ->success()
             ->send();
 
         $this->refreshData();
     }
 
     public function toggleBlockActive(int $blockId): void
     {
         $block = MenuBlock::find($blockId);
         if ($block) {
             $block->update(['active' => !$block->active]);
             $this->refreshData();
         }
     }
 
     public function toggleItemActive(int $itemId): void
     {
         $item = MenuBlockItem::find($itemId);
         if ($item) {
             $item->update(['active' => !$item->active]);
             $this->refreshData();
         }
     }
 
     #[On('reorder-blocks')]
     public function reorderBlocks(array $order): void
     {
         foreach ($order as $index => $blockId) {
             MenuBlock::where('id', $blockId)->update(['order' => $index]);
         }
         $this->refreshData();
     }
 
     #[On('reorder-items')]
     public function reorderItems(int $blockId, array $order): void
     {
         foreach ($order as $index => $itemId) {
             MenuBlockItem::where('id', $itemId)->update([
                 'menu_block_id' => $blockId,
                 'order' => $index,
             ]);
         }
         $this->refreshData();
     }
 
     public function updateMenuHref(string $href): void
     {
         if ($this->selectedMenu) {
             $this->selectedMenu->update(['href' => $href]);
             Notification::make()
                 ->title('Đã cập nhật link "Xem tất cả"')
                 ->success()
                 ->send();
             $this->refreshData();
         }
     }
 }
