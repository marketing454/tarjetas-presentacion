<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->string('photo')->nullable()->after('slug');
            $table->unsignedTinyInteger('photo_position_x')->default(50)->after('photo');
            $table->unsignedTinyInteger('photo_position_y')->default(50)->after('photo_position_x');
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn(['photo', 'photo_position_x', 'photo_position_y']);
        });
    }
};
