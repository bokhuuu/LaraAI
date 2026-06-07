<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ab_prompt_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ab_prompt_test_id')->constrained()->cascadeOnDelete();
            $table->string('session_id')->index();
            $table->string('variant');
            $table->string('prompt_key');
            $table->tinyInteger('vote')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ab_prompt_results');
    }
};
