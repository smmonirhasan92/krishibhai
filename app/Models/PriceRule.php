<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceRule extends Model
{
    protected $fillable = [
        'product_id',
        'min_qty',
        'discount_type',
        'value',
        'is_active'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
