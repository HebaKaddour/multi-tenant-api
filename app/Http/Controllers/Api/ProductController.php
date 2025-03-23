<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;

class ProductController extends Controller
{

    public function index(){
        $tenantId = auth()->user()->tenant_id;
        $products = Product::where('tenant_id', $tenantId)->get();
        return $this->successResponse($products, 'success', 200);
    }

    public function store(ProductRequest $request)
{

    $tenant = auth()->user()->tenant;

    $product = $tenant->products()->create([
        'name' => $request->name,
        'description' => $request->description,
        'price' => $request->price,
        'stock_quantity' => $request->stock_quantity,
    ]);
    return $this->successResponse($product, 'product created successfuly', 201);
}

public function update(ProductRequest $request, $id)
{
    $tenant = auth()->user()->tenant;
    $product = $tenant->products()->findOrFail($id);
    $product->update($request->all());
    return $this->successResponse($product, 'product updated successfuly', 200);
}


public function delete($id){
    $tenant = auth()->user()->tenant;
    $product = $tenant->products()->findOrFail($id);
    $product->delete();
    return $this->successResponse('null', 'product delete successfuly', 200);

}
}
