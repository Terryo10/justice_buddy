<?php

// app/Http/Controllers/Api/ChatController.php - Updated to use AIService

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatRule;
use App\Services\AIService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    protected AIService $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Get chat rules for AI system
     */
    public function getRules(Request $request): JsonResponse
    {
        try {
            $modelName = $request->get('model_name');
            
            $rules = ChatRule::forChat($modelName)->get()->map(function ($rule) {
                return [
                    'id' => $rule->id,
                    'name' => $rule->name,
                    'rule_text' => $rule->rule_text,
                    'type' => $rule->type,
                    'priority' => $rule->priority,
                    'model_name' => $rule->model_name,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $rules,
                'message' => 'Chat rules retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving chat rules: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle chat message and generate AI response
     */
    public function chat(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'message' => 'required|string|max:2000',
                'conversation_history' => 'nullable|array',
                'conversation_history.*.role' => 'required|in:user,assistant',
                'conversation_history.*.content' => 'required|string',
                'model_name' => 'nullable|string|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $message = $request->message;
            $conversationHistory = $request->conversation_history ?? [];
            $modelName = $request->model_name ?? null;

            // Get chat rules for the AI
            $rules = ChatRule::forChat($modelName)->get();
            
            // Build system prompt with rules
            $systemPrompt = $this->buildSystemPrompt($rules);

            // Prepare conversation for AI
            $conversation = [];
            
            // Add system prompt
            if (!empty($systemPrompt)) {
                $conversation[] = [
                    'role' => 'system',
                    'content' => $systemPrompt
                ];
            }

            // Add conversation history
            foreach ($conversationHistory as $historyItem) {
                $conversation[] = [
                    'role' => $historyItem['role'],
                    'content' => $historyItem['content']
                ];
            }

            // Add current user message
            $conversation[] = [
                'role' => 'user',
                'content' => $message
            ];

            // Generate AI response using the configured service
            $aiResponse = $this->aiService->generateChatResponse($conversation);

            return response()->json([
                'success' => true,
                'data' => [
                    'message' => $aiResponse,
                    'conversation_id' => uniqid(),
                    'timestamp' => now()->toISOString(),
                    'model_used' => \App\Models\AppSetting::getValue('active_ai_model', 'chatgpt'),
                ],
                'message' => 'Chat response generated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating chat response: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Build system prompt from chat rules
     */
    private function buildSystemPrompt($rules): string
    {
        if (empty($rules)) {
            return '';
        }

        $systemPromptParts = [];
        
        // Group rules by type for better organization
        $rulesByType = [];
        foreach ($rules as $rule) {
            $rulesByType[$rule->type][] = $rule->rule_text;
        }

        // Build system prompt
        foreach ($rulesByType as $type => $ruleTexts) {
            $typeTitle = ucfirst($type) . 's:';
            $systemPromptParts[] = $typeTitle;
            foreach ($ruleTexts as $ruleText) {
                $systemPromptParts[] = '- ' . $ruleText;
            }
            $systemPromptParts[] = ''; // Add blank line between types
        }

        return implode("\n", $systemPromptParts);
    }
}

// app/Http/Controllers/Api/LetterGenerationController.php - Updated to use AIService
// Just update the constructor and the generateLetter method:

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LetterTemplate;
use App\Models\LetterRequest;
use App\Services\AIService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class LetterGenerationController extends Controller
{
    protected AIService $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    // ... other methods remain the same ...

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
                    $this->aiService->processLetterRequest($letterRequest);
                })->afterResponse();

                return response()->json([
                    'success' => true,
                    'data' => [
                        'request_id' => $letterRequest->request_id,
                        'status' => $letterRequest->status,
                        'message' => 'Letter generation started. Use the request_id to check status.',
                        'check_status_url' => url('/api/letter-requests/status/' . $letterRequest->request_id),
                        'model_used' => \App\Models\AppSetting::getValue('active_ai_model', 'chatgpt'),
                    ],
                    'message' => 'Letter generation request created successfully'
                ], 202);
            } else {
                // Process synchronously (for immediate results)
                $this->aiService->processLetterRequest($letterRequest);

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
                            'model_used' => \App\Models\AppSetting::getValue('active_ai_model', 'chatgpt'),
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

    // ... rest of the methods remain the same ...
}