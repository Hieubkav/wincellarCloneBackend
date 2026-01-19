<?php

namespace App\Filament\Pages;

use App\Models\Image;
use App\Models\Setting;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Radio;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
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
                Tabs::make('Cài đặt')
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('Thông tin')
                            ->icon('heroicon-o-globe-alt')
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
                                    ->maxLength(500),
                                Textarea::make('hours')
                                    ->label('Giờ làm việc')
                                    ->rows(3),
                            ])
                            ->columns(2),
                        Tab::make('Bản đồ')
                            ->icon('heroicon-o-map-pin')
                            ->schema([
                                Textarea::make('google_map_embed')
                                    ->label('Google Map nhúng (iframe code)')
                                    ->placeholder('<iframe src="https://www.google.com/maps/embed?..." width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>')
                                    ->rows(6),
                            ]),
                        Tab::make('Branding')
                            ->icon('heroicon-o-photo')
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
                            ])
                            ->columns(2),
                        Tab::make('Watermark')
                            ->icon('heroicon-o-shield-check')
                            ->schema([
                                Radio::make('product_watermark_type')
                                    ->label('Loại watermark')
                                    ->options([
                                        'image' => 'Hình ảnh',
                                        'text' => 'Chữ',
                                    ])
                                    ->default('image')
                                    ->inline()
                                    ->live()
                                    ->columnSpanFull(),

                                Section::make('Watermark hình ảnh')
                                    ->schema([
                                        Select::make('product_watermark_image_id')
                                            ->label('Chọn hình ảnh')
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
                                        Select::make('product_watermark_position')
                                            ->label('Vị trí')
                                            ->options([
                                                'none' => 'Không hiển thị',
                                                'top_left' => 'Góc trên trái',
                                                'top_right' => 'Góc trên phải',
                                                'bottom_left' => 'Góc dưới trái',
                                                'bottom_right' => 'Góc dưới phải',
                                            ])
                                            ->default('none'),
                                        Select::make('product_watermark_size')
                                            ->label('Kích thước')
                                            ->options([
                                                '64x64' => '64 x 64 px',
                                                '96x96' => '96 x 96 px',
                                                '128x128' => '128 x 128 px',
                                                '160x160' => '160 x 160 px',
                                                '192x192' => '192 x 192 px',
                                            ])
                                            ->default('128x128'),
                                    ])
                                    ->columns(3)
                                    ->visible(fn ($get) => $get('product_watermark_type') === 'image'),

                                Section::make('Watermark chữ')
                                    ->schema([
                                        TextInput::make('product_watermark_text')
                                            ->label('Nội dung chữ')
                                            ->placeholder('VD: logo')
                                            ->maxLength(100),
                                        Select::make('product_watermark_text_size')
                                            ->label('Kích thước chữ')
                                            ->options([
                                                'small' => 'Nhỏ',
                                                'medium' => 'Vừa',
                                                'large' => 'Lớn',
                                                'xlarge' => 'Rất lớn',
                                            ])
                                            ->default('medium'),
                                        Select::make('product_watermark_text_position')
                                            ->label('Vị trí')
                                            ->options([
                                                'top' => 'Trên',
                                                'center' => 'Giữa',
                                                'bottom' => 'Dưới',
                                            ])
                                            ->default('center'),
                                        Select::make('product_watermark_text_opacity')
                                            ->label('Độ trong suốt')
                                            ->options([
                                                20 => '20%',
                                                30 => '30%',
                                                40 => '40%',
                                                50 => '50%',
                                                60 => '60%',
                                                70 => '70%',
                                                80 => '80%',
                                            ])
                                            ->default(50),
                                    ])
                                    ->columns(4)
                                    ->visible(fn ($get) => $get('product_watermark_type') === 'text'),
                            ])
                            ->columns(1),
                        Tab::make('SEO')
                            ->icon('heroicon-o-magnifying-glass')
                            ->schema([
                                TextInput::make('meta_default_title')
                                    ->label('Tiêu đề SEO mặc định')
                                    ->maxLength(255),
                                Textarea::make('meta_default_description')
                                    ->label('Mô tả SEO mặc định')
                                    ->rows(3)
                                    ->maxLength(255),
                                TagsInput::make('meta_default_keywords')
                                    ->label('Từ khóa SEO mặc định')
                                    ->placeholder('Nhập từ khóa và nhấn Enter')
                                    ->splitKeys(['Tab', ','])
                                    ->reorderable(),
                            ]),
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
