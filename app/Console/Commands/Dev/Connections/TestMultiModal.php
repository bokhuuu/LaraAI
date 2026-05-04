<?php

namespace App\Console\Commands\Dev\Connections;

use Illuminate\Console\Command;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;
use Prism\Prism\ValueObjects\Messages\UserMessage;
use Prism\Prism\ValueObjects\Media\Image;

class TestMultiModal extends Command
{
    protected $signature = 'ai:test-multimodal';
    protected $description = 'Test image analysis with AI';

    public function handle()
    {
        $this->info('Analyzing image...');

        $response = Prism::text()
            ->using(Provider::OpenRouter, 'google/gemini-2.0-flash-lite-001')
            ->withMessages([
                new UserMessage(
                    'What car is in this image? Extract: brand, model, color, condition.',
                    additionalContent: [
                        Image::fromUrl('https://images.unsplash.com/photo-1555215695-3004980ad54e?w=800')
                    ]
                )
            ])
            ->asText();

        $this->info($response->text);
    }
}
