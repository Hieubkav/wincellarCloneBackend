<?php

namespace App\Filament\Resources\HomeComponents\Schemas;

use App\Enums\HomeComponentType;
use App\Models\Article;
use App\Models\CatalogTerm;
use App\Models\Image;
use App\Models\Product;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class HomeComponentForm
{
    protected static function getImageOptionsWithPreview(): array
    {
        $images = Image::query()
            ->where('active', true)
            ->orderBy('created_at', 'desc')
            ->limit(200)
            ->get();

        return $images->mapWithKeys(function ($image) {
            $filename = basename($image->file_path);
            $imageUrl = $image->url ?? '/images/placeholder.png';
            
            $html = '<div style="display: flex; align-items: center; gap: 8px;">';
            $html .= '<img src="' . e($imageUrl) . '" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #e5e7eb;" />';
            $html .= '<div style="display: flex; flex-direction: column;">';
            $html .= '<span style="font-weight: 500;">' . e($image->alt ?: $filename) . '</span>';
            if ($image->width && $image->height) {
                $html .= '<span style="font-size: 0.75rem; color: #6b7280;">' . $image->width . 'x' . $image->height . '</span>';
            }
            $html .= '</div>';
            $html .= '</div>';
            
            return [$image->id => $html];
        })->toArray();
    }

    protected static function getProductOptionsWithPreview(): array
    {
        $products = Product::query()
            ->with('images')
            ->where('active', true)
            ->orderBy('created_at', 'desc')
            ->limit(200)
            ->get();

        return $products->mapWithKeys(function ($product) {
            $imageUrl = $product->cover_image_url ?? '/images/placeholder.png';
            
            $html = '<div style="display: flex; align-items: center; gap: 10px;">';
            $html .= '<img src="' . e($imageUrl) . '" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #e5e7eb;" />';
            $html .= '<div style="display: flex; flex-direction: column; gap: 2px;">';
            $html .= '<span style="font-weight: 500; color: #111827;">' . e($product->name) . '</span>';
            
            $priceHtml = '<div style="display: flex; gap: 8px; align-items: center;">';
            $priceHtml .= '<span style="font-size: 0.875rem; color: #059669; font-weight: 600;">' . number_format($product->price) . ' ₫</span>';
            
            if ($product->original_price && $product->original_price > $product->price) {
                $priceHtml .= '<span style="font-size: 0.75rem; color: #9ca3af; text-decoration: line-through;">' . number_format($product->original_price) . ' ₫</span>';
            }
            $priceHtml .= '</div>';
            
            $html .= $priceHtml;
            $html .= '</div>';
            $html .= '</div>';
            
            return [$product->id => $html];
        })->toArray();
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Thông tin cơ bản')
                    ->columns(2)
                    ->schema([
                        Select::make('type')
                            ->label('Loại khối giao diện')
                            ->options(HomeComponentType::options())
                            ->required()
                            ->live()
                            ->helperText(fn (Get $get) => self::getTypeDescription($get('type'))),
                        Toggle::make('active')
                            ->label('Đang hiển thị')
                            ->default(true)
                            ->inline(false),
                    ]),

                Section::make('Cấu hình nội dung')
                    ->schema([
                        Grid::make(1)
                            ->schema(fn (Get $get): array => self::getConfigFields($get('type'))),
                    ])
                    ->visible(fn (Get $get) => $get('type') !== null),
            ]);
    }

    protected static function getTypeDescription(?string $type): ?string
    {
        if (!$type) {
            return null;
        }

        $enum = HomeComponentType::tryFrom($type);
        return $enum?->getDescription();
    }

    protected static function getConfigFields(?string $type): array
    {
        if (!$type) {
            return [];
        }

        return match ($type) {
            HomeComponentType::HeroCarousel->value => self::heroCarouselFields(),
            HomeComponentType::DualBanner->value => self::dualBannerFields(),
            HomeComponentType::CategoryGrid->value => self::categoryGridFields(),
            HomeComponentType::FavouriteProducts->value => self::favouriteProductsFields(),
            HomeComponentType::BrandShowcase->value => self::brandShowcaseFields(),
            HomeComponentType::CollectionShowcase->value => self::collectionShowcaseFields(),
            HomeComponentType::EditorialSpotlight->value => self::editorialSpotlightFields(),
            HomeComponentType::Footer->value => self::footerFields(),
            default => [],
        };
    }

    protected static function heroCarouselFields(): array
    {
        return [
            Repeater::make('config.slides')
                ->label('Danh sách slide')
                ->schema([
                    Select::make('image_id')
                        ->label('Hình ảnh')
                        ->options(fn () => self::getImageOptionsWithPreview())
                        ->allowHtml()
                        ->searchable()
                        ->required()
                        ->preload(),
                    TextInput::make('href')
                        ->label('Link đến (URL)')
                        ->url()
                        ->placeholder('https://example.com/products'),
                    TextInput::make('alt')
                        ->label('Mô tả ảnh (Alt text)')
                        ->placeholder('Banner khuyến mãi'),
                ])
                ->columnSpanFull()
                ->defaultItems(1)
                ->addActionLabel('Thêm slide')
                ->collapsible()
                ->itemLabel(fn (array $state): ?string => $state['alt'] ?? 'Slide mới'),
        ];
    }

    protected static function dualBannerFields(): array
    {
        return [
            Repeater::make('config.banners')
                ->label('Danh sách banner')
                ->schema([
                    Select::make('image_id')
                        ->label('Hình ảnh')
                        ->options(fn () => self::getImageOptionsWithPreview())
                        ->allowHtml()
                        ->searchable()
                        ->required()
                        ->preload(),
                    TextInput::make('href')
                        ->label('Link đến (URL)')
                        ->url(),
                    TextInput::make('alt')
                        ->label('Mô tả ảnh (Alt text)'),
                ])
                ->columnSpanFull()
                ->minItems(2)
                ->maxItems(2)
                ->defaultItems(2)
                ->addActionLabel('Thêm banner')
                ->collapsible()
                ->itemLabel(fn (array $state): ?string => $state['alt'] ?? 'Banner'),
        ];
    }

    protected static function categoryGridFields(): array
    {
        return [
            Repeater::make('config.categories')
                ->label('Danh sách danh mục')
                ->schema([
                    Grid::make()
                        ->columns(2)
                        ->schema([
                            Select::make('term_id')
                                ->label('Danh mục')
                                ->options(fn () => CatalogTerm::pluck('name', 'id'))
                                ->searchable()
                                ->required()
                                ->preload()
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    if ($state && !$get('title')) {
                                        $term = CatalogTerm::find($state);
                                        if ($term) {
                                            $set('title', $term->name);
                                        }
                                    }
                                }),
                            Select::make('image_id')
                                ->label('Hình ảnh nền')
                                ->options(fn () => self::getImageOptionsWithPreview())
                                ->allowHtml()
                                ->searchable()
                                ->required()
                                ->preload(),
                        ]),
                    TextInput::make('title')
                        ->label('Tiêu đề hiển thị')
                        ->placeholder('VANG ĐỎ')
                        ->required()
                        ->helperText('Tự động lấy từ tên danh mục, có thể chỉnh sửa'),
                    TextInput::make('href')
                        ->label('Link đến (URL)')
                        ->url()
                        ->placeholder('/categories/vang-do')
                        ->helperText('Để trống sẽ tự động dùng link của danh mục'),
                ])
                ->columnSpanFull()
                ->defaultItems(1)
                ->addActionLabel('Thêm danh mục')
                ->collapsible()
                ->itemLabel(fn (array $state): ?string => 
                    $state['title'] ?? ($state['term_id'] ? CatalogTerm::find($state['term_id'])?->name : 'Danh mục mới')
                ),
        ];
    }

    protected static function favouriteProductsFields(): array
    {
        return [
            TextInput::make('config.title')
                ->label('Tiêu đề')
                ->placeholder('Sản phẩm yêu thích'),
            TextInput::make('config.subtitle')
                ->label('Tiêu đề phụ')
                ->placeholder('Được khách hàng đánh giá cao'),
            Repeater::make('config.products')
                ->label('Danh sách sản phẩm')
                ->schema([
                    Select::make('product_id')
                        ->label('Sản phẩm')
                        ->options(fn () => self::getProductOptionsWithPreview())
                        ->allowHtml()
                        ->searchable()
                        ->required()
                        ->preload(),
                ])
                ->simple(Select::make('product_id')
                    ->label('Sản phẩm')
                    ->options(fn () => self::getProductOptionsWithPreview())
                    ->allowHtml()
                    ->searchable()
                    ->required()
                    ->preload()
                )
                ->columnSpanFull()
                ->defaultItems(1)
                ->addActionLabel('Thêm sản phẩm'),
        ];
    }

    protected static function brandShowcaseFields(): array
    {
        return [
            TextInput::make('config.title')
                ->label('Tiêu đề')
                ->placeholder('Thương hiệu đối tác'),
            Repeater::make('config.brands')
                ->label('Danh sách thương hiệu')
                ->schema([
                    Select::make('image_id')
                        ->label('Logo thương hiệu')
                        ->options(fn () => self::getImageOptionsWithPreview())
                        ->allowHtml()
                        ->searchable()
                        ->required()
                        ->preload(),
                    TextInput::make('href')
                        ->label('Link đến (URL)')
                        ->url()
                        ->placeholder('https://example.com'),
                    TextInput::make('alt')
                        ->label('Tên thương hiệu (Alt text)')
                        ->placeholder('Tên thương hiệu'),
                ])
                ->columnSpanFull()
                ->defaultItems(1)
                ->addActionLabel('Thêm thương hiệu')
                ->collapsible()
                ->itemLabel(fn (array $state): ?string => $state['alt'] ?? 'Thương hiệu mới'),
        ];
    }

    protected static function collectionShowcaseFields(): array
    {
        return [
            Grid::make()
                ->columns(2)
                ->schema([
                    TextInput::make('config.title')
                        ->label('Tiêu đề')
                        ->rules(['required'])
                        ->placeholder('Rượu Vang'),
                    TextInput::make('config.subtitle')
                        ->label('Tiêu đề phụ')
                        ->placeholder('Rượu Vang'),
                    Textarea::make('config.description')
                        ->label('Mô tả')
                        ->rows(2)
                        ->placeholder('Khám phá những dòng rượu vang cao cấp...')
                        ->columnSpanFull(),
                    TextInput::make('config.ctaLabel')
                        ->label('Text nút xem thêm')
                        ->placeholder('Xem Thêm'),
                    TextInput::make('config.ctaHref')
                        ->label('Link nút xem thêm')
                        ->url()
                        ->placeholder('/wines'),
                    Select::make('config.tone')
                        ->label('Giao diện màu sắc')
                        ->options([
                            'wine' => 'Wine (Đỏ rượu vang)',
                            'spirit' => 'Spirit (Vàng rượu mạnh)',
                            'default' => 'Default (Mặc định)',
                        ])
                        ->default('default')
                        ->columnSpan(1),
                ]),
            Repeater::make('config.products')
                ->label('Danh sách sản phẩm')
                ->schema([
                    Select::make('product_id')
                        ->label('Sản phẩm')
                        ->options(fn () => self::getProductOptionsWithPreview())
                        ->allowHtml()
                        ->searchable()
                        ->required()
                        ->preload(),
                ])
                ->simple(Select::make('product_id')
                    ->label('Sản phẩm')
                    ->options(fn () => self::getProductOptionsWithPreview())
                    ->allowHtml()
                    ->searchable()
                    ->required()
                    ->preload()
                )
                ->columnSpanFull()
                ->defaultItems(1)
                ->addActionLabel('Thêm sản phẩm'),
        ];
    }

    protected static function editorialSpotlightFields(): array
    {
        return [
            TextInput::make('config.label')
                ->label('Nhãn')
                ->placeholder('Chuyện rượu'),
            TextInput::make('config.title')
                ->label('Tiêu đề')
                ->placeholder('Bài viết'),
            Textarea::make('config.description')
                ->label('Mô tả')
                ->rows(2)
                ->placeholder('Tập trung vào trải nghiệm sang trọng...'),
            Repeater::make('config.articles')
                ->label('Danh sách bài viết')
                ->schema([
                    Select::make('article_id')
                        ->label('Bài viết')
                        ->options(fn () => Article::pluck('title', 'id'))
                        ->searchable()
                        ->required()
                        ->preload(),
                ])
                ->simple(Select::make('article_id')
                    ->label('Bài viết')
                    ->options(fn () => Article::pluck('title', 'id'))
                    ->searchable()
                    ->required()
                    ->preload()
                )
                ->columnSpanFull()
                ->defaultItems(1)
                ->addActionLabel('Thêm bài viết'),
        ];
    }

    protected static function footerFields(): array
    {
        return [
            TextInput::make('config.company_name')
                ->label('Tên công ty')
                ->placeholder('Thiên Kim Wine'),
            Textarea::make('config.description')
                ->label('Mô tả công ty')
                ->rows(3),
            TextInput::make('config.email')
                ->label('Email')
                ->email()
                ->placeholder('contact@example.com'),
            TextInput::make('config.phone')
                ->label('Số điện thoại')
                ->tel()
                ->placeholder('0123 456 789'),
            TextInput::make('config.address')
                ->label('Địa chỉ')
                ->placeholder('123 Đường ABC, Quận 1, TP.HCM'),
            Repeater::make('config.social_links')
                ->label('Liên kết mạng xã hội')
                ->schema([
                    Select::make('platform')
                        ->label('Nền tảng')
                        ->options([
                            'facebook' => 'Facebook',
                            'instagram' => 'Instagram',
                            'youtube' => 'YouTube',
                            'tiktok' => 'TikTok',
                            'zalo' => 'Zalo',
                        ])
                        ->required(),
                    TextInput::make('url')
                        ->label('URL')
                        ->url()
                        ->required(),
                ])
                ->columnSpanFull()
                ->collapsible()
                ->itemLabel(fn (array $state): ?string => $state['platform'] ?? 'Link mới'),
        ];
    }
}

