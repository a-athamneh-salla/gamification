<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGamificationTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Tasks Table
        Schema::create('gamification_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('points')->default(0);
            $table->string('icon')->nullable();
            $table->string('event_name');
            $table->json('event_payload_conditions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Missions Table
        Schema::create('gamification_missions', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->integer('total_points')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Mission Tasks (Pivot Table)
        Schema::create('gamification_mission_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mission_id')->constrained('gamification_missions')->cascadeOnDelete();
            $table->foreignId('task_id')->constrained('gamification_tasks')->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            // Ensure unique combinations
            $table->unique(['mission_id', 'task_id']);
        });

        // Rules Table
        Schema::create('gamification_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mission_id')->constrained('gamification_missions')->cascadeOnDelete();
            $table->string('rule_type'); // 'start', 'finish'
            $table->string('condition_type'); // 'mission_completion', 'tasks_completion', 'date_range', 'custom'
            $table->json('condition_payload')->nullable();
            $table->timestamps();
        });

        // Rewards Table
        Schema::create('gamification_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mission_id')->constrained('gamification_missions')->cascadeOnDelete();
            $table->string('reward_type'); // 'points', 'badge', 'coupon', 'feature_unlock'
            $table->string('reward_value');
            $table->json('reward_meta')->nullable();
            $table->timestamps();
        });

        // Badges Table
        Schema::create('gamification_badges', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Store Progress Table
        Schema::create('gamification_store_progress', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->foreignId('mission_id')->constrained('gamification_missions')->cascadeOnDelete();
            $table->string('status')->default('not_started'); // 'not_started', 'in_progress', 'completed', 'ignored'
            $table->decimal('progress_percentage', 5, 2)->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Ensure unique combinations
            $table->unique(['store_id', 'mission_id']);
        });

        // Task Completion Table
        Schema::create('gamification_task_completion', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->foreignId('task_id')->constrained('gamification_tasks')->cascadeOnDelete();
            $table->foreignId('mission_id')->constrained('gamification_missions')->cascadeOnDelete();
            $table->string('status')->default('not_started'); // 'not_started', 'completed', 'ignored'
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Ensure unique combinations
            $table->unique(['store_id', 'task_id', 'mission_id']);
        });

        // Store Badges Table
        Schema::create('gamification_store_badges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->foreignId('badge_id')->constrained('gamification_badges')->cascadeOnDelete();
            $table->timestamp('earned_at')->useCurrent();
            $table->timestamps();

            // Ensure unique combinations
            $table->unique(['store_id', 'badge_id']);
        });

        // Lockers Table
        Schema::create('gamification_lockers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mission_id')->constrained('gamification_missions')->cascadeOnDelete();
            $table->string('condition_type'); // 'mission_completion', 'date', 'tasks_completion', 'custom'
            $table->json('condition_payload')->nullable();
            $table->timestamps();
        });

        // Events Log Table
        Schema::create('gamification_events_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->string('event_name');
            $table->json('event_payload')->nullable();
            $table->boolean('processed')->default(false);
            $table->timestamp('processed_at')->nullable();
            $table->json('result')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            // Add index for efficient querying
            $table->index(['store_id', 'event_name', 'processed']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gamification_events_log');
        Schema::dropIfExists('gamification_lockers');
        Schema::dropIfExists('gamification_store_badges');
        Schema::dropIfExists('gamification_task_completion');
        Schema::dropIfExists('gamification_store_progress');
        Schema::dropIfExists('gamification_badges');
        Schema::dropIfExists('gamification_rewards');
        Schema::dropIfExists('gamification_rules');
        Schema::dropIfExists('gamification_mission_tasks');
        Schema::dropIfExists('gamification_missions');
        Schema::dropIfExists('gamification_tasks');
    }
}