<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    /**
     * @OA\Get(
     *     path="/cart",
     *     summary="Get current user's cart",
     *     tags={"Cart"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Cart retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="items", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="total", type="number"),
     *                 @OA\Property(property="total_items", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $cart = $this->getOrCreateCart($request->user());
            $cart->load('items.product');

            return response()->json([
                'data' => [
                    'id' => $cart->id,
                    'items' => $cart->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'product_id' => $item->product_id,
                            'product' => $item->product,
                            'quantity' => $item->quantity,
                            'subtotal' => $item->subtotal,
                        ];
                    }),
                    'total' => $cart->total,
                    'total_items' => $cart->total_items,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve cart', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Failed to retrieve cart. Please try again later.',
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/cart",
     *     summary="Add item to cart",
     *     tags={"Cart"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id"},
     *             @OA\Property(property="product_id", type="integer", example=1),
     *             @OA\Property(property="quantity", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item added to cart successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Product out of stock or insufficient stock"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Product not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'integer|min:1',
            ]);

            $product = Product::findOrFail($request->product_id);
            
            if (!$product->isInStock()) {
                return response()->json([
                    'message' => 'Product is out of stock',
                ], 400);
            }

            $cart = $this->getOrCreateCart($request->user());
            $quantity = $request->input('quantity', 1);

            $cartItem = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $request->product_id)
                ->first();

            if ($cartItem) {
                $newQuantity = $cartItem->quantity + $quantity;
                if (!$product->hasStock($newQuantity)) {
                    return response()->json([
                        'message' => 'Insufficient stock. Available: ' . $product->quantity,
                    ], 400);
                }
                $cartItem->update(['quantity' => $newQuantity]);
            } else {
                if (!$product->hasStock($quantity)) {
                    return response()->json([
                        'message' => 'Insufficient stock. Available: ' . $product->quantity,
                    ], 400);
                }
                $cartItem = CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $request->product_id,
                    'quantity' => $quantity,
                ]);
            }

            $cart->load('items.product');

            Log::info('Item added to cart', [
                'user_id' => $request->user()->id,
                'product_id' => $request->product_id,
                'quantity' => $quantity,
            ]);

            return response()->json([
                'message' => 'Item added to cart successfully',
                'data' => [
                    'id' => $cart->id,
                    'items' => $cart->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'product_id' => $item->product_id,
                            'product' => $item->product,
                            'quantity' => $item->quantity,
                            'subtotal' => $item->subtotal,
                        ];
                    }),
                    'total' => $cart->total,
                    'total_items' => $cart->total_items,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to add item to cart', [
                'user_id' => $request->user()->id,
                'product_id' => $request->product_id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Failed to add item to cart. Please try again later.',
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/cart/{cartItem}",
     *     summary="Update cart item quantity",
     *     tags={"Cart"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="cartItem",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"quantity"},
     *             @OA\Property(property="quantity", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cart item updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Insufficient stock"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Cart item not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function update(Request $request, CartItem $cartItem): JsonResponse
    {
        try {
            $cart = $this->getOrCreateCart($request->user());

            if ($cartItem->cart_id !== $cart->id) {
                return response()->json(['message' => 'You do not have permission to update this cart item'], 403);
            }

            $request->validate([
                'quantity' => 'required|integer|min:1',
            ]);

            $product = $cartItem->product;
            if (!$product->hasStock($request->quantity)) {
                return response()->json([
                    'message' => 'Insufficient stock. Available: ' . $product->quantity,
                ], 400);
            }

            $cartItem->update(['quantity' => $request->quantity]);

            $cart->load('items.product');

            Log::info('Cart item updated', [
                'user_id' => $request->user()->id,
                'cart_item_id' => $cartItem->id,
                'quantity' => $request->quantity,
            ]);

            return response()->json([
                'message' => 'Cart item updated successfully',
                'data' => [
                    'id' => $cart->id,
                    'items' => $cart->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'product_id' => $item->product_id,
                            'product' => $item->product,
                            'quantity' => $item->quantity,
                            'subtotal' => $item->subtotal,
                        ];
                    }),
                    'total' => $cart->total,
                    'total_items' => $cart->total_items,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to update cart item', [
                'user_id' => $request->user()->id,
                'cart_item_id' => $cartItem->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to update cart item. Please try again later.',
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/cart/{cartItem}",
     *     summary="Remove item from cart",
     *     tags={"Cart"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="cartItem",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item removed from cart successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Cart item not found"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function destroy(Request $request, CartItem $cartItem): JsonResponse
    {
        try {
            $cart = $this->getOrCreateCart($request->user());

            if ($cartItem->cart_id !== $cart->id) {
                return response()->json(['message' => 'You do not have permission to remove this cart item'], 403);
            }

            $cartItem->delete();

            $cart->load('items.product');

            Log::info('Cart item removed', [
                'user_id' => $request->user()->id,
                'cart_item_id' => $cartItem->id,
            ]);

            return response()->json([
                'message' => 'Item removed from cart successfully',
                'data' => [
                    'id' => $cart->id,
                    'items' => $cart->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'product_id' => $item->product_id,
                            'product' => $item->product,
                            'quantity' => $item->quantity,
                            'subtotal' => $item->subtotal,
                        ];
                    }),
                    'total' => $cart->total,
                    'total_items' => $cart->total_items,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to remove cart item', [
                'user_id' => $request->user()->id,
                'cart_item_id' => $cartItem->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to remove cart item. Please try again later.',
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/cart",
     *     summary="Clear all items from cart",
     *     tags={"Cart"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Cart cleared successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function clear(Request $request): JsonResponse
    {
        try {
            $cart = $this->getOrCreateCart($request->user());
            $cart->items()->delete();

            Log::info('Cart cleared', [
                'user_id' => $request->user()->id,
                'cart_id' => $cart->id,
            ]);

            return response()->json([
                'message' => 'Cart cleared successfully',
                'data' => [
                    'id' => $cart->id,
                    'items' => [],
                    'total' => 0,
                    'total_items' => 0,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to clear cart', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to clear cart. Please try again later.',
            ], 500);
        }
    }

    private function getOrCreateCart($user): Cart
    {
        return Cart::firstOrCreate(['user_id' => $user->id]);
    }
}


