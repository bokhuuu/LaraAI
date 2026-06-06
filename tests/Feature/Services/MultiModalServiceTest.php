<?php

use App\AI\Services\MultiModalService;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Testing\TextResponseFake;

beforeEach(function () {
    $this->service = new MultiModalService;
});

test('analyze returns text response from vision model', function () {
    Prism::fake([
        TextResponseFake::make()->withText('A red BMW X5 parked in a showroom.'),
    ]);

    $imagePath = tempnam(sys_get_temp_dir(), 'test_image').'.jpg';
    file_put_contents($imagePath, 'fake-image-content');

    $result = $this->service->analyze($imagePath, 'Describe this image.');

    expect($result)->toBe('A red BMW X5 parked in a showroom.');

    unlink($imagePath);
});

test('analyze throws exception when image file does not exist', function () {
    $this->service->analyze('/nonexistent/path/image.jpg', 'Describe this.');
})->throws(RuntimeException::class, 'Image file not found');
