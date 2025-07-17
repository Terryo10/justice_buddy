<?php

namespace App\Services;

use App\Models\LetterTemplate;
use App\Models\LetterRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatGPTService
{
    protected string $apiKey;
    protected string $baseUrl;
    protected string $model;
    protected int $timeout;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
        $this->baseUrl = config('services.openai.base_url', 'https://api.openai.com/v1');
        $this->model = config('services.openai.model', 'gpt-4');
        $this->timeout = config('services.openai.timeout', 60);
    }

    /**
     * Generate letter using ChatGPT
     */
    public function generateLetter(LetterTemplate $template, array $clientMatters): array
    {
        try {
            $prompt = $this->buildPrompt($template, $clientMatters);
            
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post("{$this->baseUrl}/chat/completions", [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are a professional legal document drafter specializing in South African law. You generate formal, legally appropriate letters based on templates and client information.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 2048,
                    'top_p' => 0.95,
                    'frequency_penalty' => 0,
                    'presence_penalty' => 0,
                ]);

            if (!$response->successful()) {
                $error = $response->json('error.message') ?? 'ChatGPT API request failed';
                throw new \Exception("ChatGPT API Error: {$error}");
            }

            $responseData = $response->json();
            
            if (!isset($responseData['choices'][0]['message']['content'])) {
                throw new \Exception('Invalid response format from ChatGPT API');
            }

            $generatedText = $responseData['choices'][0]['message']['content'];

            return [
                'success' => true,
                'content' => $generatedText,
                'usage' => $responseData['usage'] ?? null,
                'model' => $this->model
            ];

        } catch (\Exception $e) {
            Log::error('ChatGPT API Error: ' . $e->getMessage(), [
                'template_id' => $template->id,
                'client_matters' => $clientMatters,
                'model' => $this->model
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'model' => $this->model
            ];
        }
    }

    /**
     * Build the prompt for ChatGPT
     */
    protected function buildPrompt(LetterTemplate $template, array $clientMatters): string
    {
        $systemInstructions = "You are a professional legal document drafter specializing in South African law. ";
        $systemInstructions .= "You must generate formal, legally appropriate letters based on the provided template and client information. ";
        $systemInstructions .= "Ensure the language is professional, clear, and follows proper legal formatting. ";
        $systemInstructions .= "All dates should be formatted as per South African standards (DD/MM/YYYY). ";
        $systemInstructions .= "Include proper salutations, body paragraphs, and professional closings. ";

        if ($template->ai_instructions) {
            $systemInstructions .= "\n\nSpecific instructions for this template: " . $template->ai_instructions;
        }

        $prompt = $systemInstructions . "\n\n";
        $prompt .= "TEMPLATE TO USE:\n";
        $prompt .= "Title: " . $template->name . "\n";
        $prompt .= "Category: " . ucfirst($template->category) . "\n";
        $prompt .= "Template Content:\n" . $template->template_content . "\n\n";

        $prompt .= "CLIENT INFORMATION PROVIDED:\n";
        foreach ($clientMatters as $key => $value) {
            if (is_array($value)) {
                $prompt .= "- " . ucfirst(str_replace('_', ' ', $key)) . ": " . implode(', ', $value) . "\n";
            } else {
                $prompt .= "- " . ucfirst(str_replace('_', ' ', $key)) . ": " . $value . "\n";
            }
        }

        $prompt .= "\nINSTRUCTIONS:\n";
        $prompt .= "1. Use the template as a guide but adapt it based on the specific client information provided\n";
        $prompt .= "2. Replace any placeholder text with appropriate content based on the client matters\n";
        $prompt .= "3. Ensure all legal terminology is accurate for South African law\n";
        $prompt .= "4. Make the letter professional and formal in tone\n";
        $prompt .= "5. Include today's date: " . now()->format('d/m/Y') . "\n";
        $prompt .= "6. Ensure proper paragraph structure and formatting\n";
        $prompt .= "7. Do not include any explanatory text - provide only the final letter content\n";
        $prompt .= "8. Begin with the sender's details, date, recipient details, subject line, and then the letter body\n\n";

        $prompt .= "Generate the complete professional legal letter now:";

        return $prompt;
    }

    /**
     * Process letter request asynchronously
     */
    public function processLetterRequest(LetterRequest $letterRequest): void
    {
        try {
            $letterRequest->markAsProcessing();

            $template = $letterRequest->letterTemplate;
            $result = $this->generateLetter($template, $letterRequest->client_matters);

            if ($result['success']) {
                // Generate PDF if needed
                $documentPath = null;
                if (config('services.letter_generator.generate_pdf', true)) {
                    $documentPath = $this->generatePDF($result['content'], $letterRequest);
                }

                $letterRequest->markAsCompleted($result['content'], $documentPath);
            } else {
                $letterRequest->markAsFailed($result['error']);
            }

        } catch (\Exception $e) {
            Log::error('Error processing letter request with ChatGPT: ' . $e->getMessage(), [
                'request_id' => $letterRequest->request_id,
                'model' => $this->model
            ]);
            
            $letterRequest->markAsFailed('An error occurred while processing your request: ' . $e->getMessage());
        }
    }

    /**
     * Generate PDF from letter content
     */
    protected function generatePDF(string $content, LetterRequest $letterRequest): ?string
    {
        try {
            // For now, we'll store as text file. You can integrate with a PDF library later
            $filename = 'letters/' . $letterRequest->request_id . '_chatgpt_' . now()->format('YmdHis') . '.txt';
            $fullPath = storage_path('app/public/' . $filename);
            
            // Ensure directory exists
            $directory = dirname($fullPath);
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            file_put_contents($fullPath, $content);

            return $filename;

        } catch (\Exception $e) {
            Log::error('Error generating PDF with ChatGPT: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate chat response using ChatGPT
     */
    public function generateChatResponse(array $conversation): string
    {
        try {
            // Convert conversation to ChatGPT format
            $messages = [];
            
            foreach ($conversation as $message) {
                $messages[] = [
                    'role' => $message['role'] === 'assistant' ? 'assistant' : $message['role'],
                    'content' => $message['content']
                ];
            }

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post("{$this->baseUrl}/chat/completions", [
                    'model' => $this->model,
                    'messages' => $messages,
                    'temperature' => 0.7,
                    'max_tokens' => 1024,
                    'top_p' => 0.95,
                    'frequency_penalty' => 0,
                    'presence_penalty' => 0,
                ]);

            if (!$response->successful()) {
                $error = $response->json('error.message') ?? 'ChatGPT API request failed';
                throw new \Exception("ChatGPT API Error: {$error}");
            }

            $responseData = $response->json();
            
            if (!isset($responseData['choices'][0]['message']['content'])) {
                throw new \Exception('Invalid response format from ChatGPT API');
            }

            return $responseData['choices'][0]['message']['content'];

        } catch (\Exception $e) {
            Log::error('ChatGPT Chat API Error: ' . $e->getMessage(), [
                'conversation' => $conversation,
                'model' => $this->model
            ]);

            throw $e;
        }
    }
}