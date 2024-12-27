<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('full_name')->nullable();
            $table->string('phone_number')->unique();
            $table->string('verification_code')->nullable();
            $table->timestamp('verification_code_expiry')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->string('image')->nullable();
            $table->enum('lang', ['en', 'ar'])->default('en');
            $table->enum('role', ['user', 'admin', 'store_manager'])->default('user');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->enum('theme_mode', ['light', 'dark'])->default('light');
            $table->boolean('allow_gps')->default(false); 
            $table->boolean('allow_notifications')->default(false);
            $table->timestamps();
            $table->softDeletes(); // Add soft deletes
            $table->index('phone_number'); 
            $table->index('role'); 
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};
