<?php

namespace App\Filament\Pages;

use App\Models\Image;
use App\Models\Setting;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class SettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected string $view = 'filament.pages.settings-page';

    protected static UnitEnum | string | null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 100;

    protected static ?string $title = 'Settings';

    public ?array $data = [];

    public function mount(): void
    {
        $setting = Setting::first();

        if (!$setting) {
            $setting = Setting::create([]);
        }

        $this->form->fill($setting->toArray());
    }

    public function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->components([
                Grid::make()
                    ->columns(2)
                    ->schema([
                        TextInput::make('site_name')
                            ->maxLength(255),
                        TextInput::make('hotline')
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('address')
                            ->maxLength(500),
                        Textarea::make('hours')
                            ->rows(3),
                    ]),
                Grid::make()
                    ->columns(2)
                    ->schema([
                        Select::make('logo_image_id')
                            ->label('Logo Image')
                            ->options(Image::where('active', true)->pluck('file_path', 'id'))
                            ->searchable(),
                        Select::make('favicon_image_id')
                            ->label('Favicon Image')
                            ->options(Image::where('active', true)->pluck('file_path', 'id'))
                            ->searchable(),
                    ]),
                Grid::make()
                    ->columns(2)
                    ->schema([
                        TextInput::make('meta_default_title')
                            ->maxLength(255),
                        Textarea::make('meta_default_description')
                            ->rows(2)
                            ->maxLength(255),
                        TextInput::make('meta_default_keywords')
                            ->maxLength(255),
                    ]),
            ]);
    }

    public function save(): void
    {
        $setting = Setting::first();

        if (!$setting) {
            $setting = Setting::create($this->form->getState());
        } else {
            $setting->update($this->form->getState());
        }

        Notification::make()
            ->title('Settings saved successfully!')
            ->success()
            ->send();
    }
}
