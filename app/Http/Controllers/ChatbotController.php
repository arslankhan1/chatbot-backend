<?php

namespace App\Http\Controllers;

use App\Services\ChatbotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChatbotController extends Controller
{
    private $chatbotService;

    public function __construct(ChatbotService $chatbotService)
    {
        $this->chatbotService = $chatbotService;
    }


    public function startSession()
    {
        try {
            $session = $this->chatbotService->createSession();

            return response()->json([
                'success' => true,
                'session_id' => $session->session_id,
                'message' => 'Session started successfully. Let me help you find the right service!'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create session',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function getNextQuestion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $sessionId = $request->get('session_id');
            $result = $this->chatbotService->getNextQuestion($sessionId);

            if ($result === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get next question',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function submitAnswer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|string',
            'question_key' => 'required|string',
            'answer' => 'required|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $result = $this->chatbotService->submitAnswer(
                $request->session_id,
                $request->question_key,
                $request->answer
            );

            if (isset($result['error'])) {
                return response()->json([
                    'success' => false,
                    'message' => $result['error']
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit answer',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function getSessionSummary(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $sessionId = $request->get('session_id');
            $summary = $this->chatbotService->getSessionSummary($sessionId);

            if ($summary === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $summary
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get session summary',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function restartSession(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $sessionId = $request->session_id;
            $session = $this->chatbotService->restartSession($sessionId);

            if ($session === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Session restarted successfully',
                'session_id' => $session->session_id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to restart session',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
