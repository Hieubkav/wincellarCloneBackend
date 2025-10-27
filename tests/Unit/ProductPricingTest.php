<?php

use App\Models\Product;
use App\Support\Product\ProductPricing;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

uses(TestCase::class);

test('discount percent is rounded and positive when original price is higher', function () {
    $discount = ProductPricing::discountPercent(100_000, 149_900);

    expect($discount)->toBe(33);
});

test('discount percent returns null for non-discountable combinations', function () {
    expect(ProductPricing::discountPercent(0, 150_000))->toBeNull(); // CTA Liên hệ
    expect(ProductPricing::discountPercent(100_000, 0))->toBeNull(); // tránh chia 0
    expect(ProductPricing::discountPercent(160_000, 150_000))->toBeNull(); // tránh discount âm
    expect(ProductPricing::discountPercent(null, 150_000))->toBeNull();
    expect(ProductPricing::discountPercent(150_000, null))->toBeNull();
});

test('pricing validation prevents negative values', function () {
    $exception = null;

    try {
        ProductPricing::assertValidPricing(-1, 10_000);
    } catch (Throwable $th) {
        $exception = $th;
    }

    expect($exception)->toBeInstanceOf(ValidationException::class);

    $exception = null;

    try {
        ProductPricing::assertValidPricing(10_000, -5);
    } catch (Throwable $th) {
        $exception = $th;
    }

    expect($exception)->toBeInstanceOf(ValidationException::class);
});

test('cta contact is flagged when price is null or zero', function () {
    expect(ProductPricing::shouldShowContactCta(null))->toBeTrue();
    expect(ProductPricing::shouldShowContactCta(0))->toBeTrue();
    expect(ProductPricing::shouldShowContactCta(1))->toBeFalse();
});

test('product accessors reuse pricing service even when inactive', function () {
    $product = new Product([
        'price' => 100_000,
        'original_price' => 150_000,
        'active' => false,
    ]);

    expect($product->discount_percent)->toBe(33)
        ->and($product->should_show_contact_cta)->toBeFalse();
});
