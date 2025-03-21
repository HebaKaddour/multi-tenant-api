<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;

class OrderController extends Controller
{

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

    public function index(){
        $tenant = auth()->user()->tenant;
        $orders = $tenant->orders()->with(['product', 'user'])->get();
        return $this->successResponse($orders, 'success', 200);
    }
}
