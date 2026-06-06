<?php

use App\AI\Services\MultiModalService;
use App\AI\Services\PdfExtractionService;

beforeEach(function () {
    $this->multiModal = Mockery::mock(MultiModalService::class);
    $this->service = new PdfExtractionService($this->multiModal);
});

test('extract returns structured data from single page pdf', function () {
    $this->multiModal
        ->shouldReceive('analyze')
        ->once()
        ->andReturn('[{"name":"BMW X5","price":35000,"category":"SUV"}]');

    $pdfPath = createFakePdf(pages: 1);

    $result = $this->service->extract($pdfPath, ['name', 'price', 'category']);

    expect($result)->toBeArray();
    expect($result)->toHaveCount(1);
    expect($result[0]['name'])->toBe('BMW X5');
    expect($result[0]['price'])->toBe(35000);

    unlink($pdfPath);
});

test('extract merges results from multiple pages', function () {
    $this->multiModal
        ->shouldReceive('analyze')
        ->times(3)
        ->andReturn(
            '[{"name":"BMW X5","price":35000}]',
            '[{"name":"Toyota Camry","price":22000}]',
            '[{"name":"Ford F150","price":45000}]',
        );

    $pdfPath = createFakePdf(pages: 3);

    $result = $this->service->extract($pdfPath, ['name', 'price']);

    expect($result)->toHaveCount(3);
    expect($result[0]['name'])->toBe('BMW X5');
    expect($result[1]['name'])->toBe('Toyota Camry');
    expect($result[2]['name'])->toBe('Ford F150');

    unlink($pdfPath);
});

test('extract skips empty pages gracefully', function () {
    $this->multiModal
        ->shouldReceive('analyze')
        ->times(2)
        ->andReturn(
            '[{"name":"BMW X5","price":35000}]',
            '[]',
        );

    $pdfPath = createFakePdf(pages: 2);

    $result = $this->service->extract($pdfPath, ['name', 'price']);

    expect($result)->toHaveCount(1);
    expect($result[0]['name'])->toBe('BMW X5');

    unlink($pdfPath);
});

test('extract throws exception for missing pdf file', function () {
    $this->service->extract('/nonexistent/path/file.pdf', ['name', 'price']);
})->throws(RuntimeException::class, 'PDF file not found');
