<?php

namespace App\Services;

use App\Models\ChatSession;
use App\Models\Question;
use App\Models\SessionAnswer;
use App\Models\Service;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChatbotService
{
    private $openaiService;

    public function __construct(OpenAIService $openaiService)
    {
        $this->openaiService = $openaiService;
    }

    public function createSession()
    {
        $sessionId = Str::uuid()->toString();

        $session = ChatSession::create([
            'session_id' => $sessionId,
            'last_activity' => now()
        ]);

        return $session;
    }

    public function getNextQuestion($sessionId)
    {
        $session = ChatSession::where('session_id', $sessionId)->first();

        if (!$session) {
            return null;
        }

        $answeredQuestionKeys = SessionAnswer::where('session_id', $sessionId)
            ->pluck('question_key')
            ->toArray();

        $nextQuestion = Question::ordered()
            ->whereNotIn('question_key', $answeredQuestionKeys)
            ->first();

        if (!$nextQuestion) {
            return $this->generateServiceSummary($session);
        }

        return [
            'question_id' => $nextQuestion->id,
            'question_key' => $nextQuestion->question_key,
            'question' => $nextQuestion->question_text,
            'is_completed' => false
        ];
    }

    public function submitAnswer($sessionId, $questionKey, $answer)
    {
        try {
            $session = ChatSession::where('session_id', $sessionId)->first();

            if (!$session) {
                return ['error' => 'Session not found'];
            }

            $question = Question::where('question_key', $questionKey)->first();

            if (!$question) {
                return ['error' => 'Question not found'];
            }

            $processedAnswer = $this->processAnswer($questionKey, $answer);

            SessionAnswer::updateOrCreate(
                ['session_id' => $sessionId, 'question_key' => $questionKey],
                ['answer' => $processedAnswer]
            );

            $this->updateSessionData($session, $questionKey, $processedAnswer);

            $session->addToHistory($answer, 'user');

            $botResponse = $this->generateBotResponse($session, $question, $processedAnswer);
            $session->addToHistory($botResponse, 'bot');

            return [
                'success' => true,
                'bot_response' => $botResponse,
                'next_question' => $this->getNextQuestion($sessionId)
            ];
        } catch (\Exception $e) {
            Log::error('Error in submitAnswer: ' . $e->getMessage() . ' - Stack: ' . $e->getTraceAsString());

            try {
                SessionAnswer::updateOrCreate(
                    ['session_id' => $sessionId, 'question_key' => $questionKey],
                    ['answer' => trim($answer)]
                );

                $session = ChatSession::where('session_id', $sessionId)->first();
                if ($session) {
                    $session->addToHistory($answer, 'user');

                    $simpleResponse = "Thank you for your answer. Let me process that for you.";
                    $session->addToHistory($simpleResponse, 'bot');

                    return [
                        'success' => true,
                        'bot_response' => $simpleResponse,
                        'next_question' => $this->getNextQuestion($sessionId)
                    ];
                }
            } catch (\Exception $fallbackError) {
                Log::error('Fallback error in submitAnswer: ' . $fallbackError->getMessage());
            }

            return [
                'error' => 'I apologize for the technical difficulty. Let me try to continue with your request.',
                'success' => false
            ];
        }
    }

    private function processAnswer($questionKey, $answer)
    {
        try {
            switch ($questionKey) {
                case 'service_type':
                    $result = $this->openaiService->extractServiceType($answer);
                    if ($result === 'unknown') {
                        $input = strtolower(trim($answer));
                        if (stripos($input, 'repair') !== false || stripos($input, 'fix') !== false || stripos($input, 'appliance') !== false) {
                            return 'repairment';
                        }
                        if (stripos($input, 'supply') !== false || stripos($input, 'delivery') !== false || stripos($input, 'office') !== false || stripos($input, 'product') !== false) {
                            return 'product_supply';
                        }
                        if (stripos($input, 'driver') !== false || stripos($input, 'car') !== false || stripos($input, 'chauffeur') !== false || stripos($input, 'transport') !== false) {
                            return 'car_driver';
                        }
                        return $answer;
                    }
                    return $result;
                case 'country':
                    return $this->openaiService->normalizeCountryName($answer);
                case 'name':
                case 'region':
                default:
                    return trim($answer);
            }
        } catch (\Exception $e) {
            Log::error('Error processing answer: ' . $e->getMessage());
        }
    }

    private function updateSessionData($session, $questionKey, $answer)
    {
        $updateData = [];

        switch ($questionKey) {
            case 'name':
                $updateData['user_name'] = $answer;
                break;
            case 'country':
                $updateData['user_country'] = $answer;
                break;
            case 'region':
                $updateData['user_region'] = $answer;
                break;
            case 'service_type':
                $updateData['requested_service'] = $answer;
                break;
        }

        if (!empty($updateData)) {
            $session->update($updateData);
        }
    }


    private function generateBotResponse($session, $question, $answer)
    {
        $responses = [
            'name' => "Nice to meet you, {$answer}! ",
            'country' => "Great, {$answer} is a wonderful country. " . $this->getCountryInfo($answer),
            'region' => "Perfect, I've noted that you're in {$answer}. Let me show you what services are available in your area:\n\n" . $this->getRegionServices($session, $answer),
            'service_type' => $this->getServiceTypeResponse($answer)
        ];

        $baseResponse = $responses[$question->question_key] ?? "Thank you for your answer. ";

        $answeredCount = SessionAnswer::where('session_id', $session->session_id)->count();
        $totalQuestions = Question::count();

        if ($answeredCount >= $totalQuestions - 1) {
            return $baseResponse . "Let me search our database for the best services for you!";
        }

        if ($question->question_key === 'region') {
            return $baseResponse . "\nWhat type of service do you need?\n\nChoose from:\n•" . $this->getRegionServices($session, $answer) . " services";
        }

        return $baseResponse . "Now let me ask you the next question.";
    }

    private function getServiceTypeResponse($answer)
    {
        $serviceTypeMap = [
            'repairment' => 'repair services',
            'product_supply' => 'product supply services',
            'car_driver' => 'car driver/chauffeur services'
        ];

        if (isset($serviceTypeMap[$answer])) {
            return "Perfect! You're looking for {$serviceTypeMap[$answer]}. ";
        }

        return "I understand you're interested in '{$answer}'. ";
    }

    private function getRegionServices($session, $region)
    {
        $query = Service::active();

        if ($session->user_country) {
            $query->byCountry($session->user_country);
        }

        $query->byRegion($region);
        $regionServices = $query->get();

        if ($regionServices->isEmpty()) {
            $query = Service::active();
            if ($session->user_country) {
                $countryServices = $query->byCountry($session->user_country)->get();

                if ($countryServices->isNotEmpty()) {
                    $servicesList = $countryServices->groupBy('type')->map(function ($services, $type) {
                        $count = $services->count();
                        $typeLabel = ucwords(str_replace('_', ' ', $type));
                        return "• {$typeLabel}: {$count} service" . ($count > 1 ? 's' : '') . " available";
                    })->values()->join("\n");

                    return "Services available in {$session->user_country}:\n{$servicesList}";
                }
            }

            $allServices = Service::active()->get()->groupBy('type');
            $servicesList = $allServices->map(function ($services, $type) {
                $count = $services->count();
                $typeLabel = ucwords(str_replace('_', ' ', $type));
                return "• {$typeLabel}: {$count} service" . ($count > 1 ? 's' : '') . " available";
            })->values()->join("\n");

            return "Available service types:\n{$servicesList}";
        }

        $servicesList = $regionServices->groupBy('type')->map(function ($services, $type) {
            $typeLabel = ucwords(str_replace('_', ' ', $type));
            $serviceNames = $services->pluck('name')->take(3)->join(', ');
            $count = $services->count();

            if ($count > 3) {
                $serviceNames .= " and " . ($count - 3) . " more";
            }

            return "• {$typeLabel}: {$serviceNames}";
        })->values()->join("\n");

        return "Services available in {$region}:\n{$servicesList}";
    }

    private function generateServiceSummary($session)
    {
        $matchedServices = $this->findMatchingServices($session);
        $allServices = Service::active()->get();
        if ($matchedServices->isEmpty()) {
            $serviceList = $allServices->map(function ($service) {
                return "• {$service->name} ({$service->type}) by {$service->provider_name} - {$service->country}, {$service->region}" .
                    ($service->price ? " - $" . number_format($service->price, 2) : "") .
                    ($service->provider_contact ? " (Contact: {$service->provider_contact})" : "");
            })->join("\n");

            $message = "I couldn't find services that exactly match your requirements, but here are all available services in our database:\n\n" . $serviceList;
            $servicesArray = $allServices->toArray();
        } else {
            $serviceList = $matchedServices->map(function ($service) {
                return "• {$service->name} by {$service->provider_name}" .
                    ($service->price ? " - $" . number_format($service->price, 2) : "") .
                    ($service->provider_contact ? " (Contact: {$service->provider_contact})" : "");
            })->join("\n");

            $message = "Great! I found the following services for you:\n\n" . $serviceList;
            $servicesArray = $matchedServices->toArray();
        }

        $session->update(['is_completed' => true]);
        $session->addToHistory($message, 'bot');

        return [
            'message' => $message,
            'services' => $servicesArray,
            'is_completed' => true
        ];
    }

    private function findMatchingServices($session)
    {
        $query = Service::active();
        $hasFilters = false;

        if ($session->user_country && $session->user_country !== 'unknown') {
            $query->byCountry($session->user_country);
            $hasFilters = true;
        }

        if ($session->user_region && $session->user_region !== 'unknown') {
            $query->byRegion($session->user_region);
            $hasFilters = true;
        }

        if ($session->requested_service && $session->requested_service !== 'unknown') {
            $query->byType($session->requested_service);
            $hasFilters = true;
        }

        $matchedServices = $query->get();

        if ($matchedServices->isEmpty() && $hasFilters) {
            $query = Service::active();

            if ($session->user_country && $session->user_country !== 'unknown') {
                $matchedServices = $query->byCountry($session->user_country)->get();
            }

            if ($matchedServices->isEmpty() && $session->requested_service && $session->requested_service !== 'unknown') {
                $matchedServices = Service::active()->byType($session->requested_service)->get();
            }
        }

        return $matchedServices;
    }

    public function getSessionSummary($sessionId)
    {
        $session = ChatSession::where('session_id', $sessionId)->first();

        if (!$session) {
            return null;
        }

        $answers = $session->answers()->with('question')->get();
        $services = $this->findMatchingServices($session);

        return [
            'session_id' => $sessionId,
            'user_info' => [
                'name' => $session->user_name,
                'country' => $session->user_country,
                'region' => $session->user_region,
                'requested_service' => $session->requested_service
            ],
            'answers' => $answers->map(function ($answer) {
                return [
                    'question' => $answer->question->question_text ?? $answer->question_key,
                    'answer' => $answer->answer
                ];
            }),
            'matched_services' => $services,
            'conversation_history' => $session->conversation_history,
            'is_completed' => $session->is_completed
        ];
    }

    public function restartSession($sessionId)
    {
        $session = ChatSession::where('session_id', $sessionId)->first();

        if (!$session) {
            return null;
        }

        SessionAnswer::where('session_id', $sessionId)->delete();

        $session->update([
            'user_name' => null,
            'user_country' => null,
            'user_region' => null,
            'requested_service' => null,
            'conversation_history' => [],
            'is_completed' => false,
            'last_activity' => now()
        ]);

        return $session;
    }

    private function getCountryInfo($country)
    {
        $countryServices = Service::active()->byCountry($country)->get();

        if ($countryServices->isNotEmpty()) {
            $regions = $countryServices->pluck('region')->unique()->values();
            $serviceTypes = $countryServices->groupBy('type')->keys()->map(function ($type) {
                return ucwords(str_replace('_', ' ', $type));
            })->values();

            $regionsList = $regions->take(5)->join(', ');
            if ($regions->count() > 5) {
                $regionsList .= " and " . ($regions->count() - 5) . " more regions";
            }

            return "We have services in these regions: {$regionsList}. Available service types: " . $serviceTypes->join(', ') . ".";
        }

        $availableCountries = Service::active()->select('country')->distinct()->pluck('country')->take(5);
        return "We currently serve these countries: " . $availableCountries->join(', ') . ". We'll still try to help you find suitable services.";
    }
}
