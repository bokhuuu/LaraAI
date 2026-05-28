<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add performance indexes to high-traffic tables.
 * Speeds up common query patterns: filtering by feature/provider,
 * loading messages by conversation, looking up active prompts.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_usage_logs', function (Blueprint $table) {
            $table->index('feature');
            $table->index('provider');
            $table->index('created_at');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->index('conversation_id');
        });

        Schema::table('prompt_versions', function (Blueprint $table) {
            $table->index('key');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('ai_usage_logs', function (Blueprint $table) {
            $table->dropIndex(['feature']);
            $table->dropIndex(['provider']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['conversation_id']);
        });

        Schema::table('prompt_versions', function (Blueprint $table) {
            $table->dropIndex(['key']);
            $table->dropIndex(['is_active']);
        });
    }
};
