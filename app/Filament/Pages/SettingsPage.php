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

    protected static UnitEnum | string | null $navigationGroup = 'Cấu hình';

    protected static ?int $navigationSort = 100;

    protected static ?string $title = 'Cài đặt chung';

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
                            ->label('Tên website')
                            ->helperText('Tên hiển thị trên website')
                            ->maxLength(255),
                        TextInput::make('hotline')
                            ->label('Số hotline')
                            ->helperText('Số điện thoại liên hệ chính')
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Email liên hệ')
                            ->helperText('Địa chỉ email chính')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('address')
                            ->label('Địa chỉ')
                            ->helperText('Địa chỉ cửa hàng/văn phòng')
                            ->maxLength(500)
                            ->columnSpanFull(),
                        Textarea::make('hours')
                            ->label('Giờ làm việc')
                            ->helperText('Thời gian mở cửa. Ví dụ: 8:00 - 22:00 hàng ngày')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                Grid::make()
                    ->columns(2)
                    ->schema([
                        Select::make('logo_image_id')
                            ->label('Logo')
                            ->helperText('Chọn hình logo từ thư viện ảnh')
                            ->options(Image::where('active', true)->pluck('file_path', 'id'))
                            ->searchable(),
                        Select::make('favicon_image_id')
                            ->label('Favicon')
                            ->helperText('Icon nhỏ hiển thị trên tab trình duyệt')
                            ->options(Image::where('active', true)->pluck('file_path', 'id'))
                            ->searchable(),
                    ]),
                Grid::make()
                    ->columns(2)
                    ->schema([
                        TextInput::make('meta_default_title')
                            ->label('Tiêu đề SEO mặc định')
                            ->helperText('Tiêu đề mặc định cho Google (tối đa 60 ký tự)')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('meta_default_description')
                            ->label('Mô tả SEO mặc định')
                            ->helperText('Mô tả mặc định cho Google (tối đa 160 ký tự)')
                            ->rows(2)
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('meta_default_keywords')
                            ->label('Từ khóa SEO mặc định')
                            ->helperText('Các từ khóa cách nhau bởi dấu phẩy')
                            ->maxLength(255)
                            ->columnSpanFull(),
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
            ->title('Đã lưu cài đặt thành công!')
            ->success()
            ->send();
    }
}
