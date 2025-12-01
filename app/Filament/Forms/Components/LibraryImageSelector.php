<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;
use Filament\Forms\Get;
use Filament\Forms\Set;

class LibraryImageSelector extends Field
{
    protected string $view = 'filament.forms.components.library-image-selector';

    protected int $perPage = 12;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->default([])
            ->reactive()
            ->afterStateUpdated(function (Set $set, $state) {
                $state = is_array($state) ? $state : (is_string($state) ? json_decode($state, true) : []);
                $set($this->getName(), $state);
            });
    }

    public function perPage(int $perPage): static
    {
        $this->perPage = $perPage;
        return $this;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }
}
