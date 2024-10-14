<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', static function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('mobile')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('profile')->nullable();
            $table->string('type')->comment('email/google/mobile');
            $table->string('password');
            $table->string('fcm_id');
            $table->boolean('notification')->default(0);
            $table->string('firebase_id')->nullable();
            $table->text('address')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['firebase_id', 'type']);
        });
        Schema::create('notifications', static function (Blueprint $table) {
            $table->id();
            $table->text('title');
            $table->text('message');
            $table->text('image');
            $table->enum('send_to', ['all', 'selected']);
            $table->string('user_id', 512)->nullable();
            $table->timestamps();
        });
        Schema::create('onboardings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->boolean('status')->default(false);
            $table->timestamps();
        });
        Schema::create('draweritems', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('image')->nullable();
            $table->string('url')->nullable();
            $table->boolean('status')->default(false);
            $table->timestamps();
        });
        Schema::create('fcms', function (Blueprint $table) {
            $table->id();
            $table->string('fcm')->unique();

            $table->timestamps();
        });
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('value')->nullable();
            $table->enum('type', ['string', 'file'])->default('string');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('onboardings');
        Schema::dropIfExists('draweritems');
        Schema::dropIfExists('fcms');
        Schema::dropIfExists('settings');
    }
};
