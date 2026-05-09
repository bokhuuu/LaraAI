<?php

use App\AI\Services\ToolService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Testing\TextResponseFake;
use Prism\Prism\Tool;
use Prism\Prism\ValueObjects\Usage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new ToolService();
});

test('registerTool adds tool and returns self for chaining', function () {
    $tool = (new Tool())
        ->as('test_tool')
        ->for('A test tool')
        ->withStringParameter('input', 'Test input')
        ->using(fn(string $input): string => 'result');

    $result = $this->service->registerTool($tool);

    expect($result)->toBeInstanceOf(ToolService::class);
});

test('run returns AI text response', function () {
    Prism::fake([
        TextResponseFake::make()
            ->withText('Tool response result')
            ->withUsage(new Usage(10, 20))
    ]);

    $result = $this->service->run('Test prompt');

    expect($result)->toBe('Tool response result');
});
