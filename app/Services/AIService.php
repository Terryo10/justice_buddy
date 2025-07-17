<?php

namespace App\Services;

use App\Models\LetterTemplate;
use App\Models\LetterRequest;
use App\Models\AppSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    protected string $activeModel;
    protected ChatGPTService $chatGptService;
    protected GeminiService $geminiService;

    public function __construct(ChatGPTService $chatGptService, GeminiService $geminiService)
    {
        $this->chatGptService = $chatGptService;
        $this->geminiService = $geminiService;
        $this->activeModel = $this->getActiveModel();
    }

    /**
     * Get the currently active AI model
     */
    protected function getActiveModel(): string
    {
        return AppSetting::getValue('active_ai_model', 'chatgpt');
    }

    /**
     * Generate letter using the active AI model
     */
    public function generateLetter(LetterTemplate $template, array $clientMatters): array
    {
        $model = $this->getActiveModel();
        
        Log::info("Generating letter using model: {$model}", [
            'template_id' => $template->id,
            'model' => $model
        ]);

        return match ($model) {
            'chatgpt' => $this->chatGptService->generateLetter($template, $clientMatters),
            'gemini' => $this->geminiService->generateLetter($template, $clientMatters),
            default => throw new \InvalidArgumentException("Unsupported AI model: {$model}")
        };
    }

    /**
     * Generate chat response using the active AI model
     */
    public function generateChatResponse(array $conversation): string
    {
        $model = $this->getActiveModel();
        
        Log::info("Generating chat response using model: {$model}");

        return match ($model) {
            'chatgpt' => $this->chatGptService->generateChatResponse($conversation),
            'gemini' => $this->geminiService->generateChatResponse($conversation),
            default => throw new \InvalidArgumentException("Unsupported AI model: {$model}")
        };
    }

    /**
     * Process letter request using the active AI model
     */
    public function processLetterRequest(LetterRequest $letterRequest): void
    {
        $model = $this->getActiveModel();
        
        Log::info("Processing letter request using model: {$model}", [
            'request_id' => $letterRequest->request_id,
            'model' => $model
        ]);

        match ($model) {
            'chatgpt' => $this->chatGptService->processLetterRequest($letterRequest),
            'gemini' => $this->geminiService->processLetterRequest($letterRequest),
            default => throw new \InvalidArgumentException("Unsupported AI model: {$model}")
        };
    }

    /**
     * Get available AI models
     */
    public static function getAvailableModels(): array
    {
        return [
            'chatgpt' => 'ChatGPT (OpenAI)',
            'gemini' => 'Gemini (Google)',
        ];
    }

    /**
     * Switch the active AI model
     */
    public static function switchModel(string $model): bool
    {
        if (!array_key_exists($model, self::getAvailableModels())) {
            return false;
        }

        AppSetting::setValue('active_ai_model', $model);
        
        Log::info("AI model switched to: {$model}");
        
        return true;
    }

    /**
     * Test if a model is working
     */
    public function testModel(string $model): array
    {
        try {
            $testConversation = [
                [
                    'role' => 'user',
                    'content' => 'Hello, this is a test. Please respond with "Test successful".'
                ]
            ];

            $response = match ($model) {
                'chatgpt' => $this->chatGptService->generateChatResponse($testConversation),
                'gemini' => $this->geminiService->generateChatResponse($testConversation),
                default => throw new \InvalidArgumentException("Unsupported AI model: {$model}")
            };

            return [
                'success' => true,
                'response' => $response,
                'model' => $model
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'model' => $model
            ];
        }
    }
}