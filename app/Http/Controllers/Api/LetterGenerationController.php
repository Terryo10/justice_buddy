<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LetterTemplate;
use App\Models\LetterRequest;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class LetterGenerationController extends Controller
{
    protected GeminiService $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    /**
     * Get all available letter templates
     */
    public function getTemplates(Request $request): JsonResponse
    {
        try {
            $query = LetterTemplate::active()->ordered();

            // Filter by category if provided
            if ($request->has('category')) {
                $query->byCategory($request->category);
            }

            // Search functionality
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $templates = $query->get()->map(function ($template) {
                return [
                    'id' => $template->id,
                    'name' => $template->name,
                    'slug' => $template->slug,
                    'description' => $template->description,
                    'category' => $template->category,
                    'required_fields' => $template->required_fields,
                    'optional_fields' => $template->optional_fields,
                    'created_at' => $template->created_at,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $templates,
                'message' => 'Templates retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving templates: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific template by ID
     */
    public function getTemplate(int $templateId): JsonResponse
    {
        try {
            $template = LetterTemplate::active()->findOrFail($templateId);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $template->id,
                    'name' => $template->name,
                    'slug' => $template->slug,
                    'description' => $template->description,
                    'category' => $template->category,
                    'required_fields' => $template->required_fields,
                    'optional_fields' => $template->optional_fields,
                    'template_content' => $template->template_content,
                    'ai_instructions' => $template->ai_instructions,
                    'created_at' => $template->created_at,
                ],
                'message' => 'Template retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Template not found'
            ], 404);
        }
    }

    /**
     * Generate a letter based on template and client matters
     */
    public function generateLetter(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'template_id' => 'required|exists:letter_templates,id',
                'client_name' => 'required|string|max:255',
                'client_email' => 'nullable|email|max:255',
                'client_phone' => 'nullable|string|max:255',
                'client_matters' => 'required|array',
                'client_matters.*' => 'required',
                'generate_async' => 'boolean',
                'device_id' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $template = LetterTemplate::active()->findOrFail($request->template_id);

            // Validate required fields
            $validationErrors = $template->validateClientMatters($request->client_matters);
            if (!empty($validationErrors)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required fields',
                    'errors' => $validationErrors
                ], 422);
            }

            // Create letter request
            $letterRequest = LetterRequest::create([
                'letter_template_id' => $template->id,
                'client_name' => $request->client_name,
                'client_email' => $request->client_email,
                'client_phone' => $request->client_phone,
                'client_matters' => $request->client_matters,
                'status' => 'pending',
                'device_id' => $request->device_id,
            ]);

            // Determine if we should process synchronously or asynchronously
            $generateAsync = $request->boolean('generate_async', true);

            if ($generateAsync) {
                // Process asynchronously (recommended for production)
                dispatch(function () use ($letterRequest) {
                    $this->geminiService->processLetterRequest($letterRequest);
                })->afterResponse();

                return response()->json([
                    'success' => true,
                    'data' => [
                        'request_id' => $letterRequest->request_id,
                        'status' => $letterRequest->status,
                        'message' => 'Letter generation started. Use the request_id to check status.',
                        'check_status_url' => url('/api/letter-requests/status/' . $letterRequest->request_id)
                    ],
                    'message' => 'Letter generation request created successfully'
                ], 202);
            } else {
                // Process synchronously (for immediate results)
                $this->geminiService->processLetterRequest($letterRequest);

                $letterRequest->refresh();

                if ($letterRequest->status === 'completed') {
                    return response()->json([
                        'success' => true,
                        'data' => [
                            'request_id' => $letterRequest->request_id,
                            'status' => $letterRequest->status,
                            'generated_letter' => $letterRequest->generated_letter,
                            'document_url' => $letterRequest->document_path ?
                                Storage::url($letterRequest->document_path) : null,
                            'generated_at' => $letterRequest->generated_at,
                        ],
                        'message' => 'Letter generated successfully'
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to generate letter',
                        'error' => $letterRequest->error_message
                    ], 500);
                }
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating letter: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check the status of a letter generation request
     */
    public function checkStatus(string $requestId): JsonResponse
    {
        try {
            $letterRequest = LetterRequest::where('request_id', $requestId)->firstOrFail();

            $response = [
                'success' => true,
                'data' => [
                    'id' => $letterRequest->id,
                    'request_id' => $letterRequest->request_id,
                    'letter_template_id' => $letterRequest->letter_template_id,
                    'status' => $letterRequest->status,
                    'client_name' => $letterRequest->client_name,
                    'client_email' => $letterRequest->client_email,
                    'client_phone' => $letterRequest->client_phone,
                    'client_matters' => $letterRequest->client_matters,
                    'template_name' => $letterRequest->letterTemplate->name,
                    'created_at' => $letterRequest->created_at,
                    'updated_at' => $letterRequest->updated_at,
                    'document_path' => $letterRequest->document_path,
                    'letter_template' => [
                        'id' => $letterRequest->letterTemplate->id,
                        'name' => $letterRequest->letterTemplate->name,
                        'slug' => $letterRequest->letterTemplate->slug,
                        'description' => $letterRequest->letterTemplate->description,
                        'category' => $letterRequest->letterTemplate->category,
                        'required_fields' => $letterRequest->letterTemplate->required_fields,
                        'optional_fields' => $letterRequest->letterTemplate->optional_fields,
                        'template_content' => $letterRequest->letterTemplate->template_content,
                        'ai_instructions' => $letterRequest->letterTemplate->ai_instructions,
                        'is_active' => $letterRequest->letterTemplate->is_active,
                        'sort_order' => $letterRequest->letterTemplate->sort_order,
                        'created_at' => $letterRequest->letterTemplate->created_at,
                    ],
                ],
                'message' => 'Status retrieved successfully'
            ];

            // Add additional data based on status
            switch ($letterRequest->status) {
                case 'completed':
                    $response['data']['generated_letter'] = $letterRequest->generated_letter;
                    $response['data']['document_url'] = $letterRequest->document_path ?
                        Storage::url($letterRequest->document_path) : null;
                    $response['data']['generated_at'] = $letterRequest->generated_at;
                    break;

                case 'failed':
                    $response['data']['error_message'] = $letterRequest->error_message;
                    break;
            }

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Request not found'
            ], 404);
        }
    }

    /**
     * Download the generated document (returns JSON with download URL for API)
     */
    public function downloadDocument(string $requestId): JsonResponse
    {
        try {
            $letterRequest = LetterRequest::where('request_id', $requestId)
                ->where('status', 'completed')
                ->firstOrFail();

            if (!$letterRequest->document_path || !Storage::disk('public')->exists($letterRequest->document_path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'download_url' => Storage::url($letterRequest->document_path),
                    'filename' => basename($letterRequest->document_path),
                    'generated_at' => $letterRequest->generated_at,
                ],
                'message' => 'Download URL generated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Request not found or document not ready'
            ], 404);
        }
    }

    /**
     * Stream/download the generated document file directly (for admin panel)
     */
    public function streamDocument(string $requestId)
    {
        try {
            $letterRequest = LetterRequest::where('request_id', $requestId)
                ->where('status', 'completed')
                ->firstOrFail();

            if (!$letterRequest->document_path || !Storage::disk('public')->exists($letterRequest->document_path)) {
                abort(404, 'Document not found');
            }

            $filePath = storage_path('app/public/' . $letterRequest->document_path);
            $filename = basename($letterRequest->document_path);

            // Determine MIME type based on file extension
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $mimeType = match ($extension) {
                'pdf' => 'application/pdf',
                'doc' => 'application/msword',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'txt' => 'text/plain',
                default => 'application/octet-stream',
            };

            return response()->download($filePath, $filename, [
                'Content-Type' => $mimeType,
            ]);
        } catch (\Exception $e) {
            abort(404, 'Document not found or not ready');
        }
    }

    /**
     * Get user's letter generation history
     */
    public function getHistory(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'client_email' => 'required|email',
                'page' => 'integer|min:1',
                'per_page' => 'integer|min:1|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $perPage = $request->get('per_page', 10);

            $letterRequests = LetterRequest::with('letterTemplate')
                ->where('client_email', $request->client_email)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            $data = $letterRequests->through(function ($request) {
                return [
                    'request_id' => $request->request_id,
                    'template_name' => $request->letterTemplate->name,
                    'status' => $request->status,
                    'created_at' => $request->created_at,
                    'generated_at' => $request->generated_at,
                    'has_document' => !empty($request->document_path),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'History retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving history: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get device's letter generation history
     */
    public function getHistoryByDevice(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'device_id' => 'required|string',
                'page' => 'integer|min:1',
                'per_page' => 'integer|min:1|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $perPage = $request->get('per_page', 10);

            $letterRequests = LetterRequest::with('letterTemplate')
                ->where('device_id', $request->device_id)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            $data = $letterRequests->through(function ($request) {
                return [
                    'id' => $request->id,
                    'request_id' => $request->request_id,
                    'template_name' => $request->letterTemplate->name,
                    'client_name' => $request->client_name,
                    'status' => $request->status,
                    'created_at' => $request->created_at,
                    'generated_at' => $request->generated_at,
                    'has_document' => !empty($request->document_path),
                    'generated_letter' => $request->status === 'completed' ? $request->generated_letter : null,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'History retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving history: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update/save letter content
     */
    public function updateLetter(Request $request, string $requestId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'generated_letter' => 'required|string',
                'client_name' => 'sometimes|string|max:255',
                'client_email' => 'sometimes|email|max:255',
                'client_phone' => 'sometimes|string|max:255',
                'device_id' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Find the letter request
            $letterRequest = LetterRequest::where('request_id', $requestId)
                ->where('device_id', $request->device_id) // Ensure user owns this letter
                ->firstOrFail();

            // Only allow updates to completed letters
            if ($letterRequest->status !== 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Can only update completed letters'
                ], 400);
            }

            // Update the letter content and any client information
            $updateData = [
                'generated_letter' => $request->generated_letter,
                'updated_at' => now(),
            ];

            if ($request->has('client_name')) {
                $updateData['client_name'] = $request->client_name;
            }

            if ($request->has('client_email')) {
                $updateData['client_email'] = $request->client_email;
            }

            if ($request->has('client_phone')) {
                $updateData['client_phone'] = $request->client_phone;
            }

            $letterRequest->update($updateData);

            // Optionally regenerate the document file with updated content
            if (config('services.letter_generator.auto_regenerate_on_update', true)) {
                try {
                    $documentPath = $this->regenerateDocument($letterRequest);
                    if ($documentPath) {
                        $letterRequest->update(['document_path' => $documentPath]);
                    }
                } catch (\Exception $e) {
                    // Log error but don't fail the update
                    Log::warning('Failed to regenerate document after update: ' . $e->getMessage());
                }
            }

            $letterRequest->refresh();
            $letterRequest->load('letterTemplate');

            return response()->json([
                'success' => true,
                'data' => [
                    'request_id' => $letterRequest->request_id,
                    'status' => $letterRequest->status,
                    'client_name' => $letterRequest->client_name,
                    'client_email' => $letterRequest->client_email,
                    'client_phone' => $letterRequest->client_phone,
                    'generated_letter' => $letterRequest->generated_letter,
                    'template_name' => $letterRequest->letterTemplate->name,
                    'document_url' => $letterRequest->document_path ? 
                        Storage::url($letterRequest->document_path) : null,
                    'updated_at' => $letterRequest->updated_at,
                ],
                'message' => 'Letter updated successfully'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Letter not found or you do not have permission to update it'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating letter: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Regenerate document file with updated content
     */
    protected function regenerateDocument(LetterRequest $letterRequest): ?string
    {
        try {
            $content = $letterRequest->generated_letter;
            $filename = 'letters/' . $letterRequest->request_id . '_updated_' . now()->format('YmdHis') . '.txt';
            $fullPath = storage_path('app/public/' . $filename);
            
            // Ensure directory exists
            $directory = dirname($fullPath);
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            file_put_contents($fullPath, $content);

            return $filename;

        } catch (\Exception $e) {
            Log::error('Error regenerating document: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get available categories
     */
    public function getCategories(): JsonResponse
    {
        try {
            $categories = LetterTemplate::getAvailableCategories();

            return response()->json([
                'success' => true,
                'data' => $categories,
                'message' => 'Categories retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving categories: ' . $e->getMessage()
            ], 500);
        }
    }
}
