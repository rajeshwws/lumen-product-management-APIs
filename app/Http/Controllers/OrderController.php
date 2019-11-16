<?php

namespace App\Http\Controllers;

use App\Components\CustomException;
use App\Components\ErrorMessage;
use App\Components\Response;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws CustomException
     */
    public function index(Request $request)
    {
        $orders = $request->user()->orders;

        if ($orders->isEmpty()) {
            throw new CustomException('You dont have any orders yet!', ErrorMessage::RECORD_NOT_EXISTING);
        }

        return Response::success($orders, 'all user orders fetched');
    }

    /**
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     * @throws CustomException
     */
    public function getOrder(Request $request, int $id)
    {
        $order = Order::with('orderItems.product')->find($id);

        if (empty($order)) {
            throw new CustomException('invalid order id!', ErrorMessage::RECORD_NOT_EXISTING);
        }

        if ((int)$order->user_id !== $request->user()->id) {
            throw new CustomException('Order not for user!', ErrorMessage::RECORD_NOT_EXISTING);
        }

        return Response::success($order, 'successfully fetched order details');
    }
}
