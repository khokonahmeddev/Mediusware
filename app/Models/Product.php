<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{

    protected $fillable = [
        'title', 'sku', 'description'
    ];

    public function productVariants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function productVariantPrice()
    {
        return $this->hasMany(ProductVariantPrice::class, 'product_id', 'id');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }
}
