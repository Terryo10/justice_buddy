<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lawyer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class LawyerController extends Controller
{
    /**
     * Display a listing of active and verified lawyers.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Lawyer::active()->verified()->with([]);

        // Filter by specialization
        if ($request->has('specialization')) {
            $query->bySpecialization($request->specialization);
        }

        // Filter by city
        if ($request->has('city')) {
            $query->byCity($request->city);
        }

        // Filter by province
        if ($request->has('province')) {
            $query->byProvince($request->province);
        }

        // Filter by accepting new clients
        if ($request->boolean('accepting_clients')) {
            $query->acceptingClients();
        }

        // Filter by language
        if ($request->has('language')) {
            $query->whereJsonContains('languages', $request->language);
        }

        // Location-based search
        if ($request->has(['latitude', 'longitude'])) {
            $latitude = $request->latitude;
            $longitude = $request->longitude;
            $radius = $request->get('radius', 50); // Default 50km radius

            $query->nearLocation($latitude, $longitude, $radius);
        }

        // Search by name or firm
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('firm_name', 'like', "%{$search}%")
                  ->orWhere('bio', 'like', "%{$search}%");
            });
        }

        // Sort options
        $sortBy = $request->get('sort_by', 'rating');
        $sortDirection = $request->get('sort_direction', 'desc');

        switch ($sortBy) {
            case 'name':
                $query->orderBy('first_name', $sortDirection)->orderBy('last_name', $sortDirection);
                break;
            case 'experience':
                $query->orderBy('years_experience', $sortDirection);
                break;
            case 'rating':
                $query->orderBy('rating', $sortDirection)->orderBy('review_count', 'desc');
                break;
            case 'distance':
                // Already ordered by distance if location search is used
                if (!$request->has(['latitude', 'longitude'])) {
                    $query->orderBy('city')->orderBy('first_name');
                }
                break;
            default:
                $query->orderBy('rating', 'desc')->orderBy('review_count', 'desc');
        }

        // Pagination
        $perPage = min($request->get('per_page', 20), 50); // Max 50 per page
        $lawyers = $query->paginate($perPage);

        // Transform the data to include computed fields
        $lawyers->getCollection()->transform(function ($lawyer) {
            return [
                'id' => $lawyer->id,
                'full_name' => $lawyer->full_name,
                'first_name' => $lawyer->first_name,
                'last_name' => $lawyer->last_name,
                'firm_name' => $lawyer->firm_name,
                'email' => $lawyer->email,
                'phone' => $lawyer->phone,
                'mobile' => $lawyer->mobile,
                'profile_image' => $lawyer->profile_image,
                'specializations' => $lawyer->specializations,
                'languages' => $lawyer->languages,
                'years_experience' => $lawyer->years_experience,
                'city' => $lawyer->city,
                'province' => $lawyer->province,
                'formatted_address' => $lawyer->formatted_address,
                'consultation_fee' => $lawyer->consultation_fee,
                'consultation_methods' => $lawyer->consultation_methods,
                'accepts_new_clients' => $lawyer->accepts_new_clients,
                'rating' => $lawyer->rating,
                'review_count' => $lawyer->review_count,
                'website' => $lawyer->website,
                'slug' => $lawyer->slug,
                'distance' => $lawyer->distance ?? null,
                'bio' => $lawyer->bio ? (strlen($lawyer->bio) > 200 ? substr($lawyer->bio, 0, 200) . '...' : $lawyer->bio) : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $lawyers,
            'message' => 'Lawyers retrieved successfully'
        ]);
    }

    /**
     * Display the specified lawyer.
     */
    public function show(string $slug): JsonResponse
    {
        $lawyer = Lawyer::where('slug', $slug)
            ->where('is_active', true)
            ->where('is_verified', true)
            ->first();

        if (!$lawyer) {
            return response()->json([
                'success' => false,
                'message' => 'Lawyer not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $lawyer->id,
                'full_name' => $lawyer->full_name,
                'first_name' => $lawyer->first_name,
                'last_name' => $lawyer->last_name,
                'firm_name' => $lawyer->firm_name,
                'email' => $lawyer->email,
                'phone' => $lawyer->phone,
                'mobile' => $lawyer->mobile,
                'fax' => $lawyer->fax,
                'license_number' => $lawyer->license_number,
                'profile_image' => $lawyer->profile_image,
                'bio' => $lawyer->bio,
                'specializations' => $lawyer->specializations,
                'languages' => $lawyer->languages,
                'years_experience' => $lawyer->years_experience,
                'address' => $lawyer->address,
                'city' => $lawyer->city,
                'province' => $lawyer->province,
                'postal_code' => $lawyer->postal_code,
                'formatted_address' => $lawyer->formatted_address,
                'latitude' => $lawyer->latitude,
                'longitude' => $lawyer->longitude,
                'admission_date' => $lawyer->admission_date,
                'law_society' => $lawyer->law_society,
                'courts_admitted' => $lawyer->courts_admitted,
                'consultation_fee' => $lawyer->consultation_fee,
                'consultation_methods' => $lawyer->consultation_methods,
                'accepts_new_clients' => $lawyer->accepts_new_clients,
                'business_hours' => $lawyer->business_hours,
                'website' => $lawyer->website,
                'social_media' => $lawyer->social_media,
                'rating' => $lawyer->rating,
                'review_count' => $lawyer->review_count,
                'slug' => $lawyer->slug,
                'verified_at' => $lawyer->verified_at,
            ],
            'message' => 'Lawyer retrieved successfully'
        ]);
    }

    /**
     * Get specializations list for filters.
     */
    public function specializations(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Lawyer::getSpecializationOptions(),
            'message' => 'Specializations retrieved successfully'
        ]);
    }

    /**
     * Get available locations (cities/provinces).
     */
    public function locations(): JsonResponse
    {
        $cities = Lawyer::active()
            ->verified()
            ->distinct()
            ->orderBy('city')
            ->pluck('city')
            ->filter()
            ->values();

        $provinces = Lawyer::getProvinceOptions();

        return response()->json([
            'success' => true,
            'data' => [
                'cities' => $cities,
                'provinces' => $provinces
            ],
            'message' => 'Locations retrieved successfully'
        ]);
    }

    /**
     * Get lawyers by specialization.
     */
    public function bySpecialization(string $specialization): JsonResponse
    {
        $lawyers = Lawyer::active()
            ->verified()
            ->bySpecialization($specialization)
            ->orderBy('rating', 'desc')
            ->orderBy('review_count', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($lawyer) {
                return [
                    'id' => $lawyer->id,
                    'full_name' => $lawyer->full_name,
                    'firm_name' => $lawyer->firm_name,
                    'phone' => $lawyer->phone,
                    'city' => $lawyer->city,
                    'province' => $lawyer->province,
                    'rating' => $lawyer->rating,
                    'review_count' => $lawyer->review_count,
                    'consultation_fee' => $lawyer->consultation_fee,
                    'slug' => $lawyer->slug,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $lawyers,
            'message' => 'Lawyers by specialization retrieved successfully'
        ]);
    }

    /**
     * Search lawyers near a location.
     */
    public function nearLocation(Request $request): JsonResponse
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:1|max:200',
            'limit' => 'nullable|integer|min:1|max:50'
        ]);

        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $radius = $request->get('radius', 50);
        $limit = $request->get('limit', 20);

        $lawyers = Lawyer::active()
            ->verified()
            ->nearLocation($latitude, $longitude, $radius)
            ->limit($limit)
            ->get()
            ->map(function ($lawyer) {
                return [
                    'id' => $lawyer->id,
                    'full_name' => $lawyer->full_name,
                    'firm_name' => $lawyer->firm_name,
                    'phone' => $lawyer->phone,
                    'city' => $lawyer->city,
                    'province' => $lawyer->province,
                    'formatted_address' => $lawyer->formatted_address,
                    'distance' => round($lawyer->distance, 2),
                    'rating' => $lawyer->rating,
                    'review_count' => $lawyer->review_count,
                    'specializations' => $lawyer->specializations,
                    'slug' => $lawyer->slug,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $lawyers,
            'message' => 'Nearby lawyers retrieved successfully'
        ]);
    }

    /**
     * Get featured/top-rated lawyers.
     */
    public function featured(): JsonResponse
    {
        $lawyers = Lawyer::active()
            ->verified()
            ->acceptingClients()
            ->where('rating', '>=', 4.0)
            ->orderBy('rating', 'desc')
            ->orderBy('review_count', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($lawyer) {
                return [
                    'id' => $lawyer->id,
                    'full_name' => $lawyer->full_name,
                    'firm_name' => $lawyer->firm_name,
                    'specializations' => $lawyer->specializations,
                    'city' => $lawyer->city,
                    'province' => $lawyer->province,
                    'rating' => $lawyer->rating,
                    'review_count' => $lawyer->review_count,
                    'years_experience' => $lawyer->years_experience,
                    'profile_image' => $lawyer->profile_image,
                    'slug' => $lawyer->slug,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $lawyers,
            'message' => 'Featured lawyers retrieved successfully'
        ]);
    }
}