<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';

    protected $fillable = ['user_id', 'address', 'payment_method', 'total_price'];

    protected $hidden = ['created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItems::class);
    }

    /**
     * @param $value
     * Mutator to set the value of price to cents
     */
    public function setTotalPriceAttribute($value)
    {
        $this->attributes['total_price'] = ($value * 100);
    }

    /**
     * @param $value
     * @return float|int
     * Accessors to help format price
     */
    public function getTotalPriceAttribute($value)
    {
        return number_format( $value / 100, 2);
    }

}
