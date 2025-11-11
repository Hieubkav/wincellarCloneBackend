<?php

namespace App\Filament\Pages;

use App\Models\Image;
use App\Models\Setting;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
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

    protected static UnitEnum | string | null $navigationGroup = 'Cấu hình';

    protected static ?int $navigationSort = 100;

    protected static ?string $title = 'Cài đặt chung';

    protected static ?string $navigationLabel = 'Cài đặt chung';

    public ?array $data = [];

    public function mount(): void
    {
        $setting = Setting::first();

        if (!$setting) {
            $setting = Setting::create([]);
        }

        $this->form->fill($setting->toArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Grid::make()
                    ->columns(2)
                    ->schema([
                        TextInput::make('site_name')
                            ->label('Tên website')
                            ->maxLength(255),
                        TextInput::make('hotline')
                            ->label('Số hotline')
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Email liên hệ')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('address')
                            ->label('Địa chỉ')
                            ->maxLength(500)
                            ->columnSpanFull(),
                        Textarea::make('hours')
                            ->label('Giờ làm việc')
                            ->rows(3)
                            ->columnSpanFull(),
                        Textarea::make('google_map_embed')
                            ->label('Google Map nhúng (iframe code)')
                            ->placeholder('<iframe src="https://www.google.com/maps/embed?..." width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
                Grid::make()
                    ->columns(2)
                    ->schema([
                        Select::make('logo_image_id')
                            ->label('Logo')
                            ->options(
                                Image::where('active', true)
                                    ->get()
                                    ->mapWithKeys(function ($image) {
                                        $url = \Storage::disk($image->disk ?? 'public')->url($image->file_path);
                                        $fileName = basename($image->file_path);
                                        
                                        return [
                                            $image->id => '<div style="display: flex; align-items: center; gap: 0.5rem;">
                                                <img src="' . $url . '" style="width: 40px; height: 40px; object-fit: cover; border-radius: 0.25rem;" />
                                                <span style="font-size: 0.875rem;">' . $fileName . '</span>
                                            </div>'
                                        ];
                                    })
                            )
                            ->allowHtml()
                            ->searchable(),
                        Select::make('favicon_image_id')
                            ->label('Favicon')
                            ->options(
                                Image::where('active', true)
                                    ->get()
                                    ->mapWithKeys(function ($image) {
                                        $url = \Storage::disk($image->disk ?? 'public')->url($image->file_path);
                                        $fileName = basename($image->file_path);
                                        
                                        return [
                                            $image->id => '<div style="display: flex; align-items: center; gap: 0.5rem;">
                                                <img src="' . $url . '" style="width: 40px; height: 40px; object-fit: cover; border-radius: 0.25rem;" />
                                                <span style="font-size: 0.875rem;">' . $fileName . '</span>
                                            </div>'
                                        ];
                                    })
                            )
                            ->allowHtml()
                            ->searchable(),
                    ]),
                Grid::make()
                    ->columns(2)
                    ->schema([
                        TextInput::make('meta_default_title')
                            ->label('Tiêu đề SEO mặc định')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('meta_default_description')
                            ->label('Mô tả SEO mặc định')
                            ->rows(2)
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('meta_default_keywords')
                            ->label('Từ khóa SEO mặc định')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
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
            ->title('Đã lưu cài đặt thành công!')
            ->success()
            ->send();
    }
}
