<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    public function variant_price()
    {
        return $this->hasOne(ProductVariantPrice::class, 'product_id');
    }
}
