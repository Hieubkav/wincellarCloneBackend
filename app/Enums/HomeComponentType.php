<?php

namespace App\Enums;

enum HomeComponentType: string
{
    case HeroCarousel = 'hero_carousel';
    case DualBanner = 'dual_banner';
    case CategoryGrid = 'category_grid';
    case FavouriteProducts = 'favourite_products';
    case BrandShowcase = 'brand_showcase';
    case CollectionShowcase = 'collection_showcase';
    case EditorialSpotlight = 'editorial_spotlight';
    case Footer = 'footer';

    public function getLabel(): string
    {
        return match ($this) {
            self::HeroCarousel => 'Hero Carousel - Banner chính',
            self::DualBanner => 'Dual Banner - 2 banner ngang',
            self::CategoryGrid => 'Category Grid - Lưới danh mục',
            self::FavouriteProducts => 'Favourite Products - Sản phẩm yêu thích',
            self::BrandShowcase => 'Brand Showcase - Giới thiệu thương hiệu',
            self::CollectionShowcase => 'Collection Showcase - Bộ sưu tập sản phẩm',
            self::EditorialSpotlight => 'Editorial Spotlight - Bài viết nổi bật',
            self::Footer => 'Footer - Chân trang',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::HeroCarousel => 'Slider banner lớn ở đầu trang với các hình ảnh và CTA',
            self::DualBanner => 'Hai banner quảng cáo nằm ngang cạnh nhau',
            self::CategoryGrid => 'Lưới hiển thị các danh mục sản phẩm',
            self::FavouriteProducts => 'Danh sách sản phẩm được yêu thích/nổi bật',
            self::BrandShowcase => 'Giới thiệu các thương hiệu đối tác',
            self::CollectionShowcase => 'Bộ sưu tập sản phẩm theo chủ đề (Rượu Vang, Rượu Mạnh...)',
            self::EditorialSpotlight => 'Khu vực hiển thị các bài viết/blog nổi bật',
            self::Footer => 'Thông tin chân trang với links, thông tin liên hệ',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::HeroCarousel => 'heroicon-o-photo',
            self::DualBanner => 'heroicon-o-rectangle-group',
            self::CategoryGrid => 'heroicon-o-squares-2x2',
            self::FavouriteProducts => 'heroicon-o-heart',
            self::BrandShowcase => 'heroicon-o-building-storefront',
            self::CollectionShowcase => 'heroicon-o-rectangle-stack',
            self::EditorialSpotlight => 'heroicon-o-newspaper',
            self::Footer => 'heroicon-o-bars-3-bottom-left',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->getLabel();
        }
        return $options;
    }
}
