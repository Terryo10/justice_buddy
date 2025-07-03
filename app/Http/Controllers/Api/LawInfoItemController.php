<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LawInfoItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LawInfoItemController extends Controller
{
    /**
     * Display a listing of active law info items.
     */
    public function index(Request $request): JsonResponse
    {
        $query = LawInfoItem::active()
            ->with('category')
            ->ordered();

        // Filter by category if provided
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by category slug if provided
        if ($request->has('category_slug')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category_slug);
            });
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('more_description', 'like', "%{$search}%");
            });
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $lawInfoItems = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $lawInfoItems,
            'message' => 'Law info items retrieved successfully'
        ]);
    }

    /**
     * Display the specified law info item.
     */
    public function show(string $slug): JsonResponse
    {
        $lawInfoItem = LawInfoItem::where('slug', $slug)
            ->where('is_active', true)
            ->with('category')
            ->first();

        if (!$lawInfoItem) {
            return response()->json([
                'success' => false,
                'message' => 'Law info item not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $lawInfoItem,
            'message' => 'Law info item retrieved successfully'
        ]);
    }

    /**
     * Get related law info items (same category).
     */
    public function related(string $slug): JsonResponse
    {
        $lawInfoItem = LawInfoItem::where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (!$lawInfoItem) {
            return response()->json([
                'success' => false,
                'message' => 'Law info item not found'
            ], 404);
        }

        $relatedItems = LawInfoItem::where('category_id', $lawInfoItem->category_id)
            ->where('id', '!=', $lawInfoItem->id)
            ->where('is_active', true)
            ->ordered()
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $relatedItems,
            'message' => 'Related law info items retrieved successfully'
        ]);
    }
}