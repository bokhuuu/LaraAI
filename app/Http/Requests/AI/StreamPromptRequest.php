<?php

declare(strict_types=1);

namespace App\Http\Requests\AI;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Validates incoming prompt requests.
 * Prevents oversized inputs, empty prompts and basic injection attempts.
 */
class StreamPromptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message' => [
                'nullable',
                'string',
                'max:2000',
                // Basic prompt injection filter - covers naive attacks
                // Production recommendation: add AI moderation layer (OpenAI Moderation API)
                // or dedicated content filtering service for high-risk applications
                'not_regex:/\bignore\b.*\binstructions\b|\bsystem\b.*\bprompt\b/i',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'message.max' => 'Prompt cannot exceed 2000 characters.',
            'message.not_regex' => 'Invalid prompt content.',
        ];
    }

    /**
     * Return JSON error response instead of redirect for API endpoints.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
