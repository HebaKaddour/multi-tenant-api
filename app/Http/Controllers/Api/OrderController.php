<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;

class OrderController extends Controller
{
    /**
 * @OA\Post(
 *     path="/api/orders",
 *     summary="Create a new order",
 *     tags={"Orders"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="product_id", type="integer"),
 *             @OA\Property(property="quantity", type="integer")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Order created successfully",
 *         @OA\JsonContent(ref="#/components/schemas/Order")
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid input"
 *     )
 * )
 */
    public function store(StoreOrderRequest $request){

        $tenant = auth()->user()->tenant;
        $product = $tenant->products()->findOrFail($request->product_id);
        $totalPrice = $product->price * $request->quantity;
        $order = Order::create([
        'product_id' => $request->product_id,
        'user_id'=>auth()->id(),
        'quantity'=>$request->quantity,
        'total_price'=> $totalPrice,
        'status' => 'pending',
        'tenant_id' => $tenant->id,
       ]);

       return $this->successResponse($order, 'Order created successfully.', 201);
    }

        /**
     * @OA\Get(
     *     path="/api/orders",
     *     summary="Get all orders",
     *     tags={"Orders"},
     *     security={{"BearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of orders",
     *         @OA\JsonContent(ref="#/components/schemas/Order")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function index(){
        $tenant = auth()->user()->tenant;
        $orders = $tenant->orders()->with(['product', 'user'])->get();
        return $this->successResponse($orders, 'success', 200);
    }
}
