<?php

namespace Tests\Unit;

use FlipperBox\Inventory\Models\Product;
use PHPUnit\Framework\TestCase;

class ProductPriceTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_calculates_selling_price_correctly(): void
    {
        $product = new Product;
        $product->cost = 100.00;
        $product->iva_percentage = 21.00;
        $product->profit_margin = 50.00;

        $price = $product->calculatePrice();

        $this->assertEquals(181.50, $price);
    }

    public function test_calculates_price_with_zero_values(): void
    {
        $product = new Product;
        $product->cost = 100.00;
        $product->iva_percentage = 0;
        $product->profit_margin = 0;

        $price = $product->calculatePrice();

        $this->assertEquals(100.00, $price);
    }
}
