<?php

namespace App\Http\Controllers;

use App\Components\Response;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    private $cartService;

    /**
     * CartController constructor.
     * @param CartService $cartService
     */
    public function __construct(CartService $cartService)
    {
        $this->middleware('auth');

        $this->cartService = $cartService;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     * @throws \App\Components\CustomException
     */
    public function addItemToCart(Request $request)
    {
        $this->validate($request, [
            'product_id' => 'required',
            'qty' => 'required'
        ]);

        $cart = $this->cartService->addToCart($request->only(['product_id', 'qty']));

        return Response::success($cart, 'item added to cart');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCart(Request $request)
    {
        $cart_items = $this->cartService->getCart();
        return Response::success($cart_items, 'cart items');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function checkoutCart(Request $request)
    {
        $this->validate($request, [
            'address' => 'required',
            'payment_method' => 'required'
        ]);

        $order = $this->cartService->placeOrder($request->only(['address', 'payment_method']));

        return Response::success($order, 'you order has been received');
    }

}
