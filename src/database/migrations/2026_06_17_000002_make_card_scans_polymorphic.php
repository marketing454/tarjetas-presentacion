<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('card_scans_new', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device_type', 20)->nullable();
            $table->string('os', 60)->nullable();
            $table->string('browser', 60)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('referrer', 500)->nullable();
            $table->timestamps();

            $table->index('employee_id');
            $table->index('branch_id');
            $table->index('created_at');
            $table->index('device_type');
            $table->index('city');
        });

        DB::statement('
            INSERT INTO card_scans_new
                (id, employee_id, branch_id, ip_address, user_agent, device_type, os, browser, country, city, referrer, created_at, updated_at)
            SELECT
                id, employee_id, NULL, ip_address, user_agent, device_type, os, browser, country, city, referrer, created_at, updated_at
            FROM card_scans
        ');

        Schema::drop('card_scans');
        Schema::rename('card_scans_new', 'card_scans');
    }

    public function down(): void
    {
        Schema::create('card_scans_old', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device_type', 20)->nullable();
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

        DB::statement('
            INSERT INTO card_scans_old
                (id, employee_id, ip_address, user_agent, device_type, os, browser, country, city, referrer, created_at, updated_at)
            SELECT
                id, employee_id, ip_address, user_agent, device_type, os, browser, country, city, referrer, created_at, updated_at
            FROM card_scans
            WHERE employee_id IS NOT NULL
        ');

        Schema::drop('card_scans');
        Schema::rename('card_scans_old', 'card_scans');
    }
};
