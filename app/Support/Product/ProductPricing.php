<?php

namespace App\Support\Product;

use Illuminate\Validation\ValidationException;

final class ProductPricing
{
    /**
     * Giá bằng 0 được hiểu là sản phẩm chỉ hiển thị CTA "Liên hệ".
     */
    public const CONTACT_PRICE = 0;

    /**
     * Tính toán phần trăm giảm giá, làm tròn 0 chữ số và trả null nếu không giảm.
     */
    public static function discountPercent(?int $price, ?int $originalPrice): ?int
    {
        if ($price === null || $originalPrice === null) {
            return null;
        }

        if ($price <= 0 || $originalPrice <= 0) {
            return null;
        }

        if ($price >= $originalPrice) {
            return null;
        }

        $percent = (int) round(
            (($originalPrice - $price) / $originalPrice) * 100,
            0,
            PHP_ROUND_HALF_UP
        );

        return $percent > 0 ? $percent : null;
    }

    /**
     * Kiểm tra dữ liệu giá trước khi lưu.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public static function assertValidPricing(?int $price, ?int $originalPrice): void
    {
        $errors = [];

        if ($price !== null && $price < 0) {
            $errors['price'] = ['Giá bán phải lớn hơn hoặc bằng 0.'];
        }

        if ($originalPrice !== null && $originalPrice < 0) {
            $errors['original_price'] = ['Giá niêm yết phải lớn hơn hoặc bằng 0.'];
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * Quyết định hiển thị CTA "Liên hệ".
     */
    public static function shouldShowContactCta(?int $price): bool
    {
        if ($price === null) {
            return true;
        }

        return $price <= self::CONTACT_PRICE;
    }
}

