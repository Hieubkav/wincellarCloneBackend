<?php

namespace App\Filament\Resources\HomeComponents\Schemas;

use App\Enums\HomeComponentType;
use App\Models\Article;
use App\Models\Image;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

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
            $html .= '<img src="'.e($imageUrl).'" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #e5e7eb;" />';
            $html .= '<div style="display: flex; flex-direction: column;">';
            $html .= '<span style="font-weight: 500;">'.e($image->alt ?: $filename).'</span>';
            if ($image->width && $image->height) {
                $html .= '<span style="font-size: 0.75rem; color: #6b7280;">'.$image->width.'x'.$image->height.'</span>';
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
            $html .= '<img src="'.e($imageUrl).'" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #e5e7eb;" />';
            $html .= '<div style="display: flex; flex-direction: column; gap: 2px;">';
            $html .= '<span style="font-weight: 500; color: #111827;">'.e($product->name).'</span>';

            $priceHtml = '<div style="display: flex; gap: 8px; align-items: center;">';
            $priceHtml .= '<span style="font-size: 0.875rem; color: #059669; font-weight: 600;">'.number_format($product->price).' ₫</span>';

            if ($product->original_price && $product->original_price > $product->price) {
                $priceHtml .= '<span style="font-size: 0.75rem; color: #9ca3af; text-decoration: line-through;">'.number_format($product->original_price).' ₫</span>';
            }
            $priceHtml .= '</div>';

            $html .= $priceHtml;
            $html .= '</div>';
            $html .= '</div>';

            return [$product->id => $html];
        })->toArray();
    }

    protected static function imageSelectWithQuickCreate(string $name = 'image_id', string $label = 'Hình ảnh'): Select
    {
        return Select::make($name)
            ->label($label)
            ->options(fn () => self::getImageOptionsWithPreview())
            ->allowHtml()
            ->searchable()
            ->required()
            ->preload()
            ->createOptionForm([
                FileUpload::make('file_path')
                    ->label('Tải lên hình ảnh')
                    ->required()
                    ->image()
                    ->imageEditor()
                    ->maxFiles(1)
                    ->maxSize(10240)
                    ->disk('public')
                    ->directory('media/images')
                    ->saveUploadedFileUsing(function (TemporaryUploadedFile $file) {
                        $filename = 'img-'.Str::uuid().'.webp';
                        $disk = 'public';
                        $path = 'media/images/'.$filename;

                        $manager = new ImageManager(new Driver);
                        $image = $manager->read($file->getRealPath());

                        if ($image->width() > 1920) {
                            $image->scale(width: 1920);
                        }

                        $webp = $image->toWebp(quality: 85);
                        Storage::disk($disk)->put($path, $webp);

                        return $path;
                    })
                    ->columnSpanFull()
                    ->helperText('Tải lên ảnh mới (tự động convert sang WebP, tối đa 1920px width)'),

                Toggle::make('active')
                    ->label('Đang hiển thị')
                    ->default(true)
                    ->inline(false),
            ])
            ->createOptionUsing(function (array $data): int {
                $image = Image::create([
                    'file_path' => $data['file_path'],
                    'active' => $data['active'] ?? true,
                    'disk' => 'public',
                ]);

                return $image->id;
            })
            ->createOptionAction(
                fn (Action $action) => $action
                    ->modalHeading('Tạo ảnh mới')
                    ->modalWidth('2xl')
                    ->modalSubmitActionLabel('Tạo ảnh')
            );
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
        if (! $type) {
            return null;
        }

        $enum = HomeComponentType::tryFrom($type);

        return $enum?->getDescription();
    }

    protected static function getConfigFields(?string $type): array
    {
        if (! $type) {
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
            HomeComponentType::SpeedDial->value => self::speedDialFields(),
            default => [],
        };
    }

    protected static function heroCarouselFields(): array
    {
        return [
            Repeater::make('config.slides')
                ->label('Danh sách slide')
                ->schema([
                    self::imageSelectWithQuickCreate('image_id', 'Hình ảnh'),
                    TextInput::make('href')
                        ->label('Link đến (URL)')
                        ->placeholder('/filter hoặc https://example.com'),
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
                    self::imageSelectWithQuickCreate('image_id', 'Hình ảnh'),
                    TextInput::make('href')
                        ->label('Link đến (URL)')
                        ->placeholder('/filter hoặc https://example.com'),
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
                ->label('Danh sách lưới')
                ->schema([
                    Grid::make()
                        ->columns(2)
                        ->schema([
                            Select::make('filter_type')
                                ->label('Loại bộ lọc')
                                ->options(function () {
                                    $options = [
                                        'product_category' => '📁 Danh mục sản phẩm',
                                        'product_type' => '📦 Loại sản phẩm',
                                    ];

                                    $groups = \App\Models\CatalogAttributeGroup::query()
                                        ->where('is_filterable', true)
                                        ->whereIn('filter_type', ['chon_don', 'chon_nhieu'])
                                        ->orderBy('position')
                                        ->get();

                                    foreach ($groups as $group) {
                                        $options['attribute_group_'.$group->id] = '🏷️ '.$group->name;
                                    }

                                    return $options;
                                })
                                ->required()
                                ->live()
                                ->searchable()
                                ->preload()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $set('filter_value_id', null);
                                    $set('title', '');
                                    $set('href', '');
                                })
                                ->helperText('Chọn loại để hiển thị danh sách giá trị tương ứng'),

                            Select::make('filter_value_id')
                                ->label('Giá trị')
                                ->options(function (Get $get) {
                                    $filterType = $get('filter_type');

                                    if (! $filterType) {
                                        return [];
                                    }

                                    if ($filterType === 'product_category') {
                                        return \App\Models\ProductCategory::query()
                                            ->where('active', true)
                                            ->orderBy('order')
                                            ->pluck('name', 'id');
                                    }

                                    if ($filterType === 'product_type') {
                                        return \App\Models\ProductType::query()
                                            ->where('active', true)
                                            ->orderBy('order')
                                            ->pluck('name', 'id');
                                    }

                                    if (str_starts_with($filterType, 'attribute_group_')) {
                                        $groupId = str_replace('attribute_group_', '', $filterType);

                                        return \App\Models\CatalogTerm::query()
                                            ->where('group_id', $groupId)
                                            ->where('is_active', true)
                                            ->orderBy('position')
                                            ->pluck('name', 'id');
                                    }

                                    return [];
                                })
                                ->required()
                                ->searchable()
                                ->preload()
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    if (! $state) {
                                        return;
                                    }

                                    $filterType = $get('filter_type');

                                    if ($filterType === 'product_category') {
                                        $category = \App\Models\ProductCategory::find($state);
                                        if ($category) {
                                            if (! $get('title')) {
                                                $set('title', $category->name);
                                            }
                                            $set('href', '/filter?category='.$category->id);
                                        }
                                    } elseif ($filterType === 'product_type') {
                                        $productType = \App\Models\ProductType::find($state);
                                        if ($productType) {
                                            if (! $get('title')) {
                                                $set('title', $productType->name);
                                            }
                                            $set('href', '/filter?type='.$productType->id);
                                        }
                                    } elseif (str_starts_with($filterType, 'attribute_group_')) {
                                        $groupId = str_replace('attribute_group_', '', $filterType);
                                        $term = \App\Models\CatalogTerm::with('group')->find($state);
                                        if ($term) {
                                            if (! $get('title')) {
                                                $set('title', $term->name);
                                            }
                                            $set('href', '/filter?'.$term->group->code.'='.$term->id);
                                        }
                                    }
                                })
                                ->helperText('Chọn giá trị cụ thể, URL sẽ tự động tạo'),

                            self::imageSelectWithQuickCreate('image_id', 'Hình ảnh nền'),
                        ]),
                    TextInput::make('title')
                        ->label('Tiêu đề hiển thị')
                        ->placeholder('VANG ĐỎ')
                        ->required()
                        ->helperText('Tự động lấy từ giá trị đã chọn, có thể chỉnh sửa'),
                    TextInput::make('href')
                        ->label('Link đến (URL)')
                        ->placeholder('/filter?brand=150')
                        ->required()
                        ->helperText('Tự động tạo dạng /filter?key=value, có thể chỉnh sửa'),
                ])
                ->columnSpanFull()
                ->defaultItems(1)
                ->addActionLabel('Thêm ô lưới')
                ->collapsible()
                ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'Ô lưới mới'
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
                    self::imageSelectWithQuickCreate('image_id', 'Logo thương hiệu'),
                    TextInput::make('href')
                        ->label('Link đến (URL)')
                        ->placeholder('/filter hoặc https://example.com'),
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
                        ->placeholder('/filter')
                        ->helperText('Nhập URL tương đối (vd: /filter) hoặc URL đầy đủ (vd: https://example.com)'),
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
                        ->options(fn () => self::getArticleOptionsWithPreview())
                        ->allowHtml()
                        ->searchable()
                        ->required()
                        ->preload(),
                ])
                ->simple(Select::make('article_id')
                    ->label('Bài viết')
                    ->options(fn () => self::getArticleOptionsWithPreview())
                    ->allowHtml()
                    ->searchable()
                    ->required()
                    ->preload()
                )
                ->columnSpanFull()
                ->defaultItems(1)
                ->addActionLabel('Thêm bài viết'),
        ];
    }

    protected static function getArticleOptionsWithPreview(): array
    {
        $articles = Article::query()
            ->with('coverImage')
            ->where('active', true)
            ->orderBy('created_at', 'desc')
            ->limit(200)
            ->get();

        return $articles->mapWithKeys(function ($article) {
            $imageUrl = $article->cover_image_url ?? '/images/placeholder.png';

            $html = '<div style="display: flex; align-items: center; gap: 10px;">';
            $html .= '<img src="'.e($imageUrl).'" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #e5e7eb;" />';
            $html .= '<div style="display: flex; flex-direction: column; gap: 2px;">';
            $html .= '<span style="font-weight: 500; color: #111827;">'.e($article->title).'</span>';

            if ($article->published_at) {
                $html .= '<span style="font-size: 0.75rem; color: #6b7280;">'.$article->published_at->format('d/m/Y').'</span>';
            }

            $html .= '</div>';
            $html .= '</div>';

            return [$article->id => $html];
        })->toArray();
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

    protected static function speedDialFields(): array
    {
        return [
            Repeater::make('config.items')
                ->label('Danh sách nút liên hệ')
                ->schema([
                    Select::make('icon_type')
                        ->label('Loại icon')
                        ->options([
                            'home' => 'Trang chủ (Home)',
                            'phone' => 'Điện thoại (Phone)',
                            'zalo' => 'Zalo',
                            'messenger' => 'Messenger',
                            'custom' => 'Tùy chỉnh (Custom Icon)',
                        ])
                        ->required()
                        ->default('home')
                        ->live(),
                    self::imageSelectWithQuickCreate('icon_image_id', 'Icon tùy chỉnh')
                        ->visible(fn (Get $get) => $get('icon_type') === 'custom')
                        ->helperText('Chỉ sử dụng khi chọn "Tùy chỉnh"')
                        ->required(fn (Get $get) => $get('icon_type') === 'custom'),
                    TextInput::make('label')
                        ->label('Nhãn hiển thị')
                        ->placeholder('Hotline')
                        ->required(),
                    TextInput::make('href')
                        ->label('Link đến')
                        ->placeholder('tel:0946698008 hoặc https://zalo.me/...')
                        ->required()
                        ->helperText('Dùng tel: cho số điện thoại, https:// cho link web'),
                    Select::make('target')
                        ->label('Cách mở link')
                        ->options([
                            '_self' => 'Cùng tab (_self)',
                            '_blank' => 'Tab mới (_blank)',
                        ])
                        ->default('_self'),
                ])
                ->columnSpanFull()
                ->defaultItems(1)
                ->addActionLabel('Thêm nút liên hệ')
                ->collapsible()
                ->itemLabel(fn (array $state): ?string => $state['label'] ?? 'Nút liên hệ mới'),
        ];
    }
}
