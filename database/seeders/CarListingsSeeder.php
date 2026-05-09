<?php

namespace Database\Seeders;

use App\AI\Services\EmbeddingService;
use Illuminate\Database\Seeder;

/**
 * CarListingsSeeder
 *
 * Seeds example car listings as embedding documents for RAG demonstration.
 * TEMPLATE USAGE: Replace with your domain content.
 * Run: php artisan db:seed --class=CarListingsSeeder
 */
class CarListingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $service = new EmbeddingService();

        $listings = [
            'BMW X5 2019, luxury SUV, 45000 km, excellent condition, price $35000',
            'Toyota Camry 2020, reliable sedan, fuel efficient, 30000 km, price $8000',
            'Mercedes C200 2018, luxury sedan, leather seats, sunroof, price $42000',
            'Ford F150 2021, powerful pickup truck, towing package, price $28000',
            'Honda Civic 2022, compact sedan, low mileage, great fuel economy, price $12000',
            'Hyundai Tucson 2020, family SUV, spacious interior, price $15000',
            'Volkswagen Golf 2019, compact hatchback, sporty, manual transmission, price $11000',
            'Audi A4 2021, premium sedan, all wheel drive, price $38000',
        ];

        foreach ($listings as $listing) {
            $service->generateAndStore($listing);
            $this->command->info('Stored: ' . $listing);
        }
    }
}
