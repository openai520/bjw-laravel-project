<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeCategoryFeaturedProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'product_id',
        'display_order',
    ];

    /**
     * Get the category that this featured product belongs to.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the product that is featured.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
