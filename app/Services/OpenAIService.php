<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    private $apiKey;
    private $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
        $this->baseUrl = 'https://api.openai.com/v1';
    }

    public function generateResponse($systemPrompt, $userMessage, $conversationHistory = [])
    {
        try {
            $messages = [
                ['role' => 'system', 'content' => $systemPrompt]
            ];

            foreach ($conversationHistory as $history) {
                $role = $history['type'] === 'bot' ? 'assistant' : 'user';
                $messages[] = ['role' => $role, 'content' => $history['message']];
            }

            $messages[] = ['role' => 'user', 'content' => $userMessage];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/chat/completions', [
                'model' => 'gpt-3.5-turbo',
                'messages' => $messages,
                'max_tokens' => 500,
                'temperature' => 0.7,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['choices'][0]['message']['content'] ?? 'I apologize, but I couldn\'t generate a response.';
            } else {
                Log::error('OpenAI API Error: ' . $response->body());
                return 'I apologize, but I\'m having trouble processing your request right now.';
            }
        } catch (\Exception $e) {
            Log::error('OpenAI Service Exception: ' . $e->getMessage());
            return 'I apologize, but I\'m having trouble processing your request right now.';
        }
    }

    public function extractServiceType($userInput)
    {
        $input = strtolower(trim($userInput));

        if (preg_match('/\b(repair|fix|broken|maintenance|repairment|appliance|computer|phone|mobile|auto|plumbing)\b/', $input)) {
            return 'repairment';
        }

        if (preg_match('/\b(supply|product|delivery|supplies|goods|items|materials|office|grocery|medical|electronics|food)\b/', $input)) {
            return 'product_supply';
        }

        if (preg_match('/\b(driver|chauffeur|car|taxi|transport|drive|ride|executive|airport|wedding|tour|personal|transfer)\b/', $input)) {
            return 'car_driver';
        }

        return 'unknown';
    }


    private function tryOpenAIServiceType($userInput)
    {
        $systemPrompt = "You are a service type classifier. Based on the user input, classify the requested service into one of these categories: 'repairment', 'product_supply', or 'car_driver'. Return only the category name, nothing else. If unclear, return 'unknown'.";

        try {
            $response = Http::timeout(10)->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/chat/completions', [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userInput]
                ],
                'max_tokens' => 50,
                'temperature' => 0.3,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $result = trim(strtolower($data['choices'][0]['message']['content'] ?? 'unknown'));
                return in_array($result, ['repairment', 'product_supply', 'car_driver']) ? $result : 'unknown';
            }
        } catch (\Exception $e) {
            Log::error('OpenAI service type extraction error: ' . $e->getMessage());
        }

        return 'unknown';
    }

    public function normalizeCountryName($userInput)
    {
        $input = strtolower(trim($userInput));

        $countryMappings = [
            'usa' => 'United States',
            'us' => 'United States',
            'america' => 'United States',
            'united states of america' => 'United States',
            'uk' => 'United Kingdom',
            'britain' => 'United Kingdom',
            'great britain' => 'United Kingdom',
            'england' => 'United Kingdom',
            'can' => 'Canada',
            'aus' => 'Australia',
            'oz' => 'Australia',
        ];

        if (isset($countryMappings[$input])) {
            return $countryMappings[$input];
        }

        return ucwords($input);
    }
}
