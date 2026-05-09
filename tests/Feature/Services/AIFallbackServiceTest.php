<?php

use App\AI\Services\AIFallbackService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Testing\TextResponseFake;
use Prism\Prism\ValueObjects\Usage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new AIFallbackService();
});

test('generateText returns response from available provider', function () {
    Prism::fake([
        TextResponseFake::make()
            ->withText('Fallback response text')
            ->withUsage(new Usage(10, 20))
    ]);

    $result = $this->service->generateText('Test prompt');

    expect($result)->toBe('Fallback response text');
});
