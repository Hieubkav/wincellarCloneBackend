<?php

namespace App\Support\Content;

class ArticleContentCatalog
{
    /**
     * @return array<int, array{key: string, label: string, description: string}>
     */
    public static function categories(): array
    {
        return [
            ['key' => 'kien-thuc', 'label' => 'Kiến thức', 'description' => 'Bài hướng dẫn, chia sẻ kinh nghiệm và kiến thức rượu.'],
            ['key' => 'chinh-sach', 'label' => 'Chính sách', 'description' => 'Nội dung pháp lý, mua hàng, vận chuyển, đổi trả.'],
            ['key' => 'gioi-thieu', 'label' => 'Giới thiệu', 'description' => 'Nội dung về thương hiệu, câu chuyện và cam kết.'],
            ['key' => 'dich-vu', 'label' => 'Dịch vụ', 'description' => 'Dịch vụ quà tặng, doanh nghiệp và tư vấn.'],
            ['key' => 'qua-tang', 'label' => 'Quà tặng', 'description' => 'Nội dung tư vấn quà tặng theo nhu cầu.'],
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function categoryKeys(): array
    {
        return array_map(fn (array $category) => $category['key'], self::categories());
    }

    /**
     * @return array<int, array{key: string, label: string, path: string, category_key: string}>
     */
    public static function slots(): array
    {
        return [
            ['key' => 'gioi-thieu/ve-thien-kim-wine', 'label' => 'Về Thiên Kim Wine', 'path' => '/gioi-thieu/ve-thien-kim-wine', 'category_key' => 'gioi-thieu'],
            ['key' => 'gioi-thieu/cau-chuyen-thuong-hieu', 'label' => 'Câu chuyện thương hiệu', 'path' => '/gioi-thieu/cau-chuyen-thuong-hieu', 'category_key' => 'gioi-thieu'],
            ['key' => 'gioi-thieu/vi-sao-chon-chung-toi', 'label' => 'Vì sao chọn chúng tôi', 'path' => '/gioi-thieu/vi-sao-chon-chung-toi', 'category_key' => 'gioi-thieu'],
            ['key' => 'gioi-thieu/chung-nhan-giay-phep', 'label' => 'Chứng nhận / giấy phép', 'path' => '/gioi-thieu/chung-nhan-giay-phep', 'category_key' => 'gioi-thieu'],
            ['key' => 'ho-tro/faq', 'label' => 'Câu hỏi thường gặp', 'path' => '/ho-tro/faq', 'category_key' => 'chinh-sach'],
            ['key' => 'ho-tro/giao-hang-van-chuyen', 'label' => 'Giao hàng & vận chuyển', 'path' => '/ho-tro/giao-hang-van-chuyen', 'category_key' => 'chinh-sach'],
            ['key' => 'ho-tro/doi-tra-hoan-tien', 'label' => 'Đổi trả & hoàn tiền', 'path' => '/ho-tro/doi-tra-hoan-tien', 'category_key' => 'chinh-sach'],
            ['key' => 'ho-tro/thanh-toan', 'label' => 'Phương thức thanh toán', 'path' => '/ho-tro/thanh-toan', 'category_key' => 'chinh-sach'],
            ['key' => 'ho-tro/cam-ket-chinh-hang', 'label' => 'Cam kết chính hãng', 'path' => '/ho-tro/cam-ket-chinh-hang', 'category_key' => 'chinh-sach'],
            ['key' => 'ho-tro/chinh-sach-bao-mat', 'label' => 'Chính sách bảo mật', 'path' => '/ho-tro/chinh-sach-bao-mat', 'category_key' => 'chinh-sach'],
            ['key' => 'ho-tro/dieu-khoan-dieu-kien', 'label' => 'Điều khoản & điều kiện', 'path' => '/ho-tro/dieu-khoan-dieu-kien', 'category_key' => 'chinh-sach'],
            ['key' => 'dich-vu/dat-hang-doanh-nghiep', 'label' => 'Đặt hàng doanh nghiệp', 'path' => '/dich-vu/dat-hang-doanh-nghiep', 'category_key' => 'dich-vu'],
            ['key' => 'dich-vu/in-logo-ten-doanh-nghiep', 'label' => 'In logo / tên doanh nghiệp', 'path' => '/dich-vu/in-logo-ten-doanh-nghiep', 'category_key' => 'dich-vu'],
            ['key' => 'dich-vu/tu-van-chon-qua', 'label' => 'Tư vấn chọn quà', 'path' => '/dich-vu/tu-van-chon-qua', 'category_key' => 'dich-vu'],
            ['key' => 'dich-vu/tang-qua-tu-xa', 'label' => 'Tặng quà từ xa', 'path' => '/dich-vu/tang-qua-tu-xa', 'category_key' => 'dich-vu'],
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function slotKeys(): array
    {
        return array_map(fn (array $slot) => $slot['key'], self::slots());
    }
}
