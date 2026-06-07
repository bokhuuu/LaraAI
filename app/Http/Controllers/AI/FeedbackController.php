<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Http\Requests\AI\StoreFeedbackRequest;
use App\Models\AiFeedback;
use Illuminate\Http\JsonResponse;

/**
 * Stores user feedback votes on AI responses.
 *
 * Endpoint: POST /api/ai/feedback
 * Accepts vote (1 or -1), the AI response text and an optional comment.
 * Session ID captured automatically from the request session.
 */
class FeedbackController extends Controller
{
    /**
     * Store a feedback vote for an AI response.
     * Returns the created feedback record as confirmation.
     */
    public function store(StoreFeedbackRequest $request): JsonResponse
    {
        $feedback = AiFeedback::create([
            'session_id' => $request->header('X-Session-ID', $request->ip()),
            'user_id' => null,
            'response_text' => $request->input('response_text'),
            'vote' => $request->input('vote'),
            'comment' => $request->input('comment'),
        ]);

        return response()->json([
            'message' => 'Feedback recorded.',
            'id' => $feedback->id,
        ], 201);
    }
}
