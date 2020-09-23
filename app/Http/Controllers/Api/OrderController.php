<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Product;
use App\Order;
Use Auth;

class OrderController extends Controller
{
    public function order(Request $request) 
    {
        try {
            $product = app(Product::class)->where('id', $request->product_id)->where('available_stock', '>=', $request->quantity)->decrement('available_stock', $request->quantity);
            
            if($product > 0) {
                $order = new Order([
                    'product_id' => $request->product_id,
                    'quantity' => $request->quantity
                ]);
                    
                $order->save();
    
                return response()->json([
                    'message' => 'You have successfully ordered this product.'
                ], Response::HTTP_CREATED);
            } else {
                return response()->json([
                    'message' => 'Failed to order this product due to unavailability of the stock.'
                ], Response::HTTP_BAD_REQUEST);
            }

        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th
            ], Response::HTTP_INTERNAL_SERVER_ERROR);  
        }
    }
}
