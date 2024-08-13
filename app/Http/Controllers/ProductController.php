<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    // Store a new product
    public function store(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'user_id' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        // Create the product
        $product = Product::create($validator->validated());

        return response()->json([
            'message' => 'Product created successfully',
            'data' => $product
        ], 201);
    }

    // Get all products
    public function index()
    {
        $products = Product::all();

        return response()->json([
            'message' => 'Products retrieved successfully',
            'data' => $products
        ], 200);
    }

    // Get a single product by ID
    public function show($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }

        return response()->json([
            'message' => 'Product retrieved successfully',
            'data' => $product
        ], 200);
    }
    public function showAllProductUser($userId)
    {
        // جلب جميع المنتجات التي تخص المستخدم المحدد بواسطة user_id
        $products = Product::where('user_id', $userId)->get();

        if ($products->isEmpty()) {
            return response()->json([
                'message' => 'No products found for this user'
            ], 404);
        }

        return response()->json([
            'message' => 'Products retrieved successfully',
            'data' => $products
        ], 200);
    }

    // Update a product
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'quantity' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        // Get validated data and filter out null values
        $validatedData = array_filter($validator->validated(), function ($value) {
            return !is_null($value);
        });

        // Check if all fields are null
        if (empty($validatedData)) {
            return response()->json([
                'message' => 'No data provided for update add name or description ...'
            ], 400);
        }

        // Update the product only with non-null fields
        $product->update($validatedData);

        return response()->json([
            'message' => 'Product updated successfully',
            'data' => $product
        ], 200);
    }


    public function search($keyword)
    {
        // Check the keyword value
        \Log::info('Search keyword: ' . $keyword);

        $products = Product::where('name', 'like', '%' . $keyword . '%')->get();

        \Log::info('Found products: ' . $products->count());

        if ($products->isEmpty()) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }

        return response()->json([
            'message' => 'Products retrieved successfully',
            'data' => $products
        ], 200);
    }


    public function delete($id)
    {
        // Find the product by ID
        $product = Product::find($id);

        // Check if the product exists
        if (!$product) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }

        // Delete the product
        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully'
        ], 200);
    }


}
