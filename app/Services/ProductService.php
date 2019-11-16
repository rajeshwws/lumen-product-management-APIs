<?php

namespace App\Services;


use App\Components\CustomException;
use App\Components\ErrorMessage;
use App\Models\Product;

class ProductService
{
    private $product_attribute = ['name', 'description', 'sku', 'qty', 'product_type_id'];
    private $price_attributes = ['price', 'discount', 'discount_percentage', 'discount_active'];

    public function __construct()
    {
    }

    /**
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\Paginator
     */
    public function getAllProduct(int $perPage = 5)
    {
        return Product::with(['price', 'productType'])->simplePaginate($perPage);
    }

    /**
     * @param int $id
     * @return Product|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     * @throws CustomException
     */
    public function getSingleProduct(int $id)
    {
        $product = Product::with(['price', 'bundle.subProduct'])->find($id);

        if (is_null($product)) {
            throw new CustomException('Invalid Product id', ErrorMessage::RECORD_NOT_EXISTING);
        }

        // this is to handle single products with no sub-product
        if ($product->bundle->count() == 0) {
            $product->unsetRelation('bundle');
        }

        return $product;
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function createProduct(array $data)
    {
        $product_data = array_only($data, $this->product_attribute);

        $product = Product::create($product_data);

        if (array_has($data, 'products') && $product->isBundle()) {
            // create the sub products
            $this->addBundleProducts($data, $product);

        }

        $this->createProductPrice($data, $product);

        return $product;
    }

    /**
     * @param array $data
     * @param Product $product
     */
    public function createProductPrice(array $data, Product $product): void
    {
        $price_data = [];

        foreach ($this->price_attributes as $attribute) {
            if (array_key_exists($attribute, $data)) {
                $price_data[$attribute] = $data[$attribute];
            }
        }

        //Create product price entry
        $product->price()->create($price_data);
    }

    /**
     * @param array $data
     * @param Product $product
     */
    public function addBundleProducts(array $data, Product $product): void
    {
        $sub_products = $data['products'];

        foreach ($sub_products as $product_id) {
            $product->bundle()->create(['product_id' => $product_id]);
        }
    }

    /**
     * @param array $data
     * @param int $id
     * @return Product|Product[]|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     * @throws CustomException
     */
    public function updateProduct(array $data, int $id)
    {
        $product = Product::with('price')->find($id);

        if (is_null($product)) {
            throw new CustomException('Invalid Product id', ErrorMessage::RECORD_NOT_EXISTING);
        }

        // update basic Product info
        $this->updateBasicProductDetails($data, $id);

        // update product prices
        $this->updateProductPrice($data, $product);

        return $product->refresh();
    }

    /**
     * @param array $data
     * @param $product
     */
    public function updateProductPrice(array $data, Product $product): void
    {
        foreach ($this->price_attributes as $attribute) {
            if (array_key_exists($attribute, $data)) {
                if ($attribute == 'discount_percentage') {
                    $product->price->$attribute = $data[$attribute];
                    $product->price->discount = $this->calculateDiscount($product->price->price, $data[$attribute]);
                    continue;
                }
                $product->price->$attribute = $data[$attribute];
            }
        }

        $product->price->save();
    }

    /**
     * @param array $data
     * @param int $id
     */
    public function updateBasicProductDetails(array $data, int $id): void
    {
        $update_data = [];

        foreach ($this->product_attribute as $attribute) {
            if (array_key_exists($attribute, $data)) {
                $update_data[$attribute] = $data[$attribute];
            }
        }

        Product::whereId($id)->update($update_data);
    }

    /**
     * @param $price
     * @param $percentage
     * @return float|int
     */
    public function calculateDiscount($price, $percentage)
    {
        return $price * ($percentage / 100);
    }
}
