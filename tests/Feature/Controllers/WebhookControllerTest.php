<?php

use App\Jobs\ProcessWebhookJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('local');
    Queue::fake();
    config(['ai.webhook.secret' => 'test-secret']);
});

function makeSignature(string $body, string $secret): string
{
    return 'sha256='.hash_hmac('sha256', $body, $secret);
}

it('accepts a valid webhook request and queues the job', function () {
    $file = UploadedFile::fake()->create('catalog.pdf', 100, 'application/pdf');

    $response = $this->call(
        'POST',
        '/api/webhook',
        [],
        [],
        ['file' => $file],
        [
            'HTTP_X-Webhook-Signature' => makeSignature('', 'test-secret'),
            'CONTENT_TYPE' => 'multipart/form-data',
        ]
    );

    $response->assertStatus(200)
        ->assertJson(['message' => 'Webhook received. Processing queued.']);

    Queue::assertPushed(ProcessWebhookJob::class);
});

it('rejects request with invalid signature', function () {
    $file = UploadedFile::fake()->create('catalog.pdf', 100, 'application/pdf');

    $response = $this->call(
        'POST',
        '/api/webhook',
        [],
        [],
        ['file' => $file],
        [
            'HTTP_X-Webhook-Signature' => 'sha256=invalidsignature',
            'CONTENT_TYPE' => 'multipart/form-data',
        ]
    );

    $response->assertStatus(401)
        ->assertJson(['message' => 'Invalid signature.']);

    Queue::assertNotPushed(ProcessWebhookJob::class);
});

it('rejects request with missing signature', function () {
    $file = UploadedFile::fake()->create('catalog.pdf', 100, 'application/pdf');

    $response = $this->call(
        'POST',
        '/api/webhook',
        [],
        [],
        ['file' => $file],
        ['CONTENT_TYPE' => 'multipart/form-data']
    );

    $response->assertStatus(401);

    Queue::assertNotPushed(ProcessWebhookJob::class);
});

it('rejects request with no file', function () {
    $response = $this->postJson('/api/webhook', [], [
        'X-Webhook-Signature' => makeSignature('{}', 'test-secret'),
    ]);

    $response->assertStatus(401);
});
