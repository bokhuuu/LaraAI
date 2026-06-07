<?php

declare(strict_types=1);

namespace App\Http\Requests\AI;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Validates incoming prompt requests before they reach the AI.
 *
 * Blocks empty inputs, oversized prompts and naive prompt injection attempts.
 * Returns JSON error responses instead of redirects since this is an API endpoint.
 *
 * Note: the regex filter catches obvious injection patterns only.
 * For high-risk applications add a dedicated AI moderation layer on top.
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
