<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ProductBundle extends Model
{
    protected $table = 'products_bundle';

    protected $fillable = ['product_id'];

    protected $hidden = ['created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parentProduct()
    {
        return $this->belongsTo(Product::class, 'bundle_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function subProduct()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

}
