<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;

class DocumentController extends Controller
{
    /**
     * Display a listing of documents with filtering and pagination
     */
    public function index(Request $request)
    {
        $query = Document::active();

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $query->search($request->search);
        }

        // Filter by category
        if ($request->has('category') && !empty($request->category)) {
            $query->byCategory($request->category);
        }

        // Filter by file type
        if ($request->has('file_type') && !empty($request->file_type)) {
            $query->byFileType($request->file_type);
        }

        // Filter by featured
        if ($request->has('featured') && $request->featured == 'true') {
            $query->featured();
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        
        switch ($sortBy) {
            case 'name':
                $query->orderBy('name', $sortOrder);
                break;
            case 'created_at':
                $query->orderBy('created_at', $sortOrder);
                break;
            case 'download_count':
                $query->orderBy('download_count', $sortOrder);
                break;
            case 'file_size':
                $query->orderBy('file_size', $sortOrder);
                break;
            case 'sort_order':
                $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc');
                break;
            default:
                $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc');
        }

        $perPage = $request->get('per_page', 15);
        $documents = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $documents->items(),
            'pagination' => [
                'current_page' => $documents->currentPage(),
                'last_page' => $documents->lastPage(),
                'per_page' => $documents->perPage(),
                'total' => $documents->total(),
                'from' => $documents->firstItem(),
                'to' => $documents->lastItem(),
            ],
        ]);
    }

    /**
     * Display the specified document
     */
    public function show($id)
    {
        $document = Document::active()->find($id);

        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $document
        ]);
    }

    /**
     * Download the specified document
     */
    public function download($id)
    {
        $document = Document::active()->find($id);

        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found'
            ], 404);
        }

        // Check if file exists
        if (!Storage::disk('public')->exists($document->file_path)) {
            return response()->json([
                'success' => false,
                'message' => 'File not found on server'
            ], 404);
        }

        // Increment download count
        $document->incrementDownloadCount();

        // Get file content
        $fileContent = Storage::disk('public')->get($document->file_path);
        
        // Return file download response
        return Response::make($fileContent, 200, [
            'Content-Type' => $document->file_type,
            'Content-Disposition' => 'attachment; filename="' . $document->file_name . '"',
            'Content-Length' => $document->file_size,
        ]);
    }

    /**
     * Get all available document categories
     */
    public function categories()
    {
        $categories = Document::active()
            ->select('category')
            ->distinct()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->orderBy('category')
            ->pluck('category')
            ->toArray();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * Get featured documents
     */
    public function featured(Request $request)
    {
        $limit = $request->get('limit', 10);
        
        $documents = Document::active()
            ->featured()
            ->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $documents
        ]);
    }

    /**
     * Get popular documents (most downloaded)
     */
    public function popular(Request $request)
    {
        $limit = $request->get('limit', 10);
        
        $documents = Document::active()
            ->popular()
            ->where('download_count', '>', 0)
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $documents
        ]);
    }

    /**
     * Get document statistics
     */
    public function statistics()
    {
        $stats = [
            'total_documents' => Document::active()->count(),
            'total_downloads' => Document::active()->sum('download_count'),
            'featured_documents' => Document::active()->featured()->count(),
            'categories_count' => Document::active()
                ->select('category')
                ->distinct()
                ->whereNotNull('category')
                ->where('category', '!=', '')
                ->count(),
            'file_types' => Document::active()
                ->select('file_type', DB::raw('count(*) as count'))
                ->groupBy('file_type')
                ->get()
                ->keyBy('file_type')
                ->map(function ($item) {
                    return $item->count;
                }),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
