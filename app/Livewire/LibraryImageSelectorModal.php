<?php

namespace App\Livewire;

use App\Models\Image;
use Livewire\Component;
use Livewire\WithPagination;

class LibraryImageSelectorModal extends Component
{
    use WithPagination;

    public array $selectedIds = [];
    public string $search = '';
    public int $perPage = 12;
    public bool $modalOpen = false;

    protected $queryString = ['search' => ['except' => ''], 'page' => ['except' => 1]];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function toggleImage(int $imageId): void
    {
        if (in_array($imageId, $this->selectedIds)) {
            $this->selectedIds = array_filter($this->selectedIds, fn($id) => $id !== $imageId);
        } else {
            $this->selectedIds[] = $imageId;
        }
        
        $this->dispatch('images-selected', $this->selectedIds);
    }

    public function toggleAll(): void
    {
        $images = $this->getImages();
        $imageIds = $images->pluck('id')->toArray();
        
        $allSelected = count(array_intersect($this->selectedIds, $imageIds)) === count($imageIds);
        
        if ($allSelected) {
            $this->selectedIds = array_filter($this->selectedIds, fn($id) => !in_array($id, $imageIds));
        } else {
            $this->selectedIds = array_unique([...$this->selectedIds, ...$imageIds]);
        }
        
        $this->dispatch('images-selected', $this->selectedIds);
    }

    public function getImages()
    {
        return Image::query()
            ->where('active', true)
            ->whereNull('deleted_at')
            ->when($this->search, fn($q) => $q->where('alt', 'like', "%{$this->search}%")
                ->orWhere('file_path', 'like', "%{$this->search}%"))
            ->orderByDesc('created_at')
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.library-image-selector-modal', [
            'images' => $this->getImages(),
        ]);
    }
}
