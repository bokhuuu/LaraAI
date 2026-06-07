<?php

namespace Database\Seeders;

use App\AI\Services\EmbeddingService;
use Illuminate\Database\Seeder;

/**
 * Indexes 55 realistic car listings as embedding vectors for RAG demonstration.
 * Calls EmbeddingService::generateAndStore() for each listing - converts text
 * to a vector and saves it to the documents table so searchListings() can find
 * relevant cars by meaning, not just keywords.
 *
 * TEMPLATE: Delete this file and create your own domain seeder.
 * The pattern stays identical - only the content changes.
 */
class CarListingsSeeder extends Seeder
{
    public function run(): void
    {
        $service = app(EmbeddingService::class);

        foreach ($this->listings() as $listing) {
            $service->generateAndStore($listing);
            $this->command->info('Indexed: ' . substr($listing, 0, 60) . '...');
        }

        $this->command->info('Done. ' . count($this->listings()) . ' listings indexed.');
    }

    /**
     * Rich text ensures semantic search returns relevant results.
     * TEMPLATE: Replace with your domain content.
     */
    private function listings(): array
    {
        return [
            // Budget cars (under $12k)
            'Toyota Corolla 2017, reliable compact sedan, 85000 km, petrol engine, excellent fuel economy, great first car or daily commuter, well maintained service history, price $7500',
            'Hyundai i30 2016, compact hatchback, 92000 km, petrol, affordable and practical city car, low running costs, good condition, price $6800',
            'Skoda Octavia 2016, spacious family sedan, 110000 km, diesel engine, exceptional fuel economy on motorway, very practical boot space, price $9500',
            'Volkswagen Polo 2018, small city hatchback, 55000 km, petrol, easy to park, low insurance, perfect for young drivers, price $10500',
            'Opel Astra 2015, comfortable hatchback, 125000 km, diesel, reliable workhorse, recently serviced, budget family car, price $7000',
            'Honda Jazz 2019, small practical hatchback, 40000 km, petrol hybrid, excellent city fuel economy, magic seat folds flat for cargo, price $11500',
            'Toyota Camry 2016, reliable midsize sedan, 95000 km, petrol, known for longevity, smooth highway cruiser, zero rust, price $8000',
            'Nissan Micra 2018, tiny city car, 38000 km, petrol, extremely cheap to run, easy parking, ideal for urban driving, price $8500',

            // Mid range sedans and hatchbacks ($12k-$25k)
            'Honda Civic 2022, sporty compact sedan, 18000 km, petrol, low mileage near new condition, excellent fuel economy, Apple CarPlay, price $19500',
            'Volkswagen Golf 2019, premium compact hatchback, 48000 km, petrol, sporty handling, manual transmission, heated seats, price $14000',
            'Seat Leon 2020, sporty hatchback, 35000 km, petrol, fun to drive, modern infotainment, good safety rating, price $15500',
            'Mazda 3 2021, sleek compact sedan, 28000 km, petrol, stunning interior quality, excellent driving dynamics, price $18000',
            'Kia Stinger 2020, sporty fastback sedan, 42000 km, petrol turbo, rear wheel drive, powerful acceleration, looks like a sports car, price $24000',
            'Subaru Impreza 2019, all wheel drive compact, 52000 km, petrol, AWD grip in all weather, reliable Japanese engineering, price $16000',
            'Volkswagen Passat 2018, spacious family sedan, 68000 km, diesel, executive feel at mid price, large boot, comfortable long distance, price $17500',
            'Peugeot 3008 2020, stylish mid size SUV, 39000 km, diesel, award winning interior design, great driving position, price $22000',

            // Family SUVs ($15k-$35k)
            'Hyundai Tucson 2020, family mid size SUV, 45000 km, petrol, spacious interior for families, large boot, 5 comfortable seats, smooth ride, price $21000',
            'Nissan Qashqai 2021, popular compact SUV, 31000 km, petrol, perfect family car, roof rails, parking sensors, price $23000',
            'Honda CR-V 2020, practical family SUV, 44000 km, petrol hybrid, hybrid efficiency with SUV practicality, 5 seats, great safety score, price $27000',
            'Mazda CX-5 2021, refined mid size SUV, 29000 km, diesel, premium feel, quiet cabin, excellent build quality, AWD available, price $29000',
            'Renault Kadjar 2019, affordable family SUV, 58000 km, diesel, spacious for the price, good motorway cruiser, hands free parking, price $16500',
            'Volkswagen Tiguan 2020, well built family SUV, 37000 km, petrol, solid German engineering, panoramic sunroof, lane assist, price $28000',
            'Kia Sportage 2021, dependable family SUV, 26000 km, diesel, 7 year warranty, modern tech, competitive price for quality, price $25000',
            'Ford Kuga 2020, versatile family SUV, 48000 km, petrol PHEV plug in hybrid, electric range for commuting, full SUV capability, price $26000',

            // 7 seat SUVs
            'Toyota Land Cruiser 2018, large 7 seat SUV, 72000 km, diesel, legendary off road capability, bulletproof reliability, third row seats, price $45000',
            'Kia Sorento 2021, 7 seat family SUV, 33000 km, diesel, three rows of seats, ideal for large families, smooth diesel engine, price $32000',
            'Audi Q7 2019, luxury 7 seat SUV, 58000 km, diesel, premium interior, adaptive air suspension, third row seats, all wheel drive, price $52000',
            'Volvo XC90 2020, premium 7 seat SUV, 41000 km, petrol hybrid, Scandinavian safety focus, stunning interior, plug in hybrid range, price $58000',
            'Skoda Kodiaq 2020, practical 7 seat SUV, 44000 km, diesel, best value 7 seater on market, large boot even with third row up, price $27000',
            'Ford Explorer 2019, large American 7 seat SUV, 65000 km, petrol, commanding road presence, towing capability, entertainment system, price $34000',
            'Chevrolet Tahoe 2018, full size 7 seat American SUV, 88000 km, petrol V8, massive interior, powerful towing, genuine American truck SUV, price $38000',

            // Diesel cars
            'BMW 3 Series 2019, executive diesel sedan, 61000 km, 2.0L diesel, exceptional motorway fuel economy, sporty handling, heated leather seats, price $31000',
            'BMW X5 2019, luxury diesel SUV, 52000 km, 3.0L diesel, powerful and efficient, panoramic roof, 7 speed automatic, excellent condition, price $47000',
            'Mercedes E-Class 2018, premium diesel executive sedan, 74000 km, diesel, air suspension, massage seats, semi autonomous driving, price $38000',
            'Volkswagen Arteon 2020, elegant diesel fastback, 43000 km, 2.0 diesel, sophisticated design, spacious rear, digital cockpit, price $33000',
            'Audi A6 2019, executive diesel saloon, 66000 km, diesel, quattro AWD, virtual cockpit, adaptive cruise control, price $41000',
            'Toyota Hilux 2020, indestructible diesel pickup truck, 78000 km, diesel 4x4, legendary reliability, towing 3500 kg, double cab, price $36000',

            // Luxury cars
            'Mercedes C200 2021, compact luxury sedan, 22000 km, petrol, leather seats, sunroof, MBUX infotainment, prestige brand, price $42000',
            'BMW 5 Series 2020, executive luxury sedan, 38000 km, petrol, long wheelbase comfort, business class cabin, heads up display, price $46000',
            'Audi A4 2021, premium all wheel drive sedan, 19000 km, petrol, quattro AWD, virtual cockpit, matrix LED headlights, price $44000',
            'BMW 7 Series 2019, flagship luxury sedan, 49000 km, petrol, rear executive lounge, massage seats, ambient lighting, pinnacle of comfort, price $72000',
            'Mercedes S-Class 2018, ultimate luxury limousine, 55000 km, petrol V8, autonomous driving, air suspension, price $85000',
            'Lexus ES 2021, refined luxury sedan, 27000 km, petrol hybrid, whisper quiet cabin, exceptional build quality, Japanese luxury, price $49000',
            'Range Rover Sport 2020, premium luxury SUV, 44000 km, diesel, off road capability with luxury interior, air suspension, meridian audio, price $68000',
            'Porsche Cayenne 2019, sports luxury SUV, 51000 km, petrol, Porsche driving dynamics in an SUV, panoramic roof, price $74000',

            // Sports and performance
            'Ford Mustang 2019, iconic American muscle car, 38000 km, V8 petrol, 450 horsepower, rear wheel drive, throaty exhaust, price $34000',
            'BMW M3 2020, high performance sports sedan, 29000 km, petrol turbo, track ready, carbon fibre package, limited slip diff, price $68000',
            'Porsche 911 2018, legendary sports car, 44000 km, flat six petrol, rear engine rear wheel drive, timeless design, price $92000',
            'Chevrolet Camaro 2019, American performance coupe, 41000 km, V8, aggressive styling, loud exhaust, weekend fun car, price $31000',
            'Audi TT 2020, premium sports coupe, 24000 km, petrol turbo, quattro AWD grip, stunning interior, compact and fun, price $38000',

            // Electric and hybrid
            'Tesla Model 3 2021, electric sedan, 35000 km, long range battery, 500 km range, autopilot, over the air updates, supercharger network, price $42000',
            'Tesla Model Y 2022, electric SUV, 18000 km, long range, 505 km range, spacious interior, dog mode, minimalist design, price $52000',
            'Nissan Leaf 2020, affordable electric hatchback, 42000 km, 270 km range, low running costs, perfect city electric car, price $18000',
            'Hyundai Kona Electric 2021, compact electric SUV, 31000 km, 450 km range, practical size, fast charging, excellent warranty, price $28000',
            'Toyota Prius 2020, hybrid pioneer, 48000 km, petrol hybrid, legendary fuel economy, 4.5L per 100 km, reliable hybrid system, price $22000',
            'Toyota RAV4 2021, popular hybrid SUV, 27000 km, petrol hybrid, self charging hybrid, no plug needed, family practical, price $34000',
            'Mitsubishi Outlander 2020, plug in hybrid SUV, 36000 km, PHEV, electric range for daily commute, full petrol engine for long trips, 7 seats, price $29000',

            // Pickup trucks
            'Ford F150 2021, full size American pickup, 44000 km, petrol V6, maximum tow rating 5900 kg, crew cab 5 seats, work or lifestyle truck, price $42000',
            'Dodge Ram 1500 2020, premium pickup truck, 52000 km, petrol Hemi V8, air suspension, massive interior, coil spring rear, comfortable daily driver, price $46000',
        ];
    }
}
