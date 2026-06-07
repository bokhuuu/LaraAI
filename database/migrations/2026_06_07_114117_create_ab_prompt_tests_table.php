<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ab_prompt_tests', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('prompt_key_a');
            $table->string('prompt_key_b');
            $table->unsignedTinyInteger('traffic_split')->default(50);
            $table->boolean('is_active')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ab_prompt_tests');
    }
};
