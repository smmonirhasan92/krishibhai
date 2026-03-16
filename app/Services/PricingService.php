<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\PriceRule;
use Illuminate\Support\Facades\Auth;

class PricingService
{
    /**
     * Calculate the final price for a product or variation based on quantity and user role.
     *
     * @param int|Product $product
     * @param int $quantity
     * @param int|null $variationId
     * @param string|null $userRole (optional, defaults to current user)
     * @return array
     */
    public function calculatePrice($product, int $quantity = 1, int $variationId = null, string $userRole = null)
    {
        if (is_numeric($product)) {
            $product = Product::findOrFail($product);
        }

        $userRole = $userRole ?? (Auth::check() ? Auth::user()->role : 'retailer');
        
        $basePrice = $product->retail_price;
        $wholesalePrice = $product->wholesale_price;
        $minWholesaleQty = $product->min_wholesale_qty;

        // If variation exists, use variation pricing
        if ($variationId) {
            $variation = ProductVariation::find($variationId);
            if ($variation) {
                $basePrice = $variation->price;
                $wholesalePrice = $variation->wholesale_price ?? $wholesalePrice;
            }
        }

        $finalPrice = $basePrice;
        $appliedLogic = 'retail';

        // 1. Check Wholesale Logic (Either by role or by quantity)
        if ($userRole === 'wholesaler' || ($wholesalePrice && $quantity >= $minWholesaleQty)) {
            $finalPrice = $wholesalePrice ?? $basePrice;
            $appliedLogic = 'wholesale';
        }

        // 2. Check Tiered Pricing (Price Rules)
        $tierRule = PriceRule::where('product_id', $product->id)
            ->where('is_active', true)
            ->where('min_qty', '<=', $quantity)
            ->orderBy('min_qty', 'desc')
            ->first();

        if ($tierRule) {
            if ($tierRule->discount_type === 'percentage') {
                $discountAmount = ($finalPrice * $tierRule->value) / 100;
                $finalPrice -= $discountAmount;
            } else {
                $finalPrice -= $tierRule->value; // Fixed amount discount per unit
            }
            $appliedLogic = 'tiered';
        }

        return [
            'unit_price' => (float) $finalPrice,
            'total_price' => (float) ($finalPrice * $quantity),
            'currency' => 'BDT',
            'logic' => $appliedLogic
        ];
    }
}
