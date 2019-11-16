<?php

namespace App\Services;

use App\Components\CustomException;
use App\Components\ErrorMessage;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CartService
{
    const CACHE_PREFIX = 'CART';
    const DEFAULT_TIMEOUT = 60 * 60; // in minutes

    private $user;

    public function __construct(Request $request)
    {
        $this->user = $request->user();
    }

    /**
     * @param array $data
     * @return array
     * @throws CustomException
     */
    public function addToCart(array $data) : array
    {
        $product = Product::with('price')->find($data['product_id']);

        if (empty($product)) {
            throw new CustomException('Invalid Product id', ErrorMessage::RECORD_NOT_EXISTING);
        }

        if ($product->qty < $data['qty']) {
            throw (new CustomException('Product out of stock', ErrorMessage::OUT_OF_STOCK))->setData([]);
        }

        $cache_key = $this->getCacheKey();

        if ($cart = Cache::get($cache_key)) {

            if (array_key_exists($data['product_id'], $cart)) {
                $old_qty = $cart[$data['product_id']]['qty'];

                $data['qty'] = $old_qty + $data['qty'];
            }

            $this->updateCart($data, $product, $cart);

        } else {

            $this->updateCart($data, $product, $cart);

        }

        return Cache::get($cache_key);
    }

    /**
     * @return array
     */
    public function getCart() : array
    {
        $cache_key = $this->getCacheKey();

        if ($values = Cache::get($cache_key)) {
            $values = array_values($values);
            $total = array_pluck($values, 'total');

            $response['total_price'] = array_sum($total);
            $response['items'] = $values;

            return $response;
        }

        return [];
    }

    /**
     * @return array
     */
    public function clearCart() : array
    {
        $cache_key = $this->getCacheKey();

        return Cache::pull($cache_key);
    }

    /**
     * @return string
     */
    private function getCacheKey() : string
    {
        return self::CACHE_PREFIX . ':' . $this->user->id;
    }

    /**
     * @param array $data
     * @param $product
     * @param $cart
     */
    public function updateCart(array $data, $product, $cart): void
    {
        $cache_key = $this->getCacheKey();

        $data['name'] = $product->name;
        $data['price'] = $product->price->final_price;
        $data['total'] = sprintf("%.2f", ($data['price'] * $data['qty']));

        $cart[$data['product_id']] = $data;

        Cache::put($cache_key, $cart, self::DEFAULT_TIMEOUT);
    }

    /**
     * @param array $order_data
     * @return mixed
     */
    public function placeOrder(array $order_data)
    {
        $items = array_values($this->clearCart());

        $total = array_pluck($items, 'total');

        $order_data['total_price'] = array_sum($total);

        $order = $this->user->createOrder($order_data);

        array_map(function ($item) use ($order) {
            $order->orderItems()->create($item);
        }, $items);

        $order->refresh();

        return $order;
    }
}
