<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * @property integer product_type_id
 * @property integer price
 * @property string name
 */
class Product extends Model
{
    protected $table = 'products';

    protected $fillable = ['name', 'description', 'sku', 'qty', 'product_type_id'];

    protected $hidden = ['created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function productType()
    {
        return $this->belongsTo(ProductType::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function price()
    {
        return $this->hasOne(Price::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItems::class);
    }

    /**
     * @return bool
     */
    public function hasPrice() : bool
    {
        if ($this->price !== null) {
            return true;
        }
        return false;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bundle()
    {
        return $this->hasMany(ProductBundle::class, 'bundle_id');
    }

    /**
     * @return bool
     */
    public function isBundle() : bool
    {
        if ($this->product_type_id == ProductType::BUNDLE_PRODUCT_ID) {
            return true;
        }
        return false;
    }

}
