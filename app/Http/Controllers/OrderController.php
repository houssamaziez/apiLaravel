<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductSell;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        try {
            // Validate incoming request
            $validated = $request->validate([
                'user_id' => 'required|integer|exists:users,id',
                'order_date' => 'required|date',
                'products_sells' => 'required|array',
                'products_sells.*.product_id' => 'required|integer|exists:products,id',
                'products_sells.*.name' => 'nullable|string',
                'products_sells.*.description' => 'nullable|string',
                'products_sells.*.price' => 'required|numeric',
                'products_sells.*.quantity' => 'required|integer',
            ]);

            // Generate a unique order ID
            $orderId = $this->generateUniqueOrderId();

            // Create the order
            $order = Order::create([
                'user_id' => $validated['user_id'],
                'order_id' => $orderId,
                'order_date' => $validated['order_date'],
            ]);

            // Add product sells to the order
            foreach ($validated['products_sells'] as $productData) {
                ProductSell::create([
                    'order_id' => $order->id,
                    'product_id' => $productData['product_id'],
                    'name' => $productData['name'],
                    'description' => $productData['description'] ?? null,
                    'price' => $productData['price'],
                    'quantity' => $productData['quantity'],
                ]);
            }

            return response()->json(['message' => 'Order created successfully!'], 200);

        } catch (ValidationException $e) {
            // Handle validation errors
            return response()->json(['error' => 'Validation failed', 'details' => $e->errors()], 422);
        } catch (ModelNotFoundException $e) {
            // Handle model not found errors
            return response()->json(['error' => 'Model not found', 'details' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            // Handle any other exceptions
            return response()->json(['error' => 'An error occurred while creating the order', 'details' => $e->getMessage()], 500);
        }
    }

    public function index()
    {
        try {
            // Retrieve all orders with their associated product sells, ordered by creation time
            $orders = Order::with('productSells')
                ->orderBy('created_at', 'desc')
                ->get();

            // Return a success response with the orders data
            return response()->json([
                'success' => true,
                'data' => $orders,
            ], 200);

        } catch (\Exception $e) {
            // Return an error response if something goes wrong
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve orders.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function generateUniqueOrderId()
    {
        do {
            $orderId = rand(1000000, 9999999); // Generate a random order ID or use a different logic
        } while (Order::where('order_id', $orderId)->exists());

        return $orderId;
    }
}
