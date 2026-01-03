<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * @OA\Get(
     *     path="/orders",
     *     summary="Get current user's orders",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending", "processing", "shipped", "delivered", "cancelled"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Orders retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Order::where('user_id', $request->user()->id)
                ->with('items.product');

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            $orders = $query->latest()->get();

            return response()->json(['data' => $orders]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve orders', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to retrieve orders. Please try again later.',
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/orders/{order}",
     *     summary="Get order details",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="order",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order details",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Order not found"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function show(Request $request, Order $order): JsonResponse
    {
        try {
            if ($order->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
                return response()->json(['message' => 'You do not have permission to view this order'], 403);
            }

            $order->load('items.product', 'user');

            return response()->json([
                'data' => $order,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve order', [
                'order_id' => $order->id,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to retrieve order details.',
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/orders",
     *     summary="Create order from cart (Checkout)",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"shipping_name", "shipping_phone", "shipping_address", "payment_method"},
     *             @OA\Property(property="shipping_name", type="string", example="John Doe"),
     *             @OA\Property(property="shipping_phone", type="string", example="081234567890"),
     *             @OA\Property(property="shipping_address", type="string", example="Jl. Example No. 123"),
     *             @OA\Property(property="payment_method", type="string", example="bank_transfer"),
     *             @OA\Property(property="notes", type="string", example="Please handle with care"),
     *             @OA\Property(property="selected_item_ids", type="array", @OA\Items(type="integer"), description="Optional: Array of cart item IDs to checkout. If not provided, all cart items will be checked out.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Cart is empty or insufficient stock"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'shipping_name' => 'required|string|max:255',
            'shipping_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string',
            'payment_method' => 'required|string|in:bank_transfer,e_wallet,cod',
            'notes' => 'nullable|string',
            'selected_item_ids' => 'nullable|array',
            'selected_item_ids.*' => 'integer|exists:cart_items,id',
        ]);

        $cart = Cart::where('user_id', $request->user()->id)
            ->with('items.product')
            ->first();

        if (!$cart) {
            return response()->json(['message' => 'Cart not found'], 400);
        }

        $checkoutItems = $cart->items;
        if ($request->has('selected_item_ids') && !empty($request->selected_item_ids)) {
            $selectedIds = $request->selected_item_ids;
            $checkoutItems = $cart->items->filter(function ($item) use ($selectedIds) {
                return in_array($item->id, $selectedIds);
            });

            if ($checkoutItems->isEmpty()) {
                return response()->json(['message' => 'No valid items selected for checkout'], 400);
            }
        }

        if ($checkoutItems->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 400);
        }

        foreach ($checkoutItems as $item) {
            if (!$item->product->hasStock($item->quantity)) {
                return response()->json([
                    'message' => "Insufficient stock for '{$item->product->name}'. Available: {$item->product->quantity}"
                ], 400);
            }
        }

        $totalAmount = $checkoutItems->sum('subtotal');

        try {
            DB::beginTransaction();

            $stockReduced = [];
            foreach ($checkoutItems as $item) {
                if ($item->product->reduceStock($item->quantity)) {
                    $stockReduced[] = $item;
                } else {
                    foreach ($stockReduced as $reducedItem) {
                        $reducedItem->product->restoreStock($reducedItem->quantity);
                    }
                    return response()->json([
                        'message' => "Failed to reduce stock for '{$item->product->name}'"
                    ], 400);
                }
            }

            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $request->user()->id,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => $request->payment_method,
                'shipping_name' => $request->shipping_name,
                'shipping_phone' => $request->shipping_phone,
                'shipping_address' => $request->shipping_address,
                'notes' => $request->notes,
            ]);

            foreach ($checkoutItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'price' => $item->product->price,
                    'quantity' => $item->quantity,
                    'subtotal' => $item->subtotal,
                ]);
            }

            if ($request->has('selected_item_ids') && !empty($request->selected_item_ids)) {
                $cart->items()->whereIn('id', $request->selected_item_ids)->delete();
            } else {
                $cart->items()->delete();
            }

            DB::commit();

            $order->load('items.product');

            Log::info('Order created successfully', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'user_id' => $request->user()->id,
                'total_amount' => $order->total_amount,
            ]);

            return response()->json([
                'message' => 'Order created successfully',
                'data' => $order,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            foreach ($checkoutItems as $item) {
                try {
                    $item->product->restoreStock($item->quantity);
                } catch (\Exception $restoreException) {
                    Log::error('Failed to restore stock during order rollback', [
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'error' => $restoreException->getMessage(),
                    ]);
                }
            }

            Log::error('Failed to create order', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Failed to create order. Please try again later.',
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/orders/{order}/pay",
     *     summary="Simulate payment for order",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="order",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Invalid order status"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function pay(Request $request, Order $order): JsonResponse
    {
        try {
            if ($order->user_id !== $request->user()->id) {
                return response()->json(['message' => 'You do not have permission to pay this order'], 403);
            }

            if ($order->payment_status !== 'pending') {
                return response()->json(['message' => 'Order already paid or cancelled'], 400);
            }

            $order->update([
                'payment_status' => 'paid',
                'status' => 'processing',
                'paid_at' => now(),
            ]);

            $order->load('items.product');

            Log::info('Order payment successful', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'user_id' => $order->user_id,
                'total_amount' => $order->total_amount,
            ]);

            return response()->json([
                'message' => 'Payment successful',
                'data' => $order,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to process payment', [
                'order_id' => $order->id,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Failed to process payment. Please try again later.',
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/orders/{order}/cancel",
     *     summary="Cancel order",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="order",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order cancelled successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Cannot cancel order"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function cancel(Request $request, Order $order): JsonResponse
    {
        try {
            if ($order->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
                return response()->json(['message' => 'You do not have permission to cancel this order'], 403);
            }

            if (!in_array($order->status, ['pending', 'processing'])) {
                return response()->json(['message' => 'Cannot cancel order in current status'], 400);
            }

            DB::beginTransaction();

            $order->load('items.product');
            
            foreach ($order->items as $item) {
                if ($item->product) {
                    $item->product->restoreStock($item->quantity);
                }
            }

            $order->update([
                'status' => 'cancelled',
                'payment_status' => $order->payment_status === 'paid' ? 'refunded' : 'failed',
            ]);

            DB::commit();

            Log::info('Order cancelled', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'user_id' => $request->user()->id,
                'previous_status' => $order->getOriginal('status'),
            ]);

            return response()->json([
                'message' => 'Order cancelled successfully',
                'data' => $order,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to cancel order', [
                'order_id' => $order->id,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to cancel order. Please try again later.',
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/admin/orders",
     *     summary="Get all orders (Admin only)",
     *     tags={"Admin - Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="payment_status",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="All orders retrieved",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function adminIndex(Request $request): JsonResponse
    {
        try {
            $query = Order::with(['items.product', 'user']);

            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            if ($request->has('payment_status') && $request->payment_status) {
                $query->where('payment_status', $request->payment_status);
            }

            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                        ->orWhere('shipping_name', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            }

            $orders = $query->latest()->get();

            return response()->json(['data' => $orders]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve admin orders', [
                'admin_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to retrieve orders. Please try again later.',
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/admin/orders/{order}/status",
     *     summary="Update order status (Admin only)",
     *     tags={"Admin - Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="order",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"pending", "processing", "shipped", "delivered", "cancelled"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        try {
            $request->validate([
                'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
            ]);

            $previousStatus = $order->status;

            DB::beginTransaction();

            if ($request->status === 'cancelled' && $previousStatus !== 'cancelled') {
                $order->load('items.product');
                
                foreach ($order->items as $item) {
                    if ($item->product) {
                        $item->product->restoreStock($item->quantity);
                    }
                }
            }

            $order->update([
                'status' => $request->status,
            ]);

            if ($request->status === 'cancelled' && $order->payment_status === 'paid') {
                $order->update(['payment_status' => 'refunded']);
            }

            $order->load('items.product', 'user');

            DB::commit();

            Log::info('Order status updated', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'previous_status' => $previousStatus,
                'new_status' => $request->status,
                'admin_id' => $request->user()->id,
            ]);

            return response()->json([
                'message' => 'Order status updated successfully',
                'data' => $order,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to update order status', [
                'order_id' => $order->id,
                'admin_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to update order status. Please try again later.',
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/admin/orders/stats",
     *     summary="Get order statistics (Admin only)",
     *     tags={"Admin - Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Order statistics",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = [
                'total_orders' => Order::count(),
                'pending_orders' => Order::where('status', 'pending')->count(),
                'processing_orders' => Order::where('status', 'processing')->count(),
                'shipped_orders' => Order::where('status', 'shipped')->count(),
                'delivered_orders' => Order::where('status', 'delivered')->count(),
                'cancelled_orders' => Order::where('status', 'cancelled')->count(),
                'total_revenue' => Order::where('payment_status', 'paid')->sum('total_amount'),
                'today_orders' => Order::whereDate('created_at', today())->count(),
                'today_revenue' => Order::whereDate('created_at', today())
                    ->where('payment_status', 'paid')
                    ->sum('total_amount'),
                'total_users' => User::where('role', 'user')->count(),
            ];

            return response()->json([
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve order statistics', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Failed to retrieve statistics. Please try again later.',
            ], 500);
        }
    }
}

