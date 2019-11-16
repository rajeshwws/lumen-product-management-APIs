<?php

namespace App\Http\Controllers;


use App\Components\Response;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductsController extends Controller
{
    private $product_service;

    /**
     * ProductsController constructor.
     * @param ProductService $service
     */
    public function __construct(ProductService $service)
    {
        $this->middleware('auth', [
            'except' => ['index', 'show']
        ]);
        $this->middleware('admin', [
            'except' => ['index', 'show']
        ]);

        $this->product_service = $service;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $perPage = $request->get('perPage') ?: 5;

        $products = $this->product_service->getAllProduct($perPage);

        return Response::success($products, 'Successfully fetched all products');
    }

    /**
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Components\CustomException
     */
    public function show(Request $request, int $id)
    {
        $product = $this->product_service->getSingleProduct($id);

        return Response::success($product, 'Successfully fetched a single product');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'sku' => 'required|alpha_num|unique:products',
            'description' => 'required',
            'qty' => 'required|numeric',
            'price' => 'required|numeric',
            'product_type_id' => ['required', Rule::in([1,2])],
            'products' => 'sometimes|required|array'
        ]);

        $product = $this->product_service->createProduct($request->all());

        return Response::success($product, 'product successfully created');
    }

    /**
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Components\CustomException
     */
    public function updateProduct(Request $request, int $id)
    {
        $response = $this->product_service->updateProduct($request->all(), $id);

        return Response::success($response, 'product successfully updated');
    }

}
