<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    /**
     * @OA\Get(
     *     path="/wishlist",
     *     summary="Get current user's wishlist",
     *     tags={"Wishlist"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Wishlist retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $wishlists = Wishlist::where('user_id', $request->user()->id)
            ->with('product')
            ->latest()
            ->get();

        return response()->json([
            'data' => $wishlists->map(function ($wishlist) {
                return [
                    'id' => $wishlist->id,
                    'product_id' => $wishlist->product_id,
                    'product' => $wishlist->product,
                    'created_at' => $wishlist->created_at,
                ];
            }),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/wishlist",
     *     summary="Add product to wishlist",
     *     tags={"Wishlist"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id"},
     *             @OA\Property(property="product_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product added to wishlist successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $existing = Wishlist::where('user_id', $request->user()->id)
            ->where('product_id', $request->product_id)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Product already in wishlist',
                'data' => [
                    'id' => $existing->id,
                    'product_id' => $existing->product_id,
                    'product' => $existing->product,
                ],
            ]);
        }

        $wishlist = Wishlist::create([
            'user_id' => $request->user()->id,
            'product_id' => $request->product_id,
        ]);

        $wishlist->load('product');

        return response()->json([
            'message' => 'Product added to wishlist successfully',
            'data' => [
                'id' => $wishlist->id,
                'product_id' => $wishlist->product_id,
                'product' => $wishlist->product,
            ],
        ], 201);
    }

    /**
     * @OA\Delete(
     *     path="/wishlist/{wishlist}",
     *     summary="Remove product from wishlist",
     *     tags={"Wishlist"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="wishlist",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product removed from wishlist successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Wishlist item not found")
     * )
     */
    public function destroy(Request $request, Wishlist $wishlist): JsonResponse
    {
        if ($wishlist->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $wishlist->delete();

        return response()->json([
            'message' => 'Product removed from wishlist successfully',
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/wishlist/product/{productId}",
     *     summary="Remove product from wishlist by product ID",
     *     tags={"Wishlist"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="productId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product removed from wishlist successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Product not in wishlist")
     * )
     */
    public function destroyByProduct(Request $request, int $productId): JsonResponse
    {
        $wishlist = Wishlist::where('user_id', $request->user()->id)
            ->where('product_id', $productId)
            ->first();

        if (!$wishlist) {
            return response()->json(['message' => 'Product not in wishlist'], 404);
        }

        $wishlist->delete();

        return response()->json([
            'message' => 'Product removed from wishlist successfully',
        ]);
    }

    /**
     * @OA\Get(
     *     path="/wishlist/check/{productId}",
     *     summary="Check if product is in wishlist",
     *     tags={"Wishlist"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="productId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Check result",
     *         @OA\JsonContent(
     *             @OA\Property(property="in_wishlist", type="boolean")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function check(Request $request, int $productId): JsonResponse
    {
        $exists = Wishlist::where('user_id', $request->user()->id)
            ->where('product_id', $productId)
            ->exists();

        return response()->json([
            'in_wishlist' => $exists,
        ]);
    }
}


