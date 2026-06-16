<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('card_scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device_type', 20)->nullable(); // mobile, tablet, desktop
            $table->string('os', 60)->nullable();
            $table->string('browser', 60)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('referrer', 500)->nullable();
            $table->timestamps();

            $table->index('employee_id');
            $table->index('created_at');
            $table->index('device_type');
            $table->index('city');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_scans');
    }
};
