<?php

namespace App\Services;

use App\Models\LetterTemplate;
use App\Models\LetterRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected string $apiKey;
    protected string $baseUrl;
    protected string $model;
    protected int $timeout;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->baseUrl = config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta');
        $this->model = config('services.gemini.model', 'gemini-pro');
        $this->timeout = config('services.gemini.timeout', 60);
    }

    /**
     * Generate letter using Gemini AI
     */
    public function generateLetter(LetterTemplate $template, array $clientMatters): array
    {
        try {
            $prompt = $this->buildPrompt($template, $clientMatters);
            
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/models/{$this->model}:generateContent?key={$this->apiKey}", [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'topK' => 40,
                        'topP' => 0.95,
                        'maxOutputTokens' => 2048,
                    ],
                    'safetySettings' => [
                        [
                            'category' => 'HARM_CATEGORY_HARASSMENT',
                            'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                        ],
                        [
                            'category' => 'HARM_CATEGORY_HATE_SPEECH',
                            'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                        ],
                        [
                            'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                            'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                        ],
                        [
                            'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                            'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                        ]
                    ]
                ]);

            if (!$response->successful()) {
                throw new \Exception('Gemini API request failed: ' . $response->body());
            }

            $responseData = $response->json();
            
            if (!isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
                throw new \Exception('Invalid response format from Gemini API');
            }

            $generatedText = $responseData['candidates'][0]['content']['parts'][0]['text'];

            return [
                'success' => true,
                'content' => $generatedText,
                'usage' => $responseData['usageMetadata'] ?? null
            ];

        } catch (\Exception $e) {
            Log::error('Gemini API Error: ' . $e->getMessage(), [
                'template_id' => $template->id,
                'client_matters' => $clientMatters
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Build the prompt for Gemini AI
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
            Log::error('Error processing letter request: ' . $e->getMessage(), [
                'request_id' => $letterRequest->request_id
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
            $filename = 'letters/' . $letterRequest->request_id . '_' . now()->format('YmdHis') . '.txt';
            $fullPath = storage_path('app/public/' . $filename);
            
            // Ensure directory exists
            $directory = dirname($fullPath);
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            file_put_contents($fullPath, $content);

            return $filename;

        } catch (\Exception $e) {
            Log::error('Error generating PDF: ' . $e->getMessage());
            return null;
        }
    }
}