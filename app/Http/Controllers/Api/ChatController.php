<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatRule;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    protected GeminiService $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
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
            $modelName = $request->model_name ?? 'gemini-pro';

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

            // Generate AI response
            $aiResponse = $this->geminiService->generateChatResponse($conversation);

            return response()->json([
                'success' => true,
                'data' => [
                    'message' => $aiResponse,
                    'conversation_id' => uniqid(), // Simple conversation ID for client tracking
                    'timestamp' => now()->toISOString(),
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
